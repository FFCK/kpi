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

        // Search by matric (exact) or by name (LIKE)
        if (is_numeric($query)) {
            $sql = "SELECT Matric, Nom, Prenom, Sexe, Naissance, Numero_club, Club,
                           Pagaie_ECA, Pagaie_EVI, Pagaie_MER,
                           Etat_certificat_CK
                    FROM kp_licence
                    WHERE Matric = ?
                    LIMIT " . (int) $limit;
            $params = [(int) $query];
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
    // HELPER METHODS
    // ============================================

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
