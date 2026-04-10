<?php

namespace App\Controller;

use App\Entity\User;
use App\Trait\AdminLoggableTrait;
use App\Trait\DateValidationTrait;
use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin Gamedays Controller
 *
 * CRUD operations for gamedays/phases management (kp_journee table)
 */
#[Route('/admin/gamedays')]
#[IsGranted('ROLE_USER')]
#[OA\Tag(name: '30. App4 - Gamedays')]
class AdminGamedaysController extends AbstractController
{
    use AdminLoggableTrait;
    use DateValidationTrait;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * List gamedays with pagination and filters
     */
    #[Route('', name: 'admin_gamedays_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        // Pagination
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(200, max(1, (int) $request->query->get('limit', 25)));
        $offset = ($page - 1) * $limit;

        // Filters
        $season = $request->query->get('season', '');
        $competitions = $request->query->get('competitions', '');
        $eventId = $request->query->get('event', '');
        $month = $request->query->get('month', '');
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_asc');

        // Fallback to active season
        if (empty($season)) {
            $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
            $result = $this->connection->executeQuery($sql);
            $season = $result->fetchOne() ?: '';
        }

        if (empty($season)) {
            return $this->json(['items' => [], 'total' => 0, 'page' => 1, 'totalPages' => 0]);
        }

        // Build WHERE clause
        $where = ['j.Code_saison = ?'];
        $params = [$season];

