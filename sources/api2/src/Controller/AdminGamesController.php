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
 * Admin Games Controller
 *
 * CRUD operations for match management (kp_match table)
 */
#[Route('/admin/games')]
#[IsGranted('ROLE_USER')]
#[OA\Tag(name: '31. App4 - Games')]
class AdminGamesController extends AbstractController
{
    use AdminLoggableTrait;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * List games with pagination and filters
     */
    #[Route('', name: 'admin_games_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        // Pagination (limit=0 means all)
        $page = max(1, (int) $request->query->get('page', 1));
        $limitParam = (int) $request->query->get('limit', 50);
        $limit = $limitParam > 0 ? min(2000, $limitParam) : 0;
        $offset = $limit > 0 ? ($page - 1) * $limit : 0;

        // Filters
        $season = $request->query->get('season', '');
        $competitions = $request->query->get('competitions', '');
        $eventId = $request->query->get('event', '');
        $tour = $request->query->get('tour', '');
        $journeeId = $request->query->get('journee', '');
        $date = $request->query->get('date', '');
        $terrain = $request->query->get('terrain', '');
        $sort = $request->query->get('sort', 'date_time_terrain');
        $search = $request->query->get('search', '');
        $unlocked = $request->query->get('unlocked', '');

        // Fallback to active season
        if (empty($season)) {
            $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
            $result = $this->connection->executeQuery($sql);
            $season = $result->fetchOne() ?: '';
        }

        if (empty($season)) {
            return $this->json(['games' => [], 'total' => 0, 'page' => 1, 'totalPages' => 0, 'phaseLibelle' => false, 'dates' => []]);
        }

        // Build WHERE clause
        $where = ['j.Code_saison = ?'];
        $params = [$season];

        // Competition filter (multi, comma-separated)
        $competitionCodes = !empty($competitions) ? array_filter(explode(',', $competitions)) : [];
        if (count($competitionCodes) > 0) {
            $placeholders = implode(',', array_fill(0, count($competitionCodes), '?'));
            $where[] = "j.Code_competition IN ($placeholders)";
            $params = array_merge($params, $competitionCodes);
        }

        // User competition filter
        if ($user) {
            $allowedCompetitions = $user->getAllowedCompetitions();
            if ($allowedCompetitions !== null && count($allowedCompetitions) > 0) {
                $placeholders = implode(',', array_fill(0, count($allowedCompetitions), '?'));
                $where[] = "j.Code_competition IN ($placeholders)";
                $params = array_merge($params, $allowedCompetitions);
            }
        }

        // Event filter
        $joinEvent = '';
        if (!empty($eventId) && $eventId !== '-1') {
            $joinEvent = 'INNER JOIN kp_evenement_journee ej ON ej.Id_journee = j.Id';
            $where[] = 'ej.Id_evenement = ?';
            $params[] = (int) $eventId;
        }

        // Tour/Etape filter
        if (!empty($tour) && is_numeric($tour)) {
            $where[] = 'j.Etape = ?';
            $params[] = (int) $tour;
        }

        // Journee filter
        if (!empty($journeeId) && $journeeId !== '*') {
            $where[] = 'm.Id_journee = ?';
            $params[] = (int) $journeeId;
        }

        // Date filter
        if (!empty($date)) {
            $where[] = 'm.Date_match = ?';
            $params[] = $date;
        }

        // Terrain filter
        if (!empty($terrain)) {
            $where[] = 'm.Terrain = ?';
            $params[] = $terrain;
        }

        // Unlocked only filter
        if ($unlocked === '1') {
            $where[] = "(m.Validation IS NULL OR m.Validation != 'O')";
        }

        // Profile 7 restrictions: only published games from published gamedays, skip ATT competitions
        if ($user && $user->getEffectiveNiveau() === 7) {
            $where[] = "m.Publication = 'O'";
            $where[] = "j.Publication = 'O'";
            $where[] = "EXISTS (SELECT 1 FROM kp_competition c2 WHERE c2.Code = j.Code_competition AND c2.Code_saison = j.Code_saison AND c2.Statut != 'ATT')";
        }

        // Search filter
        if (!empty($search)) {
            $where[] = '(m.Libelle LIKE ? OR cea.Libelle LIKE ? OR ceb.Libelle LIKE ? OR m.Arbitre_principal LIKE ? OR m.Arbitre_secondaire LIKE ? OR CAST(m.Numero_ordre AS CHAR) LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Sort
        $orderBy = match ($sort) {
            'competition_date' => 'j.Code_competition, m.Date_match, m.Heure_match, m.Terrain, m.Numero_ordre',
            'competition_phase' => 'j.Code_competition, j.Niveau, j.Phase, m.Heure_match, m.Terrain, m.Numero_ordre',
            'terrain_date' => 'm.Terrain, m.Date_match, m.Heure_match, m.Numero_ordre',
            'number' => 'm.Numero_ordre, m.Date_match, m.Heure_match, m.Terrain',
            default => 'm.Date_match, m.Heure_match, m.Terrain, m.Numero_ordre', // date_time_terrain
        };

        // Count total
        $countSql = "SELECT COUNT(*)
                     FROM kp_match m
                     INNER JOIN kp_journee j ON m.Id_journee = j.Id
                     LEFT JOIN kp_competition_equipe cea ON m.Id_equipeA = cea.Id
                     LEFT JOIN kp_competition_equipe ceb ON m.Id_equipeB = ceb.Id
                     $joinEvent
                     $whereClause";
        $total = (int) $this->connection->prepare($countSql)->executeQuery($params)->fetchOne();

        // Fetch games
        $sql = "SELECT m.Id, m.Id_journee, m.Numero_ordre, m.Date_match, m.Heure_match,
                       m.Libelle, m.Terrain, m.Publication, m.Validation, m.Statut,
                       m.Type, m.Periode, m.ScoreA, m.ScoreB, m.ScoreDetailA, m.ScoreDetailB,
                       m.Imprime, m.CoeffA, m.CoeffB,
                       m.Id_equipeA, m.Id_equipeB,
                       m.Arbitre_principal, m.Matric_arbitre_principal,
                       m.Arbitre_secondaire, m.Matric_arbitre_secondaire,
                       j.Code_competition, j.Phase, j.Niveau, j.Etape, j.Lieu,
                       j.Libelle AS LibelleJournee,
                       c.Soustitre2, c.Code_typeclt,
                       cea.Libelle AS equipeA,
                       ceb.Libelle AS equipeB
                FROM kp_match m
                INNER JOIN kp_journee j ON m.Id_journee = j.Id
                LEFT JOIN kp_competition c ON j.Code_competition = c.Code AND j.Code_saison = c.Code_saison
                LEFT JOIN kp_competition_equipe cea ON m.Id_equipeA = cea.Id
                LEFT JOIN kp_competition_equipe ceb ON m.Id_equipeB = ceb.Id
                $joinEvent
                $whereClause
                ORDER BY $orderBy"
                . ($limit > 0 ? " LIMIT " . (int) $limit . " OFFSET " . (int) $offset : '');

        $rows = $this->connection->prepare($sql)->executeQuery($params)->fetchAllAssociative();

        // Check user authorization per match (based on journee filter)
        $allowedJournees = $user?->getAllowedJournees();

        // Detect phaseLibelle mode
        $singleCompetition = count($competitionCodes) === 1 ? $competitionCodes[0] : '';
        $phaseLibelle = $this->detectPhaseLibelle($singleCompetition, $season, $rows);

        // Collect distinct dates
        $datesSql = "SELECT DISTINCT m.Date_match
                     FROM kp_match m
                     INNER JOIN kp_journee j ON m.Id_journee = j.Id
                     LEFT JOIN kp_competition_equipe cea ON m.Id_equipeA = cea.Id
                     LEFT JOIN kp_competition_equipe ceb ON m.Id_equipeB = ceb.Id
                     $joinEvent
                     $whereClause
                     AND m.Date_match IS NOT NULL
                     ORDER BY m.Date_match";
        $datesRows = $this->connection->prepare($datesSql)->executeQuery($params)->fetchAllAssociative();
        $dates = array_map(fn($r) => $r['Date_match'], $datesRows);

        $games = array_map(function ($row) use ($allowedJournees) {
            $authorized = $allowedJournees === null || in_array((int) $row['Id_journee'], $allowedJournees);

            return [
                'id' => (int) $row['Id'],
                'idJournee' => (int) $row['Id_journee'],
                'numeroOrdre' => $row['Numero_ordre'] !== null ? (int) $row['Numero_ordre'] : null,
                'dateMatch' => $row['Date_match'],
                'heureMatch' => $row['Heure_match'],
                'libelle' => $row['Libelle'],
                'terrain' => $row['Terrain'],
                'publication' => $row['Publication'] ?? 'N',
                'validation' => $row['Validation'] ?? 'N',
                'statut' => $row['Statut'] ?? 'ATT',
                'type' => $row['Type'] ?? 'C',
                'periode' => $row['Periode'],
                'scoreA' => $row['ScoreA'],
                'scoreB' => $row['ScoreB'],
                'scoreDetailA' => $row['ScoreDetailA'],
                'scoreDetailB' => $row['ScoreDetailB'],
                'imprime' => $row['Imprime'] ?? 'N',
                'coeffA' => (int) ($row['CoeffA'] ?? 1),
                'coeffB' => (int) ($row['CoeffB'] ?? 1),
                'idEquipeA' => $row['Id_equipeA'] !== null ? (int) $row['Id_equipeA'] : null,
                'equipeA' => $row['equipeA'],
                'idEquipeB' => $row['Id_equipeB'] !== null ? (int) $row['Id_equipeB'] : null,
                'equipeB' => $row['equipeB'],
                'arbitrePrincipal' => $row['Arbitre_principal'],
                'matricArbitrePrincipal' => (int) ($row['Matric_arbitre_principal'] ?? 0),
                'arbitreSecondaire' => $row['Arbitre_secondaire'],
                'matricArbitreSecondaire' => (int) ($row['Matric_arbitre_secondaire'] ?? 0),
                'codeCompetition' => $row['Code_competition'],
                'phase' => $row['Phase'],
                'niveau' => $row['Niveau'] !== null ? (int) $row['Niveau'] : null,
                'etape' => (int) $row['Etape'],
                'lieu' => $row['Lieu'],
                'libelleJournee' => $row['LibelleJournee'],
                'soustitre2' => $row['Soustitre2'],
                'codeTypeclt' => $row['Code_typeclt'],
                'authorized' => $authorized,
            ];
        }, $rows);

        return $this->json([
            'games' => $games,
            'total' => $total,
            'page' => $page,
            'totalPages' => $limit > 0 ? (int) ceil($total / $limit) : 1,
            'phaseLibelle' => $phaseLibelle,
            'dates' => $dates,
        ]);
    }

