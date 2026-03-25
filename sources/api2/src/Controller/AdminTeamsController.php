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
 * Admin Teams Controller
 *
 * CRUD operations for competition teams management
 * (kp_competition_equipe, kp_equipe tables)
 * Migrated from GestionEquipe.php
 */
#[IsGranted('ROLE_TEAM')]
#[OA\Tag(name: '26. App4 - Teams')]
class AdminTeamsController extends AbstractController
{
    use AdminLoggableTrait;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * List teams for a competition
     */
    #[Route('/admin/competition-teams', name: 'admin_competition_teams_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $season = $request->query->get('season', '');
        $competition = $request->query->get('competition', '');

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        $allowedCompetitions = $user?->getAllowedCompetitions();

        // Check user access to this competition
        if ($allowedCompetitions !== null && !in_array($competition, $allowedCompetitions)) {
            return $this->json(['message' => 'Access denied to this competition'], Response::HTTP_FORBIDDEN);
        }

        // Get competition info
        $sql = "SELECT Code, Libelle, Code_niveau, Code_typeclt, Statut, Verrou
                FROM kp_competition
                WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$competition, $season]);
        $competitionRow = $result->fetchAssociative();

        if (!$competitionRow) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        // Get teams with match count
        $sql = "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, ce.Tirage,
                       ce.logo, ce.color1, ce.color2, ce.colortext,
                       c.Libelle AS club_libelle,
                       (SELECT COUNT(*) FROM kp_match m
                        WHERE (m.Id_equipeA = ce.Id OR m.Id_equipeB = ce.Id)
                        AND m.Validation = 'O') AS nb_matchs
                FROM kp_competition_equipe ce
                LEFT JOIN kp_club c ON ce.Code_club = c.Code
                WHERE ce.Code_compet = ? AND ce.Code_saison = ?
                ORDER BY ce.Poule ASC, ce.Tirage ASC, ce.Libelle ASC";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$competition, $season]);
        $rows = $result->fetchAllAssociative();

        $teams = array_map(function ($row) {
            return [
                'id' => (int) $row['Id'],
                'libelle' => $row['Libelle'],
                'codeClub' => $row['Code_club'],
                'clubLibelle' => $row['club_libelle'] ?? '',
                'numero' => (int) $row['Numero'],
                'poule' => $row['Poule'] ?? '',
                'tirage' => (int) ($row['Tirage'] ?? 0),
                'logo' => $row['logo'] ?: null,
                'color1' => $row['color1'] ?: null,
                'color2' => $row['color2'] ?: null,
                'colortext' => $row['colortext'] ?: null,
                'nbMatchs' => (int) $row['nb_matchs'],
            ];
        }, $rows);

        return $this->json([
            'teams' => $teams,
            'competition' => [
                'code' => $competitionRow['Code'],
                'libelle' => $competitionRow['Libelle'],
                'codeNiveau' => $competitionRow['Code_niveau'],
                'codeTypeclt' => $competitionRow['Code_typeclt'],
                'statut' => $competitionRow['Statut'],
                'verrou' => (bool) $competitionRow['Verrou'],
            ],
            'total' => count($teams),
        ]);
    }

    /**
     * Search historical teams (kp_equipe)
     */
    #[Route('/admin/teams/search', name: 'admin_teams_search', methods: ['GET'])]
    public function searchTeams(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $q = trim($request->query->get('q', ''));
        $limit = min(50, max(1, (int) $request->query->get('limit', 20)));

        if (strlen($q) < 2) {
            return $this->json([]);
        }

        $sql = "SELECT e.Numero, e.Libelle, e.Code_club, c.Libelle AS club_libelle,
                       CASE WHEN cr.Code = '98' THEN 1 ELSE 0 END AS international
                FROM kp_equipe e
                LEFT JOIN kp_club c ON e.Code_club = c.Code
                LEFT JOIN kp_cd cd ON c.Code_comite_dep = cd.Code
                LEFT JOIN kp_cr cr ON cd.Code_comite_reg = cr.Code
                WHERE e.Libelle LIKE ?
                ORDER BY e.Libelle
                LIMIT " . (int) $limit;

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(["%$q%"]);
        $rows = $result->fetchAllAssociative();

        $teams = array_map(function ($row) {
            return [
                'numero' => (int) $row['Numero'],
                'libelle' => $row['Libelle'],
                'codeClub' => $row['Code_club'],
                'clubLibelle' => $row['club_libelle'] ?? '',
                'international' => (bool) $row['international'],
            ];
        }, $rows);

        return $this->json($teams);
    }

    /**
     * Get available compositions for a team (for copy)
     */
    #[Route('/admin/teams/{numero}/compositions', name: 'admin_teams_compositions', methods: ['GET'])]
    public function getCompositions(int $numero, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $season = $request->query->get('season', '');

        $sql = "SELECT ce.Code_saison, ce.Code_compet, comp.Libelle AS compet_libelle,
                       (SELECT COUNT(*) FROM kp_competition_equipe_joueur cej
                        WHERE cej.Id_equipe = ce.Id) AS player_count
                FROM kp_competition_equipe ce
                LEFT JOIN kp_competition comp ON ce.Code_compet = comp.Code AND ce.Code_saison = comp.Code_saison
                WHERE ce.Numero = ?
                ORDER BY ce.Code_saison DESC, ce.Code_compet ASC";

        $params = [$numero];

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $rows = $result->fetchAllAssociative();

        $compositions = array_map(function ($row) {
            return [
                'season' => $row['Code_saison'],
                'competition' => $row['Code_compet'],
                'competitionLibelle' => $row['compet_libelle'] ?? $row['Code_compet'],
                'playerCount' => (int) $row['player_count'],
            ];
        }, $rows);

        return $this->json($compositions);
    }

    /**
     * Search clubs (autocomplete)
     */
    #[Route('/admin/clubs/search', name: 'admin_clubs_search', methods: ['GET'])]
    public function searchClubs(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $q = trim($request->query->get('q', ''));
        $limit = min(50, max(1, (int) $request->query->get('limit', 20)));

        if (strlen($q) < 2) {
            return $this->json([]);
        }

        $sql = "SELECT Code, Libelle, Code_comite_dep
                FROM kp_club
                WHERE Libelle LIKE ? OR Code LIKE ?
                ORDER BY Libelle
                LIMIT " . (int) $limit;

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery(["%$q%", "%$q%"]);
        $rows = $result->fetchAllAssociative();

        $clubs = array_map(function ($row) {
            return [
                'code' => $row['Code'],
                'libelle' => $row['Libelle'],
                'codeComiteDep' => $row['Code_comite_dep'] ?? '',
            ];
        }, $rows);

        return $this->json($clubs);
    }

    /**
     * List regional committees
     */
    #[Route('/admin/regional-committees', name: 'admin_regional_committees', methods: ['GET'])]
    public function listRegionalCommittees(): JsonResponse
    {
        $sql = "SELECT Code, Libelle FROM kp_cr ORDER BY Libelle";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $rows = $result->fetchAllAssociative();

        $items = array_map(function ($row) {
            return [
                'code' => $row['Code'],
                'libelle' => $row['Libelle'],
            ];
        }, $rows);

        return $this->json($items);
    }

    /**
     * List departmental committees
     */
    #[Route('/admin/departmental-committees', name: 'admin_departmental_committees', methods: ['GET'])]
    public function listDepartmentalCommittees(Request $request): JsonResponse
    {
        $cr = $request->query->get('cr', '');

        $whereConditions = [];
        $params = [];

        if (!empty($cr)) {
            $whereConditions[] = 'Code_comite_reg = ?';
            $params[] = $cr;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "SELECT Code, Libelle, Code_comite_reg FROM kp_cd $whereClause ORDER BY Libelle";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $rows = $result->fetchAllAssociative();

        $items = array_map(function ($row) {
            return [
                'code' => $row['Code'],
                'libelle' => $row['Libelle'],
                'codeComiteReg' => $row['Code_comite_reg'],
            ];
        }, $rows);

        return $this->json($items);
    }

    /**
     * List clubs (with optional CR/CD filter)
     */
    #[Route('/admin/clubs', name: 'admin_clubs_list', methods: ['GET'])]
    public function listClubs(Request $request): JsonResponse
    {
        $cd = $request->query->get('cd', '');

        $whereConditions = [];
        $params = [];

        if (!empty($cd)) {
            $whereConditions[] = 'Code_comite_dep = ?';
            $params[] = $cd;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "SELECT Code, Libelle, Code_comite_dep FROM kp_club $whereClause ORDER BY Libelle";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $rows = $result->fetchAllAssociative();

        $items = array_map(function ($row) {
            return [
                'code' => $row['Code'],
                'libelle' => $row['Libelle'],
                'codeComiteDep' => $row['Code_comite_dep'] ?? '',
            ];
        }, $rows);

        return $this->json($items);
    }

    /**
     * Add team(s) to a competition
     */
    #[Route('/admin/competition-teams', name: 'admin_competition_teams_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $season = $data['season'] ?? '';
        $competition = $data['competition'] ?? '';
        $mode = $data['mode'] ?? 'manual';

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        // Verify competition exists
        $sql = "SELECT Code, Verrou FROM kp_competition WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$competition, $season]);
        $compRow = $result->fetchAssociative();
        if (!$compRow) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        if ($compRow['Verrou']) {
            return $this->json(['message' => 'Competition is locked'], Response::HTTP_FORBIDDEN);
        }

        $this->connection->beginTransaction();
        try {
            $addedCount = 0;

            if ($mode === 'manual') {
                $libelle = trim($data['libelle'] ?? '');
                $codeClub = trim($data['codeClub'] ?? '');
                $poule = strtoupper(trim($data['poule'] ?? ''));
                $tirage = (int) ($data['tirage'] ?? 0);

                if (empty($libelle)) {
                    return $this->json(['message' => 'Team name is required'], Response::HTTP_BAD_REQUEST);
                }

                // Verify club exists
                if (!empty($codeClub)) {
                    $sql = "SELECT Code FROM kp_club WHERE Code = ?";
                    $stmt = $this->connection->prepare($sql);
                    $result = $stmt->executeQuery([$codeClub]);
                    if (!$result->fetchOne()) {
                        return $this->json(['message' => 'Club not found'], Response::HTTP_BAD_REQUEST);
                    }
                }

                // Create in kp_equipe first (get team number)
                $sql = "INSERT INTO kp_equipe (Libelle, Code_club) VALUES (?, ?)";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([$libelle, $codeClub]);
                $teamNumero = (int) $this->connection->lastInsertId();

                // Create in kp_competition_equipe
                $sql = "INSERT INTO kp_competition_equipe
                        (Code_compet, Code_saison, Libelle, Code_club, Numero, Poule, Tirage)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([$competition, $season, $libelle, $codeClub, $teamNumero, $poule, $tirage]);
                $addedCount = 1;
            } elseif ($mode === 'history') {
                $teamNumbers = $data['teamNumbers'] ?? [];
                $poule = strtoupper(trim($data['poule'] ?? ''));
                $tirage = (int) ($data['tirage'] ?? 0);
                $copyComposition = $data['copyComposition'] ?? null;

                if (empty($teamNumbers)) {
                    return $this->json(['message' => 'At least one team must be selected'], Response::HTTP_BAD_REQUEST);
                }

                foreach ($teamNumbers as $numero) {
                    $numero = (int) $numero;

                    // Get team info from kp_equipe
                    $sql = "SELECT Numero, Libelle, Code_club, logo, color1, color2, colortext
                            FROM kp_equipe WHERE Numero = ?";
                    $stmt = $this->connection->prepare($sql);
                    $result = $stmt->executeQuery([$numero]);
                    $team = $result->fetchAssociative();

                    if (!$team) {
                        continue;
                    }

                    // Check if already in competition
                    $sql = "SELECT Id FROM kp_competition_equipe
                            WHERE Code_compet = ? AND Code_saison = ? AND Numero = ?";
                    $stmt = $this->connection->prepare($sql);
                    $result = $stmt->executeQuery([$competition, $season, $numero]);
                    if ($result->fetchOne()) {
                        continue; // Skip duplicates
                    }

                    // Insert into competition
                    $sql = "INSERT INTO kp_competition_equipe
                            (Code_compet, Code_saison, Libelle, Code_club, Numero, Poule, Tirage, logo, color1, color2, colortext)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->executeStatement([
                        $competition, $season, $team['Libelle'], $team['Code_club'],
                        $numero, $poule, $tirage,
                        $team['logo'], $team['color1'], $team['color2'], $team['colortext']
                    ]);
                    $newTeamId = (int) $this->connection->lastInsertId();

                    // Copy composition if requested
                    if ($copyComposition && !empty($copyComposition['season']) && !empty($copyComposition['competition'])) {
                        $this->copyComposition($numero, $copyComposition['season'], $copyComposition['competition'], $newTeamId, $season);
                    }

                    $addedCount++;
                }
            }

            $this->connection->commit();
            $this->logActionForSeason('Ajout equipe', $season, "$competition: $addedCount équipe(s)");

            return $this->json([
                'message' => "$addedCount team(s) added successfully",
                'count' => $addedCount,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Error adding teams: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a team from competition
     */
    #[Route('/admin/competition-teams/{id}', name: 'admin_competition_teams_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Get team info
        $sql = "SELECT ce.Id, ce.Libelle, ce.Code_compet, ce.Code_saison,
                       (SELECT COUNT(*) FROM kp_match m
                        WHERE (m.Id_equipeA = ce.Id OR m.Id_equipeB = ce.Id)
                        AND m.Validation = 'O') AS nb_matchs
                FROM kp_competition_equipe ce
                WHERE ce.Id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $team = $result->fetchAssociative();

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        if ((int) $team['nb_matchs'] > 0) {
            return $this->json(['message' => 'Cannot delete: team has played matches'], Response::HTTP_CONFLICT);
        }

        // Check competition lock
        $sql = "SELECT Verrou FROM kp_competition WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$team['Code_compet'], $team['Code_saison']]);
        $compRow = $result->fetchAssociative();
        if ($compRow && $compRow['Verrou']) {
            return $this->json(['message' => 'Competition is locked'], Response::HTTP_FORBIDDEN);
        }

        $this->connection->beginTransaction();
        try {
            // Delete players first
            $sql = "DELETE FROM kp_competition_equipe_joueur WHERE Id_equipe = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$id]);

            // Delete team from competition
            $sql = "DELETE FROM kp_competition_equipe WHERE Id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$id]);

            $this->connection->commit();
            $this->logActionForSeason('Suppression equipes', $team['Code_saison'], "{$team['Code_compet']}: {$team['Libelle']}");

            return $this->json(['message' => 'Team deleted successfully']);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Error deleting team: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete teams
     */
    #[Route('/admin/competition-teams/bulk-delete', name: 'admin_competition_teams_bulk_delete', methods: ['POST'])]
    public function bulkDelete(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];
        $season = $data['season'] ?? '';
        $competition = $data['competition'] ?? '';

        if (empty($ids)) {
            return $this->json(['message' => 'No teams selected'], Response::HTTP_BAD_REQUEST);
        }

        // Check competition lock
        if (!empty($season) && !empty($competition)) {
            $sql = "SELECT Verrou FROM kp_competition WHERE Code = ? AND Code_saison = ?";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery([$competition, $season]);
            $compRow = $result->fetchAssociative();
            if ($compRow && $compRow['Verrou']) {
                return $this->json(['message' => 'Competition is locked'], Response::HTTP_FORBIDDEN);
            }
        }

        $this->connection->beginTransaction();
        try {
            $deletedCount = 0;
            $skippedCount = 0;
            $season = '';

            foreach ($ids as $id) {
                $id = (int) $id;

                // Check if team has matches
                $sql = "SELECT ce.Id, ce.Libelle, ce.Code_saison, ce.Code_compet,
                               (SELECT COUNT(*) FROM kp_match m
                                WHERE (m.Id_equipeA = ce.Id OR m.Id_equipeB = ce.Id)
                                AND m.Validation = 'O') AS nb_matchs
                        FROM kp_competition_equipe ce
                        WHERE ce.Id = ?";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery([$id]);
                $team = $result->fetchAssociative();

                if (!$team) {
                    continue;
                }

                $season = $team['Code_saison'];

                if ((int) $team['nb_matchs'] > 0) {
                    $skippedCount++;
                    continue;
                }

                // Delete players
                $sql = "DELETE FROM kp_competition_equipe_joueur WHERE Id_equipe = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([$id]);

                // Delete team
                $sql = "DELETE FROM kp_competition_equipe WHERE Id = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([$id]);

                $deletedCount++;
            }

            $this->connection->commit();
            $this->logActionForSeason('Suppression equipes', $season, "$deletedCount équipe(s) supprimée(s)");

            return $this->json([
                'message' => "$deletedCount team(s) deleted" . ($skippedCount > 0 ? ", $skippedCount skipped (have matches)" : ''),
                'deleted' => $deletedCount,
                'skipped' => $skippedCount,
            ]);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Error deleting teams: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update pool and draw for a team
     */
    #[Route('/admin/competition-teams/{id}/pool-draw', name: 'admin_competition_teams_pool_draw', methods: ['PATCH'])]
    public function updatePoolDraw(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        // Verify team exists
        $sql = "SELECT Id, Code_saison, Code_compet FROM kp_competition_equipe WHERE Id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $team = $result->fetchAssociative();

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $poule = strtoupper(trim($data['poule'] ?? ''));
        $tirage = (int) ($data['tirage'] ?? 0);

        // Validate poule: 0-5 uppercase letters or empty
        if (!empty($poule) && !preg_match('/^[A-Z]{0,5}$/', $poule)) {
            return $this->json(['message' => 'Pool must be 0 to 5 A-Z letters'], Response::HTTP_BAD_REQUEST);
        }

        // Validate tirage: 0-99
        if ($tirage < 0 || $tirage > 99) {
            return $this->json(['message' => 'Draw must be between 0 and 99'], Response::HTTP_BAD_REQUEST);
        }

        $sql = "UPDATE kp_competition_equipe SET Poule = ?, Tirage = ? WHERE Id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement([$poule, $tirage, $id]);

        $this->logActionForSeason('Tirage au sort', $team['Code_saison'], "{$team['Code_compet']}: id=$id poule=$poule tirage=$tirage");

        return $this->json([
            'id' => $id,
            'poule' => $poule,
            'tirage' => $tirage,
        ]);
    }

    /**
     * Update team colors and logo
     */
    #[Route('/admin/competition-teams/{id}/colors', name: 'admin_competition_teams_colors', methods: ['PATCH'])]
    public function updateColors(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 2) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        // Verify team exists
        $sql = "SELECT Id, Numero, Code_saison, Code_compet FROM kp_competition_equipe WHERE Id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $team = $result->fetchAssociative();

        if (!$team) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $logo = trim($data['logo'] ?? '');
        $color1 = trim($data['color1'] ?? '');
        $color2 = trim($data['color2'] ?? '');
        $colortext = trim($data['colortext'] ?? '');
        $propagateNext = (bool) ($data['propagateNext'] ?? false);
        $propagatePrevious = (bool) ($data['propagatePrevious'] ?? false);
        $propagateClub = (bool) ($data['propagateClub'] ?? false);

        $this->connection->beginTransaction();
        try {
            // Update current team
            $sql = "UPDATE kp_competition_equipe SET logo = ?, color1 = ?, color2 = ?, colortext = ? WHERE Id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$logo, $color1, $color2, $colortext, $id]);

            // Update kp_equipe base record
            $sql = "UPDATE kp_equipe SET logo = ?, color1 = ?, color2 = ?, colortext = ? WHERE Numero = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$logo, $color1, $color2, $colortext, $team['Numero']]);

            // Propagate to future competitions
            if ($propagateNext) {
                $sql = "UPDATE kp_competition_equipe SET logo = ?, color1 = ?, color2 = ?, colortext = ?
                        WHERE Numero = ? AND Code_saison >= ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([$logo, $color1, $color2, $colortext, $team['Numero'], $team['Code_saison']]);
            }

            // Propagate to previous competitions
            if ($propagatePrevious) {
                $sql = "UPDATE kp_competition_equipe SET logo = ?, color1 = ?, color2 = ?, colortext = ?
                        WHERE Numero = ? AND Code_saison < ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([$logo, $color1, $color2, $colortext, $team['Numero'], $team['Code_saison']]);
            }

            // Propagate to all teams in the same club
            if ($propagateClub) {
                $sql = "SELECT Code_club FROM kp_competition_equipe WHERE Id = ?";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery([$id]);
                $codeClub = $result->fetchOne();

                if ($codeClub) {
                    $sql = "UPDATE kp_competition_equipe SET logo = ?, color1 = ?, color2 = ?, colortext = ?
                            WHERE Code_club = ?";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->executeStatement([$logo, $color1, $color2, $colortext, $codeClub]);

                    $sql = "UPDATE kp_equipe SET logo = ?, color1 = ?, color2 = ?, colortext = ?
                            WHERE Code_club = ?";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->executeStatement([$logo, $color1, $color2, $colortext, $codeClub]);
                }
            }

            $this->connection->commit();
            $this->logActionForSeason('Update couleurs equipe', $team['Code_saison'], "{$team['Code_compet']}: id=$id");

            return $this->json([
                'id' => $id,
                'logo' => $logo,
                'color1' => $color1,
                'color2' => $color2,
                'colortext' => $colortext,
            ]);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Error updating colors: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Duplicate teams from another competition
     */
    #[Route('/admin/competition-teams/duplicate', name: 'admin_competition_teams_duplicate', methods: ['POST'])]
    public function duplicate(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $season = $data['season'] ?? '';
        $targetCompetition = $data['targetCompetition'] ?? '';
        $sourceCompetition = $data['sourceCompetition'] ?? '';
        $sourceSeason = $data['sourceSeason'] ?? $season;
        $mode = $data['mode'] ?? 'append';
        $copyPlayers = (bool) ($data['copyPlayers'] ?? false);

        if (empty($season) || empty($targetCompetition) || empty($sourceCompetition)) {
            return $this->json(['message' => 'Season, target and source competition are required'], Response::HTTP_BAD_REQUEST);
        }

        $this->connection->beginTransaction();
        try {
            // If replace mode, delete existing teams first
            if ($mode === 'replace') {
                // Delete players
                $sql = "DELETE cej FROM kp_competition_equipe_joueur cej
                        INNER JOIN kp_competition_equipe ce ON cej.Id_equipe = ce.Id
                        WHERE ce.Code_compet = ? AND ce.Code_saison = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([$targetCompetition, $season]);

                // Delete teams
                $sql = "DELETE FROM kp_competition_equipe WHERE Code_compet = ? AND Code_saison = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([$targetCompetition, $season]);
            }

            // Get source teams
            $sql = "SELECT ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, ce.Tirage,
                           ce.logo, ce.color1, ce.color2, ce.colortext, ce.Id
                    FROM kp_competition_equipe ce
                    WHERE ce.Code_compet = ? AND ce.Code_saison = ?
                    ORDER BY ce.Poule, ce.Tirage";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery([$sourceCompetition, $sourceSeason]);
            $sourceTeams = $result->fetchAllAssociative();

            $addedCount = 0;
            foreach ($sourceTeams as $sourceTeam) {
                // Check if already exists in target
                $sql = "SELECT Id FROM kp_competition_equipe
                        WHERE Code_compet = ? AND Code_saison = ? AND Numero = ?";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery([$targetCompetition, $season, $sourceTeam['Numero']]);
                if ($result->fetchOne()) {
                    continue;
                }

                // Insert team
                $sql = "INSERT INTO kp_competition_equipe
                        (Code_compet, Code_saison, Libelle, Code_club, Numero, Poule, Tirage, logo, color1, color2, colortext)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([
                    $targetCompetition, $season,
                    $sourceTeam['Libelle'], $sourceTeam['Code_club'], $sourceTeam['Numero'],
                    $sourceTeam['Poule'], $sourceTeam['Tirage'],
                    $sourceTeam['logo'], $sourceTeam['color1'], $sourceTeam['color2'], $sourceTeam['colortext']
                ]);
                $newTeamId = (int) $this->connection->lastInsertId();

                // Copy players if requested
                if ($copyPlayers) {
                    $sourceTeamId = (int) $sourceTeam['Id'];
                    $sql = "INSERT INTO kp_competition_equipe_joueur (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine)
                            SELECT ?, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine
                            FROM kp_competition_equipe_joueur
                            WHERE Id_equipe = ?";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->executeStatement([$newTeamId, $sourceTeamId]);
                }

                $addedCount++;
            }

            $this->connection->commit();
            $this->logActionForSeason('Duplication equipes', $season, "$sourceCompetition -> $targetCompetition: $addedCount équipe(s)");

            return $this->json([
                'message' => "$addedCount team(s) duplicated successfully",
                'count' => $addedCount,
            ]);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Error duplicating teams: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update logos automatically (scan files)
     */
    #[Route('/admin/competition-teams/update-logos', name: 'admin_competition_teams_update_logos', methods: ['POST'])]
    public function updateLogos(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 2) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $season = $data['season'] ?? '';
        $competition = $data['competition'] ?? '';

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        // Get teams for this competition
        $sql = "SELECT ce.Id, ce.Code_club, ce.Numero, ce.logo
                FROM kp_competition_equipe ce
                WHERE ce.Code_compet = ? AND ce.Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$competition, $season]);
        $teams = $result->fetchAllAssociative();

        // Document root for file existence checks (img/ is sibling to api2/)
        $imgRoot = $this->getParameter('kernel.project_dir') . '/../img/';

        $updatedCount = 0;
        foreach ($teams as $team) {
            // Only update teams that don't already have a logo set (same as legacy PHP)
            if (!empty($team['logo'])) {
                continue;
            }

            $codeClub = $team['Code_club'];
            $logoPath = null;

            if (!empty($codeClub)) {
                // French clubs (4-char code): KIP/logo/{code}-logo.png
                if (strlen($codeClub) === 4 && is_file($imgRoot . 'KIP/logo/' . $codeClub . '-logo.png')) {
                    $logoPath = 'KIP/logo/' . $codeClub . '-logo.png';
                }
                // International teams: Nations/{code}.png
                elseif (strlen($codeClub) !== 4 && is_file($imgRoot . 'Nations/' . substr($codeClub, 0, 3) . '.png')) {
                    $logoPath = 'Nations/' . substr($codeClub, 0, 3) . '.png';
                }
            }

            if ($logoPath) {
                $sql = "UPDATE kp_competition_equipe SET logo = ? WHERE Id = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([$logoPath, $team['Id']]);

                $sql = "UPDATE kp_equipe SET logo = ? WHERE Numero = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([$logoPath, $team['Numero']]);

                $updatedCount++;
            }
        }

        $this->logActionForSeason('Update logo equipes', $season, "$competition: $updatedCount logo(s)");

        return $this->json([
            'message' => "$updatedCount logo(s) updated",
            'count' => $updatedCount,
        ]);
    }

    /**
     * Initialize starters for a competition
     */
    #[Route('/admin/competition-teams/init-starters', name: 'admin_competition_teams_init_starters', methods: ['POST'])]
    public function initStarters(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $season = $data['season'] ?? '';
        $competition = $data['competition'] ?? '';

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        $this->connection->beginTransaction();
        try {
            // Lock the competition
            $sql = "UPDATE kp_competition SET Verrou = 1 WHERE Code = ? AND Code_saison = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$competition, $season]);

            // Get all teams with their players
            $sql = "SELECT ce.Id
                    FROM kp_competition_equipe ce
                    WHERE ce.Code_compet = ? AND ce.Code_saison = ?";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery([$competition, $season]);
            $teams = $result->fetchAllAssociative();

            $teamsInitialized = 0;
            foreach ($teams as $team) {
                $teamId = (int) $team['Id'];

                // Get all matches for this team that don't have players yet
                $sql = "SELECT m.Id, m.Id_equipeA, m.Id_equipeB
                        FROM kp_match m
                        WHERE (m.Id_equipeA = ? OR m.Id_equipeB = ?)
                        AND m.Code_compet = ? AND m.Code_saison = ?";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery([$teamId, $teamId, $competition, $season]);
                $matches = $result->fetchAllAssociative();

                foreach ($matches as $match) {
                    $isTeamA = (int) $match['Id_equipeA'] === $teamId;
                    $prefix = $isTeamA ? 'A' : 'B';

                    // Check if match already has players for this team
                    $sql = "SELECT COUNT(*) FROM kp_match_joueur
                            WHERE Id_match = ? AND Equipe = ?";
                    $stmt = $this->connection->prepare($sql);
                    $result = $stmt->executeQuery([$match['Id'], $prefix]);
                    $existingPlayers = (int) $result->fetchOne();

                    if ($existingPlayers > 0) {
                        continue;
                    }

                    // Copy team composition to match
                    $sql = "INSERT INTO kp_match_joueur (Id_match, Equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine)
                            SELECT ?, ?, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine
                            FROM kp_competition_equipe_joueur
                            WHERE Id_equipe = ?";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->executeStatement([$match['Id'], $prefix, $teamId]);
                }

                $teamsInitialized++;
            }

            $this->connection->commit();
            $this->logActionForSeason('Init titulaires', $season, "$competition: $teamsInitialized équipe(s)");

            return $this->json([
                'message' => "Starters initialized for $teamsInitialized team(s). Competition locked.",
                'count' => $teamsInitialized,
            ]);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Error initializing starters: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle competition lock status
     */
    #[Route('/admin/competition-teams/toggle-lock', name: 'admin_competition_teams_toggle_lock', methods: ['PATCH'])]
    public function toggleLock(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $season = $data['season'] ?? '';
        $competition = $data['competition'] ?? '';

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        // Get current lock state
        $sql = "SELECT Verrou FROM kp_competition WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$competition, $season]);
        $current = $result->fetchOne();

        if ($current === false) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        $newVerrou = $current ? 0 : 1;
        $sql = "UPDATE kp_competition SET Verrou = ? WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement([$newVerrou, $competition, $season]);

        return $this->json([
            'verrou' => (bool) $newVerrou,
        ]);
    }

    /**
     * Copy player composition from a source team/competition to a new team
     */
    private function copyComposition(int $teamNumero, string $sourceSeason, string $sourceCompetition, int $targetTeamId, string $targetSeason): void
    {
        // Find source team entry
        $sql = "SELECT Id FROM kp_competition_equipe
                WHERE Numero = ? AND Code_compet = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$teamNumero, $sourceCompetition, $sourceSeason]);
        $sourceTeamId = $result->fetchOne();

        if (!$sourceTeamId) {
            return;
        }

        // Copy players (recalculate categories if needed)
        $sql = "INSERT INTO kp_competition_equipe_joueur (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine)
                SELECT ?, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine
                FROM kp_competition_equipe_joueur
                WHERE Id_equipe = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement([$targetTeamId, $sourceTeamId]);
    }

}
