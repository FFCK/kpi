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
 * Admin RC (Responsables de Compétition) Controller
 *
 * CRUD operations for competition officials management (kp_rc table)
 * Migrated from GestionRc.php
 */
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '27. App4 - RC')]
class AdminRcController extends AbstractController
{
    use AdminLoggableTrait;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * List RC for a season
     */
    #[Route('/admin/rc', name: 'admin_rc_list', methods: ['GET'])]
    #[OA\Get(
        path: '/admin/rc',
        summary: 'List RC for a season',
        tags: ['27. App4 - RC']
    )]
    #[OA\Parameter(
        name: 'season',
        in: 'query',
        required: true,
        description: 'Season code',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'competitions',
        in: 'query',
        required: false,
        description: 'Comma-separated competition codes (optional filter)',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'List of RC with total count'
    )]
    public function list(Request $request): JsonResponse
    {
        $season = $request->query->get('season', '');
        $competitionsFilter = $request->query->get('competitions', '');

        if (empty($season)) {
            return $this->json(['message' => 'Season is required'], Response::HTTP_BAD_REQUEST);
        }

        // Build query
        $sql = "SELECT rc.Id, rc.Code_competition, rc.Code_saison, rc.Ordre, rc.Matric,
                       lc.Nom, lc.Prenom, lc.Numero_club,
                       u.Mail,
                       comp.Libelle AS competition_libelle
                FROM kp_rc rc
                LEFT JOIN kp_licence lc ON rc.Matric = lc.Matric
                LEFT JOIN kp_user u ON rc.Matric = u.Code
                LEFT JOIN kp_competition comp ON rc.Code_competition = comp.Code AND rc.Code_saison = comp.Code_saison
                WHERE rc.Code_saison = ?";

        $params = [$season];

        // Apply competition filter if provided
        if (!empty($competitionsFilter)) {
            $codes = array_filter(array_map('trim', explode(',', $competitionsFilter)));
            if (!empty($codes)) {
                $placeholders = implode(',', array_fill(0, count($codes), '?'));
                $sql .= " AND rc.Code_competition IN ($placeholders)";
                $params = array_merge($params, $codes);
            }
        }

        $sql .= " ORDER BY rc.Code_competition ASC, rc.Ordre ASC";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $rows = $result->fetchAllAssociative();

        $items = array_map(function ($row) {
            return [
                'id' => (int) $row['Id'],
                'competitionCode' => $row['Code_competition'], // null = RC national
                'competitionLabel' => $row['Code_competition']
                    ? $row['competition_libelle'] ?? $row['Code_competition']
                    : 'National (sans compétition)',
                'season' => $row['Code_saison'],
                'ordre' => (int) $row['Ordre'],
                'matric' => (int) $row['Matric'],
                'nom' => $row['Nom'] ?? '',
                'prenom' => $row['Prenom'] ?? '',
                'club' => $row['Numero_club'] ?? '',
                'email' => $row['Mail'] ?? null,
            ];
        }, $rows);

        return $this->json([
            'items' => $items,
            'total' => count($items),
        ]);
    }

    /**
     * Create a new RC
     */
    #[Route('/admin/rc', name: 'admin_rc_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: '/admin/rc',
        summary: 'Create a new RC',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'season', type: 'string', example: '2026'),
                    new OA\Property(property: 'competitionCode', type: 'string', nullable: true, example: 'N1H-A'),
                    new OA\Property(property: 'matric', type: 'integer', example: 12345),
                    new OA\Property(property: 'ordre', type: 'integer', example: 1),
                ]
            )
        ),
        tags: ['27. App4 - RC']
    )]
    #[OA\Response(response: 201, description: 'RC created successfully')]
    #[OA\Response(response: 400, description: 'Invalid data')]
    #[OA\Response(response: 409, description: 'RC already exists')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (empty($data['season']) || empty($data['matric']) || !isset($data['ordre'])) {
            return $this->json(['message' => 'Season, matric, and ordre are required'], Response::HTTP_BAD_REQUEST);
        }

        $season = $data['season'];
        $competitionCode = $data['competitionCode'] ?? null; // null = RC national
        $matric = (int) $data['matric'];
        $ordre = (int) $data['ordre'];

        // Validate ordre range
        if ($ordre < 1 || $ordre > 99) {
            return $this->json(['message' => 'Ordre must be between 1 and 99'], Response::HTTP_BAD_REQUEST);
        }

        // Check if matric exists in kp_licence
        $sql = "SELECT Matric FROM kp_licence WHERE Matric = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$matric]);
        if (!$result->fetchAssociative()) {
            return $this->json(['message' => 'Licence not found'], Response::HTTP_NOT_FOUND);
        }

        // Check for duplicate (same season + competition + matric)
        $sql = "SELECT Id FROM kp_rc
                WHERE Code_saison = ? AND Matric = ?
                AND (Code_competition = ? OR (Code_competition IS NULL AND ? IS NULL))";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$season, $matric, $competitionCode, $competitionCode]);
        if ($result->fetchAssociative()) {
            return $this->json(['message' => 'Ce RC existe déjà pour cette compétition et cette saison'], Response::HTTP_CONFLICT);
        }

        // Insert RC
        $this->connection->beginTransaction();
        try {
            $sql = "INSERT INTO kp_rc (Code_competition, Code_saison, Ordre, Matric)
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$competitionCode, $season, $ordre, $matric]);

            $id = (int) $this->connection->lastInsertId();

            // Log action
            $this->logActionForCompetition(
                'Ajout Rc',
                $season,
                $competitionCode ?? 'CNA',
                "Matric: $matric, Ordre: $ordre"
            );

            $this->connection->commit();

            return $this->json([
                'id' => $id,
                'message' => 'RC ajouté avec succès',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Error creating RC: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update an existing RC
     */
    #[Route('/admin/rc/{id}', name: 'admin_rc_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        path: '/admin/rc/{id}',
        summary: 'Update an existing RC',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'competitionCode', type: 'string', nullable: true),
                    new OA\Property(property: 'matric', type: 'integer'),
                    new OA\Property(property: 'ordre', type: 'integer'),
                ]
            )
        ),
        tags: ['27. App4 - RC']
    )]
    #[OA\Response(response: 200, description: 'RC updated successfully')]
    #[OA\Response(response: 404, description: 'RC not found')]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Check if RC exists
        $sql = "SELECT Id, Code_saison, Code_competition FROM kp_rc WHERE Id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $rc = $result->fetchAssociative();

        if (!$rc) {
            return $this->json(['message' => 'RC not found'], Response::HTTP_NOT_FOUND);
        }

        $competitionCode = $data['competitionCode'] ?? $rc['Code_competition'];
        $matric = (int) ($data['matric'] ?? 0);
        $ordre = (int) ($data['ordre'] ?? 0);

        if ($matric === 0 || $ordre === 0) {
            return $this->json(['message' => 'Matric and ordre are required'], Response::HTTP_BAD_REQUEST);
        }

        // Validate ordre range
        if ($ordre < 1 || $ordre > 99) {
            return $this->json(['message' => 'Ordre must be between 1 and 99'], Response::HTTP_BAD_REQUEST);
        }

        // Check for duplicate (excluding current RC)
        $sql = "SELECT Id FROM kp_rc
                WHERE Code_saison = ? AND Matric = ? AND Id != ?
                AND (Code_competition = ? OR (Code_competition IS NULL AND ? IS NULL))";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$rc['Code_saison'], $matric, $id, $competitionCode, $competitionCode]);
        if ($result->fetchAssociative()) {
            return $this->json(['message' => 'Ce RC existe déjà pour cette compétition et cette saison'], Response::HTTP_CONFLICT);
        }

        // Update RC
        $this->connection->beginTransaction();
        try {
            $sql = "UPDATE kp_rc
                    SET Code_competition = ?, Matric = ?, Ordre = ?
                    WHERE Id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$competitionCode, $matric, $ordre, $id]);

            // Log action
            $this->logActionForCompetition(
                'Modif Rc',
                $rc['Code_saison'],
                $competitionCode ?? 'CNA',
                "Id: $id, Matric: $matric, Ordre: $ordre"
            );

            $this->connection->commit();

            return $this->json(['message' => 'RC modifié avec succès']);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Error updating RC: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete RC (bulk) - DELETE method (legacy)
     */
    #[Route('/admin/rc', name: 'admin_rc_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[OA\Delete(
        path: '/admin/rc',
        summary: 'Delete RC in bulk (legacy - use POST /bulk-delete instead)',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'ids', type: 'array', items: new OA\Items(type: 'integer')),
                ]
            )
        ),
        tags: ['27. App4 - RC']
    )]
    #[OA\Response(response: 200, description: 'RC deleted successfully')]
    public function delete(Request $request): JsonResponse
    {
        return $this->bulkDelete($request);
    }

    /**
     * Delete RC (bulk) - POST method (preferred)
     */
    #[Route('/admin/rc/bulk-delete', name: 'admin_rc_bulk_delete', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[OA\Post(
        path: '/admin/rc/bulk-delete',
        summary: 'Delete RC in bulk',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'ids', type: 'array', items: new OA\Items(type: 'integer')),
                ]
            )
        ),
        tags: ['27. App4 - RC']
    )]
    #[OA\Response(response: 200, description: 'RC deleted successfully')]
    public function bulkDelete(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            return $this->json(['message' => 'IDs array is required'], Response::HTTP_BAD_REQUEST);
        }

        $this->connection->beginTransaction();
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "DELETE FROM kp_rc WHERE Id IN ($placeholders)";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement($ids);

            $deleted = $stmt->rowCount();

            // Log action
            $idsStr = implode(', ', $ids);
            $this->logActionForCompetition(
                'Suppression Rc',
                null,
                null,
                "IDs: $idsStr"
            );

            $this->connection->commit();

            return $this->json([
                'deleted' => $deleted,
                'message' => "$deleted RC supprimé(s) avec succès",
            ]);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Error deleting RC: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
