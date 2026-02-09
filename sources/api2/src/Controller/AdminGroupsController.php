<?php

namespace App\Controller;

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
 * Admin Groups Controller
 *
 * CRUD operations for competition groups management (kp_groupe table)
 */
#[Route('/admin/groups')]
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '27. App4 - Groups')]
class AdminGroupsController extends AbstractController
{
    use AdminLoggableTrait;

    private const SECTION_LABELS = [
        1 => 'International',
        2 => 'National',
        3 => 'Régional',
        4 => 'Tournoi',
        5 => 'Continental',
        100 => 'Divers',
    ];

    private const VALID_SECTIONS = [1, 2, 3, 4, 5, 100];
    private const VALID_NIVEAUX = ['REG', 'NAT', 'INT'];

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * List all groups ordered by section and ordre
     */
    #[Route('', name: 'admin_groups_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        $sectionFilter = $request->query->get('section', '');

        // Build query
        $whereClause = '';
        $params = [];

        $conditions = [];
        if (!empty($search)) {
            $conditions[] = "(g.Groupe LIKE ? OR g.Libelle LIKE ? OR g.Libelle_en LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if (!empty($sectionFilter) && is_numeric($sectionFilter)) {
            $conditions[] = "g.section = ?";
            $params[] = (int) $sectionFilter;
        }

        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        // Get groups with competition count and distinct code count
        $sql = "SELECT g.id, g.section, g.ordre, g.Code_niveau, g.Groupe, g.Libelle, g.Libelle_en,
                       COUNT(c.Code) as competitionCount,
                       COUNT(DISTINCT c.Code) as distinctCodeCount
                FROM kp_groupe g
                LEFT JOIN kp_competition c ON c.Code_ref = g.Groupe
                $whereClause
                GROUP BY g.id, g.section, g.ordre, g.Code_niveau, g.Groupe, g.Libelle, g.Libelle_en
                ORDER BY g.section ASC, g.ordre ASC";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $groups = $result->fetchAllAssociative();

        $items = array_map(function ($group) {
            return [
                'id' => (int) $group['id'],
                'section' => (int) $group['section'],
                'sectionName' => self::SECTION_LABELS[(int) $group['section']] ?? 'Inconnu',
                'ordre' => (int) $group['ordre'],
                'codeNiveau' => $group['Code_niveau'],
                'groupe' => $group['Groupe'],
                'libelle' => $group['Libelle'],
                'libelleEn' => $group['Libelle_en'] ?? '',
                'competitionCount' => (int) $group['competitionCount'],
                'distinctCodeCount' => (int) $group['distinctCodeCount'],
            ];
        }, $groups);

