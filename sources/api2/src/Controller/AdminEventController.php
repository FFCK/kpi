<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin Event Controller
 *
 * CRUD operations for events management (kp_evenement table)
 */
#[Route('/admin/events')]
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '22. App4 - Events')]
class AdminEventController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * List all events with pagination
     */
    #[Route('', name: 'admin_events_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        // Optional filters
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sortBy', 'Date_debut');
        $sortOrder = strtoupper($request->query->get('sortOrder', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        // Validate sortBy column
        $allowedSortColumns = ['Id', 'Libelle', 'Lieu', 'Date_debut', 'Date_fin', 'Publication', 'app'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'Date_debut';
        }

        // Build query
        $whereClause = '';
        $params = [];

        if (!empty($search)) {
            // Check if search is a numeric ID
            if (is_numeric($search)) {
                $whereClause = "WHERE Id = ? OR Libelle LIKE ? OR Lieu LIKE ?";
                $params = [(int) $search, "%$search%", "%$search%"];
            } else {
                $whereClause = "WHERE Libelle LIKE ? OR Lieu LIKE ?";
                $params = ["%$search%", "%$search%"];
            }
        }

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM kp_evenement $whereClause";
        $stmt = $this->connection->prepare($countSql);
        $result = $stmt->executeQuery($params);
        $total = (int) $result->fetchOne();

        // Get events
        $sql = "SELECT Id, Libelle, Lieu, Date_debut, Date_fin, Publication, app
                FROM kp_evenement
                $whereClause
                ORDER BY $sortBy $sortOrder
                LIMIT $limit OFFSET $offset";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $events = $result->fetchAllAssociative();

        // Format events
        $items = array_map(function ($event) {
            return [
                'id' => (int) $event['Id'],
                'libelle' => $event['Libelle'],
                'lieu' => $event['Lieu'],
                'dateDebut' => $event['Date_debut'],
                'dateFin' => $event['Date_fin'],
                'publication' => $event['Publication'] === 'O',
                'app' => $event['app'] === 'O',
            ];
        }, $events);

        return $this->json([
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int) ceil($total / $limit),
        ]);
    }

    /**
     * Get a single event by ID
     */
    #[Route('/{id}', name: 'admin_events_get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(int $id): JsonResponse
    {
        $sql = "SELECT Id, Libelle, Lieu, Date_debut, Date_fin, Publication, app
                FROM kp_evenement
                WHERE Id = ?";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $event = $result->fetchAssociative();

        if (!$event) {
            return $this->json(['message' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => (int) $event['Id'],
            'libelle' => $event['Libelle'],
            'lieu' => $event['Lieu'],
            'dateDebut' => $event['Date_debut'],
            'dateFin' => $event['Date_fin'],
            'publication' => $event['Publication'] === 'O',
            'app' => $event['app'] === 'O',
        ]);
    }

    /**
     * Create a new event
     */
    #[Route('', name: 'admin_events_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        $libelle = trim($data['libelle'] ?? '');
        if (empty($libelle)) {
            return $this->json(['message' => 'Libelle is required'], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($libelle) > 40) {
            return $this->json(['message' => 'Libelle must be 40 characters or less'], Response::HTTP_BAD_REQUEST);
        }

        $lieu = trim($data['lieu'] ?? '');
        if (strlen($lieu) > 40) {
            return $this->json(['message' => 'Lieu must be 40 characters or less'], Response::HTTP_BAD_REQUEST);
        }

        $dateDebut = $data['dateDebut'] ?? null;
        $dateFin = $data['dateFin'] ?? null;

        // Validate dates if provided
        if ($dateDebut && !$this->isValidDate($dateDebut)) {
            return $this->json(['message' => 'Invalid dateDebut format'], Response::HTTP_BAD_REQUEST);
        }
        if ($dateFin && !$this->isValidDate($dateFin)) {
            return $this->json(['message' => 'Invalid dateFin format'], Response::HTTP_BAD_REQUEST);
        }

        // Insert event
        $sql = "INSERT INTO kp_evenement (Libelle, Lieu, Date_debut, Date_fin, Publication, app)
                VALUES (?, ?, ?, ?, 'N', 'N')";

        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement([$libelle, $lieu ?: null, $dateDebut, $dateFin]);

        $id = (int) $this->connection->lastInsertId();

        // Log action
        $this->logAction('Ajout Evenement', $id, $libelle);

        return $this->json([
            'id' => $id,
            'libelle' => $libelle,
            'lieu' => $lieu ?: null,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'publication' => false,
            'app' => false,
        ], Response::HTTP_CREATED);
    }

    /**
     * Update an existing event
     */
    #[Route('/{id}', name: 'admin_events_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        // Check if event exists
        $checkSql = "SELECT Id FROM kp_evenement WHERE Id = ?";
        $stmt = $this->connection->prepare($checkSql);
        $result = $stmt->executeQuery([$id]);
        if (!$result->fetchOne()) {
            return $this->json(['message' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Validate required fields
        $libelle = trim($data['libelle'] ?? '');
        if (empty($libelle)) {
            return $this->json(['message' => 'Libelle is required'], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($libelle) > 40) {
            return $this->json(['message' => 'Libelle must be 40 characters or less'], Response::HTTP_BAD_REQUEST);
        }

        $lieu = trim($data['lieu'] ?? '');
        if (strlen($lieu) > 40) {
            return $this->json(['message' => 'Lieu must be 40 characters or less'], Response::HTTP_BAD_REQUEST);
        }

        $dateDebut = $data['dateDebut'] ?? null;
        $dateFin = $data['dateFin'] ?? null;

        // Validate dates if provided
        if ($dateDebut && !$this->isValidDate($dateDebut)) {
            return $this->json(['message' => 'Invalid dateDebut format'], Response::HTTP_BAD_REQUEST);
        }
        if ($dateFin && !$this->isValidDate($dateFin)) {
            return $this->json(['message' => 'Invalid dateFin format'], Response::HTTP_BAD_REQUEST);
        }

        // Update event
        $sql = "UPDATE kp_evenement
                SET Libelle = ?, Lieu = ?, Date_debut = ?, Date_fin = ?
                WHERE Id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement([$libelle, $lieu ?: null, $dateDebut, $dateFin, $id]);

        // Log action
        $this->logAction('Modif Evenement', $id);

        return $this->json([
            'id' => $id,
            'libelle' => $libelle,
            'lieu' => $lieu ?: null,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
        ]);
    }

    /**
     * Delete an event (Super Admin only)
     */
    #[Route('/{id}', name: 'admin_events_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        // Check if event exists
        $checkSql = "SELECT Id FROM kp_evenement WHERE Id = ?";
        $stmt = $this->connection->prepare($checkSql);
        $result = $stmt->executeQuery([$id]);
        if (!$result->fetchOne()) {
            return $this->json(['message' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $sql = "DELETE FROM kp_evenement WHERE Id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$id]);

            // Log action
            $this->logAction('Suppression Evenement', $id);

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Cannot delete event: it may have related data'
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Delete multiple events (Super Admin only)
     */
    #[Route('/bulk-delete', name: 'admin_events_bulk_delete', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function bulkDelete(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            return $this->json(['message' => 'No event IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        // Filter to integers only
        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (empty($ids)) {
            return $this->json(['message' => 'No valid event IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "DELETE FROM kp_evenement WHERE Id IN ($placeholders)";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement($ids);

            // Log action
            $this->logAction('Suppression Evenements', null, implode(',', $ids));

            return $this->json(['deleted' => count($ids)]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Cannot delete some events: they may have related data'
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Toggle event publication status
     */
    #[Route('/{id}/publish', name: 'admin_events_toggle_publish', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function togglePublish(int $id): JsonResponse
    {
        // Get current value
        $sql = "SELECT Publication FROM kp_evenement WHERE Id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $current = $result->fetchOne();

        if ($current === false) {
            return $this->json(['message' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        $newValue = $current === 'O' ? 'N' : 'O';

        $updateSql = "UPDATE kp_evenement SET Publication = ? WHERE Id = ?";
        $stmt = $this->connection->prepare($updateSql);
        $stmt->executeStatement([$newValue, $id]);

        // Log action
        $this->logAction('Publication evenement', $id, $newValue);

        return $this->json([
            'id' => $id,
            'publication' => $newValue === 'O',
        ]);
    }

    /**
     * Toggle event app visibility status
     */
    #[Route('/{id}/app', name: 'admin_events_toggle_app', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function toggleApp(int $id): JsonResponse
    {
        // Get current value
        $sql = "SELECT app FROM kp_evenement WHERE Id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $current = $result->fetchOne();

        if ($current === false) {
            return $this->json(['message' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        $newValue = $current === 'O' ? 'N' : 'O';

        $updateSql = "UPDATE kp_evenement SET app = ? WHERE Id = ?";
        $stmt = $this->connection->prepare($updateSql);
        $stmt->executeStatement([$newValue, $id]);

        // Log action
        $this->logAction('App evenement', $id, $newValue);

        return $this->json([
            'id' => $id,
            'app' => $newValue === 'O',
        ]);
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    private function isValidDate(?string $date): bool
    {
        if (empty($date)) {
            return true;
        }
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Log admin action to journal table
     */
    private function logAction(string $action, ?int $eventId = null, ?string $details = null): void
    {
        try {
            $user = $this->getUser();
            $userId = $user?->getUserIdentifier() ?? 'system';

            $sql = "INSERT INTO kp_journal (Date, Heure, User, Action, Code_evenement, Details)
                    VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?)";

            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$userId, $action, $eventId, $details]);
        } catch (\Exception $e) {
            // Log silently fails - don't break the main operation
        }
    }
}
