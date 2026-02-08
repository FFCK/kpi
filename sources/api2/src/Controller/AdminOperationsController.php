<?php

namespace App\Controller;

use App\Service\EventExportImportService;
use App\Service\ImageOperationsService;
use App\Service\PlayerMergeService;
use App\Service\SeasonOperationsService;
use App\Service\TeamOperationsService;
use App\Trait\AdminLoggableTrait;
use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin Operations Controller
 *
 * System administration operations (Super Admin only)
 * Migrated from GestionOperations.php
 */
#[Route('/admin/operations')]
#[IsGranted('ROLE_SUPER_ADMIN')]
#[OA\Tag(name: '24. App4 - Operations')]
class AdminOperationsController extends AbstractController
{
    use AdminLoggableTrait;

    public function __construct(
        private readonly Connection $connection,
        private readonly SeasonOperationsService $seasonService,
        private readonly ImageOperationsService $imageService,
        private readonly PlayerMergeService $playerService,
        private readonly TeamOperationsService $teamService,
        private readonly EventExportImportService $eventService
    ) {
    }

    // ==================== SEASONS ====================

    /**
     * List all seasons
     */
    #[Route('/seasons', name: 'admin_operations_seasons_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function listSeasons(): JsonResponse
    {
        return $this->json($this->seasonService->listSeasons());
    }

    /**
     * Add a new season
     */
    #[Route('/seasons', name: 'admin_operations_seasons_add', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addSeason(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $code = $data['code'] ?? '';
        $natDebut = $data['natDebut'] ?? null;
        $natFin = $data['natFin'] ?? null;
        $interDebut = $data['interDebut'] ?? null;
        $interFin = $data['interFin'] ?? null;