        return $this->json([
            'items' => $items,
            'total' => count($items),
        ]);
    }

    /**
     * Create a new group
     */
    #[Route('', name: 'admin_groups_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate
        $errors = $this->validateGroupData($data);
        if (!empty($errors)) {
            return $this->json(['message' => implode('. ', $errors)], Response::HTTP_BAD_REQUEST);
        }

        $section = (int) $data['section'];
        $ordre = (int) $data['ordre'];
        $codeNiveau = $data['codeNiveau'];
        $groupe = trim($data['groupe']);
        $libelle = trim($data['libelle']);
        $libelleEn = trim($data['libelleEn'] ?? '');

        // Check uniqueness of group code
        $checkSql = "SELECT id FROM kp_groupe WHERE Groupe = ?";
        $stmt = $this->connection->prepare($checkSql);
        $result = $stmt->executeQuery([$groupe]);
        if ($result->fetchOne()) {
            return $this->json(['message' => "Le code groupe '$groupe' existe déjà"], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check uniqueness of ordre within section
        $checkOrdreSql = "SELECT id FROM kp_groupe WHERE section = ? AND ordre = ?";
        $stmt = $this->connection->prepare($checkOrdreSql);
        $result = $stmt->executeQuery([$section, $ordre]);
        if ($result->fetchOne()) {
            return $this->json(['message' => "L'ordre $ordre est déjà utilisé dans cette section"], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->connection->beginTransaction();
        try {
            // Insert new group
            $insertSql = "INSERT INTO kp_groupe (section, ordre, Code_niveau, Groupe, Libelle, Libelle_en)
                          VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($insertSql);
            $stmt->executeStatement([$section, $ordre, $codeNiveau, $groupe, $libelle, $libelleEn ?: null]);

            $id = (int) $this->connection->lastInsertId();

            $this->connection->commit();

            // Log action
            $this->logAction('Ajout Groupe', "$groupe - $libelle");

            return $this->json([
                'id' => $id,
                'section' => $section,
                'sectionName' => self::SECTION_LABELS[$section] ?? 'Inconnu',
                'ordre' => $ordre,
                'codeNiveau' => $codeNiveau,
                'groupe' => $groupe,
                'libelle' => $libelle,
                'libelleEn' => $libelleEn,
                'competitionCount' => 0,
                'distinctCodeCount' => 0,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Erreur lors de la création du groupe'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update an existing group
     */
    #[Route('/{id}', name: 'admin_groups_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        // Check if group exists
        $currentSql = "SELECT id, Groupe, section, ordre FROM kp_groupe WHERE id = ?";
        $stmt = $this->connection->prepare($currentSql);
        $result = $stmt->executeQuery([$id]);
        $current = $result->fetchAssociative();

        if (!$current) {
            return $this->json(['message' => 'Groupe non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Validate
        $errors = $this->validateGroupData($data);
        if (!empty($errors)) {
            return $this->json(['message' => implode('. ', $errors)], Response::HTTP_BAD_REQUEST);
        }

        $section = (int) $data['section'];
        $ordre = (int) $data['ordre'];
        $codeNiveau = $data['codeNiveau'];
        $groupe = trim($data['groupe']);
        $libelle = trim($data['libelle']);
        $libelleEn = trim($data['libelleEn'] ?? '');
        $oldGroupe = $current['Groupe'];

        // Check uniqueness if code changed
        if ($groupe !== $oldGroupe) {
            $checkSql = "SELECT id FROM kp_groupe WHERE Groupe = ? AND id != ?";
            $stmt = $this->connection->prepare($checkSql);
            $result = $stmt->executeQuery([$groupe, $id]);
            if ($result->fetchOne()) {
                return $this->json(['message' => "Le code groupe '$groupe' existe déjà"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        // Check uniqueness of ordre within section (exclude self)
        if ($section !== (int) $current['section'] || $ordre !== (int) $current['ordre']) {
            $checkOrdreSql = "SELECT id FROM kp_groupe WHERE section = ? AND ordre = ? AND id != ?";
            $stmt = $this->connection->prepare($checkOrdreSql);
            $result = $stmt->executeQuery([$section, $ordre, $id]);
            if ($result->fetchOne()) {
                return $this->json(['message' => "L'ordre $ordre est déjà utilisé dans cette section"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $this->connection->beginTransaction();
        try {
            // If group code changed, update references in kp_competition
            if ($groupe !== $oldGroupe) {
                $this->connection->executeStatement("SET FOREIGN_KEY_CHECKS = 0");
                $updateRefSql = "UPDATE kp_competition SET Code_ref = ? WHERE Code_ref = ?";
                $stmt = $this->connection->prepare($updateRefSql);
                $stmt->executeStatement([$groupe, $oldGroupe]);
                $this->connection->executeStatement("SET FOREIGN_KEY_CHECKS = 1");
            }

            // Update group
            $updateSql = "UPDATE kp_groupe SET section = ?, ordre = ?, Code_niveau = ?, Groupe = ?, Libelle = ?, Libelle_en = ?
                          WHERE id = ?";
            $stmt = $this->connection->prepare($updateSql);
            $stmt->executeStatement([$section, $ordre, $codeNiveau, $groupe, $libelle, $libelleEn ?: null, $id]);

            $this->connection->commit();

            // Log action
            $details = $groupe !== $oldGroupe ? "Code: $oldGroupe -> $groupe" : $groupe;
            $this->logAction('Modif Groupe', $details);

            return $this->json([
                'id' => $id,
                'section' => $section,
                'sectionName' => self::SECTION_LABELS[$section] ?? 'Inconnu',
                'ordre' => $ordre,
                'codeNiveau' => $codeNiveau,
                'groupe' => $groupe,
                'libelle' => $libelle,
                'libelleEn' => $libelleEn,
            ]);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            // Make sure FK checks are re-enabled
            try {
                $this->connection->executeStatement("SET FOREIGN_KEY_CHECKS = 1");
            } catch (\Exception) {
            }
            return $this->json(['message' => 'Erreur lors de la modification du groupe'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a group (Super Admin only)
     */
    #[Route('/{id}', name: 'admin_groups_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        // Get group info
        $sql = "SELECT id, Groupe, Libelle, section, ordre FROM kp_groupe WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $group = $result->fetchAssociative();

        if (!$group) {
            return $this->json(['message' => 'Groupe non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Check for linked competitions
        $checkSql = "SELECT Code FROM kp_competition WHERE Code_ref = ?";
        $stmt = $this->connection->prepare($checkSql);
        $result = $stmt->executeQuery([$group['Groupe']]);
        $linkedCompetitions = $result->fetchAllAssociative();

        if (!empty($linkedCompetitions)) {
            $codes = array_map(fn($c) => $c['Code'], $linkedCompetitions);
            $count = count($codes);
            $list = implode(', ', array_slice($codes, 0, 10));
            if ($count > 10) {
                $list .= '...';
            }
            return $this->json([
                'message' => "Impossible de supprimer le groupe {$group['Groupe']}. $count compétition(s) liée(s) : $list."
            ], Response::HTTP_CONFLICT);
        }

        $this->connection->beginTransaction();
        try {
            $section = (int) $group['section'];
            $ordre = (int) $group['ordre'];

            // Delete group
            $deleteSql = "DELETE FROM kp_groupe WHERE id = ?";
            $stmt = $this->connection->prepare($deleteSql);
            $stmt->executeStatement([$id]);

            // Reorder remaining groups in the same section
            $reorderSql = "UPDATE kp_groupe SET ordre = ordre - 1 WHERE section = ? AND ordre > ?";
            $stmt = $this->connection->prepare($reorderSql);
            $stmt->executeStatement([$section, $ordre]);

            $this->connection->commit();

            // Log action
            $this->logAction('Suppression Groupe', "{$group['Groupe']} - {$group['Libelle']}");

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Erreur lors de la suppression du groupe'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reorder a group (move up or down within its section)
     */
    #[Route('/{id}/reorder', name: 'admin_groups_reorder', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function reorder(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $direction = $data['direction'] ?? '';

        if (!in_array($direction, ['up', 'down'])) {
            return $this->json(['message' => 'Direction must be "up" or "down"'], Response::HTTP_BAD_REQUEST);
        }

        // Get current group
        $sql = "SELECT id, section, ordre, Groupe FROM kp_groupe WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $current = $result->fetchAssociative();

        if (!$current) {
            return $this->json(['message' => 'Groupe non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $section = (int) $current['section'];
        $currentOrdre = (int) $current['ordre'];

        // Find the adjacent group
        if ($direction === 'up') {
            $adjacentSql = "SELECT id, ordre FROM kp_groupe WHERE section = ? AND ordre < ? ORDER BY ordre DESC LIMIT 1";
        } else {
            $adjacentSql = "SELECT id, ordre FROM kp_groupe WHERE section = ? AND ordre > ? ORDER BY ordre ASC LIMIT 1";
        }

        $stmt = $this->connection->prepare($adjacentSql);
        $result = $stmt->executeQuery([$section, $currentOrdre]);
        $adjacent = $result->fetchAssociative();

        if (!$adjacent) {
            return $this->json(['message' => 'Cannot move further in this direction'], Response::HTTP_BAD_REQUEST);
        }

        // Swap ordre values
        $this->connection->beginTransaction();
        try {
            $swap1 = "UPDATE kp_groupe SET ordre = ? WHERE id = ?";
            $stmt = $this->connection->prepare($swap1);
            $stmt->executeStatement([(int) $adjacent['ordre'], $id]);

            $swap2 = "UPDATE kp_groupe SET ordre = ? WHERE id = ?";
            $stmt = $this->connection->prepare($swap2);
            $stmt->executeStatement([$currentOrdre, (int) $adjacent['id']]);

            $this->connection->commit();

            // Log action
            $this->logAction('Reorder Groupe', "{$current['Groupe']} $direction");

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Erreur lors du réordonnancement'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validate group data
     */
    private function validateGroupData(array $data): array
    {
        $errors = [];

        // Section
        $section = $data['section'] ?? null;
        if ($section === null || !in_array((int) $section, self::VALID_SECTIONS)) {
            $errors[] = 'Section invalide';
        }

        // Code niveau
        $codeNiveau = $data['codeNiveau'] ?? '';
        if (!in_array($codeNiveau, self::VALID_NIVEAUX)) {
            $errors[] = 'Niveau invalide (REG, NAT ou INT)';
        }

        // Ordre
        $ordre = $data['ordre'] ?? null;
        if ($ordre === null || (int) $ordre < 1 || (int) $ordre > 99999) {
            $errors[] = 'Ordre invalide (1-99999)';
        }

        // Groupe code
        $groupe = trim($data['groupe'] ?? '');
        if (empty($groupe)) {
            $errors[] = 'Le code groupe est obligatoire';
        } elseif (strlen($groupe) > 10) {
            $errors[] = 'Le code groupe doit faire 10 caractères maximum';
        }

        // Libelle
        $libelle = trim($data['libelle'] ?? '');
        if (empty($libelle)) {
            $errors[] = 'Le libellé est obligatoire';
        } elseif (strlen($libelle) > 50) {
            $errors[] = 'Le libellé doit faire 50 caractères maximum';
        }

        // Libelle EN (optional)
        $libelleEn = trim($data['libelleEn'] ?? '');
        if (strlen($libelleEn) > 255) {
            $errors[] = 'Le libellé EN doit faire 255 caractères maximum';
        }

        return $errors;
    }

    /**
     * Log a group action
     */
    private function logAction(string $action, ?string $details = null): void
    {
        try {
            $user = $this->getUser();
            $userId = $user?->getUserIdentifier() ?? 'system';

            $sql = "INSERT INTO kp_journal (Date, Heure, User, Action, Details)
                    VALUES (CURDATE(), CURTIME(), ?, ?, ?)";

            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$userId, $action, $details]);
        } catch (\Exception) {
            // Log silently fails
        }
    }
}
