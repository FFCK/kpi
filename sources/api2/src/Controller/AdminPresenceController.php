<?php

namespace App\Controller;

use App\Entity\User;
use App\Trait\AdminLoggableTrait;
use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin Presence Controller
 *
 * Unified management of team and match player compositions
 * (kp_competition_equipe_joueur, kp_match_joueur tables)
 * Migrated from GestionEquipeJoueur.php and GestionMatchEquipeJoueur.php
 */
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '27. App4 - Presence')]
class AdminPresenceController extends AbstractController
{
    use AdminLoggableTrait;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    // ============================================
    // TEAM MODE - Team Composition Management
    // ============================================

    /**
     * Get team players composition
     */
    #[Route('/admin/teams/{teamId}/players', name: 'admin_team_players_list', methods: ['GET'])]
    #[OA\Parameter(name: 'teamId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    public function getTeamPlayers(int $teamId): JsonResponse
    {
        // Get team info
        $sql = "SELECT ce.Id, ce.Libelle, ce.Numero, ce.Code_compet, ce.Code_saison,
                       ce.Code_club, ce.Poule, ce.Tirage, ce.logo,
                       c.Libelle AS club_libelle,
                       comp.Code, comp.Libelle AS comp_libelle, comp.Verrou,
                       comp.Code_niveau, comp.Statut
                FROM kp_competition_equipe ce
                LEFT JOIN kp_club c ON ce.Code_club = c.Code
                LEFT JOIN kp_competition comp ON ce.Code_compet = comp.Code
                    AND ce.Code_saison = comp.Code_saison
                WHERE ce.Id = ?";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$teamId]);
        $teamRow = $result->fetchAssociative();

        if (!$teamRow) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        // Check user access
        /** @var User|null $user */
        $user = $this->getUser();
        $allowedCompetitions = $user?->getAllowedCompetitions();

        if ($allowedCompetitions !== null && !in_array($teamRow['Code_compet'], $allowedCompetitions)) {
            return $this->json(['message' => 'Access denied to this competition'], Response::HTTP_FORBIDDEN);
        }

        // Get players with full info (license, pagaie, certificates, surclassement)
        $sql = "SELECT cej.Matric, cej.Nom, cej.Prenom, cej.Sexe, cej.Categ,
                       cej.Numero, cej.Capitaine,
                       lc.Naissance, lc.Origine, lc.Numero_club, lc.Club,
                       lc.Pagaie_ECA, lc.Pagaie_EVI, lc.Pagaie_MER,
                       lc.Etat_certificat_CK, lc.Date_certificat_CK,
                       lc.Etat_certificat_APS, lc.Date_certificat_APS,
                       arb.arbitre, arb.niveau,
                       s.Date AS date_surclassement,
                       lc.Reserve AS icf
                FROM kp_competition_equipe_joueur cej
                LEFT JOIN kp_licence lc ON cej.Matric = lc.Matric
                LEFT JOIN kp_arbitre arb ON cej.Matric = arb.Matric
                    AND arb.saison = ?
                LEFT JOIN kp_surclassement s ON cej.Matric = s.Matric
                    AND s.Saison = ?
                WHERE cej.Id_equipe = ?
                ORDER BY
                    FIELD(IF(cej.Capitaine='C', '-', cej.Capitaine), '-', 'E', 'A', 'X'),
                    cej.Numero,
                    cej.Nom,
                    cej.Prenom";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([
            $teamRow['Code_saison'],
            $teamRow['Code_saison'],
            $teamId
        ]);
        $playerRows = $result->fetchAllAssociative();

        $players = array_map(function ($row) {
            return $this->formatPlayer($row);
        }, $playerRows);

        // Get last update from journal
        $lastUpdate = $this->getLastUpdate('kp_competition_equipe_joueur', $teamId);

        return $this->json([
            'team' => [
                'id' => (int) $teamRow['Id'],
                'libelle' => $teamRow['Libelle'],
                'numero' => (int) $teamRow['Numero'],
                'codeCompet' => $teamRow['Code_compet'],
                'codeSaison' => $teamRow['Code_saison'],
                'codeClub' => $teamRow['Code_club'],
                'clubLibelle' => $teamRow['club_libelle'] ?? '',
                'poule' => $teamRow['Poule'] ?? '',
                'tirage' => (int) ($teamRow['Tirage'] ?? 0),
                'logo' => $teamRow['logo'] ?: null
            ],
            'competition' => [
                'code' => $teamRow['Code_compet'],
                'libelle' => $teamRow['comp_libelle'] ?? '',
                'verrou' => $teamRow['Verrou'] === 'O',
                'codeNiveau' => $teamRow['Code_niveau'] ?? '',
                'statut' => $teamRow['Statut'] ?? ''
            ],
            'players' => $players,
            'lastUpdate' => $lastUpdate
        ]);
    }

    /**
     * Add player to team composition
     */
    #[Route('/admin/teams/{teamId}/players/add', name: 'admin_team_players_add', methods: ['POST'])]
    public function addTeamPlayer(int $teamId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Get team and competition info
        $teamInfo = $this->getTeamInfo($teamId);
        if (!$teamInfo) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        // Check lock status
        if ($teamInfo['verrou']) {
            return $this->json(['message' => 'Competition is locked'], Response::HTTP_FORBIDDEN);
        }

        // Check user profile
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 8) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $mode = $data['mode'] ?? 'existing';
        $matric = null;

        if ($mode === 'create') {
            // Create new non-licensed player (profile <= 4 required)
            if ($user->getNiveau() > 4) {
                return $this->json(['message' => 'Profile <= 4 required to create players'], Response::HTTP_FORBIDDEN);
            }

            // Generate new matric >= 2000000
            $sql = "SELECT MAX(Matric) as max_matric FROM kp_licence WHERE Matric >= 2000000";
            $result = $this->connection->executeQuery($sql);
            $row = $result->fetchAssociative();
            $matric = max(2000000, ($row['max_matric'] ?? 1999999) + 1);

            // Insert into kp_licence
            $this->connection->insert('kp_licence', [
                'Matric' => $matric,
                'Origine' => $teamInfo['code_saison'],
                'Nom' => $data['nom'] ?? '',
                'Prenom' => $data['prenom'] ?? '',
                'Sexe' => $data['sexe'] ?? 'M',
                'Naissance' => $data['naissance'] ?? null,
                'Club' => $teamInfo['club_libelle'],
                'Numero_club' => $teamInfo['code_club'],
                'Etat' => 'Actif',
                'Pagaie_ECA' => '',
                'Pagaie_EVI' => '',
                'Pagaie_MER' => '',
                'Etat_certificat_CK' => 'NON',
                'Etat_certificat_APS' => 'NON',
                'Reserve' => $data['numicf'] ?? null
            ]);

            // Insert referee info if provided
            if (!empty($data['arbitre'])) {
                $this->connection->insert('kp_arbitre', [
                    'Matric' => $matric,
                    'saison' => $teamInfo['code_saison'],
                    'arbitre' => $data['arbitre'],
                    'niveau' => $data['niveau'] ?? '',
                    'regional' => in_array($data['arbitre'], ['REG', 'IR', 'NAT', 'INT', 'OTM', 'JO']) ? 'O' : null,
                    'interregional' => in_array($data['arbitre'], ['IR', 'NAT', 'INT', 'OTM', 'JO']) ? 'O' : null,
                    'national' => in_array($data['arbitre'], ['NAT', 'INT', 'OTM', 'JO']) ? 'O' : null,
                    'international' => in_array($data['arbitre'], ['INT', 'OTM', 'JO']) ? 'O' : null
                ]);
            }
        } else {
            // Use existing player
            $matric = $data['matric'] ?? null;
            if (!$matric) {
                return $this->json(['message' => 'Matric is required'], Response::HTTP_BAD_REQUEST);
            }

            // Validate player for national competitions
            if ($this->isNationalCompetition($teamInfo['code_compet'])) {
                $validationErrors = $this->validatePlayerForNational($matric, $teamInfo);
                if (!empty($validationErrors)) {
                    return $this->json([
                        'message' => 'Player not valid for national competition',
                        'errors' => $validationErrors
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // Get player info from kp_licence
        $sql = "SELECT Nom, Prenom, Sexe, Naissance FROM kp_licence WHERE Matric = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$matric]);
        $licenceRow = $result->fetchAssociative();

        if (!$licenceRow) {
            return $this->json(['message' => 'Player not found in license database'], Response::HTTP_NOT_FOUND);
        }

        // Calculate category
        $categ = $this->calculateCategory($licenceRow['Naissance'], $teamInfo['code_saison']);

        // Insert into kp_competition_equipe_joueur
        try {
            $this->connection->insert('kp_competition_equipe_joueur', [
                'Id_equipe' => $teamId,
                'Matric' => $matric,
                'Nom' => $licenceRow['Nom'],
                'Prenom' => $licenceRow['Prenom'],
                'Sexe' => $licenceRow['Sexe'],
                'Categ' => $categ,
                'Numero' => $data['numero'] ?? 0,
                'Capitaine' => $data['capitaine'] ?? '-'
            ]);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) {
                return $this->json(['message' => 'Player already in composition'], Response::HTTP_CONFLICT);
            }
            throw $e;
        }

        // Log action
        $this->logActionForSeason(
            'Ajout titulaire',
            $teamInfo['code_saison'],
            "{$teamInfo['code_compet']}: Equipe {$teamId} - Joueur {$matric}"
        );

        return $this->json([
            'success' => true,
            'matric' => $matric
        ], Response::HTTP_CREATED);
    }

    /**
     * Update player inline (numero or capitaine)
     */
    #[Route('/admin/teams/{teamId}/players/{matric}', name: 'admin_team_players_update', methods: ['PATCH'])]
    public function updateTeamPlayer(int $teamId, int $matric, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Get team info and check lock
        $teamInfo = $this->getTeamInfo($teamId);
        if (!$teamInfo) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        if ($teamInfo['verrou']) {
            return $this->json(['message' => 'Competition is locked'], Response::HTTP_FORBIDDEN);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 8) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $updateData = [];
        if (isset($data['numero'])) {
            $updateData['Numero'] = (int) $data['numero'];
        }
        if (isset($data['capitaine'])) {
            $updateData['Capitaine'] = $data['capitaine'];
        }

        if (empty($updateData)) {
            return $this->json(['message' => 'No fields to update'], Response::HTTP_BAD_REQUEST);
        }

        $this->connection->update(
            'kp_competition_equipe_joueur',
            $updateData,
            ['Id_equipe' => $teamId, 'Matric' => $matric]
        );

        // Log action
        $field = array_key_first($updateData);
        $value = $updateData[$field];
        $this->logActionForSeason(
            'Modification kp_competition_equipe_joueur',
            $teamInfo['code_saison'],
            "{$teamInfo['code_compet']}: Equipe {$teamId} - {$field}={$value}"
        );

        return $this->json(['success' => true]);
    }

    /**
     * Delete players from team composition
     */
    #[Route('/admin/teams/{teamId}/players', name: 'admin_team_players_delete', methods: ['DELETE'])]
    public function deleteTeamPlayers(int $teamId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $matricIds = $data['matricIds'] ?? [];

        if (empty($matricIds)) {
            return $this->json(['message' => 'No players to delete'], Response::HTTP_BAD_REQUEST);
        }

        // Get team info and check lock
        $teamInfo = $this->getTeamInfo($teamId);
        if (!$teamInfo) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        if ($teamInfo['verrou']) {
            return $this->json(['message' => 'Competition is locked'], Response::HTTP_FORBIDDEN);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 8) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $placeholders = implode(',', array_fill(0, count($matricIds), '?'));
        $sql = "DELETE FROM kp_competition_equipe_joueur
                WHERE Id_equipe = ? AND Matric IN ($placeholders)";

        $params = array_merge([$teamId], $matricIds);
        $this->connection->executeStatement($sql, $params);

        // Log action
        $matricList = implode(',', $matricIds);
        $this->logActionForSeason(
            'Suppression titulaire',
            $teamInfo['code_saison'],
            "{$teamInfo['code_compet']}: Equipe {$teamId} - Joueurs {$matricList}"
        );

        return $this->json([
            'success' => true,
            'deleted' => count($matricIds)
        ]);
    }

    // ============================================
    // PLAYER SEARCH & COMPOSITIONS
    // ============================================

    /**
     * Search players by name or matric (for adding to team composition)
     */
    #[Route('/admin/players/search', name: 'admin_players_search', methods: ['GET'])]
    #[OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string'))]
    public function searchPlayers(Request $request): JsonResponse
    {
        $query = trim($request->query->get('q', ''));
        $limit = min((int) $request->query->get('limit', 20), 50);

        if (strlen($query) < 2) {
            return $this->json(['players' => []]);
        }

        // Search by matric (exact), ICF/Reserve (exact) or by name (LIKE)
        if (is_numeric($query)) {
            $sql = "SELECT Matric, Nom, Prenom, Sexe, Naissance, Numero_club, Club,
                           Pagaie_ECA, Pagaie_EVI, Pagaie_MER,
                           Etat_certificat_CK
                    FROM kp_licence
                    WHERE Matric = ? OR Reserve = ?
                    LIMIT " . (int) $limit;
            $params = [(int) $query, $query];
        } else {
            $sql = "SELECT Matric, Nom, Prenom, Sexe, Naissance, Numero_club, Club,
                           Pagaie_ECA, Pagaie_EVI, Pagaie_MER,
                           Etat_certificat_CK
                    FROM kp_licence
                    WHERE (Nom LIKE ? OR Prenom LIKE ? OR CONCAT(Nom, ' ', Prenom) LIKE ?)
                    AND Etat = 'Actif'
                    ORDER BY Nom, Prenom
                    LIMIT " . (int) $limit;
            $like = "%{$query}%";
            $params = [$like, $like, $like];
        }

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $rows = $result->fetchAllAssociative();

        $players = array_map(function ($row) {
            $pagaieLabel = '';
            $pagaieValide = 0;
            if (!empty($row['Pagaie_ECA']) && !in_array($row['Pagaie_ECA'], ['', 'PAGJ', 'PAGB'])) {
                $pagaieValide = 1;
                $pagaieLabel = $this->getPagaieLabel($row['Pagaie_ECA']);
            } elseif (!empty($row['Pagaie_EVI'])) {
                $pagaieValide = 2;
                $pagaieLabel = $this->getPagaieLabel($row['Pagaie_EVI']);
            } elseif (!empty($row['Pagaie_MER'])) {
                $pagaieValide = 3;
                $pagaieLabel = $this->getPagaieLabel($row['Pagaie_MER']);
            }

            return [
                'matric' => (int) $row['Matric'],
                'nom' => $row['Nom'] ?? '',
                'prenom' => $row['Prenom'] ?? '',
                'sexe' => $row['Sexe'] ?? 'M',
                'categ' => $row['Naissance'] ? $this->calculateCategory($row['Naissance'], date('Y')) : '',
                'numeroClub' => $row['Numero_club'] ?? '',
                'clubLibelle' => $row['Club'] ?? '',
                'pagaieLabel' => $pagaieLabel,
                'pagaieValide' => $pagaieValide,
                'certifCK' => $row['Etat_certificat_CK'] ?? 'NON',
            ];
        }, $rows);

        return $this->json(['players' => $players]);
    }

    /**
     * Get available compositions for copy (other competitions where this team's club has players)
     */
    #[Route('/admin/teams/{teamId}/compositions', name: 'admin_team_compositions', methods: ['GET'])]
    public function getAvailableCompositions(int $teamId, Request $request): JsonResponse
    {
        $teamInfo = $this->getTeamInfo($teamId);
        if (!$teamInfo) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $season = $request->query->get('season', $teamInfo['code_saison']);

        // Find other teams of the same club and same Numero in the same season (different competition)
        $sql = "SELECT ce.Id, ce.Code_compet, comp.Libelle AS comp_libelle,
                       (SELECT COUNT(*) FROM kp_competition_equipe_joueur cej WHERE cej.Id_equipe = ce.Id) AS player_count
                FROM kp_competition_equipe ce
                LEFT JOIN kp_competition comp ON ce.Code_compet = comp.Code AND ce.Code_saison = comp.Code_saison
                WHERE ce.Code_club = ? AND ce.Code_saison = ? AND ce.Numero = ? AND ce.Id != ?
                HAVING player_count > 0
                ORDER BY ce.Code_compet";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$teamInfo['code_club'], $season, $teamInfo['numero'], $teamId]);
        $rows = $result->fetchAllAssociative();

        $compositions = array_map(function ($row) use ($season) {
            return [
                'competitionCode' => $row['Code_compet'],
                'competitionLibelle' => $row['comp_libelle'] ?? '',
                'season' => $season,
                'teamId' => (int) $row['Id'],
                'playerCount' => (int) $row['player_count'],
            ];
        }, $rows);

        return $this->json(['compositions' => $compositions]);
    }

    /**
     * Copy composition from another team (same club, different competition)
     */
    #[Route('/admin/teams/{teamId}/players/copy', name: 'admin_team_players_copy', methods: ['POST'])]
    public function copyComposition(int $teamId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $teamInfo = $this->getTeamInfo($teamId);
        if (!$teamInfo) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        if ($teamInfo['verrou']) {
            return $this->json(['message' => 'Competition is locked'], Response::HTTP_FORBIDDEN);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 8) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $sourceCompetition = $data['sourceCompetition'] ?? '';
        $sourceSeason = $data['sourceSeason'] ?? $teamInfo['code_saison'];

        if (empty($sourceCompetition)) {
            return $this->json(['message' => 'Source competition is required'], Response::HTTP_BAD_REQUEST);
        }

        // Find the source team (same club, same Numero, source competition)
        $sql = "SELECT Id FROM kp_competition_equipe
                WHERE Code_club = ? AND Code_compet = ? AND Code_saison = ? AND Numero = ?
                LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$teamInfo['code_club'], $sourceCompetition, $sourceSeason, $teamInfo['numero']]);
        $sourceRow = $result->fetchAssociative();

        if (!$sourceRow) {
            return $this->json(['message' => 'Source team not found'], Response::HTTP_NOT_FOUND);
        }

        $sourceTeamId = (int) $sourceRow['Id'];

        // Get source players
        $sql = "SELECT Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine
                FROM kp_competition_equipe_joueur
                WHERE Id_equipe = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$sourceTeamId]);
        $sourcePlayers = $result->fetchAllAssociative();

        if (empty($sourcePlayers)) {
            return $this->json(['message' => 'Source composition is empty'], Response::HTTP_BAD_REQUEST);
        }

        // Delete existing players from target team (full replacement)
        $this->connection->executeStatement(
            "DELETE FROM kp_competition_equipe_joueur WHERE Id_equipe = ?",
            [$teamId]
        );

        // Insert all source players
        $copied = 0;
        foreach ($sourcePlayers as $player) {
            $this->connection->insert('kp_competition_equipe_joueur', [
                'Id_equipe' => $teamId,
                'Matric' => $player['Matric'],
                'Nom' => $player['Nom'],
                'Prenom' => $player['Prenom'],
                'Sexe' => $player['Sexe'],
                'Categ' => $player['Categ'],
                'Numero' => $player['Numero'],
                'Capitaine' => $player['Capitaine'],
            ]);
            $copied++;
        }

        $this->logActionForSeason(
            'Copie composition',
            $teamInfo['code_saison'],
            "{$teamInfo['code_compet']}: Equipe {$teamId} <- {$sourceCompetition}: {$copied} joueur(s) (remplacement complet)"
        );

        return $this->json([
            'success' => true,
            'copied' => $copied,
            'total' => count($sourcePlayers),
        ]);
    }

    // ============================================
    // MATCH MODE - Match Player Management
    // ============================================

    /**
     * Get match players for a team (A or B)
     */
    #[Route('/admin/matches/{matchId}/players', name: 'admin_match_players_list', methods: ['GET'])]
    #[OA\Parameter(name: 'matchId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'teamCode', in: 'query', required: true, schema: new OA\Schema(type: 'string', enum: ['A', 'B']))]
    public function getMatchPlayers(int $matchId, Request $request): JsonResponse
    {
        $teamCode = $request->query->get('teamCode', 'A');
        if (!in_array($teamCode, ['A', 'B'])) {
            return $this->json(['message' => 'teamCode must be A or B'], Response::HTTP_BAD_REQUEST);
        }

        // Get match info
        $sql = "SELECT m.Id, m.Date_match, m.Heure_match, m.Terrain, m.Numero_ordre,
                       m.Validation, m.Libelle, m.Id_journee,
                       m.Id_equipeA, m.Id_equipeB
                FROM kp_match m
                WHERE m.Id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$matchId]);
        $matchRow = $result->fetchAssociative();

        if (!$matchRow) {
            return $this->json(['message' => 'Match not found'], Response::HTTP_NOT_FOUND);
        }

        // Determine the team ID based on team code
        $teamId = $teamCode === 'A' ? $matchRow['Id_equipeA'] : $matchRow['Id_equipeB'];

        if (!$teamId) {
            return $this->json(['message' => "Team {$teamCode} not assigned to this match"], Response::HTTP_NOT_FOUND);
        }

        // Get team info
        $sql = "SELECT ce.Id, ce.Libelle, ce.Code_compet, ce.Code_saison, ce.Code_club,
                       comp.Libelle AS comp_libelle, comp.Code
                FROM kp_competition_equipe ce
                LEFT JOIN kp_competition comp ON ce.Code_compet = comp.Code
                    AND ce.Code_saison = comp.Code_saison
                WHERE ce.Id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$teamId]);
        $teamRow = $result->fetchAssociative();

        if (!$teamRow) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        // Check user access
        /** @var User|null $user */
        $user = $this->getUser();
        $allowedCompetitions = $user?->getAllowedCompetitions();

        if ($allowedCompetitions !== null && !in_array($teamRow['Code_compet'], $allowedCompetitions)) {
            return $this->json(['message' => 'Access denied to this competition'], Response::HTTP_FORBIDDEN);
        }

        // Get match players for this team
        $sql = "SELECT mj.Matric, mj.Numero, mj.Capitaine,
                       lc.Nom, lc.Prenom, lc.Sexe, lc.Naissance, lc.Origine,
                       lc.Numero_club, lc.Club,
                       lc.Pagaie_ECA, lc.Pagaie_EVI, lc.Pagaie_MER,
                       lc.Etat_certificat_CK, lc.Date_certificat_CK,
                       lc.Etat_certificat_APS, lc.Date_certificat_APS,
                       arb.arbitre, arb.niveau,
                       s.Date AS date_surclassement,
                       lc.Reserve AS icf
                FROM kp_match_joueur mj
                LEFT JOIN kp_licence lc ON mj.Matric = lc.Matric
                LEFT JOIN kp_arbitre arb ON mj.Matric = arb.Matric
                    AND arb.saison = ?
                LEFT JOIN kp_surclassement s ON mj.Matric = s.Matric
                    AND s.Saison = ?
                WHERE mj.Id_match = ?
                AND mj.Equipe = ?
                ORDER BY
                    FIELD(IF(mj.Capitaine='C', '-', IF(mj.Capitaine='', '-', mj.Capitaine)), '-', 'E', 'A', 'X'),
                    mj.Numero,
                    lc.Nom,
                    lc.Prenom";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([
            $teamRow['Code_saison'],
            $teamRow['Code_saison'],
            $matchId,
            $teamCode
        ]);
        $playerRows = $result->fetchAllAssociative();

        $players = array_map(function ($row) {
            return $this->formatMatchPlayer($row);
        }, $playerRows);

        return $this->json([
            'match' => [
                'id' => (int) $matchRow['Id'],
                'idJournee' => (int) $matchRow['Id_journee'],
                'dateMatch' => $matchRow['Date_match'] ?? '',
                'heureMatch' => $matchRow['Heure_match'] ?? '',
                'terrain' => $matchRow['Terrain'] ?? '',
                'numeroOrdre' => (int) ($matchRow['Numero_ordre'] ?? 0),
                'validation' => $matchRow['Validation'] === 'O',
                'libelle' => $matchRow['Libelle'] ?? ''
            ],
            'team' => [
                'id' => (int) $teamRow['Id'],
                'libelle' => $teamRow['Libelle'],
                'codeCompet' => $teamRow['Code_compet'],
                'codeSaison' => $teamRow['Code_saison'],
                'codeClub' => $teamRow['Code_club']
            ],
            'competition' => [
                'code' => $teamRow['Code_compet'],
                'libelle' => $teamRow['comp_libelle'] ?? ''
            ],
            'players' => $players
        ]);
    }

    /**
     * Add player to match
     */
    #[Route('/admin/matches/{matchId}/players/add', name: 'admin_match_players_add', methods: ['POST'])]
    public function addMatchPlayer(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $teamCode = $data['teamCode'] ?? null;
        $matric = $data['matric'] ?? null;

        if (!in_array($teamCode, ['A', 'B'])) {
            return $this->json(['message' => 'teamCode must be A or B'], Response::HTTP_BAD_REQUEST);
        }
        if (!$matric) {
            return $this->json(['message' => 'matric is required'], Response::HTTP_BAD_REQUEST);
        }

        // Check match lock
        $matchInfo = $this->getMatchInfo($matchId);
        if (!$matchInfo) {
            return $this->json(['message' => 'Match not found'], Response::HTTP_NOT_FOUND);
        }
        if ($matchInfo['validation'] === 'O') {
            return $this->json(['message' => 'Match is locked'], Response::HTTP_FORBIDDEN);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 8) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Get player info from kp_licence
        $sql = "SELECT Nom, Prenom, Sexe FROM kp_licence WHERE Matric = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$matric]);
        $licenceRow = $result->fetchAssociative();

        if (!$licenceRow) {
            return $this->json(['message' => 'Player not found in license database'], Response::HTTP_NOT_FOUND);
        }

        $numero = $data['numero'] ?? 0;
        $capitaine = $data['capitaine'] ?? '-';

        try {
            // REPLACE INTO to handle re-adding
            $sql = "REPLACE INTO kp_match_joueur (Id_match, Matric, Numero, Equipe, Capitaine)
                    VALUES (?, ?, ?, ?, ?)";
            $this->connection->executeStatement($sql, [
                $matchId, $matric, $numero, $teamCode, $capitaine
            ]);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Failed to add player: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->logAction('Ajout joueur match', "Match:{$matchId} - Equipe:{$teamCode} - Joueur:{$matric}");

        return $this->json(['success' => true, 'matric' => $matric], Response::HTTP_CREATED);
    }

    /**
     * Initialize match players from team composition
     */
    #[Route('/admin/matches/{matchId}/players/initialize', name: 'admin_match_players_initialize', methods: ['POST'])]
    public function initializeMatchPlayers(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $teamCode = $data['teamCode'] ?? null;

        if (!in_array($teamCode, ['A', 'B'])) {
            return $this->json(['message' => 'teamCode must be A or B'], Response::HTTP_BAD_REQUEST);
        }

        $matchInfo = $this->getMatchInfo($matchId);
        if (!$matchInfo) {
            return $this->json(['message' => 'Match not found'], Response::HTTP_NOT_FOUND);
        }
        if ($matchInfo['validation'] === 'O') {
            return $this->json(['message' => 'Match is locked'], Response::HTTP_FORBIDDEN);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 8) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Get the team ID from match
        $teamId = $teamCode === 'A' ? $matchInfo['id_equipe_a'] : $matchInfo['id_equipe_b'];
        if (!$teamId) {
            return $this->json(['message' => "Team {$teamCode} not assigned to this match"], Response::HTTP_BAD_REQUEST);
        }

        // Copy from kp_competition_equipe_joueur (excluding X and A statuses)
        $sql = "REPLACE INTO kp_match_joueur (Id_match, Matric, Numero, Equipe, Capitaine)
                SELECT ?, Matric, Numero, ?, Capitaine
                FROM kp_competition_equipe_joueur
                WHERE Id_equipe = ?
                AND Capitaine <> 'X'
                AND Capitaine <> 'A'";

        $this->connection->executeStatement($sql, [$matchId, $teamCode, $teamId]);

        $this->logAction('Ajout titulaires match', "Match:{$matchId} - Equipe:{$teamId}");

        return $this->json(['success' => true]);
    }

    /**
     * Update match player inline (numero or capitaine)
     */
    #[Route('/admin/matches/{matchId}/players/{matric}', name: 'admin_match_players_update', methods: ['PATCH'])]
    public function updateMatchPlayer(int $matchId, int $matric, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $teamCode = $data['teamCode'] ?? null;

        if (!in_array($teamCode, ['A', 'B'])) {
            return $this->json(['message' => 'teamCode must be A or B'], Response::HTTP_BAD_REQUEST);
        }

        $matchInfo = $this->getMatchInfo($matchId);
        if (!$matchInfo) {
            return $this->json(['message' => 'Match not found'], Response::HTTP_NOT_FOUND);
        }
        if ($matchInfo['validation'] === 'O') {
            return $this->json(['message' => 'Match is locked'], Response::HTTP_FORBIDDEN);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 8) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $updateData = [];
        if (isset($data['numero'])) {
            $updateData['Numero'] = (int) $data['numero'];
        }
        if (isset($data['capitaine'])) {
            $updateData['Capitaine'] = $data['capitaine'];
        }

        if (empty($updateData)) {
            return $this->json(['message' => 'No fields to update'], Response::HTTP_BAD_REQUEST);
        }

        $this->connection->update(
            'kp_match_joueur',
            $updateData,
            ['Id_match' => $matchId, 'Matric' => $matric, 'Equipe' => $teamCode]
        );

        $this->logAction('Modification kp_match_joueur', "Match:{$matchId} - Equipe:{$teamCode} - Joueur:{$matric}");

        return $this->json(['success' => true]);
    }

    /**
     * Delete players from match
     */
    #[Route('/admin/matches/{matchId}/players', name: 'admin_match_players_delete', methods: ['DELETE'])]
    public function deleteMatchPlayers(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $teamCode = $data['teamCode'] ?? null;
        $matricIds = $data['matricIds'] ?? [];

        if (!in_array($teamCode, ['A', 'B'])) {
            return $this->json(['message' => 'teamCode must be A or B'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($matricIds)) {
            return $this->json(['message' => 'No players to delete'], Response::HTTP_BAD_REQUEST);
        }

        $matchInfo = $this->getMatchInfo($matchId);
        if (!$matchInfo) {
            return $this->json(['message' => 'Match not found'], Response::HTTP_NOT_FOUND);
        }
        if ($matchInfo['validation'] === 'O') {
            return $this->json(['message' => 'Match is locked'], Response::HTTP_FORBIDDEN);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 8) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $placeholders = implode(',', array_fill(0, count($matricIds), '?'));
        $sql = "DELETE FROM kp_match_joueur
                WHERE Id_match = ? AND Equipe = ? AND Matric IN ($placeholders)";

        $params = array_merge([$matchId, $teamCode], $matricIds);
        $this->connection->executeStatement($sql, $params);

        $this->logAction('Suppression joueurs match', "Match:{$matchId} - Equipe:{$teamCode} - Joueurs:" . implode(',', $matricIds));

        return $this->json(['success' => true, 'deleted' => count($matricIds)]);
    }

    /**
     * Clear all players from match for a team
     */
    #[Route('/admin/matches/{matchId}/players/clear', name: 'admin_match_players_clear', methods: ['DELETE'])]
    public function clearMatchPlayers(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $teamCode = $data['teamCode'] ?? null;

        if (!in_array($teamCode, ['A', 'B'])) {
            return $this->json(['message' => 'teamCode must be A or B'], Response::HTTP_BAD_REQUEST);
        }

        $matchInfo = $this->getMatchInfo($matchId);
        if (!$matchInfo) {
            return $this->json(['message' => 'Match not found'], Response::HTTP_NOT_FOUND);
        }
        if ($matchInfo['validation'] === 'O') {
            return $this->json(['message' => 'Match is locked'], Response::HTTP_FORBIDDEN);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 8) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $sql = "DELETE FROM kp_match_joueur WHERE Id_match = ? AND Equipe = ?";
        $this->connection->executeStatement($sql, [$matchId, $teamCode]);

        $this->logAction('Suppression joueurs match', "Match:{$matchId} - Equipe:{$teamCode} - Tous");

        return $this->json(['success' => true]);
    }

    /**
     * Get copyable matches (same team in same journee or competition)
     */
    #[Route('/admin/matches/{matchId}/copyable-matches', name: 'admin_match_copyable', methods: ['GET'])]
    #[OA\Parameter(name: 'matchId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'teamCode', in: 'query', required: true, schema: new OA\Schema(type: 'string', enum: ['A', 'B']))]
    #[OA\Parameter(name: 'scope', in: 'query', required: true, schema: new OA\Schema(type: 'string', enum: ['day', 'competition']))]
    public function getCopyableMatches(int $matchId, Request $request): JsonResponse
    {
        $teamCode = $request->query->get('teamCode', 'A');
        $scope = $request->query->get('scope', 'day');

        if (!in_array($teamCode, ['A', 'B'])) {
            return $this->json(['message' => 'teamCode must be A or B'], Response::HTTP_BAD_REQUEST);
        }

        // Get match info to find the team
        $matchInfo = $this->getMatchInfo($matchId);
        if (!$matchInfo) {
            return $this->json(['message' => 'Match not found'], Response::HTTP_NOT_FOUND);
        }

        $teamId = $teamCode === 'A' ? $matchInfo['id_equipe_a'] : $matchInfo['id_equipe_b'];
        if (!$teamId) {
            return $this->json(['message' => "Team {$teamCode} not assigned"], Response::HTTP_BAD_REQUEST);
        }

        if ($scope === 'day') {
            // Same journee
            $sql = "SELECT m.Id, m.Date_match, m.Heure_match, m.Terrain, m.Numero_ordre,
                           m.Validation,
                           ceA.Libelle AS equipeA, ceB.Libelle AS equipeB,
                           (SELECT COUNT(*) FROM kp_match_joueur mj
                            WHERE mj.Id_match = m.Id
                            AND mj.Equipe = IF(m.Id_equipeA = ?, 'A', 'B')) AS playerCount
                    FROM kp_match m
                    LEFT JOIN kp_competition_equipe ceA ON m.Id_equipeA = ceA.Id
                    LEFT JOIN kp_competition_equipe ceB ON m.Id_equipeB = ceB.Id
                    WHERE m.Id_journee = ?
                    AND m.Validation != 'O'
                    AND m.Id != ?
                    AND (m.Id_equipeA = ? OR m.Id_equipeB = ?)
                    ORDER BY m.Date_match, m.Heure_match";

            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery([$teamId, $matchInfo['id_journee'], $matchId, $teamId, $teamId]);
        } else {
            // Same competition (all journees)
            $sql = "SELECT j.Id FROM kp_journee j
                    WHERE j.Code_competition = (
                        SELECT Code_competition FROM kp_journee WHERE Id = ?
                    )";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery([$matchInfo['id_journee']]);
            $journeeIds = array_column($result->fetchAllAssociative(), 'Id');

            if (empty($journeeIds)) {
                return $this->json(['matches' => []]);
            }

            $placeholders = implode(',', array_fill(0, count($journeeIds), '?'));
            $sql = "SELECT m.Id, m.Date_match, m.Heure_match, m.Terrain, m.Numero_ordre,
                           m.Validation,
                           ceA.Libelle AS equipeA, ceB.Libelle AS equipeB,
                           (SELECT COUNT(*) FROM kp_match_joueur mj
                            WHERE mj.Id_match = m.Id
                            AND mj.Equipe = IF(m.Id_equipeA = ?, 'A', 'B')) AS playerCount
                    FROM kp_match m
                    LEFT JOIN kp_competition_equipe ceA ON m.Id_equipeA = ceA.Id
                    LEFT JOIN kp_competition_equipe ceB ON m.Id_equipeB = ceB.Id
                    WHERE m.Id_journee IN ($placeholders)
                    AND m.Validation != 'O'
                    AND m.Id != ?
                    AND (m.Id_equipeA = ? OR m.Id_equipeB = ?)
                    ORDER BY m.Date_match, m.Heure_match";

            $params = array_merge([$teamId], $journeeIds, [$matchId, $teamId, $teamId]);
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery($params);
        }

        $rows = $result->fetchAllAssociative();
        $matches = array_map(function ($row) {
            return [
                'id' => (int) $row['Id'],
                'dateMatch' => $row['Date_match'] ?? '',
                'heureMatch' => $row['Heure_match'] ?? '',
                'terrain' => $row['Terrain'] ?? '',
                'numeroOrdre' => (int) ($row['Numero_ordre'] ?? 0),
                'equipeA' => $row['equipeA'] ?? '',
                'equipeB' => $row['equipeB'] ?? '',
                'playerCount' => (int) ($row['playerCount'] ?? 0)
            ];
        }, $rows);

        return $this->json(['matches' => $matches]);
    }

    /**
     * Copy match players to other matches (same journee)
     */
    #[Route('/admin/matches/{matchId}/players/copy-to-day', name: 'admin_match_players_copy_day', methods: ['POST'])]
    public function copyMatchPlayersToDayMatches(int $matchId, Request $request): JsonResponse
    {
        return $this->copyMatchPlayersToMatches($matchId, $request, 'day');
    }

    /**
     * Copy match players to other matches (same competition)
     */
    #[Route('/admin/matches/{matchId}/players/copy-to-competition', name: 'admin_match_players_copy_competition', methods: ['POST'])]
    public function copyMatchPlayersToCompetitionMatches(int $matchId, Request $request): JsonResponse
    {
        return $this->copyMatchPlayersToMatches($matchId, $request, 'competition');
    }

    private function copyMatchPlayersToMatches(int $matchId, Request $request, string $scope): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $teamCode = $data['teamCode'] ?? null;
        $targetMatchIds = $data['matchIds'] ?? [];

        if (!in_array($teamCode, ['A', 'B'])) {
            return $this->json(['message' => 'teamCode must be A or B'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($targetMatchIds)) {
            return $this->json(['message' => 'No target matches specified'], Response::HTTP_BAD_REQUEST);
        }

        $matchInfo = $this->getMatchInfo($matchId);
        if (!$matchInfo) {
            return $this->json(['message' => 'Match not found'], Response::HTTP_NOT_FOUND);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 8) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $teamId = $teamCode === 'A' ? $matchInfo['id_equipe_a'] : $matchInfo['id_equipe_b'];
        if (!$teamId) {
            return $this->json(['message' => "Team {$teamCode} not assigned"], Response::HTTP_BAD_REQUEST);
        }

        // Get source players
        $sql = "SELECT Matric, Numero, Capitaine FROM kp_match_joueur
                WHERE Id_match = ? AND Equipe = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$matchId, $teamCode]);
        $sourcePlayers = $result->fetchAllAssociative();

        if (empty($sourcePlayers)) {
            return $this->json(['message' => 'No players to copy'], Response::HTTP_BAD_REQUEST);
        }

        $copied = 0;
        foreach ($targetMatchIds as $targetMatchId) {
            // Get target match to find correct A/B assignment
            $sql = "SELECT Id_equipeA, Id_equipeB, `Validation`
                    FROM kp_match WHERE Id = ?";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery([$targetMatchId]);
            $targetMatch = $result->fetchAssociative();

            if (!$targetMatch || $targetMatch['Validation'] === 'O') {
                continue; // Skip locked or non-existent matches
            }

            // Determine team code in target match
            $targetTeamCode = null;
            if ((int) $targetMatch['Id_equipeA'] === (int) $teamId) {
                $targetTeamCode = 'A';
            } elseif ((int) $targetMatch['Id_equipeB'] === (int) $teamId) {
                $targetTeamCode = 'B';
            }
            if (!$targetTeamCode) {
                continue; // Team not in this match
            }

            // Clear existing players for this team in target match
            $this->connection->executeStatement(
                "DELETE FROM kp_match_joueur WHERE Id_match = ? AND Equipe = ?",
                [$targetMatchId, $targetTeamCode]
            );

            // Insert source players
            foreach ($sourcePlayers as $player) {
                $this->connection->executeStatement(
                    "INSERT INTO kp_match_joueur (Id_match, Matric, Numero, Equipe, Capitaine) VALUES (?, ?, ?, ?, ?)",
                    [$targetMatchId, $player['Matric'], $player['Numero'], $targetTeamCode, $player['Capitaine']]
                );
            }
            $copied++;
        }

        $this->logAction(
            "Copie Compo sur " . ($scope === 'day' ? 'Journée' : 'Compet'),
            "Match:{$matchId} - Equipe:{$teamId} - {$copied} match(s)"
        );

        return $this->json(['success' => true, 'copied' => $copied]);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    private function getMatchInfo(int $matchId): ?array
    {
        $sql = "SELECT Id, Id_journee, Id_equipeA, Id_equipeB, `Validation`
                FROM kp_match WHERE Id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$matchId]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['Id'],
            'id_journee' => (int) $row['Id_journee'],
            'id_equipe_a' => $row['Id_equipeA'] ? (int) $row['Id_equipeA'] : null,
            'id_equipe_b' => $row['Id_equipeB'] ? (int) $row['Id_equipeB'] : null,
            'validation' => $row['Validation']
        ];
    }

    private function formatMatchPlayer(array $row): array
    {
        $pagaieValide = 0;
        $pagaieLabel = '';

        if (!empty($row['Pagaie_ECA']) && !in_array($row['Pagaie_ECA'], ['', 'PAGJ', 'PAGB'])) {
            $pagaieValide = 1;
            $pagaieLabel = $this->getPagaieLabel($row['Pagaie_ECA']);
        } elseif (!empty($row['Pagaie_EVI'])) {
            $pagaieValide = 2;
            $pagaieLabel = $this->getPagaieLabel($row['Pagaie_EVI']);
        } elseif (!empty($row['Pagaie_MER'])) {
            $pagaieValide = 3;
            $pagaieLabel = $this->getPagaieLabel($row['Pagaie_MER']);
        }

        $capitaine = $row['Capitaine'] ?? '-';
        if ($capitaine === '') {
            $capitaine = '-';
        }

        return [
            'matric' => (int) $row['Matric'],
            'nom' => $row['Nom'] ?? '',
            'prenom' => $row['Prenom'] ?? '',
            'sexe' => $row['Sexe'] ?? 'M',
            'categ' => $row['Naissance'] ? $this->calculateCategory($row['Naissance'], date('Y')) : '',
            'naissance' => $row['Naissance'] ?? null,
            'numero' => (int) ($row['Numero'] ?? 0),
            'capitaine' => $capitaine,
            'origine' => $row['Origine'] ?? '',
            'numeroClub' => $row['Numero_club'] ?? '',
            'clubLibelle' => $row['Club'] ?? '',
            'pagaieECA' => $row['Pagaie_ECA'] ?? '',
            'pagaieEVI' => $row['Pagaie_EVI'] ?? '',
            'pagaieMER' => $row['Pagaie_MER'] ?? '',
            'pagaieLabel' => $pagaieLabel,
            'pagaieValide' => $pagaieValide,
            'certifCK' => $row['Etat_certificat_CK'] ?? 'NON',
            'certifAPS' => $row['Etat_certificat_APS'] ?? 'NON',
            'dateCertifCK' => $row['Date_certificat_CK'] ?? null,
            'dateCertifAPS' => $row['Date_certificat_APS'] ?? null,
            'arbitre' => $row['arbitre'] ?? '',
            'niveau' => $row['niveau'] ?? '',
            'dateSurclassement' => $row['date_surclassement'] ?? null,
            'icf' => ($row['icf'] ?? null) ? (int) $row['icf'] : null
        ];
    }

    private function logAction(string $action, string $detail): void
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $username = $user?->getUserIdentifier() ?? 'unknown';

        $this->connection->insert('kp_journal', [
            'Dates' => (new \DateTime())->format('Y-m-d H:i:s'),
            'Users' => $username,
            'Actions' => $action,
            'Journal' => $detail
        ]);
    }

    private function formatPlayer(array $row): array
    {
        // Determine pagaie validity and label
        $pagaieValide = 0;
        $pagaieLabel = '';

        if (!empty($row['Pagaie_ECA']) && !in_array($row['Pagaie_ECA'], ['', 'PAGJ', 'PAGB'])) {
            $pagaieValide = 1;
            $pagaieLabel = $this->getPagaieLabel($row['Pagaie_ECA']);
        } elseif (!empty($row['Pagaie_EVI'])) {
            $pagaieValide = 2;
            $pagaieLabel = $this->getPagaieLabel($row['Pagaie_EVI']);
        } elseif (!empty($row['Pagaie_MER'])) {
            $pagaieValide = 3;
            $pagaieLabel = $this->getPagaieLabel($row['Pagaie_MER']);
        }

        return [
            'matric' => (int) $row['Matric'],
            'nom' => $row['Nom'] ?? '',
            'prenom' => $row['Prenom'] ?? '',
            'sexe' => $row['Sexe'] ?? 'M',
            'categ' => $row['Categ'] ?? '',
            'naissance' => $row['Naissance'] ?? null,
            'numero' => (int) ($row['Numero'] ?? 0),
            'capitaine' => $row['Capitaine'] ?? '-',
            'origine' => $row['Origine'] ?? '',
            'numeroClub' => $row['Numero_club'] ?? '',
            'clubLibelle' => $row['Club'] ?? '',
            'pagaieECA' => $row['Pagaie_ECA'] ?? '',
            'pagaieEVI' => $row['Pagaie_EVI'] ?? '',
            'pagaieMER' => $row['Pagaie_MER'] ?? '',
            'pagaieLabel' => $pagaieLabel,
            'pagaieValide' => $pagaieValide,
            'certifCK' => $row['Etat_certificat_CK'] ?? 'NON',
            'certifAPS' => $row['Etat_certificat_APS'] ?? 'NON',
            'dateCertifCK' => $row['Date_certificat_CK'] ?? null,
            'dateCertifAPS' => $row['Date_certificat_APS'] ?? null,
            'arbitre' => $row['arbitre'] ?? '',
            'niveau' => $row['niveau'] ?? '',
            'dateSurclassement' => $row['date_surclassement'] ?? null,
            'icf' => $row['icf'] ? (int) $row['icf'] : null
        ];
    }

    private function getPagaieLabel(string $code): string
    {
        $labels = [
            'PAGR' => 'Rouge',
            'PAGN' => 'Noire',
            'PAGBL' => 'Bleue',
            'PAGV' => 'Verte',
            'PAGJ' => 'Jaune',
            'PAGB' => 'Blanche'
        ];

        return $labels[$code] ?? $code;
    }

    private function getTeamInfo(int $teamId): ?array
    {
        $sql = "SELECT ce.Code_compet, ce.Code_saison, ce.Code_club, ce.Numero,
                       c.Libelle AS club_libelle,
                       comp.Verrou
                FROM kp_competition_equipe ce
                LEFT JOIN kp_club c ON ce.Code_club = c.Code
                LEFT JOIN kp_competition comp ON ce.Code_compet = comp.Code
                    AND ce.Code_saison = comp.Code_saison
                WHERE ce.Id = ?";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$teamId]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        return [
            'code_compet' => $row['Code_compet'],
            'code_saison' => $row['Code_saison'],
            'code_club' => $row['Code_club'],
            'numero' => (int) $row['Numero'],
            'club_libelle' => $row['club_libelle'] ?? '',
            'verrou' => $row['Verrou'] === 'O'
        ];
    }

    private function isNationalCompetition(string $code): bool
    {
        return str_starts_with($code, 'N') || str_starts_with($code, 'CF');
    }

    private function validatePlayerForNational(int $matric, array $teamInfo): array
    {
        $errors = [];

        $sql = "SELECT lc.Origine, lc.Pagaie_ECA, lc.Etat_certificat_CK, lc.Naissance,
                       s.Date AS date_surclassement
                FROM kp_licence lc
                LEFT JOIN kp_surclassement s ON lc.Matric = s.Matric
                    AND s.Saison = ?
                WHERE lc.Matric = ?";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$teamInfo['code_saison'], $matric]);
        $row = $result->fetchAssociative();

        if (!$row) {
            $errors[] = 'Player not found';
            return $errors;
        }

        // Check license season
        if ($row['Origine'] < $teamInfo['code_saison']) {
            $errors[] = 'Saison_licence';
        }

        // Check certificate
        if ($row['Etat_certificat_CK'] !== 'OUI') {
            $errors[] = 'Certif';
        }

        // Check pagaie
        if (in_array($row['Pagaie_ECA'], ['', 'PAGJ', 'PAGB'])) {
            $errors[] = 'Pagaie_couleur';
        }

        // Check surclassement if needed
        $categ = $this->calculateCategory($row['Naissance'], $teamInfo['code_saison']);
        if ($this->requiresSurclassement($teamInfo['code_compet'], $categ) && !$row['date_surclassement']) {
            $errors[] = 'Surclassement';
        }

        return $errors;
    }

    private function requiresSurclassement(string $competitionCode, string $categ): bool
    {
        $surclNecessaire = ['N1D', 'N1F', 'N1H', 'N2', 'N2H', 'N3H', 'N4H', 'NQH', 'CFF', 'CFH', 'MCP'];
        $surclNecessaire2 = ['N3', 'N4'];
        $exemptCategs = ['JUN', 'SEN', 'V1', 'V2', 'V3', 'V4'];

        if (in_array($categ, $exemptCategs)) {
            return false;
        }

        return in_array($competitionCode, $surclNecessaire) || in_array($competitionCode, $surclNecessaire2);
    }

    private function calculateCategory(?string $birthDate, string $season): string
    {
        if (!$birthDate) {
            return '';
        }

        $birth = new \DateTime($birthDate);
        $seasonYear = (int) $season;
        $age = $seasonYear - (int) $birth->format('Y');

        if ($age < 12) return 'BEN';
        if ($age < 14) return 'M12';
        if ($age < 16) return 'M14';
        if ($age < 18) return 'M16';
        if ($age < 21) return 'M18';
        if ($age < 23) return 'M21';
        if ($age < 35) return 'SEN';
        if ($age < 45) return 'V1';
        if ($age < 55) return 'V2';
        if ($age < 65) return 'V3';
        return 'V4';
    }

    private function getLastUpdate(string $table, int $id): ?array
    {
        $sql = "SELECT Dates, Users, Actions
                FROM kp_journal
                WHERE Journal LIKE ?
                ORDER BY Dates DESC
                LIMIT 1";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(["%{$table}%{$id}%"]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        return [
            'date' => $row['Dates'],
            'user' => $row['Users'],
            'action' => $row['Actions']
        ];
    }
}