        if (empty($code)) {
            return $this->json(['message' => 'Season code is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->seasonService->addSeason($code, $natDebut, $natFin, $interDebut, $interFin);
            $this->logActionForEvent('Ajout Saison', null, $code);
            return $this->json(['message' => 'Season added successfully', 'code' => $code], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Activate a season
     */
    #[Route('/seasons/{code}/activate', name: 'admin_operations_seasons_activate', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function activateSeason(string $code): JsonResponse
    {
        try {
            $this->seasonService->activateSeason($code);
            $this->logActionForEvent('Change Saison Active', null, $code);
            return $this->json(['message' => 'Season activated', 'code' => $code]);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Copy RC from one season to another
     */
    #[Route('/seasons/copy-rc', name: 'admin_operations_seasons_copy_rc', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function copyRc(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $sourceCode = $data['sourceCode'] ?? '';
        $targetCode = $data['targetCode'] ?? '';

        if (empty($sourceCode) || empty($targetCode)) {
            return $this->json(['message' => 'Source and target season codes are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->seasonService->copyRc($sourceCode, $targetCode);
            $this->logActionForEvent('Copie RC', null, "De $sourceCode vers $targetCode: {$result['copied']} copiés, {$result['skipped']} ignorés");
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Copy competitions from one season to another
     */
    #[Route('/seasons/copy-competitions', name: 'admin_operations_seasons_copy_competitions', methods: ['POST'])]
    public function copyCompetitions(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $sourceCode = $data['sourceCode'] ?? '';
        $targetCode = $data['targetCode'] ?? '';
        $competitionCodes = $data['competitionCodes'] ?? [];
        $copyMatches = $data['copyMatches'] ?? false;

        if (empty($sourceCode) || empty($targetCode) || empty($competitionCodes)) {
            return $this->json(['message' => 'Source, target season codes and competition codes are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->seasonService->copyCompetitions($sourceCode, $targetCode, $competitionCodes, $copyMatches);
            $this->logActionForEvent('Copie Compétitions', null, "Vers saison $targetCode: {$result['copied']} copiées");
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get competitions for a season
     */
    #[Route('/seasons/{code}/competitions', name: 'admin_operations_seasons_competitions', methods: ['GET'])]
    public function getSeasonCompetitions(string $code): JsonResponse
    {
        return $this->json($this->seasonService->getCompetitions($code));
    }

    // ==================== IMAGES ====================

    /**
     * Get image types configuration
     */
    #[Route('/images/types', name: 'admin_operations_images_types', methods: ['GET'])]
    public function getImageTypes(): JsonResponse
    {
        return $this->json($this->imageService->getImageTypesConfig());
    }

    /**
     * Upload an image
     */
    #[Route('/images/upload', name: 'admin_operations_images_upload', methods: ['POST'])]
    public function uploadImage(Request $request): JsonResponse
    {
        $imageType = $request->request->get('imageType', '');
        $file = $request->files->get('imageFile');

        if (!$file) {
            return $this->json(['message' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $params = [];
        if ($imageType === 'logo_competition' || $imageType === 'bandeau_competition' || $imageType === 'sponsor_competition') {
            $params['codeCompetition'] = $request->request->get('codeCompetition', '');
            $params['saison'] = $request->request->get('saison', '');
        } elseif ($imageType === 'logo_club') {
            $params['numeroClub'] = $request->request->get('numeroClub', '');
        } elseif ($imageType === 'logo_nation') {
            $params['codeNation'] = $request->request->get('codeNation', '');
        }

        try {
            $result = $this->imageService->uploadImage($imageType, $file, $params);
            $this->logActionForEvent('Upload Image', null, $result['filename']);
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Rename an image
     */
    #[Route('/images/rename', name: 'admin_operations_images_rename', methods: ['POST'])]
    public function renameImage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $imageType = $data['imageType'] ?? '';
        $currentName = $data['currentName'] ?? '';
        $newName = $data['newName'] ?? '';

        if (empty($imageType) || empty($currentName) || empty($newName)) {
            return $this->json(['message' => 'Image type, current name and new name are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->imageService->renameImage($imageType, $currentName, $newName);
            $this->logActionForEvent('Rename Image', null, "$currentName -> $newName");
            return $this->json(['message' => 'Image renamed successfully']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    // ==================== PLAYERS ====================

    /**
     * Merge two players
     */
    #[Route('/players/merge', name: 'admin_operations_players_merge', methods: ['POST'])]
    public function mergePlayers(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $sourceMatric = (int) ($data['sourceMatric'] ?? 0);
        $targetMatric = (int) ($data['targetMatric'] ?? 0);

        if ($sourceMatric <= 0 || $targetMatric <= 0) {
            return $this->json(['message' => 'Source and target matric are required'], Response::HTTP_BAD_REQUEST);
        }

        if ($sourceMatric === $targetMatric) {
            return $this->json(['message' => 'Source and target cannot be the same'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->playerService->mergePlayers($sourceMatric, $targetMatric);
            $this->logActionForEvent('Fusion Joueurs', null, "$sourceMatric => $targetMatric");
            return $this->json(['message' => 'Players merged successfully']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Auto-merge non-federal players
     */
    #[Route('/players/auto-merge', name: 'admin_operations_players_auto_merge', methods: ['POST'])]
    public function autoMergePlayers(): JsonResponse
    {
        try {
            $result = $this->playerService->autoMergeNonFederalPlayers();
            $this->logActionForEvent('Fusion Auto Licenciés Non Fédéraux', null, "{$result['count']} fusions effectuées");
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Search players for autocomplete
     */
    #[Route('/autocomplete/players', name: 'admin_operations_autocomplete_players', methods: ['GET'])]
    public function searchPlayers(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $limit = min(20, max(1, (int) $request->query->get('limit', 10)));

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $sql = "SELECT l.Matric, l.Nom, l.Prenom, l.Naissance, l.Numero_club, c.Libelle as Club
                FROM kp_licence l
                LEFT JOIN kp_club c ON l.Numero_club = c.Code
                WHERE l.Nom LIKE ? OR l.Prenom LIKE ? OR l.Matric LIKE ?
                ORDER BY l.Nom, l.Prenom
                LIMIT $limit";

        $searchTerm = "%$query%";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$searchTerm, $searchTerm, $searchTerm]);

        $players = array_map(function ($row) {
            return [
                'matric' => (int) $row['Matric'],
                'nom' => $row['Nom'],
                'prenom' => $row['Prenom'],
                'naissance' => $row['Naissance'],
                'numeroClub' => $row['Numero_club'],
                'club' => $row['Club'],
                'label' => "{$row['Nom']} {$row['Prenom']} ({$row['Matric']}) - {$row['Club']}"
            ];
        }, $result->fetchAllAssociative());

        return $this->json($players);
    }

    // ==================== TEAMS ====================

    /**
     * Rename a team
     */
    #[Route('/teams/rename', name: 'admin_operations_teams_rename', methods: ['POST'])]
    public function renameTeam(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $teamId = (int) ($data['teamId'] ?? 0);
        $newName = trim($data['newName'] ?? '');

        if ($teamId <= 0 || empty($newName)) {
            return $this->json(['message' => 'Team ID and new name are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->teamService->renameTeam($teamId, $newName);
            $this->logActionForEvent('Rename Equipe', null, "ID $teamId => $newName");
            return $this->json(['message' => 'Team renamed successfully']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Merge two teams
     */
    #[Route('/teams/merge', name: 'admin_operations_teams_merge', methods: ['POST'])]
    public function mergeTeams(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $sourceId = (int) ($data['sourceId'] ?? 0);
        $targetId = (int) ($data['targetId'] ?? 0);

        if ($sourceId <= 0 || $targetId <= 0) {
            return $this->json(['message' => 'Source and target team IDs are required'], Response::HTTP_BAD_REQUEST);
        }

        if ($sourceId === $targetId) {
            return $this->json(['message' => 'Source and target cannot be the same'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->teamService->mergeTeams($sourceId, $targetId);
            $this->logActionForEvent('Fusion Equipes', null, "$sourceId => $targetId");
            return $this->json(['message' => 'Teams merged successfully']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Move team to another club
     */
    #[Route('/teams/move', name: 'admin_operations_teams_move', methods: ['POST'])]
    public function moveTeam(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $teamId = (int) ($data['teamId'] ?? 0);
        $clubCode = trim($data['clubCode'] ?? '');

        if ($teamId <= 0 || empty($clubCode)) {
            return $this->json(['message' => 'Team ID and club code are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->teamService->moveTeamToClub($teamId, $clubCode);
            $this->logActionForEvent('Déplacement Equipe', null, "ID $teamId => Club $clubCode");
            return $this->json(['message' => 'Team moved successfully']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Search teams for autocomplete
     */
    #[Route('/autocomplete/teams', name: 'admin_operations_autocomplete_teams', methods: ['GET'])]
    public function searchTeams(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $limit = min(20, max(1, (int) $request->query->get('limit', 10)));

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $sql = "SELECT e.Numero, e.Libelle, e.Code_club, c.Libelle as Club
                FROM kp_equipe e
                LEFT JOIN kp_club c ON e.Code_club = c.Code
                WHERE e.Libelle LIKE ? OR e.Numero LIKE ?
                ORDER BY e.Libelle
                LIMIT $limit";

        $searchTerm = "%$query%";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$searchTerm, $searchTerm]);

        $teams = array_map(function ($row) {
            return [
                'numero' => (int) $row['Numero'],
                'libelle' => $row['Libelle'],
                'codeClub' => $row['Code_club'],
                'club' => $row['Club'],
                'label' => "{$row['Libelle']} ({$row['Numero']}) - {$row['Club']}"
            ];
        }, $result->fetchAllAssociative());

        return $this->json($teams);
    }

    /**
     * Search clubs for autocomplete
     */
    #[Route('/autocomplete/clubs', name: 'admin_operations_autocomplete_clubs', methods: ['GET'])]
    public function searchClubs(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $limit = min(20, max(1, (int) $request->query->get('limit', 10)));

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $sql = "SELECT Code, Libelle, Code_comite_dep
                FROM kp_club
                WHERE Libelle LIKE ? OR Code LIKE ?
                ORDER BY Libelle
                LIMIT $limit";

        $searchTerm = "%$query%";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$searchTerm, $searchTerm]);

        $clubs = array_map(function ($row) {
            return [
                'numero' => $row['Code'],
                'nom' => $row['Libelle'],
                'departement' => $row['Code_comite_dep'],
                'label' => "{$row['Libelle']} ({$row['Code']})"
            ];
        }, $result->fetchAllAssociative());

        return $this->json($clubs);
    }

    // ==================== CODES ====================

    /**
     * Change competition/club code
     */
    #[Route('/codes/change', name: 'admin_operations_codes_change', methods: ['POST'])]
    public function changeCode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $sourceCode = trim($data['sourceCode'] ?? '');
        $targetCode = trim(strtoupper($data['targetCode'] ?? ''));
        $allSeasons = $data['allSeasons'] ?? false;
        $targetExists = $data['targetExists'] ?? false;

        if (strlen($sourceCode) < 2 || strlen($targetCode) < 2) {
            return $this->json(['message' => 'Source and target codes must be at least 2 characters'], Response::HTTP_BAD_REQUEST);
        }

        if ($sourceCode === $targetCode) {
            return $this->json(['message' => 'Source and target codes must be different'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->changeCodeInternal($sourceCode, $targetCode, $allSeasons, $targetExists);
            $mode = $allSeasons ? 'All seasons' : 'Current season';
            $this->logActionForEvent('Change code', null, "$mode: $sourceCode => $targetCode");
            return $this->json(['message' => 'Code changed successfully']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    // ==================== IMPORT/EXPORT ====================

    /**
     * Export event data as JSON
     */
    #[Route('/events/{id}/export', name: 'admin_operations_events_export', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function exportEvent(int $id): StreamedResponse
    {
        $exportData = $this->eventService->exportEvent($id);

        $response = new StreamedResponse(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        });

        $filename = "kp_evenement_{$id}.json";
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }

    /**
     * Import event data from JSON
     */
    #[Route('/events/{id}/import', name: 'admin_operations_events_import', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function importEvent(int $id, Request $request): JsonResponse
    {
        $file = $request->files->get('jsonFile');

        if (!$file) {
            return $this->json(['message' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $content = file_get_contents($file->getPathname());
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json(['message' => 'Invalid JSON file'], Response::HTTP_BAD_REQUEST);
            }

            $this->eventService->importEvent($id, $data);
            $this->logActionForEvent('Import Evenement', $id);
            return $this->json(['message' => 'Event imported successfully']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Import PCE license file
     */
    #[Route('/licenses/import-pce', name: 'admin_operations_licenses_import_pce', methods: ['POST'])]
    public function importPce(Request $request): JsonResponse
    {
        $file = $request->files->get('licenseFile');

        if (!$file) {
            return $this->json(['message' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        // This would require additional implementation similar to the PHP version
        // For now, return a placeholder response
        return $this->json(['message' => 'PCE import not yet implemented'], Response::HTTP_NOT_IMPLEMENTED);
    }

    // ==================== CACHE ====================

    /**
     * Purge cache files
     */
    #[Route('/cache/purge', name: 'admin_operations_cache_purge', methods: ['POST'])]
    public function purgeCache(): JsonResponse
    {
        // In Docker: api2/src/Controller -> dirname 3 levels = /var/www/html, then /live/cache/
        $cacheDir = dirname(__DIR__, 3) . '/live/cache/';

        if (!is_dir($cacheDir)) {
            return $this->json(['message' => 'Cache directory does not exist'], Response::HTTP_NOT_FOUND);
        }

        $now = time();
        $oneYearAgo = $now - (365 * 24 * 60 * 60);
        $twoYearsAgo = $now - (2 * 365 * 24 * 60 * 60);

        $deletedMatchFiles = 0;
        $deletedEventFiles = 0;
        $readFiles = 0;

        $files = scandir($cacheDir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $readFiles++;
            $filePath = $cacheDir . $file;

            if (!is_file($filePath)) {
                continue;
            }

            $fileTime = filemtime($filePath);

            // Match files (older than 1 year)
            if (preg_match('/^\d+_match_(chrono|score|global)\.json$/', $file)) {
                if ($fileTime < $oneYearAgo) {
                    if (unlink($filePath)) {
                        $deletedMatchFiles++;
                    }
                }
            }

            // Event files (older than 2 years)
            if (preg_match('/^event\d+_pitch\d+\.json$/', $file)) {
                if ($fileTime < $twoYearsAgo) {
                    if (unlink($filePath)) {
                        $deletedEventFiles++;
                    }
                }
            }
        }

        $this->logActionForEvent('Purge Cache', null, "Match: $deletedMatchFiles, Event: $deletedEventFiles");

        return $this->json([
            'message' => 'Cache purged successfully',
            'filesRead' => $readFiles,
            'matchFilesDeleted' => $deletedMatchFiles,
            'eventFilesDeleted' => $deletedEventFiles
        ]);
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Change competition code internally
     */
    private function changeCodeInternal(string $sourceCode, string $targetCode, bool $allSeasons, bool $targetExists): void
    {
        // Get active season
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $activeSeason = $result->fetchOne();

        if ($allSeasons) {
            // Check source exists
            $sql = "SELECT COUNT(*) FROM kp_competition WHERE Code = ?";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery([$sourceCode]);
            if ((int) $result->fetchOne() < 1) {
                throw new \Exception('Source code not found');
            }

            // Check target doesn't exist (unless targetExists is true)
            if (!$targetExists) {
                $sql = "SELECT COUNT(*) FROM kp_competition WHERE Code = ?";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery([$targetCode]);
                if ((int) $result->fetchOne() >= 1) {
                    throw new \Exception('Target code already exists');
                }
            }

            $this->connection->beginTransaction();

            try {
                $this->connection->executeStatement("SET FOREIGN_KEY_CHECKS=0");

                // Update kp_competition
                $sql = "UPDATE kp_competition SET Code = ? WHERE Code = ?";
                $this->connection->executeStatement($sql, [$targetCode, $sourceCode]);

                // Update kp_competition_equipe
                $sql = "UPDATE kp_competition_equipe SET Code_compet = ? WHERE Code_compet = ?";
                $this->connection->executeStatement($sql, [$targetCode, $sourceCode]);

                // Update kp_journee
                $sql = "UPDATE kp_journee SET Code_competition = ? WHERE Code_competition = ?";
                $this->connection->executeStatement($sql, [$targetCode, $sourceCode]);

                // Update kp_user filters
                $sql = "UPDATE kp_user
                        SET Filtre_competition = REPLACE(Filtre_competition, ?, ?),
                            Filtre_competition_sql = REPLACE(Filtre_competition_sql, ?, ?)
                        WHERE Filtre_competition LIKE ?";
                $this->connection->executeStatement($sql, [
                    "|$sourceCode|", "|$targetCode|",
                    "'$sourceCode'", "'$targetCode'",
                    "%|$sourceCode|%"
                ]);

                $this->connection->executeStatement("SET FOREIGN_KEY_CHECKS=1");
                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollBack();
                throw $e;
            }
        } else {
            // Single season update
            $sql = "SELECT COUNT(*) FROM kp_competition WHERE Code = ? AND Code_saison = ?";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery([$sourceCode, $activeSeason]);
            if ((int) $result->fetchOne() !== 1) {
                throw new \Exception('Source code not found in current season');
            }

            $sql = "SELECT COUNT(*) FROM kp_competition WHERE Code = ? AND Code_saison = ?";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery([$targetCode, $activeSeason]);
            if ((int) $result->fetchOne() >= 1) {
                throw new \Exception('Target code already exists in current season');
            }

            $this->connection->beginTransaction();

            try {
                $this->connection->executeStatement("SET FOREIGN_KEY_CHECKS=0");

                // Update kp_competition
                $sql = "UPDATE kp_competition SET Code = ? WHERE Code = ? AND Code_saison = ?";
                $this->connection->executeStatement($sql, [$targetCode, $sourceCode, $activeSeason]);

                // Update kp_competition_equipe
                $sql = "UPDATE kp_competition_equipe SET Code_compet = ? WHERE Code_compet = ? AND Code_saison = ?";
                $this->connection->executeStatement($sql, [$targetCode, $sourceCode, $activeSeason]);

                // Update kp_journee
                $sql = "UPDATE kp_journee SET Code_competition = ? WHERE Code_competition = ? AND Code_saison = ?";
                $this->connection->executeStatement($sql, [$targetCode, $sourceCode, $activeSeason]);

                // Update kp_user filters (only for users with this season filter)
                $sql = "UPDATE kp_user
                        SET Filtre_competition = REPLACE(Filtre_competition, ?, ?),
                            Filtre_competition_sql = REPLACE(Filtre_competition_sql, ?, ?)
                        WHERE Filtre_competition LIKE ?
                        AND (Filtre_saison LIKE ? OR Filtre_saison = '')";
                $this->connection->executeStatement($sql, [
                    "|$sourceCode|", "|$targetCode|",
                    "'$sourceCode'", "'$targetCode'",
                    "%|$sourceCode|%",
                    "%|$activeSeason|%"
                ]);

                $this->connection->executeStatement("SET FOREIGN_KEY_CHECKS=1");
                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollBack();
                throw $e;
            }
        }
    }

}