    /**
     * Get a single game
     */
    #[Route('/{id}', name: 'admin_games_get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(int $id): JsonResponse
    {
        $sql = "SELECT m.*, j.Code_competition, j.Phase, j.Niveau, j.Etape, j.Lieu,
                       j.Libelle AS LibelleJournee, j.Code_saison, j.Type AS JourneeType,
                       c.Soustitre2, c.Code_typeclt,
                       cea.Libelle AS equipeA, ceb.Libelle AS equipeB
                FROM kp_match m
                INNER JOIN kp_journee j ON m.Id_journee = j.Id
                LEFT JOIN kp_competition c ON j.Code_competition = c.Code AND j.Code_saison = c.Code_saison
                LEFT JOIN kp_competition_equipe cea ON m.Id_equipeA = cea.Id
                LEFT JOIN kp_competition_equipe ceb ON m.Id_equipeB = ceb.Id
                WHERE m.Id = ?";
        $row = $this->connection->prepare($sql)->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => (int) $row['Id'],
            'idJournee' => (int) $row['Id_journee'],
            'numeroOrdre' => $row['Numero_ordre'] !== null ? (int) $row['Numero_ordre'] : null,
            'dateMatch' => $row['Date_match'],
            'heureMatch' => $row['Heure_match'],
            'libelle' => $row['Libelle'],
            'terrain' => $row['Terrain'],
            'publication' => $row['Publication'] ?? 'N',
            'validation' => $row['Validation'] ?? 'N',
            'statut' => $row['Statut'] ?? 'ATT',
            'type' => $row['Type'] ?? 'C',
            'periode' => $row['Periode'],
            'scoreA' => $row['ScoreA'],
            'scoreB' => $row['ScoreB'],
            'scoreDetailA' => $row['ScoreDetailA'],
            'scoreDetailB' => $row['ScoreDetailB'],
            'imprime' => $row['Imprime'] ?? 'N',
            'coeffA' => (int) ($row['CoeffA'] ?? 1),
            'coeffB' => (int) ($row['CoeffB'] ?? 1),
            'idEquipeA' => $row['Id_equipeA'] !== null ? (int) $row['Id_equipeA'] : null,
            'equipeA' => $row['equipeA'],
            'idEquipeB' => $row['Id_equipeB'] !== null ? (int) $row['Id_equipeB'] : null,
            'equipeB' => $row['equipeB'],
            'arbitrePrincipal' => $row['Arbitre_principal'],
            'matricArbitrePrincipal' => (int) ($row['Matric_arbitre_principal'] ?? 0),
            'arbitreSecondaire' => $row['Arbitre_secondaire'],
            'matricArbitreSecondaire' => (int) ($row['Matric_arbitre_secondaire'] ?? 0),
            'codeCompetition' => $row['Code_competition'],
            'phase' => $row['Phase'],
            'niveau' => $row['Niveau'] !== null ? (int) $row['Niveau'] : null,
            'etape' => (int) $row['Etape'],
            'lieu' => $row['Lieu'],
            'libelleJournee' => $row['LibelleJournee'],
            'soustitre2' => $row['Soustitre2'],
            'codeTypeclt' => $row['Code_typeclt'],
        ]);
    }

    /**
     * Create a new game
     */
    #[Route('', name: 'admin_games_create', methods: ['POST'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function create(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Invalid request body'], Response::HTTP_BAD_REQUEST);
        }

        $idJournee = (int) ($data['idJournee'] ?? 0);
        $dateMatch = $data['dateMatch'] ?? '';

        if ($idJournee <= 0) {
            return $this->json(['message' => 'Journée is required'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($dateMatch)) {
            return $this->json(['message' => 'Date is required'], Response::HTTP_BAD_REQUEST);
        }

        // Verify journee exists
        $journee = $this->connection->prepare(
            "SELECT Id, Code_competition, Code_saison, Type FROM kp_journee WHERE Id = ?"
        )->executeQuery([$idJournee])->fetchAssociative();

        if (!$journee) {
            return $this->json(['message' => 'Journée not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized($idJournee, $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($journee['Code_competition'], $journee['Code_saison'])) return $err;

        // Get next ID
        $nextId = (int) $this->connection->executeQuery("SELECT COALESCE(MAX(Id), 0) + 1 FROM kp_match")->fetchOne();

        $insertData = [
            'Id' => $nextId,
            'Id_journee' => $idJournee,
            'Date_match' => $dateMatch,
            'Heure_match' => !empty($data['heureMatch']) ? substr($data['heureMatch'], 0, 5) : null,
            'Numero_ordre' => isset($data['numeroOrdre']) && $data['numeroOrdre'] !== '' ? (int) $data['numeroOrdre'] : null,
            'Terrain' => !empty($data['terrain']) ? substr($data['terrain'], 0, 12) : null,
            'Type' => in_array($data['type'] ?? '', ['C', 'E']) ? $data['type'] : ($journee['Type'] ?? 'C'),
            'Libelle' => !empty($data['libelle']) ? substr($data['libelle'], 0, 30) : null,
            'Id_equipeA' => !empty($data['idEquipeA']) ? (int) $data['idEquipeA'] : null,
            'Id_equipeB' => !empty($data['idEquipeB']) ? (int) $data['idEquipeB'] : null,
            'CoeffA' => isset($data['coeffA']) ? (string) (int) $data['coeffA'] : '1',
            'CoeffB' => isset($data['coeffB']) ? (string) (int) $data['coeffB'] : '1',
            'Arbitre_principal' => !empty($data['arbitrePrincipal']) ? substr($data['arbitrePrincipal'], 0, 60) : null,
            'Matric_arbitre_principal' => isset($data['matricArbitrePrincipal']) ? (int) $data['matricArbitrePrincipal'] : 0,
            'Arbitre_secondaire' => !empty($data['arbitreSecondaire']) ? substr($data['arbitreSecondaire'], 0, 60) : null,
            'Matric_arbitre_secondaire' => isset($data['matricArbitreSecondaire']) ? (int) $data['matricArbitreSecondaire'] : 0,
            'Publication' => 'N',
            'Validation' => 'N',
            'Statut' => 'ATT',
            'Imprime' => 'N',
            'Code_uti' => $user?->getUserIdentifier(),
        ];

        $this->connection->insert('kp_match', $insertData);

        $this->logActionForMatch('Ajout match', $journee['Code_saison'], $journee['Code_competition'], $idJournee, $nextId);

        return $this->json(['id' => $nextId, 'message' => 'Game created'], Response::HTTP_CREATED);
    }

    /**
     * Update a game (full form)
     */
    #[Route('/{id}', name: 'admin_games_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function update(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $existing = $this->connection->prepare(
            "SELECT m.Id, m.Id_journee, j.Code_competition, j.Code_saison FROM kp_match m INNER JOIN kp_journee j ON m.Id_journee = j.Id WHERE m.Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$existing) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized((int) $existing['Id_journee'], $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($existing['Code_competition'], $existing['Code_saison'])) return $err;

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Invalid request body'], Response::HTTP_BAD_REQUEST);
        }

        $updateData = [];

        if (array_key_exists('idJournee', $data) && $data['idJournee']) {
            $updateData['Id_journee'] = (int) $data['idJournee'];
        }
        if (array_key_exists('dateMatch', $data)) {
            $updateData['Date_match'] = !empty($data['dateMatch']) ? $data['dateMatch'] : null;
        }
        if (array_key_exists('heureMatch', $data)) {
            $updateData['Heure_match'] = !empty($data['heureMatch']) ? substr($data['heureMatch'], 0, 5) : null;
        }
        if (array_key_exists('numeroOrdre', $data)) {
            $updateData['Numero_ordre'] = $data['numeroOrdre'] !== '' && $data['numeroOrdre'] !== null ? (int) $data['numeroOrdre'] : null;
        }
        if (array_key_exists('terrain', $data)) {
            $updateData['Terrain'] = !empty($data['terrain']) ? substr($data['terrain'], 0, 12) : null;
        }
        if (array_key_exists('type', $data) && in_array($data['type'], ['C', 'E'])) {
            $updateData['Type'] = $data['type'];
        }
        if (array_key_exists('libelle', $data)) {
            $updateData['Libelle'] = !empty($data['libelle']) ? substr($data['libelle'], 0, 30) : null;
        }
        if (array_key_exists('idEquipeA', $data)) {
            $updateData['Id_equipeA'] = !empty($data['idEquipeA']) ? (int) $data['idEquipeA'] : null;
        }
        if (array_key_exists('idEquipeB', $data)) {
            $updateData['Id_equipeB'] = !empty($data['idEquipeB']) ? (int) $data['idEquipeB'] : null;
        }
        if (array_key_exists('coeffA', $data)) {
            $updateData['CoeffA'] = (string) (int) ($data['coeffA'] ?? 1);
        }
        if (array_key_exists('coeffB', $data)) {
            $updateData['CoeffB'] = (string) (int) ($data['coeffB'] ?? 1);
        }
        if (array_key_exists('arbitrePrincipal', $data)) {
            $updateData['Arbitre_principal'] = !empty($data['arbitrePrincipal']) ? substr($data['arbitrePrincipal'], 0, 60) : null;
        }
        if (array_key_exists('matricArbitrePrincipal', $data)) {
            $updateData['Matric_arbitre_principal'] = (int) ($data['matricArbitrePrincipal'] ?? 0);
        }
        if (array_key_exists('arbitreSecondaire', $data)) {
            $updateData['Arbitre_secondaire'] = !empty($data['arbitreSecondaire']) ? substr($data['arbitreSecondaire'], 0, 60) : null;
        }
        if (array_key_exists('matricArbitreSecondaire', $data)) {
            $updateData['Matric_arbitre_secondaire'] = (int) ($data['matricArbitreSecondaire'] ?? 0);
        }

        if (empty($updateData)) {
            return $this->json(['message' => 'No fields to update'], Response::HTTP_BAD_REQUEST);
        }

        $updateData['Code_uti'] = $user?->getUserIdentifier();
        $this->connection->update('kp_match', $updateData, ['Id' => $id]);

        $this->logActionForMatch('Modification match', $existing['Code_saison'], $existing['Code_competition'], (int) $existing['Id_journee'], $id);

        return $this->json(['message' => 'Game updated']);
    }

    /**
     * Delete a game
     */
    #[Route('/{id}', name: 'admin_games_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function delete(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $row = $this->connection->prepare(
            "SELECT m.Id, m.Id_journee, m.Validation, m.ScoreA, m.ScoreB, j.Code_competition, j.Code_saison
             FROM kp_match m INNER JOIN kp_journee j ON m.Id_journee = j.Id
             WHERE m.Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized((int) $row['Id_journee'], $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($row['Code_competition'], $row['Code_saison'])) return $err;

        // Check locked
        if ($row['Validation'] === 'O') {
            return $this->json([
                'message' => 'Game is locked. Unlock before deleting.',
                'code' => 'LOCKED',
            ], Response::HTTP_CONFLICT);
        }

        // Check for score
        if (($row['ScoreA'] !== null && $row['ScoreA'] !== '') || ($row['ScoreB'] !== null && $row['ScoreB'] !== '')) {
            return $this->json([
                'message' => 'Game has a score. Cannot delete.',
                'code' => 'HAS_SCORE',
            ], Response::HTTP_CONFLICT);
        }

        // Check for match events
        $eventCount = (int) $this->connection->prepare(
            "SELECT COUNT(*) FROM kp_match_detail WHERE Id_match = ?"
        )->executeQuery([$id])->fetchOne();

        if ($eventCount > 0) {
            return $this->json([
                'message' => 'Game events still exist. Cannot delete.',
                'code' => 'HAS_EVENTS',
            ], Response::HTTP_CONFLICT);
        }

        // Cascade delete
        $this->connection->executeStatement("DELETE FROM kp_match_joueur WHERE Id_match = ?", [$id]);
        $this->connection->executeStatement("DELETE FROM kp_chrono WHERE IdMatch = ?", [$id]);
        $this->connection->executeStatement("DELETE FROM kp_match WHERE Id = ? AND Validation != 'O'", [$id]);

        $this->logActionForMatch('Suppression match', $row['Code_saison'], $row['Code_competition'], (int) $row['Id_journee'], $id);

        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Inline field update
     */
    #[Route('/{id}/inline', name: 'admin_games_inline', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function inlineUpdate(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $field = $data['field'] ?? '';
        $value = $data['value'] ?? '';

        // Score fields editable by profile <= 9, other fields by <= 6
        $scoreFields = ['ScoreA', 'ScoreB'];
        $otherFields = ['Numero_ordre', 'Date_match', 'Heure_match', 'Libelle', 'Terrain'];
        $refereeFields = ['Arbitre_principal', 'Arbitre_secondaire'];

        if (in_array($field, $scoreFields)) {
            if ($user && $user->getNiveau() > 9) {
                return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
            }
        } elseif (in_array($field, $otherFields) || in_array($field, $refereeFields)) {
            if ($user && $user->getNiveau() > 6) {
                return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
            }
        } else {
            return $this->json(['message' => 'Field not allowed'], Response::HTTP_BAD_REQUEST);
        }

        // Verify game exists
        $row = $this->connection->prepare(
            "SELECT m.Id, m.Id_journee, m.Validation, j.Code_saison, j.Code_competition FROM kp_match m INNER JOIN kp_journee j ON m.Id_journee = j.Id WHERE m.Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized((int) $row['Id_journee'], $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($row['Code_competition'], $row['Code_saison'])) return $err;

        // Validate and format value
        if (in_array($field, ['Numero_ordre'])) {
            $value = $value !== '' ? (int) $value : null;
        } elseif ($field === 'Date_match') {
            if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $value, $m)) {
                $value = "$m[3]-$m[2]-$m[1]";
            }
            if ($value !== '' && !preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                return $this->json(['message' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
            }
            if ($value === '') {
                $value = null;
            }
        } elseif ($field === 'Heure_match') {
            $value = $value !== '' ? substr($value, 0, 5) : null;
        } elseif ($field === 'Terrain') {
            $value = $value !== '' ? substr($value, 0, 12) : null;
        } elseif ($field === 'Libelle') {
            $value = $value !== '' ? substr($value, 0, 30) : null;
        } elseif (in_array($field, ['ScoreA', 'ScoreB'])) {
            $value = $value !== '' ? substr($value, 0, 4) : null;
        } elseif (in_array($field, $refereeFields)) {
            $value = $value !== '' ? substr($value, 0, 60) : null;
        }

        $updateData = [$field => $value, 'Code_uti' => $user?->getUserIdentifier()];

        // For referee fields, also update the associated matricule
        if (in_array($field, $refereeFields)) {
            $matric = isset($data['matric']) ? (int) $data['matric'] : 0;
            $matricField = $field === 'Arbitre_principal' ? 'Matric_arbitre_principal' : 'Matric_arbitre_secondaire';
            $updateData[$matricField] = $matric;
        }

        $this->connection->update('kp_match', $updateData, ['Id' => $id]);

        $this->logActionForMatch('Modification match inline', $row['Code_saison'], $row['Code_competition'], (int) $row['Id_journee'], $id, $field);

        return $this->json(['id' => $id, 'field' => $field, 'value' => $value]);
    }

    /**
     * Toggle publication
     */
    #[Route('/{id}/publication', name: 'admin_games_toggle_publication', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function togglePublication(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $row = $this->connection->prepare(
            "SELECT m.Id, m.Id_journee, m.Publication, j.Code_saison, j.Code_competition FROM kp_match m INNER JOIN kp_journee j ON m.Id_journee = j.Id WHERE m.Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized((int) $row['Id_journee'], $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($row['Code_competition'], $row['Code_saison'])) return $err;

        $newValue = $row['Publication'] === 'O' ? 'N' : 'O';
        $this->connection->update('kp_match', ['Publication' => $newValue, 'Code_uti' => $user?->getUserIdentifier()], ['Id' => $id]);

        $this->logActionForMatch('Publication match', $row['Code_saison'], $row['Code_competition'], (int) $row['Id_journee'], $id, $newValue);

        return $this->json(['id' => $id, 'publication' => $newValue]);
    }

    /**
     * Toggle validation/lock
     */
    #[Route('/{id}/validation', name: 'admin_games_toggle_validation', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function toggleValidation(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $row = $this->connection->prepare(
            "SELECT m.Id, m.Id_journee, m.Validation, j.Code_saison, j.Code_competition FROM kp_match m INNER JOIN kp_journee j ON m.Id_journee = j.Id WHERE m.Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized((int) $row['Id_journee'], $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($row['Code_competition'], $row['Code_saison'])) return $err;

        $newValue = $row['Validation'] === 'O' ? 'N' : 'O';
        $this->connection->update('kp_match', ['Validation' => $newValue, 'Code_uti' => $user?->getUserIdentifier()], ['Id' => $id]);

        $this->logActionForMatch('Validation match', $row['Code_saison'], $row['Code_competition'], (int) $row['Id_journee'], $id, $newValue);

        return $this->json(['id' => $id, 'validation' => $newValue]);
    }

    /**
     * Toggle type C/E
     */
    #[Route('/{id}/type', name: 'admin_games_toggle_type', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function toggleType(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $row = $this->connection->prepare(
            "SELECT m.Id, m.Id_journee, m.Type, j.Code_saison, j.Code_competition FROM kp_match m INNER JOIN kp_journee j ON m.Id_journee = j.Id WHERE m.Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized((int) $row['Id_journee'], $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($row['Code_competition'], $row['Code_saison'])) return $err;

        $newValue = $row['Type'] === 'C' ? 'E' : 'C';
        $this->connection->update('kp_match', ['Type' => $newValue, 'Code_uti' => $user?->getUserIdentifier()], ['Id' => $id]);

        $this->logActionForMatch('Type match', $row['Code_saison'], $row['Code_competition'], (int) $row['Id_journee'], $id, $newValue);

        return $this->json(['id' => $id, 'type' => $newValue]);
    }

    /**
     * Cycle statut ATT → ON → END → ATT
     */
    #[Route('/{id}/statut', name: 'admin_games_toggle_statut', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function toggleStatut(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $row = $this->connection->prepare(
            "SELECT m.Id, m.Id_journee, m.Statut, j.Code_saison, j.Code_competition FROM kp_match m INNER JOIN kp_journee j ON m.Id_journee = j.Id WHERE m.Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized((int) $row['Id_journee'], $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($row['Code_competition'], $row['Code_saison'])) return $err;

        $newValue = match ($row['Statut'] ?? 'ATT') {
            'ATT' => 'ON',
            'ON' => 'END',
            'END' => 'ATT',
            default => 'ON',
        };

        $this->connection->update('kp_match', ['Statut' => $newValue, 'Code_uti' => $user?->getUserIdentifier()], ['Id' => $id]);

        $this->logActionForMatch('Statut match', $row['Code_saison'], $row['Code_competition'], (int) $row['Id_journee'], $id, $newValue);

        return $this->json(['id' => $id, 'statut' => $newValue]);
    }

    /**
     * Toggle printed
     */
    #[Route('/{id}/printed', name: 'admin_games_toggle_printed', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function togglePrinted(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $row = $this->connection->prepare(
            "SELECT m.Id, m.Id_journee, m.Imprime, j.Code_saison, j.Code_competition FROM kp_match m INNER JOIN kp_journee j ON m.Id_journee = j.Id WHERE m.Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized((int) $row['Id_journee'], $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($row['Code_competition'], $row['Code_saison'])) return $err;

        $newValue = $row['Imprime'] === 'O' ? 'N' : 'O';
        $this->connection->update('kp_match', ['Imprime' => $newValue, 'Code_uti' => $user?->getUserIdentifier()], ['Id' => $id]);

        $this->logActionForMatch('Impression match', $row['Code_saison'], $row['Code_competition'], (int) $row['Id_journee'], $id, $newValue);

        return $this->json(['id' => $id, 'imprime' => $newValue]);
    }

    /**
     * Change team A or B
     */
    #[Route('/{id}/team', name: 'admin_games_change_team', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function changeTeam(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $team = $data['team'] ?? ''; // 'A' or 'B'
        $idEquipe = isset($data['idEquipe']) ? (int) $data['idEquipe'] : null;

        if (!in_array($team, ['A', 'B'])) {
            return $this->json(['message' => 'Invalid team (A or B)'], Response::HTTP_BAD_REQUEST);
        }

        $column = $team === 'A' ? 'Id_equipeA' : 'Id_equipeB';
        $row = $this->connection->prepare(
            "SELECT m.Id, m.Id_journee, m.{$column} AS currentTeamId, j.Code_saison, j.Code_competition FROM kp_match m INNER JOIN kp_journee j ON m.Id_journee = j.Id WHERE m.Id = ?"
        )->executeQuery([$id])->fetchAssociative();
        if (!$row) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized((int) $row['Id_journee'], $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($row['Code_competition'], $row['Code_saison'])) return $err;

        $updateVal = $idEquipe && $idEquipe > 0 ? $idEquipe : null;
        $currentTeamId = $row['currentTeamId'] ? (int) $row['currentTeamId'] : null;

        // If team changed or was removed, clear the roster for that side
        if ($updateVal !== $currentTeamId) {
            $this->connection->executeStatement(
                "DELETE FROM kp_match_joueur WHERE Id_match = ? AND Equipe = ?",
                [$id, $team]
            );
        }

        $this->connection->update('kp_match', [$column => $updateVal, 'Code_uti' => $user?->getUserIdentifier()], ['Id' => $id]);

        // Get team name
        $teamName = null;

        if ($updateVal) {
            $teamName = $this->connection->prepare(
                "SELECT Libelle FROM kp_competition_equipe WHERE Id = ?"
            )->executeQuery([$updateVal])->fetchOne() ?: null;
        }

        $this->logActionForMatch('Changement équipe match', $row['Code_saison'], $row['Code_competition'], (int) $row['Id_journee'], $id, "équipe $team -> " . ($teamName ?? 'vide'));

        return $this->json(['id' => $id, 'team' => $team, 'idEquipe' => $updateVal, 'equipe' => $teamName]);
    }

    /**
     * Move game to another journee
     */
    #[Route('/{id}/journee', name: 'admin_games_change_journee', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function changeJournee(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $newJourneeId = (int) ($data['idJournee'] ?? 0);

        if ($newJourneeId <= 0) {
            return $this->json(['message' => 'Journée ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $row = $this->connection->prepare(
            "SELECT m.Id, m.Id_journee, j.Code_saison, j.Code_competition FROM kp_match m INNER JOIN kp_journee j ON m.Id_journee = j.Id WHERE m.Id = ?"
        )->executeQuery([$id])->fetchAssociative();
        if (!$row) {
            return $this->json(['message' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }

        if ($err = $this->assertJourneeAuthorized((int) $row['Id_journee'], $user)) return $err;
        if ($err = $this->assertCompetitionNotEnded($row['Code_competition'], $row['Code_saison'])) return $err;

        $this->connection->update('kp_match', ['Id_journee' => $newJourneeId, 'Code_uti' => $user?->getUserIdentifier()], ['Id' => $id]);

        $this->logActionForMatch('Changement journée match', $row['Code_saison'], $row['Code_competition'], (int) $row['Id_journee'], $id, "-> journée $newJourneeId");

        return $this->json(['id' => $id, 'idJournee' => $newJourneeId]);
    }

    /**
     * Bulk delete
     */
    #[Route('/bulk', name: 'admin_games_bulk_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkDelete(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        $deleted = 0;
        $skipped = [];

        foreach ($ids as $matchId) {
            // Check match events
            $eventCount = (int) $this->connection->prepare(
                "SELECT COUNT(*) FROM kp_match_detail WHERE Id_match = ?"
            )->executeQuery([$matchId])->fetchOne();

            if ($eventCount > 0) {
                $skipped[] = ['id' => $matchId, 'reason' => 'has_events'];
                continue;
            }

            // Cascade delete
            $this->connection->executeStatement("DELETE FROM kp_match_joueur WHERE Id_match = ?", [$matchId]);
            $this->connection->executeStatement("DELETE FROM kp_chrono WHERE IdMatch = ?", [$matchId]);
            $affected = $this->connection->executeStatement(
                "DELETE FROM kp_match WHERE Id = ? AND Validation != 'O'",
                [$matchId]
            );

            if ($affected > 0) {
                $deleted++;
            } else {
                $skipped[] = ['id' => $matchId, 'reason' => 'locked'];
            }
        }

        if ($deleted > 0) {
            $this->logActionForSeason('Suppression masse matchs', null, "$deleted match(s) supprimé(s)");
        }

        return $this->json([
            'deleted' => $deleted,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Bulk toggle publication
     */
    #[Route('/bulk/publication', name: 'admin_games_bulk_publication', methods: ['PATCH'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkPublication(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Toggle: if any are unpublished, publish all. Otherwise unpublish all.
        $unpublishedCount = (int) $this->connection->prepare(
            "SELECT COUNT(*) FROM kp_match WHERE Id IN ($placeholders) AND (Publication IS NULL OR Publication = 'N')"
        )->executeQuery($ids)->fetchOne();

        $newValue = $unpublishedCount > 0 ? 'O' : 'N';

        $this->connection->prepare(
            "UPDATE kp_match SET Publication = ?, Code_uti = ? WHERE Id IN ($placeholders)"
        )->executeStatement(array_merge([$newValue, $user?->getUserIdentifier()], $ids));

        $this->logActionForSeason('Publication masse matchs', null, count($ids) . " match(s) -> $newValue");

        return $this->json(['updated' => count($ids), 'publication' => $newValue]);
    }

    /**
     * Bulk toggle validation
     */
    #[Route('/bulk/validation', name: 'admin_games_bulk_validation', methods: ['PATCH'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkValidation(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $unlockedCount = (int) $this->connection->prepare(
            "SELECT COUNT(*) FROM kp_match WHERE Id IN ($placeholders) AND (Validation IS NULL OR Validation = 'N')"
        )->executeQuery($ids)->fetchOne();

        $newValue = $unlockedCount > 0 ? 'O' : 'N';

        $this->connection->prepare(
            "UPDATE kp_match SET Validation = ?, Code_uti = ? WHERE Id IN ($placeholders)"
        )->executeStatement(array_merge([$newValue, $user?->getUserIdentifier()], $ids));

        $this->logActionForSeason('Validation masse matchs', null, count($ids) . " match(s) -> $newValue");

        return $this->json(['updated' => count($ids), 'validation' => $newValue]);
    }

    /**
     * Bulk lock + publish
     */
    #[Route('/bulk/lock-publish', name: 'admin_games_bulk_lock_publish', methods: ['PATCH'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkLockPublish(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $this->connection->prepare(
            "UPDATE kp_match SET Validation = 'O', Publication = 'O', Code_uti = ? WHERE Id IN ($placeholders)"
        )->executeStatement(array_merge([$user?->getUserIdentifier()], $ids));

        $this->logActionForSeason('Verrouillage+publication masse matchs', null, count($ids) . ' match(s)');

        return $this->json(['updated' => count($ids)]);
    }

    /**
     * Bulk change journée (move matches to another journée)
     */
    #[Route('/bulk/journee', name: 'admin_games_bulk_journee', methods: ['PATCH'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkChangeJournee(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);
        $journeeId = (int) ($data['journeeId'] ?? 0);

        if (empty($ids) || $journeeId <= 0) {
            return $this->json(['message' => 'Missing ids or journeeId'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $this->connection->prepare(
            "UPDATE kp_match SET Id_journee = ?, Code_uti = ? WHERE Id IN ($placeholders) AND Validation != 'O'"
        )->executeStatement(array_merge([$journeeId, $user?->getUserIdentifier()], $ids));

        $this->logActionForSeason('Changement journée masse', null, count($ids) . " match(s) → journée $journeeId");

        return $this->json(['updated' => count($ids)]);
    }

    /**
     * Bulk renumber matches
     */
    #[Route('/bulk/renumber', name: 'admin_games_bulk_renumber', methods: ['PATCH'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkRenumber(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);
        $startNumber = (int) ($data['startNumber'] ?? 1);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        // Fetch matches in current order
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->connection->prepare(
            "SELECT Id FROM kp_match WHERE Id IN ($placeholders) AND Validation != 'O' ORDER BY Numero_ordre ASC, Id ASC"
        )->executeQuery($ids)->fetchAllAssociative();

        $num = $startNumber;
        foreach ($rows as $row) {
            $this->connection->prepare(
                "UPDATE kp_match SET Numero_ordre = ?, Code_uti = ? WHERE Id = ?"
            )->executeStatement([$num, $user?->getUserIdentifier(), (int) $row['Id']]);
            $num++;
        }

        $this->logActionForSeason('Renumérotation masse', null, count($rows) . " match(s) à partir de $startNumber");

        return $this->json(['updated' => count($rows)]);
    }

    /**
     * Bulk change date
     */
    #[Route('/bulk/date', name: 'admin_games_bulk_date', methods: ['PATCH'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkChangeDate(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);
        $date = $data['date'] ?? '';

        if (empty($ids) || empty($date)) {
            return $this->json(['message' => 'Missing ids or date'], Response::HTTP_BAD_REQUEST);
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->json(['message' => 'Invalid date format (expected YYYY-MM-DD)'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $affected = $this->connection->prepare(
            "UPDATE kp_match SET Date_match = ?, Code_uti = ? WHERE Id IN ($placeholders) AND Validation != 'O'"
        )->executeStatement(array_merge([$date, $user?->getUserIdentifier()], $ids));

        $this->logActionForSeason('Changement date masse', null, "$affected match(s) → $date");

        return $this->json(['updated' => $affected]);
    }

    /**
     * Bulk increment time (sequential start time + interval)
     */
    #[Route('/bulk/time', name: 'admin_games_bulk_time', methods: ['PATCH'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkIncrementTime(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);
        $startTime = $data['startTime'] ?? '10:00';
        $interval = (int) ($data['interval'] ?? 40);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        // Parse start time to minutes
        $parts = explode(':', $startTime);
        $currentMinutes = ((int) ($parts[0] ?? 10)) * 60 + ((int) ($parts[1] ?? 0));

        // Fetch matches in order (unlocked only)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->connection->prepare(
            "SELECT Id FROM kp_match WHERE Id IN ($placeholders) AND Validation != 'O' ORDER BY Numero_ordre ASC, Id ASC"
        )->executeQuery($ids)->fetchAllAssociative();

        $updated = 0;
        foreach ($rows as $row) {
            $hours = intdiv($currentMinutes, 60);
            $mins = $currentMinutes % 60;
            $timeStr = sprintf('%02d:%02d', $hours, $mins);

            $this->connection->prepare(
                "UPDATE kp_match SET Heure_match = ?, Code_uti = ? WHERE Id = ?"
            )->executeStatement([$timeStr, $user?->getUserIdentifier(), (int) $row['Id']]);

            $currentMinutes += $interval;
            $updated++;
        }

        $this->logActionForSeason('Incrémentation heure masse', null, "$updated match(s) à partir de $startTime, intervalle $interval min");

        return $this->json(['updated' => $updated]);
    }

    /**
     * Bulk replace group code in match labels
     */
    #[Route('/bulk/group', name: 'admin_games_bulk_group', methods: ['PATCH'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkChangeGroup(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);
        $oldGroup = strtoupper(trim($data['oldGroup'] ?? ''));
        $newGroup = strtoupper(trim($data['newGroup'] ?? ''));

        if (empty($ids) || empty($oldGroup) || empty($newGroup)) {
            return $this->json(['message' => 'Missing ids, oldGroup, or newGroup'], Response::HTTP_BAD_REQUEST);
        }

        if (!preg_match('/^[A-Z]{1,5}$/', $oldGroup) || !preg_match('/^[A-Z]{1,5}$/', $newGroup)) {
            return $this->json(['message' => 'Groups must be 1-5 uppercase letters'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        // Fetch unlocked matches only
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->connection->prepare(
            "SELECT Id, Libelle FROM kp_match WHERE Id IN ($placeholders) AND Validation != 'O'"
        )->executeQuery($ids)->fetchAllAssociative();

        $updated = 0;
        foreach ($rows as $row) {
            $libelle = $row['Libelle'] ?? '';
            // Replace group codes: digit+oldGroup → digit+newGroup (e.g. 1A→1X, 2A→2X)
            $newLibelle = preg_replace('/(\d)' . preg_quote($oldGroup, '/') . '/', '$1' . $newGroup, $libelle);
            if ($newLibelle !== $libelle) {
                $this->connection->prepare(
                    "UPDATE kp_match SET Libelle = ?, Code_uti = ? WHERE Id = ?"
                )->executeStatement([$newLibelle, $user?->getUserIdentifier(), (int) $row['Id']]);
                $updated++;
            }
        }

        $this->logActionForSeason('Changement groupe masse', null, "$updated match(s) : $oldGroup → $newGroup");

        return $this->json(['updated' => $updated]);
    }

    /**
     * Bulk cancel assignment (clear teams + referees)
     */
    #[Route('/bulk/cancel-assign', name: 'admin_games_bulk_cancel_assign', methods: ['POST'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkCancelAssign(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        // Only unlocked matches without score
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->connection->prepare(
            "SELECT m.Id, j.Code_competition, j.Code_saison
             FROM kp_match m
             JOIN kp_journee j ON m.Id_journee = j.Id
             WHERE m.Id IN ($placeholders)
               AND m.Validation != 'O'
               AND (m.ScoreA = '' OR m.ScoreA = '?' OR m.ScoreA IS NULL)
               AND j.Phase NOT IN ('Break', 'Pause')"
        )->executeQuery($ids)->fetchAllAssociative();

        $updated = 0;
        $this->connection->beginTransaction();
        try {
            foreach ($rows as $row) {
                $id = (int) $row['Id'];
                $this->connection->prepare(
                    "UPDATE kp_match
                     SET Id_equipeA = NULL, Id_equipeB = NULL,
                         Arbitre_principal = NULL, Arbitre_secondaire = NULL,
                         Matric_arbitre_principal = 0, Matric_arbitre_secondaire = 0,
                         Code_uti = ?
                     WHERE Id = ?"
                )->executeStatement([$user?->getUserIdentifier(), $id]);

                $this->connection->prepare(
                    "DELETE FROM kp_match_joueur WHERE Id_match = ?"
                )->executeStatement([$id]);

                $this->logActionForMatch('Annul auto équipes', $row['Code_saison'], $row['Code_competition'], null, $id, '');
                $updated++;
            }
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Database error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['updated' => $updated]);
    }

    /**
     * Bulk auto-assign teams and referees from bracket notation in Libelle
     */
    #[Route('/bulk/auto-assign', name: 'admin_games_bulk_auto_assign', methods: ['POST'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function bulkAutoAssign(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $ids = $this->filterAuthorizedMatchIds(array_values($ids), $user);
        if (empty($ids)) {
            return $this->json(['message' => 'No authorized games in selection'], Response::HTTP_FORBIDDEN);
        }

        // Load unlocked matches without score
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->connection->prepare(
            "SELECT m.Id, m.Libelle, m.Id_equipeA, m.Id_equipeB,
                    m.Arbitre_principal, m.Arbitre_secondaire,
                    m.Matric_arbitre_principal, m.Matric_arbitre_secondaire,
                    j.Code_competition, j.Code_saison, j.Phase
             FROM kp_match m
             JOIN kp_journee j ON m.Id_journee = j.Id
             WHERE m.Id IN ($placeholders)
               AND m.Validation != 'O'
               AND (m.ScoreA = '' OR m.ScoreA = '?' OR m.ScoreA IS NULL)"
        )->executeQuery($ids)->fetchAllAssociative();

        $errors = [];
        $updated = 0;

        $this->connection->beginTransaction();
        try {
            foreach ($rows as $row) {
                $id = (int) $row['Id'];

                if (in_array($row['Phase'], ['Break', 'Pause'])) {
                    continue;
                }

                // Parse bracket notation: [PART1-PART2-PART3-PART4]
                $libelle = preg_replace('/\s/', '', (string) $row['Libelle']);
                if (!preg_match('/\[([^\]]+)\]/', $libelle, $m)) {
                    $errors[] = ['id' => $id, 'reason' => 'no_bracket'];
                    continue;
                }
                $parts = preg_split('/[\-\/*,;]/', $m[1]);

                $selectNum = [null, null, null, null];
                $selectNom = ['', '', '', ''];
                $hasError = false;

                for ($j = 0; $j < 4; $j++) {
                    // Skip referee slots if already nominatively assigned or contain free text
                    if ($j === 2 && ((int) $row['Matric_arbitre_principal'] !== 0 || trim((string) $row['Arbitre_principal']) !== '')) {
                        continue;
                    }
                    if ($j === 3 && ((int) $row['Matric_arbitre_secondaire'] !== 0 || trim((string) $row['Arbitre_secondaire']) !== '')) {
                        continue;
                    }

                    if (!isset($parts[$j]) || trim($parts[$j]) === '') {
                        continue;
                    }

                    $part = $parts[$j];
                    preg_match('/([A-Z_]+)/', $part, $letters);
                    preg_match('/([0-9]+)/', $part, $numbers);

                    if (empty($letters[1]) || empty($numbers[1])) {
                        continue;
                    }

                    $letter = $letters[1];
                    $number = (int) $numbers[1];
                    $posLetter = strpos($part, $letter);
                    $posNumber = strpos($part, (string) $number);

                    if ($posNumber > $posLetter) {
                        // Letter before number: tirage (T/D), winner (V/G/W), loser (P/L)
                        switch ($letter) {
                            case 'T':
                            case 'D':
                                $team = $this->connection->prepare(
                                    "SELECT ce.Id, ce.Libelle
                                     FROM kp_competition_equipe ce
                                     WHERE ce.Tirage = ? AND ce.Code_compet = ? AND ce.Code_saison = ?"
                                )->executeQuery([$number, $row['Code_competition'], $row['Code_saison']])->fetchAssociative();
                                if ($team) {
                                    $selectNum[$j] = (int) $team['Id'];
                                    $selectNom[$j] = $team['Libelle'];
                                } else {
                                    $errors[] = ['id' => $id, 'reason' => "draw_not_found:$number"];
                                    $hasError = true;
                                }
                                break;

                            case 'V':
                            case 'G':
                            case 'W':
                                $match = $this->connection->prepare(
                                    "SELECT m.Id_equipeA, m.Id_equipeB, ce.Libelle Nom_equipeA, ce2.Libelle Nom_equipeB,
                                            m.ScoreA, m.ScoreB
                                     FROM kp_match m
                                     JOIN kp_journee j ON m.Id_journee = j.Id
                                     JOIN kp_competition_equipe ce ON m.Id_equipeA = ce.Id
                                     JOIN kp_competition_equipe ce2 ON m.Id_equipeB = ce2.Id
                                     WHERE m.Numero_ordre = ? AND m.ScoreA != m.ScoreB
                                       AND j.Code_competition = ? AND j.Code_saison = ?"
                                )->executeQuery([$number, $row['Code_competition'], $row['Code_saison']])->fetchAssociative();
                                if ($match) {
                                    $aWins = ($match['ScoreA'] > $match['ScoreB'] && $match['ScoreA'] !== 'F') || $match['ScoreB'] === 'F';
                                    $selectNum[$j] = $aWins ? (int) $match['Id_equipeA'] : (int) $match['Id_equipeB'];
                                    $selectNom[$j] = $aWins ? $match['Nom_equipeA'] : $match['Nom_equipeB'];
                                } else {
                                    $errors[] = ['id' => $id, 'reason' => "winner_not_found:$number"];
                                    $hasError = true;
                                }
                                break;

                            case 'P':
                            case 'L':
                                $match = $this->connection->prepare(
                                    "SELECT m.Id_equipeA, m.Id_equipeB, ce.Libelle Nom_equipeA, ce2.Libelle Nom_equipeB,
                                            m.ScoreA, m.ScoreB
                                     FROM kp_match m
                                     JOIN kp_journee j ON m.Id_journee = j.Id
                                     JOIN kp_competition_equipe ce ON m.Id_equipeA = ce.Id
                                     JOIN kp_competition_equipe ce2 ON m.Id_equipeB = ce2.Id
                                     WHERE m.Numero_ordre = ? AND m.ScoreA != m.ScoreB
                                       AND j.Code_competition = ? AND j.Code_saison = ?"
                                )->executeQuery([$number, $row['Code_competition'], $row['Code_saison']])->fetchAssociative();
                                if ($match) {
                                    $aLoses = ($match['ScoreA'] < $match['ScoreB'] && $match['ScoreB'] !== 'F') || $match['ScoreA'] === 'F';
                                    $selectNum[$j] = $aLoses ? (int) $match['Id_equipeA'] : (int) $match['Id_equipeB'];
                                    $selectNom[$j] = $aLoses ? $match['Nom_equipeA'] : $match['Nom_equipeB'];
                                } else {
                                    $errors[] = ['id' => $id, 'reason' => "loser_not_found:$number"];
                                    $hasError = true;
                                }
                                break;

                            default:
                                $errors[] = ['id' => $id, 'reason' => "unknown_code:$letter"];
                                $hasError = true;
                        }
                    } else {
                        // Number before letter: ranking in pool (e.g. 1A = 1st of pool A)
                        $poolLetter = $letter;
                        $team = $this->connection->prepare(
                            "SELECT cej.Id, ce.Libelle
                             FROM kp_competition_equipe_journee cej
                             JOIN kp_journee j ON cej.Id_journee = j.Id
                             JOIN kp_competition_equipe ce ON cej.Id = ce.Id
                             WHERE cej.Clt = ?
                               AND j.Phase REGEXP ?
                               AND j.Code_competition = ? AND j.Code_saison = ?"
                        )->executeQuery([
                            $number,
                            '(^|[[:space:]])(Group|Groupe|Poule|poule)[[:space:]]+' . $poolLetter . '([[:space:]]|$)',
                            $row['Code_competition'],
                            $row['Code_saison'],
                        ])->fetchAssociative();
                        if ($team) {
                            $selectNum[$j] = (int) $team['Id'];
                            $selectNom[$j] = $team['Libelle'];
                        } else {
                            $errors[] = ['id' => $id, 'reason' => "pool_rank_not_found:{$number}{$poolLetter}"];
                            $hasError = true;
                        }
                    }
                }

                // Skip only if nothing at all could be resolved
                $hasAnyResolved = $selectNum[0] !== null || $selectNum[1] !== null
                    || $selectNom[2] !== '' || $selectNom[3] !== '';
                if (!$hasAnyResolved) {
                    continue;
                }

                $oldEquipeA = (int) ($row['Id_equipeA'] ?? 0);
                $oldEquipeB = (int) ($row['Id_equipeB'] ?? 0);

                // Build UPDATE — only include slots that were successfully resolved
                $setClauses = [];
                $params = [];
                if ($selectNum[0] !== null) {
                    $setClauses[] = 'Id_equipeA = ?';
                    $params[] = $selectNum[0];
                }
                if ($selectNum[1] !== null) {
                    $setClauses[] = 'Id_equipeB = ?';
                    $params[] = $selectNum[1];
                }
                if ($selectNom[2] !== '') {
                    $setClauses[] = 'Arbitre_principal = ?';
                    $params[] = $selectNom[2];
                }
                if ($selectNom[3] !== '') {
                    $setClauses[] = 'Arbitre_secondaire = ?';
                    $params[] = $selectNom[3];
                }
                $setClauses[] = 'Code_uti = ?';
                $params[] = $user?->getUserIdentifier();
                $params[] = $id;

                $this->connection->prepare(
                    'UPDATE kp_match SET ' . implode(', ', $setClauses) . ' WHERE Id = ?'
                )->executeStatement($params);

                // Delete players for changed teams
                if ($selectNum[0] !== null && $selectNum[0] !== $oldEquipeA) {
                    $this->connection->prepare(
                        "DELETE FROM kp_match_joueur WHERE Id_match = ? AND Equipe = 'A'"
                    )->executeStatement([$id]);
                }
                if ($selectNum[1] !== null && $selectNum[1] !== $oldEquipeB) {
                    $this->connection->prepare(
                        "DELETE FROM kp_match_joueur WHERE Id_match = ? AND Equipe = 'B'"
                    )->executeStatement([$id]);
                }

                $this->logActionForMatch('Affect auto équipes', $row['Code_saison'], $row['Code_competition'], null, $id, '');
                $updated++;
            }
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Database error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['updated' => $updated, 'errors' => $errors]);
    }

    /**
     * Get teams for a journee (for team select dropdowns)
     */
    #[Route('/teams', name: 'admin_games_teams', methods: ['GET'])]
    public function getTeams(Request $request): JsonResponse
    {
        $journeeId = (int) $request->query->get('journeeId', 0);

        if ($journeeId <= 0) {
            return $this->json(['teams' => []]);
        }

        // Get the competition for this journee
        $journee = $this->connection->prepare(
            "SELECT Code_competition, Code_saison FROM kp_journee WHERE Id = ?"
        )->executeQuery([$journeeId])->fetchAssociative();

        if (!$journee) {
            return $this->json(['teams' => []]);
        }

        // Get teams for this competition/season
        $sql = "SELECT Id, Libelle, Code_club
                FROM kp_competition_equipe
                WHERE Code_compet = ? AND Code_saison = ?
                ORDER BY Libelle";
        $rows = $this->connection->prepare($sql)->executeQuery([
            $journee['Code_competition'],
            $journee['Code_saison'],
        ])->fetchAllAssociative();

        $teams = array_map(fn($r) => [
            'id' => (int) $r['Id'],
            'libelle' => $r['Libelle'],
            'codeClub' => $r['Code_club'],
        ], $rows);

        return $this->json(['teams' => $teams]);
    }

    /**
     * Get journees for filter dropdown
     */
    #[Route('/journees', name: 'admin_games_journees', methods: ['GET'])]
    public function getJournees(Request $request): JsonResponse
    {
        $season = $request->query->get('season', '');
        $competitions = $request->query->get('competitions', '');
        $eventId = $request->query->get('event', '');
        $tour = $request->query->get('tour', '');

        /** @var User|null $user */
        $user = $this->getUser();

        if (empty($season)) {
            return $this->json(['journees' => []]);
        }

        $where = ['j.Code_saison = ?'];
        $params = [$season];

        // Competition filter (multi, comma-separated)
        $codes = !empty($competitions) ? array_filter(explode(',', $competitions)) : [];
        if (count($codes) > 0) {
            $placeholders = implode(',', array_fill(0, count($codes), '?'));
            $where[] = "j.Code_competition IN ($placeholders)";
            $params = array_merge($params, $codes);
        }

        if (!empty($tour) && is_numeric($tour)) {
            $where[] = 'j.Etape = ?';
            $params[] = (int) $tour;
        }

        // User competition filter
        if ($user) {
            $allowedCompetitions = $user->getAllowedCompetitions();
            if ($allowedCompetitions !== null && count($allowedCompetitions) > 0) {
                $placeholders = implode(',', array_fill(0, count($allowedCompetitions), '?'));
                $where[] = "j.Code_competition IN ($placeholders)";
                $params = array_merge($params, $allowedCompetitions);
            }
        }

        // Event filter
        $joinEvent = '';
        if (!empty($eventId) && $eventId !== '-1') {
            $joinEvent = 'INNER JOIN kp_evenement_journee ej ON ej.Id_journee = j.Id';
            $where[] = 'ej.Id_evenement = ?';
            $params[] = (int) $eventId;
        }

        // Profile 7 restrictions: only published gamedays, skip ATT competitions
        if ($user && $user->getEffectiveNiveau() === 7) {
            $where[] = "j.Publication = 'O'";
            $where[] = "EXISTS (SELECT 1 FROM kp_competition c2 WHERE c2.Code = j.Code_competition AND c2.Code_saison = j.Code_saison AND c2.Statut != 'ATT')";
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT j.Id, j.Code_competition, j.Phase, j.Etape, j.Date_debut, j.Lieu, j.Type,
                       c.Code_typeclt
                FROM kp_journee j
                LEFT JOIN kp_competition c ON j.Code_competition = c.Code AND j.Code_saison = c.Code_saison
                $joinEvent
                $whereClause
                ORDER BY j.Code_competition, j.Niveau, j.Phase, j.Date_debut";

        $rows = $this->connection->prepare($sql)->executeQuery($params)->fetchAllAssociative();

        $allowedJournees = $user?->getAllowedJournees();

        $journees = array_map(function ($r) use ($allowedJournees) {
            $authorized = $allowedJournees === null || in_array((int) $r['Id'], $allowedJournees);
            return [
                'id' => (int) $r['Id'],
                'codeCompetition' => $r['Code_competition'],
                'phase' => $r['Phase'],
                'etape' => (int) $r['Etape'],
                'dateDebut' => $r['Date_debut'],
                'lieu' => $r['Lieu'],
                'type' => $r['Type'],
                'codeTypeclt' => $r['Code_typeclt'],
                'authorized' => $authorized,
            ];
        }, $rows);

        return $this->json(['journees' => $journees]);
    }

    /**
     * List events (for the event filter dropdown)
     */
    #[Route('/events', name: 'admin_games_events', methods: ['GET'])]
    public function listEvents(): JsonResponse
    {
        $sql = "SELECT Id, Libelle, Date_debut, Date_fin
                FROM kp_evenement
                ORDER BY Date_debut DESC, Libelle";
        $rows = $this->connection->executeQuery($sql)->fetchAllAssociative();

        $items = array_map(fn($r) => [
            'id' => (int) $r['Id'],
            'libelle' => $r['Libelle'],
            'dateDebut' => $r['Date_debut'],
            'dateFin' => $r['Date_fin'],
        ], $rows);

        return $this->json(['items' => $items]);
    }

    /**
     * Autocomplete referees for a journee
     */
    #[Route('/autocomplete/referees', name: 'admin_games_autocomplete_referees', methods: ['GET'])]
    #[IsGranted('ROLE_ORGANIZER')]
    public function autocompleteReferees(Request $request): JsonResponse
    {
        $q = trim($request->query->get('q', ''));
        $q = ltrim($q, '0');
        $journeeId = (int) $request->query->get('journeeId', 0);
        $lang = $request->query->get('lang', 'fr');

        if ($journeeId <= 0) {
            $msg = $lang === 'en' ? 'Select a gameday / phase' : 'Sélectionnez une journée / phase';
            return $this->json([['type' => 'error', 'label' => $msg, 'value' => '']]);
        }
        if (mb_strlen($q) < 2) {
            $msg = $lang === 'en' ? '2 characters minimum' : '2 caractères minimum';
            return $this->json([['type' => 'error', 'label' => $msg, 'value' => '']]);
        }

        $results = [];
        $likeQ = '%' . $q . '%';

        $sepTeams = $lang === 'en' ? '---------- Teams ----------' : '---------- Équipes ----------';
        $sepPlayers = $lang === 'en' ? '---------- Players ----------' : '---------- Joueurs ----------';
        $sepPool = $lang === 'en' ? '---------- Referee Pool ----------' : '---------- Pool Arbitres ----------';
        $sepOther = $lang === 'en' ? '---------- Other Referees ----------' : '---------- Autres Arbitres ----------';

        // 1. Équipes engagées
        $results[] = ['type' => 'separator', 'label' => $sepTeams];
        $sql = "SELECT a.Id, a.Libelle, a.Poule, a.Tirage, a.Code_compet
                FROM kp_competition_equipe a
                INNER JOIN kp_journee b ON a.Code_compet = b.Code_competition AND a.Code_saison = b.Code_saison
                WHERE b.Id = ? AND UPPER(a.Libelle) LIKE UPPER(?)
                GROUP BY a.Libelle
                ORDER BY a.Poule, a.Tirage, a.Libelle";
        $rows = $this->connection->prepare($sql)->executeQuery([$journeeId, $likeQ])->fetchAllAssociative();
        foreach ($rows as $row) {
            $results[] = [
                'type' => 'equipe',
                'matric' => '',
                'nom' => $row['Libelle'],
                'prenom' => '',
                'libelle' => '',
                'arbitre' => '',
                'label' => $row['Libelle'],
                'value' => $row['Libelle'],
            ];
        }

        // 2. Joueurs engagés
        $results[] = ['type' => 'separator', 'label' => $sepPlayers];
        $sql = "SELECT DISTINCT a.Matric, a.Nom, a.Prenom, b.Libelle, c.arbitre, c.niveau,
                    (c.arbitre IS NULL) AS sortCol
                FROM kp_competition_equipe b
                INNER JOIN kp_journee d ON b.Code_compet = d.Code_competition AND b.Code_saison = d.Code_saison
                INNER JOIN kp_match e ON d.Id = e.Id_journee
                INNER JOIN kp_competition_equipe_joueur a ON a.Id_equipe = b.Id
                LEFT JOIN kp_arbitre c ON a.Matric = c.Matric
                WHERE d.Id = ?
                AND a.Capitaine <> 'X'
                AND (a.Matric LIKE ? OR UPPER(CONCAT_WS(' ', a.Nom, a.Prenom)) LIKE UPPER(?)
                     OR UPPER(CONCAT_WS(' ', a.Prenom, a.Nom)) LIKE UPPER(?) OR UPPER(b.Libelle) LIKE UPPER(?))
                ORDER BY b.Libelle, sortCol, c.arbitre, a.Nom, a.Prenom";
        $rows = $this->connection->prepare($sql)->executeQuery([$journeeId, $likeQ, $likeQ, $likeQ, $likeQ])->fetchAllAssociative();
        foreach ($rows as $row) {
            $arb = strtoupper($row['arbitre'] ?? '');
            if (!empty($row['niveau'])) {
                $arb .= '-' . $row['niveau'];
            }
            $nom = mb_strtoupper($row['Nom'] ?? '');
            $prenom = mb_convert_case(strtolower($row['Prenom'] ?? ''), MB_CASE_TITLE, 'UTF-8');
            $libelle = $row['Libelle'];
            $arbSuffix = $arb !== '' ? " $arb" : '';
            $results[] = [
                'type' => 'joueur',
                'matric' => $row['Matric'],
                'nom' => $nom,
                'prenom' => $prenom,
                'libelle' => $libelle,
                'arbitre' => $arb,
                'label' => "($libelle) $nom $prenom$arbSuffix",
                'value' => "$nom $prenom ($libelle)$arbSuffix",
            ];
        }

        // 3. Pool Arbitres
        $results[] = ['type' => 'separator', 'label' => $sepPool];
        $sql = "SELECT a.Matric, a.Nom, a.Prenom, b.Libelle, c.arbitre, c.niveau
                FROM kp_competition_equipe b
                INNER JOIN kp_competition_equipe_joueur a ON a.Id_equipe = b.Id
                LEFT JOIN kp_arbitre c ON a.Matric = c.Matric
                WHERE b.Code_compet = 'POOL'
                AND (a.Matric LIKE ? OR UPPER(CONCAT_WS(' ', a.Nom, a.Prenom)) LIKE UPPER(?)
                     OR UPPER(CONCAT_WS(' ', a.Prenom, a.Nom)) LIKE UPPER(?) OR UPPER(b.Libelle) LIKE UPPER(?))
                ORDER BY a.Nom, a.Prenom";
        $rows = $this->connection->prepare($sql)->executeQuery([$likeQ, $likeQ, $likeQ, $likeQ])->fetchAllAssociative();
        foreach ($rows as $row) {
            $libelle = substr($row['Libelle'], 0, 3);
            $libelle = str_replace('Poo', 'Pool', $libelle);
            $arb = strtoupper($row['arbitre'] ?? '');
            if (!empty($row['niveau'])) {
                $arb .= '-' . $row['niveau'];
            }
            $nom = mb_strtoupper($row['Nom'] ?? '');
            $prenom = mb_convert_case(strtolower($row['Prenom'] ?? ''), MB_CASE_TITLE, 'UTF-8');
            $arbSuffix = $arb !== '' ? " $arb" : '';
            $results[] = [
                'type' => 'pool',
                'matric' => $row['Matric'],
                'nom' => $nom,
                'prenom' => $prenom,
                'libelle' => $libelle,
                'arbitre' => $arb,
                'label' => "$nom $prenom ($libelle)$arbSuffix",
                'value' => "$nom $prenom ($libelle)$arbSuffix",
            ];
        }

        // 4. Autres arbitres licenciés
        $results[] = ['type' => 'separator', 'label' => $sepOther];
        $sql = "SELECT lc.Matric, lc.Nom, lc.Prenom, lc.Numero_club, c.Libelle, b.arbitre, b.niveau
                FROM kp_licence lc
                INNER JOIN kp_arbitre b ON lc.Matric = b.Matric
                INNER JOIN kp_club c ON lc.Numero_club = c.Code
                WHERE (lc.Matric LIKE ? OR UPPER(CONCAT_WS(' ', lc.Nom, lc.Prenom)) LIKE UPPER(?)
                       OR UPPER(CONCAT_WS(' ', lc.Prenom, lc.Nom)) LIKE UPPER(?))
                ORDER BY lc.Nom, lc.Prenom";
        $rows = $this->connection->prepare($sql)->executeQuery([$likeQ, $likeQ, $likeQ])->fetchAllAssociative();
        foreach ($rows as $row) {
            $libelle = mb_convert_case(strtolower($row['Libelle'] ?? ''), MB_CASE_TITLE, 'UTF-8');
            $arb = strtoupper($row['arbitre'] ?? '');
            if (!empty($row['niveau'])) {
                $arb .= '-' . $row['niveau'];
            }
            $nom = mb_strtoupper($row['Nom'] ?? '');
            $prenom = mb_convert_case(strtolower($row['Prenom'] ?? ''), MB_CASE_TITLE, 'UTF-8');
            $arbSuffix = $arb !== '' ? " $arb" : '';
            $results[] = [
                'type' => 'autre',
                'matric' => $row['Matric'],
                'nom' => $nom,
                'prenom' => $prenom,
                'libelle' => $libelle,
                'arbitre' => $arb,
                'label' => "$nom $prenom ($libelle)$arbSuffix",
                'value' => "$nom $prenom ($libelle)$arbSuffix",
            ];
        }

        return $this->json($results);
    }

    /**
     * Returns 403 if the competition linked to $journeeId has Statut = 'END'.
     */
    private function assertCompetitionNotEnded(string $codeCompetition, string $codeSaison): ?JsonResponse
    {
        $statut = $this->connection->prepare(
            "SELECT Statut FROM kp_competition WHERE Code = ? AND Code_saison = ?"
        )->executeQuery([$codeCompetition, $codeSaison])->fetchOne();

        if ($statut === 'END') {
            return $this->json(['message' => 'Competition is ended'], Response::HTTP_FORBIDDEN);
        }
        return null;
    }

    /**
     * Returns 403 if the user has a journee restriction and $journeeId is not in the allowed list.
     */
    private function assertJourneeAuthorized(int $journeeId, ?User $user): ?JsonResponse
    {
        if (!$user) return null;
        $allowed = $user->getAllowedJournees();
        if ($allowed !== null && !in_array($journeeId, $allowed)) {
            return $this->json(['message' => 'Access denied for this journée'], Response::HTTP_FORBIDDEN);
        }
        return null;
    }

    /**
     * Filters an array of match IDs to only those the user is allowed to edit:
     * - journée restriction (if any)
     * - competition not ended (Statut != 'END')
     */
    private function filterAuthorizedMatchIds(array $ids, ?User $user): array
    {
        if (empty($ids)) return $ids;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $allowed = $user?->getAllowedJournees();
        if ($allowed !== null && count($allowed) > 0) {
            $journeePlaceholders = implode(',', array_fill(0, count($allowed), '?'));
            $rows = $this->connection->prepare(
                "SELECT m.Id FROM kp_match m
                 INNER JOIN kp_journee j ON j.Id = m.Id_journee
                 INNER JOIN kp_competition c ON c.Code = j.Code_competition AND c.Code_saison = j.Code_saison
                 WHERE m.Id IN ($placeholders)
                   AND m.Id_journee IN ($journeePlaceholders)
                   AND c.Statut != 'END'"
            )->executeQuery(array_merge($ids, $allowed))->fetchAllAssociative();
        } else {
            $rows = $this->connection->prepare(
                "SELECT m.Id FROM kp_match m
                 INNER JOIN kp_journee j ON j.Id = m.Id_journee
                 INNER JOIN kp_competition c ON c.Code = j.Code_competition AND c.Code_saison = j.Code_saison
                 WHERE m.Id IN ($placeholders)
                   AND c.Statut != 'END'"
            )->executeQuery($ids)->fetchAllAssociative();
        }

        return array_map(fn($r) => (int) $r['Id'], $rows);
    }

    /**
     * Detect PhaseLibelle mode
     */
    private function detectPhaseLibelle(string $competition, string $season, array $rows): bool
    {
        // If single competition selected and it's CP type → phaseLibelle
        if (!empty($competition)) {
            $typeclt = $this->connection->prepare(
                "SELECT Code_typeclt FROM kp_competition WHERE Code = ? AND Code_saison = ?"
            )->executeQuery([$competition, $season])->fetchOne();

            if ($typeclt === 'CP') {
                return true;
            }
        }

        // Otherwise check if matches have non-empty Phase+Libelle
        foreach ($rows as $row) {
            if (!empty($row['Phase']) && !empty($row['Libelle'])) {
                return true;
            }
        }

        return false;
    }
}
