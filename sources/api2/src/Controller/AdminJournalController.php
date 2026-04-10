<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin Journal Controller
 *
 * Read-only access to the activity log (kp_journal table)
 */
#[Route('/admin/journal')]
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '23. App4 - Journal')]
class AdminJournalController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * List journal entries with pagination and filters
     */
    #[Route('', name: 'admin_journal_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(200, max(1, (int) $request->query->get('limit', 50)));
        $offset = ($page - 1) * $limit;

        // Optional filters
        $user = $request->query->get('user', '');
        $action = $request->query->get('action', '');
        $actionMode = $request->query->get('actionMode', 'prefix');
        $season = $request->query->get('season', '');
        $competition = $request->query->get('competition', '');
        $search = $request->query->get('search', '');
        $dateFrom = $request->query->get('dateFrom', '');
        $dateTo = $request->query->get('dateTo', '');

        // Build WHERE clauses
        $conditions = [];
        $params = [];

        if (!empty($user)) {
            $conditions[] = 'j.Users = ?';
            $params[] = $user;
        }

        if (!empty($action)) {
            if ($actionMode === 'exact') {
                $conditions[] = 'j.Actions = ?';
                $params[] = $action;
            } else {
                $conditions[] = 'j.Actions LIKE ?';
                $params[] = $action . '%';
            }
        }

        if (!empty($season)) {
            $conditions[] = 'j.Saisons = ?';
            $params[] = $season;
        }

        if (!empty($competition)) {
            $conditions[] = 'j.Competitions LIKE ?';
            $params[] = $competition . '%';
        }

        if (!empty($search)) {
            $conditions[] = '(j.Journal LIKE ? OR j.Actions LIKE ? OR j.Competitions LIKE ? OR j.Matchs LIKE ?)';
            $searchPattern = '%' . $search . '%';
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }

        if (!empty($dateFrom)) {
            $conditions[] = 'j.Dates >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }

        if (!empty($dateTo)) {
            $conditions[] = 'j.Dates <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Count total
        $countSql = "SELECT COUNT(*) FROM kp_journal j $whereClause";
        $stmt = $this->connection->prepare($countSql);
        $total = (int) $stmt->executeQuery($params)->fetchOne();

        // Get journal entries (LIMIT interpolated for MariaDB compatibility)
        $sql = "SELECT j.Id, j.Dates, j.Users, j.Actions, j.Saisons, j.Competitions,
                       j.Evenements, j.Journees, j.Matchs, j.Journal,
                       u.Identite, u.Fonction
                FROM kp_journal j
                INNER JOIN kp_user u ON u.Code = j.Users
                $whereClause
                ORDER BY j.Dates DESC
                LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $entries = $result->fetchAllAssociative();

        $items = array_map(function ($entry) {
            return [
                'id' => (int) $entry['Id'],
                'date' => $entry['Dates'],
                'userCode' => $entry['Users'],
                'userIdentite' => $entry['Identite'],
                'userFonction' => $entry['Fonction'],
                'action' => $entry['Actions'],
                'journal' => $entry['Journal'],
                'saison' => $entry['Saisons'],
                'competition' => $entry['Competitions'],
                'journee' => $entry['Journees'] ? (int) $entry['Journees'] : null,
                'match' => $entry['Matchs'],
            ];
        }, $entries);

        return $this->json([
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int) ceil($total / $limit),
        ]);
    }

    /**
     * Get distinct users who have journal entries
     */
    #[Route('/users', name: 'admin_journal_users', methods: ['GET'])]
    public function users(): JsonResponse
    {
        $sql = "SELECT DISTINCT j.Users AS code, u.Identite AS identite, u.Fonction AS fonction
                FROM kp_journal j
                INNER JOIN kp_user u ON u.Code = j.Users
                ORDER BY u.Identite";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $users = $result->fetchAllAssociative();

        $items = array_map(function ($user) {
            return [
                'code' => $user['code'],
                'identite' => $user['identite'],
                'fonction' => $user['fonction'],
            ];
        }, $users);

        return $this->json($items);
    }

    /**
     * Get distinct actions from the journal
     */
    #[Route('/actions', name: 'admin_journal_actions', methods: ['GET'])]
    public function actions(): JsonResponse
    {
        $sql = "SELECT DISTINCT j.Actions
                FROM kp_journal j
                ORDER BY j.Actions";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $actions = $result->fetchFirstColumn();

        return $this->json($actions);
    }
}