        // Competition filter
        if (!empty($competitions)) {
            $codes = array_filter(explode(',', $competitions));
            if (count($codes) > 0) {
                $placeholders = implode(',', array_fill(0, count($codes), '?'));
                $where[] = "j.Code_competition IN ($placeholders)";
                $params = array_merge($params, $codes);
            }
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

        // Month filter
        if (!empty($month) && is_numeric($month)) {
            $where[] = '(MONTH(j.Date_debut) = ? OR MONTH(j.Date_fin) = ?)';
            $params[] = (int) $month;
            $params[] = (int) $month;
        }

        // Search filter
        if (!empty($search)) {
            if (is_numeric($search)) {
                $where[] = '(j.Id = ? OR j.Phase LIKE ? OR j.Nom LIKE ? OR j.Lieu LIKE ?)';
                $params[] = (int) $search;
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            } else {
                $where[] = '(j.Phase LIKE ? OR j.Nom LIKE ? OR j.Lieu LIKE ? OR j.Code_competition LIKE ?)';
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Sort
        $orderBy = match ($sort) {
            'date_desc' => 'j.Date_debut DESC, j.Niveau, j.Phase, j.Lieu, j.Libelle',
            'name' => 'j.Libelle, j.Niveau, j.Phase',
            'number' => 'j.Id, j.Niveau, j.Phase',
            'level' => 'j.Niveau, j.Phase, j.Date_debut',
            default => 'j.Date_debut, j.Niveau, j.Phase, j.Lieu, j.Libelle, j.Id', // date_asc
        };

        // Count total
        $countSql = "SELECT COUNT(*) FROM kp_journee j $joinEvent $whereClause";
        $stmt = $this->connection->prepare($countSql);
        $total = (int) $stmt->executeQuery($params)->fetchOne();

        // Fetch gamedays with match count
        $sql = "SELECT j.Id, j.Code_competition, j.Code_saison, j.Phase, j.Niveau, j.Etape,
                       j.Nbequipes, j.Type, j.Date_debut, j.Date_fin, j.Nom, j.Libelle,
                       j.Lieu, j.Departement, j.Plan_eau, j.Organisateur,
                       j.Responsable_insc, j.Responsable_R1, j.Delegue, j.ChefArbitre,
                       j.Rep_athletes, j.Arb_nj1, j.Arb_nj2, j.Arb_nj3, j.Arb_nj4, j.Arb_nj5,
                       j.Publication, j.Code_organisateur, j.Validation,
                       c.Libelle AS CompetitionLibelle, c.Code_typeclt,
                       (SELECT COUNT(*) FROM kp_match m WHERE m.Id_journee = j.Id) AS matchCount
                FROM kp_journee j
                LEFT JOIN kp_competition c ON c.Code = j.Code_competition AND c.Code_saison = j.Code_saison
                $joinEvent
                $whereClause
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

        $stmt = $this->connection->prepare($sql);
        $rows = $stmt->executeQuery($params)->fetchAllAssociative();

        // Check user authorization per gameday
        $allowedJournees = $user?->getAllowedJournees();

        $items = array_map(function ($row) use ($allowedJournees) {
            $authorized = $allowedJournees === null || in_array((int) $row['Id'], $allowedJournees);

            return [
                'id' => (int) $row['Id'],
                'codeCompetition' => $row['Code_competition'],
                'codeSaison' => $row['Code_saison'],
                'phase' => $row['Phase'],
                'niveau' => $row['Niveau'] !== null ? (int) $row['Niveau'] : null,
                'etape' => (int) $row['Etape'],
                'nbEquipes' => (int) $row['Nbequipes'],
                'type' => $row['Type'],
                'dateDebut' => $row['Date_debut'],
                'dateFin' => $row['Date_fin'],
                'nom' => $row['Nom'],
                'libelle' => $row['Libelle'],
                'lieu' => $row['Lieu'],
                'departement' => $row['Departement'],
                'planEau' => $row['Plan_eau'],
                'organisateur' => $row['Organisateur'],
                'responsableInsc' => $row['Responsable_insc'],
                'responsableR1' => $row['Responsable_R1'],
                'delegue' => $row['Delegue'],
                'chefArbitre' => $row['ChefArbitre'],
                'repAthletes' => $row['Rep_athletes'],
                'arbNj1' => $row['Arb_nj1'],
                'arbNj2' => $row['Arb_nj2'],
                'arbNj3' => $row['Arb_nj3'],
                'arbNj4' => $row['Arb_nj4'],
                'arbNj5' => $row['Arb_nj5'],
                'publication' => $row['Publication'] === 'O',
                'matchCount' => (int) $row['matchCount'],
                'authorized' => $authorized,
                'competitionLibelle' => $row['CompetitionLibelle'],
                'competitionTypeClt' => $row['Code_typeclt'],
            ];
        }, $rows);

        return $this->json([
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'totalPages' => (int) ceil($total / $limit),
        ]);
    }

    /**
     * Get a single gameday
     */
    #[Route('/{id}', name: 'admin_gamedays_get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(int $id): JsonResponse
    {
        $sql = "SELECT j.*, c.Libelle AS CompetitionLibelle, c.Code_typeclt,
                       (SELECT COUNT(*) FROM kp_match m WHERE m.Id_journee = j.Id) AS matchCount
                FROM kp_journee j
                LEFT JOIN kp_competition c ON c.Code = j.Code_competition AND c.Code_saison = j.Code_saison
                WHERE j.Id = ?";
        $row = $this->connection->prepare($sql)->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Gameday not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => (int) $row['Id'],
            'codeCompetition' => $row['Code_competition'],
            'codeSaison' => $row['Code_saison'],
            'phase' => $row['Phase'],
            'niveau' => $row['Niveau'] !== null ? (int) $row['Niveau'] : null,
            'etape' => (int) $row['Etape'],
            'nbEquipes' => (int) $row['Nbequipes'],
            'type' => $row['Type'],
            'dateDebut' => $row['Date_debut'],
            'dateFin' => $row['Date_fin'],
            'nom' => $row['Nom'],
            'libelle' => $row['Libelle'],
            'lieu' => $row['Lieu'],
            'departement' => $row['Departement'],
            'planEau' => $row['Plan_eau'],
            'organisateur' => $row['Organisateur'],
            'codeOrganisateur' => $row['Code_organisateur'],
            'responsableInsc' => $row['Responsable_insc'],
            'responsableR1' => $row['Responsable_R1'],
            'delegue' => $row['Delegue'],
            'chefArbitre' => $row['ChefArbitre'],
            'repAthletes' => $row['Rep_athletes'],
            'arbNj1' => $row['Arb_nj1'],
            'arbNj2' => $row['Arb_nj2'],
            'arbNj3' => $row['Arb_nj3'],
            'arbNj4' => $row['Arb_nj4'],
            'arbNj5' => $row['Arb_nj5'],
            'publication' => $row['Publication'] === 'O',
            'matchCount' => (int) $row['matchCount'],
            'competitionLibelle' => $row['CompetitionLibelle'],
            'competitionTypeClt' => $row['Code_typeclt'],
        ]);
    }

    /**
     * Create a new gameday
     */
    #[Route('', name: 'admin_gamedays_create', methods: ['POST'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function create(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Invalid request body'], Response::HTTP_BAD_REQUEST);
        }

        // Required fields
        $competition = trim($data['codeCompetition'] ?? '');
        $season = trim($data['codeSaison'] ?? '');
        $phase = trim($data['phase'] ?? '');

        if (empty($competition) || empty($season) || empty($phase)) {
            return $this->json(['message' => 'Competition, season and phase are required'], Response::HTTP_BAD_REQUEST);
        }

        // Verify competition exists
        $exists = $this->connection->prepare(
            "SELECT COUNT(*) FROM kp_competition WHERE Code = ? AND Code_saison = ?"
        )->executeQuery([$competition, $season])->fetchOne();

        if (!$exists) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        // Get next ID for journee (max + 1, with upper limit to avoid conflicts with existing federal IDs > 19000000)
        $nextId = (int) $this->connection->executeQuery("SELECT COALESCE(MAX(Id), 0) + 1 FROM kp_journee WHERE Id < 19000001")->fetchOne();

        // Build insert data
        $insertData = [
            'Id' => $nextId,
            'Code_competition' => $competition,
            'Code_saison' => $season,
            'Phase' => $phase,
            'Niveau' => (int) ($data['niveau'] ?? 1),
            'Etape' => (int) ($data['etape'] ?? 1),
            'Nbequipes' => (int) ($data['nbEquipes'] ?? 1),
            'Type' => in_array($data['type'] ?? 'C', ['C', 'E']) ? ($data['type'] ?? 'C') : 'C',
            'Date_debut' => !empty($data['dateDebut']) ? $data['dateDebut'] : null,
            'Date_fin' => !empty($data['dateFin']) ? $data['dateFin'] : null,
            'Nom' => !empty($data['nom']) ? substr($data['nom'], 0, 80) : null,
            'Libelle' => !empty($data['libelle']) ? substr($data['libelle'], 0, 80) : null,
            'Lieu' => !empty($data['lieu']) ? substr($data['lieu'], 0, 40) : null,
            'Departement' => !empty($data['departement']) ? substr($data['departement'], 0, 3) : null,
            'Plan_eau' => !empty($data['planEau']) ? substr($data['planEau'], 0, 80) : null,
            'Organisateur' => !empty($data['organisateur']) ? substr($data['organisateur'], 0, 40) : null,
            'Code_organisateur' => !empty($data['codeOrganisateur']) ? substr($data['codeOrganisateur'], 0, 5) : null,
            'Responsable_insc' => !empty($data['responsableInsc']) ? substr($data['responsableInsc'], 0, 80) : null,
            'Responsable_R1' => !empty($data['responsableR1']) ? substr($data['responsableR1'], 0, 80) : null,
            'Delegue' => !empty($data['delegue']) ? substr($data['delegue'], 0, 80) : null,
            'ChefArbitre' => !empty($data['chefArbitre']) ? substr($data['chefArbitre'], 0, 80) : null,
            'Rep_athletes' => !empty($data['repAthletes']) ? substr($data['repAthletes'], 0, 80) : null,
            'Arb_nj1' => !empty($data['arbNj1']) ? substr($data['arbNj1'], 0, 80) : null,
            'Arb_nj2' => !empty($data['arbNj2']) ? substr($data['arbNj2'], 0, 80) : null,
            'Arb_nj3' => !empty($data['arbNj3']) ? substr($data['arbNj3'], 0, 80) : null,
            'Arb_nj4' => !empty($data['arbNj4']) ? substr($data['arbNj4'], 0, 80) : null,
            'Arb_nj5' => !empty($data['arbNj5']) ? substr($data['arbNj5'], 0, 80) : null,
            'Publication' => 'N',
            'Code_uti' => $user?->getUserIdentifier(),
        ];

        $this->connection->insert('kp_journee', $insertData);

        $this->logActionForSeason('Ajout journee', $season, "$competition: $phase (Id: $nextId)");

        return $this->json(['id' => $nextId, 'message' => 'Gameday created'], Response::HTTP_CREATED);
    }

    /**
     * Update a gameday
     */
    #[Route('/{id}', name: 'admin_gamedays_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function update(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Verify gameday exists
        $existing = $this->connection->prepare(
            "SELECT Id, Code_competition, Code_saison FROM kp_journee WHERE Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$existing) {
            return $this->json(['message' => 'Gameday not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Invalid request body'], Response::HTTP_BAD_REQUEST);
        }

        // Build update data
        $updateData = [];
        $allowedFields = [
            'phase' => ['col' => 'Phase', 'max' => 30],
            'niveau' => ['col' => 'Niveau', 'type' => 'int'],
            'etape' => ['col' => 'Etape', 'type' => 'int'],
            'nbEquipes' => ['col' => 'Nbequipes', 'type' => 'int'],
            'type' => ['col' => 'Type', 'max' => 1],
            'dateDebut' => ['col' => 'Date_debut'],
            'dateFin' => ['col' => 'Date_fin'],
            'nom' => ['col' => 'Nom', 'max' => 80],
            'libelle' => ['col' => 'Libelle', 'max' => 80],
            'lieu' => ['col' => 'Lieu', 'max' => 40],
            'departement' => ['col' => 'Departement', 'max' => 3],
            'planEau' => ['col' => 'Plan_eau', 'max' => 80],
            'organisateur' => ['col' => 'Organisateur', 'max' => 40],
            'codeOrganisateur' => ['col' => 'Code_organisateur', 'max' => 5],
            'responsableInsc' => ['col' => 'Responsable_insc', 'max' => 80],
            'responsableR1' => ['col' => 'Responsable_R1', 'max' => 80],
            'delegue' => ['col' => 'Delegue', 'max' => 80],
            'chefArbitre' => ['col' => 'ChefArbitre', 'max' => 80],
            'repAthletes' => ['col' => 'Rep_athletes', 'max' => 80],
            'arbNj1' => ['col' => 'Arb_nj1', 'max' => 80],
            'arbNj2' => ['col' => 'Arb_nj2', 'max' => 80],
            'arbNj3' => ['col' => 'Arb_nj3', 'max' => 80],
            'arbNj4' => ['col' => 'Arb_nj4', 'max' => 80],
            'arbNj5' => ['col' => 'Arb_nj5', 'max' => 80],
        ];

        // Season/competition editable only for profile <= 2
        if ($user && $user->getNiveau() <= 2) {
            if (isset($data['codeSaison'])) {
                $updateData['Code_saison'] = $data['codeSaison'];
            }
            if (isset($data['codeCompetition'])) {
                $updateData['Code_competition'] = $data['codeCompetition'];
            }
        }

        foreach ($allowedFields as $key => $config) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $value = $data[$key];
            if (isset($config['type']) && $config['type'] === 'int') {
                $updateData[$config['col']] = $value !== null ? (int) $value : null;
            } elseif (isset($config['max'])) {
                $updateData[$config['col']] = $value !== null && $value !== '' ? substr((string) $value, 0, $config['max']) : null;
            } else {
                $updateData[$config['col']] = $value !== '' ? $value : null;
            }
        }

        if (empty($updateData)) {
            return $this->json(['message' => 'No fields to update'], Response::HTTP_BAD_REQUEST);
        }

        $updateData['Code_uti'] = $user?->getUserIdentifier();

        $this->connection->update('kp_journee', $updateData, ['Id' => $id]);

        $this->logActionForSeason(
            'Modification journee',
            $existing['Code_saison'],
            "{$existing['Code_competition']}: Id $id"
        );

        return $this->json(['message' => 'Gameday updated']);
    }

    /**
     * Toggle publication
     */
    #[Route('/{id}/publication', name: 'admin_gamedays_toggle_publication', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function togglePublication(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $sql = "SELECT Id, Publication, Code_saison, Code_competition FROM kp_journee WHERE Id = ?";
        $row = $this->connection->prepare($sql)->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Gameday not found'], Response::HTTP_NOT_FOUND);
        }

        $newValue = $row['Publication'] === 'O' ? 'N' : 'O';
        $this->connection->update('kp_journee', ['Publication' => $newValue], ['Id' => $id]);

        $this->logActionForSeason(
            'Publication journee',
            $row['Code_saison'],
            "{$row['Code_competition']}: Id $id -> $newValue"
        );

        return $this->json(['id' => $id, 'publication' => $newValue === 'O']);
    }

    /**
     * Toggle type C/E
     */
    #[Route('/{id}/type', name: 'admin_gamedays_toggle_type', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function toggleType(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $sql = "SELECT Id, Type, Code_saison, Code_competition FROM kp_journee WHERE Id = ?";
        $row = $this->connection->prepare($sql)->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Gameday not found'], Response::HTTP_NOT_FOUND);
        }

        $newValue = $row['Type'] === 'C' ? 'E' : 'C';
        $this->connection->update('kp_journee', ['Type' => $newValue], ['Id' => $id]);

        return $this->json(['id' => $id, 'type' => $newValue]);
    }

    /**
     * Inline field update
     */
    #[Route('/{id}/inline', name: 'admin_gamedays_inline_update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function inlineUpdate(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $field = $data['field'] ?? '';
        $value = $data['value'] ?? '';

        // Whitelist of inline-editable fields
        $allowedFields = [
            'Phase' => 30,
            'Niveau' => null,
            'Etape' => null,
            'Nbequipes' => null,
            'Nom' => 80,
            'Date_debut' => null,
            'Date_fin' => null,
            'Lieu' => 40,
            'Departement' => 3,
        ];

        if (!array_key_exists($field, $allowedFields)) {
            return $this->json(['message' => 'Field not allowed'], Response::HTTP_BAD_REQUEST);
        }

        // Verify gameday exists
        $row = $this->connection->prepare(
            "SELECT Id, Code_saison, Code_competition FROM kp_journee WHERE Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Gameday not found'], Response::HTTP_NOT_FOUND);
        }

        // Validate and format value
        $maxLen = $allowedFields[$field];
        if (in_array($field, ['Niveau', 'Etape', 'Nbequipes'])) {
            $value = $value !== '' ? (int) $value : null;
        } elseif (in_array($field, ['Date_debut', 'Date_fin'])) {
            // Accept YYYY-MM-DD or DD/MM/YYYY
            if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $value, $m)) {
                $value = "$m[3]-$m[2]-$m[1]";
            }
            if ($value !== '' && !preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                return $this->json(['message' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
            }
            if ($value === '') {
                $value = null;
            }
        } elseif ($maxLen !== null) {
            $value = $value !== '' ? substr((string) $value, 0, $maxLen) : null;
        }

        $this->connection->update('kp_journee', [$field => $value, 'Code_uti' => $user?->getUserIdentifier()], ['Id' => $id]);

        return $this->json(['id' => $id, 'field' => $field, 'value' => $value]);
    }

    /**
     * Duplicate a gameday
     */
    #[Route('/{id}/duplicate', name: 'admin_gamedays_duplicate', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function duplicate(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $includeMatches = !empty($data['includeMatches']);

        // Fetch source gameday
        $source = $this->connection->prepare(
            "SELECT * FROM kp_journee WHERE Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$source) {
            return $this->json(['message' => 'Gameday not found'], Response::HTTP_NOT_FOUND);
        }

        // Get next ID for journee (max + 1, with upper limit to avoid conflicts with existing federal IDs > 19000000)
        $nextId = (int) $this->connection->executeQuery("SELECT COALESCE(MAX(Id), 0) + 1 FROM kp_journee WHERE Id < 19000001")->fetchOne();

        $this->connection->beginTransaction();
        try {
            // Copy gameday
            $insertData = $source;
            $insertData['Id'] = $nextId;
            $insertData['Id_dupli'] = $id;
            $insertData['Publication'] = 'N';
            $insertData['Code_uti'] = $user?->getUserIdentifier();

            $this->connection->insert('kp_journee', $insertData);

            // Optionally duplicate matches
            $matchCount = 0;
            if ($includeMatches) {
                $matches = $this->connection->prepare(
                    "SELECT * FROM kp_match WHERE Id_journee = ?"
                )->executeQuery([$id])->fetchAllAssociative();

                $nextMatchId = (int) $this->connection->executeQuery("SELECT COALESCE(MAX(Id), 0) FROM kp_match")->fetchOne();

                foreach ($matches as $match) {
                    $nextMatchId++;
                    $match['Id'] = $nextMatchId;
                    $match['Id_journee'] = $nextId;
                    $match['Validation'] = 'N';
                    $this->connection->insert('kp_match', $match);
                    $matchCount++;
                }
            }

            $this->connection->commit();

            $this->logActionForSeason(
                'Duplication journee',
                $source['Code_saison'],
                "{$source['Code_competition']}: Id $id -> $nextId" . ($matchCount > 0 ? " ($matchCount matchs)" : '')
            );

            return $this->json([
                'id' => $nextId,
                'matchCount' => $matchCount,
                'message' => 'Gameday duplicated',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a gameday
     */
    #[Route('/{id}', name: 'admin_gamedays_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function delete(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $row = $this->connection->prepare(
            "SELECT Id, Code_competition, Code_saison, Phase FROM kp_journee WHERE Id = ?"
        )->executeQuery([$id])->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Gameday not found'], Response::HTTP_NOT_FOUND);
        }

        // Check for matches
        $matchCount = (int) $this->connection->prepare(
            "SELECT COUNT(*) FROM kp_match WHERE Id_journee = ?"
        )->executeQuery([$id])->fetchOne();

        if ($matchCount > 0) {
            return $this->json([
                'message' => 'Matches still exist in this gameday. Cannot delete.',
                'code' => 'HAS_MATCHES',
            ], Response::HTTP_CONFLICT);
        }

        // Check for event associations
        $eventCount = (int) $this->connection->prepare(
            "SELECT COUNT(*) FROM kp_evenement_journee WHERE Id_journee = ?"
        )->executeQuery([$id])->fetchOne();

        if ($eventCount > 0) {
            return $this->json([
                'message' => 'This gameday is linked to an event. Cannot delete.',
                'code' => 'HAS_EVENTS',
            ], Response::HTTP_CONFLICT);
        }

        $this->connection->delete('kp_journee', ['Id' => $id]);

        $this->logActionForSeason(
            'Suppression journee',
            $row['Code_saison'],
            "{$row['Code_competition']}: {$row['Phase']} (Id: $id)"
        );

        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Bulk toggle publication
     */
    #[Route('/bulk/publication', name: 'admin_gamedays_bulk_publication', methods: ['PATCH'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function bulkPublication(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Toggle: if any are unpublished, publish all. Otherwise unpublish all.
        $sql = "SELECT COUNT(*) FROM kp_journee WHERE Id IN ($placeholders) AND (Publication IS NULL OR Publication = 'N')";
        $unpublishedCount = (int) $this->connection->prepare($sql)->executeQuery($ids)->fetchOne();

        $newValue = $unpublishedCount > 0 ? 'O' : 'N';

        $sql = "UPDATE kp_journee SET Publication = ? WHERE Id IN ($placeholders)";
        $this->connection->prepare($sql)->executeStatement(array_merge([$newValue], $ids));

        $this->logActionForSeason(
            'Publication masse journees',
            null,
            count($ids) . " journees -> $newValue"
        );

        return $this->json([
            'updated' => count($ids),
            'publication' => $newValue === 'O',
        ]);
    }

    /**
     * Bulk calendar update (Nom, Dates, Lieu, Departement)
     */
    #[Route('/bulk/calendar', name: 'admin_gamedays_bulk_calendar', methods: ['PATCH'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function bulkCalendar(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        // Build SET clause from non-empty fields
        $sets = [];
        $params = [];

        if (!empty($data['nom'])) {
            $sets[] = 'Nom = ?';
            $params[] = substr($data['nom'], 0, 80);
        }
        if (!empty($data['dateDebut'])) {
            $sets[] = 'Date_debut = ?';
            $params[] = $data['dateDebut'];
        }
        if (!empty($data['dateFin'])) {
            $sets[] = 'Date_fin = ?';
            $params[] = $data['dateFin'];
        }
        if (!empty($data['lieu'])) {
            $sets[] = 'Lieu = ?';
            $params[] = substr($data['lieu'], 0, 40);
        }
        if (!empty($data['departement'])) {
            $sets[] = 'Departement = ?';
            $params[] = substr($data['departement'], 0, 3);
        }

        if (empty($sets)) {
            return $this->json(['message' => 'No fields to update'], Response::HTTP_BAD_REQUEST);
        }

        $sets[] = 'Code_uti = ?';
        $params[] = $user?->getUserIdentifier();

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $setClause = implode(', ', $sets);

        $sql = "UPDATE kp_journee SET $setClause WHERE Id IN ($placeholders)";
        $this->connection->prepare($sql)->executeStatement(array_merge($params, $ids));

        $this->logActionForSeason(
            'Modification masse calendrier',
            null,
            count($ids) . ' journees modifiees'
        );

        return $this->json(['updated' => count($ids)]);
    }

    /**
     * Bulk copy officials + calendar from a source gameday to target gamedays
     */
    #[Route('/bulk/officials', name: 'admin_gamedays_bulk_officials', methods: ['PATCH'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function bulkOfficials(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $sourceId = (int) ($data['sourceId'] ?? 0);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);

        if ($sourceId <= 0) {
            return $this->json(['message' => 'Source gameday ID required'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($ids)) {
            return $this->json(['message' => 'No target IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        // Remove source from targets if present
        $ids = array_values(array_filter($ids, fn($id) => $id !== $sourceId));
        if (empty($ids)) {
            return $this->json(['message' => 'No target IDs after excluding source'], Response::HTTP_BAD_REQUEST);
        }

        // Fetch source gameday
        $source = $this->connection->prepare(
            "SELECT Nom, Date_debut, Date_fin, Lieu, Departement, Plan_eau,
                    Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre,
                    Rep_athletes, Arb_nj1, Arb_nj2, Arb_nj3, Arb_nj4, Arb_nj5,
                    Code_saison, Code_competition
             FROM kp_journee WHERE Id = ?"
        )->executeQuery([$sourceId])->fetchAssociative();

        if (!$source) {
            return $this->json(['message' => 'Source gameday not found'], Response::HTTP_NOT_FOUND);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE kp_journee SET
                    Nom = ?, Date_debut = ?, Date_fin = ?, Lieu = ?, Departement = ?, Plan_eau = ?,
                    Responsable_insc = ?, Responsable_R1 = ?, Organisateur = ?,
                    Delegue = ?, ChefArbitre = ?, Rep_athletes = ?,
                    Arb_nj1 = ?, Arb_nj2 = ?, Arb_nj3 = ?, Arb_nj4 = ?, Arb_nj5 = ?,
                    Code_uti = ?
                WHERE Id IN ($placeholders)";

        $params = [
            $source['Nom'], $source['Date_debut'], $source['Date_fin'],
            $source['Lieu'], $source['Departement'], $source['Plan_eau'],
            $source['Responsable_insc'], $source['Responsable_R1'], $source['Organisateur'],
            $source['Delegue'], $source['ChefArbitre'], $source['Rep_athletes'],
            $source['Arb_nj1'], $source['Arb_nj2'], $source['Arb_nj3'],
            $source['Arb_nj4'], $source['Arb_nj5'],
            $user?->getUserIdentifier(),
        ];

        $this->connection->prepare($sql)->executeStatement(array_merge($params, $ids));

        $this->logActionForSeason(
            'Copie officiels+calendrier',
            $source['Code_saison'],
            "{$source['Code_competition']}: source $sourceId -> " . count($ids) . ' cibles'
        );

        return $this->json(['updated' => count($ids)]);
    }

    /**
     * Bulk delete gamedays
     */
    #[Route('/bulk', name: 'admin_gamedays_bulk_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_COMPETITION')]
    public function bulkDelete(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $ids = array_filter(array_map('intval', $data['ids'] ?? []), fn($id) => $id > 0);

        if (empty($ids)) {
            return $this->json(['message' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $deleted = 0;
        $skipped = [];

        foreach ($ids as $id) {
            // Check matches
            $matchCount = (int) $this->connection->prepare(
                "SELECT COUNT(*) FROM kp_match WHERE Id_journee = ?"
            )->executeQuery([$id])->fetchOne();

            if ($matchCount > 0) {
                $skipped[] = ['id' => $id, 'reason' => 'has_matches'];
                continue;
            }

            // Check events
            $eventCount = (int) $this->connection->prepare(
                "SELECT COUNT(*) FROM kp_evenement_journee WHERE Id_journee = ?"
            )->executeQuery([$id])->fetchOne();

            if ($eventCount > 0) {
                $skipped[] = ['id' => $id, 'reason' => 'has_events'];
                continue;
            }

            $this->connection->delete('kp_journee', ['Id' => $id]);
            $deleted++;
        }

        if ($deleted > 0) {
            $this->logActionForSeason('Suppression masse journees', null, "$deleted journee(s) supprimee(s)");
        }

        return $this->json([
            'deleted' => $deleted,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Associate gameday with event
     */
    #[Route('/{id}/event/{eventId}', name: 'admin_gamedays_link_event', methods: ['PUT'], requirements: ['id' => '\d+', 'eventId' => '\d+'])]
    #[IsGranted('ROLE_DIVISION')]
    public function linkEvent(int $id, int $eventId): JsonResponse
    {
        // Verify gameday exists
        $exists = (int) $this->connection->prepare(
            "SELECT COUNT(*) FROM kp_journee WHERE Id = ?"
        )->executeQuery([$id])->fetchOne();

        if (!$exists) {
            return $this->json(['message' => 'Gameday not found'], Response::HTTP_NOT_FOUND);
        }

        // Use REPLACE INTO for idempotent association
        $sql = "REPLACE INTO kp_evenement_journee (Id_evenement, Id_journee) VALUES (?, ?)";
        $this->connection->prepare($sql)->executeStatement([$eventId, $id]);

        $this->logActionForEvent('Association journee-evenement', $eventId, "Journee $id");

        return $this->json(['message' => 'Event linked']);
    }

    /**
     * Dissociate gameday from event
     */
    #[Route('/{id}/event/{eventId}', name: 'admin_gamedays_unlink_event', methods: ['DELETE'], requirements: ['id' => '\d+', 'eventId' => '\d+'])]
    #[IsGranted('ROLE_DIVISION')]
    public function unlinkEvent(int $id, int $eventId): JsonResponse
    {
        $this->connection->delete('kp_evenement_journee', [
            'Id_evenement' => $eventId,
            'Id_journee' => $id,
        ]);

        $this->logActionForEvent('Dissociation journee-evenement', $eventId, "Journee $id");

        return $this->json(['message' => 'Event unlinked']);
    }

    /**
     * List events (for the event filter dropdown)
     */
    #[Route('/events', name: 'admin_gamedays_events', methods: ['GET'])]
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
     * Autocomplete gameday names (kp_journee_ref)
     */
    #[Route('/autocomplete/names', name: 'admin_gamedays_autocomplete_names', methods: ['GET'])]
    public function autocompleteNames(Request $request): JsonResponse
    {
        $q = $request->query->get('q', '');
        if (strlen($q) < 2) {
            return $this->json([]);
        }

        $sql = "SELECT nom FROM kp_journee_ref WHERE nom LIKE ? ORDER BY nom LIMIT 20";
        $rows = $this->connection->prepare($sql)->executeQuery(["%$q%"])->fetchAllAssociative();

        return $this->json(array_map(fn($r) => $r['nom'], $rows));
    }

    /**
     * Autocomplete communes (villes_france_free)
     */
    #[Route('/autocomplete/communes', name: 'admin_gamedays_autocomplete_communes', methods: ['GET'])]
    public function autocompleteCommunes(Request $request): JsonResponse
    {
        $q = trim($request->query->get('q', ''));
        if (strlen($q) < 2) {
            return $this->json([]);
        }

        $sql = "SELECT ville_nom_reel, ville_code_postal, ville_departement
                FROM villes_france_free
                WHERE ville_nom_reel LIKE ? OR ville_nom_simple LIKE ?
                ORDER BY ville_nom_reel
                LIMIT 20";

        $likeQ = "%$q%";
        $rows = $this->connection->fetchAllAssociative($sql, [$likeQ, $likeQ]);

        return $this->json(array_map(fn(array $r) => [
            'label' => $r['ville_nom_reel'],
            'detail' => $r['ville_code_postal'] . ' - ' . $r['ville_departement'],
            'departement' => $r['ville_departement'],
        ], $rows));
    }
}
