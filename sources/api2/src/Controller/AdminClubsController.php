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
 * Admin Clubs Controller
 *
 * CRUD operations for clubs, departmental/regional committees management.
 * Migrated from GestionStructure.php
 */
#[IsGranted('ROLE_USER')]
#[OA\Tag(name: '27. App4 - Clubs')]
class AdminClubsController extends AbstractController
{
    use AdminLoggableTrait;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Search clubs by name or code (accessible to all authenticated users)
     */
    #[Route('/admin/clubs/search-all', name: 'admin_clubs_search_all', methods: ['GET'])]
    public function searchAll(Request $request): JsonResponse
    {
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

        $rows = $this->connection->fetchAllAssociative($sql, ["%$q%", "%$q%"]);

        return $this->json(array_map(fn(array $row) => [
            'code' => $row['Code'],
            'libelle' => $row['Libelle'],
            'codeComiteDep' => $row['Code_comite_dep'] ?? '',
        ], $rows));
    }

    /**
     * Get all clubs with GPS coordinates for the map
     */
    #[Route('/admin/clubs/map', name: 'admin_clubs_map', methods: ['GET'])]
    public function map(): JsonResponse
    {
        $sql = "SELECT c.Code, c.Libelle, c.Coord, c.Postal, c.www, c.email
                FROM kp_club c
                WHERE c.Coord IS NOT NULL AND c.Coord != ''
                ORDER BY c.Code";

        $rows = $this->connection->fetchAllAssociative($sql);

        $clubs = array_map(fn(array $row) => [
            'code' => $row['Code'],
            'libelle' => $row['Libelle'],
            'coord' => $row['Coord'],
            'postal' => $row['Postal'] ?? '',
            'www' => $row['www'] ?? '',
            'email' => $row['email'] ?? '',
        ], $rows);

        return $this->json(['clubs' => $clubs]);
    }

    /**
     * Get a single club detail
     */
    #[Route('/admin/clubs/{code}', name: 'admin_clubs_detail', methods: ['GET'])]
    public function detail(string $code): JsonResponse
    {
        $sql = "SELECT c.Code, c.Libelle, c.Code_comite_dep, c.Coord, c.Coord2, c.Postal, c.www, c.email,
                       cd.Libelle AS libelleComiteDep
                FROM kp_club c
                LEFT JOIN kp_cd cd ON cd.Code = c.Code_comite_dep
                WHERE c.Code = ?";

        $row = $this->connection->fetchAssociative($sql, [$code]);

        if (!$row) {
            return $this->json(['error' => true, 'message' => 'Club not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'code' => $row['Code'],
            'libelle' => $row['Libelle'],
            'codeComiteDep' => $row['Code_comite_dep'],
            'libelleComiteDep' => $row['libelleComiteDep'] ?? '',
            'coord' => $row['Coord'] ?? '',
            'coord2' => $row['Coord2'] ?? '',
            'postal' => $row['Postal'] ?? '',
            'www' => $row['www'] ?? '',
            'email' => $row['email'] ?? '',
        ]);
    }

    /**
     * Update a club (coord, postal, www, email)
     */
    #[Route('/admin/clubs/{code}', name: 'admin_clubs_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(string $code, Request $request): JsonResponse
    {
        // Check club exists
        $exists = $this->connection->fetchOne("SELECT Code FROM kp_club WHERE Code = ?", [$code]);
        if (!$exists) {
            return $this->json(['error' => true, 'message' => 'Club not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $postal = isset($data['postal']) ? mb_substr(trim($data['postal']), 0, 100) : null;
        $www = isset($data['www']) ? mb_substr(trim($data['www']), 0, 60) : null;
        $email = isset($data['email']) ? mb_substr(trim($data['email']), 0, 60) : null;
        $coord = isset($data['coord']) ? mb_substr(trim($data['coord']), 0, 50) : null;

        $sets = [];
        $params = [];

        if ($postal !== null) {
            $sets[] = "Postal = ?";
            $params[] = $postal;
        }
        if ($www !== null) {
            $sets[] = "www = ?";
            $params[] = $www;
        }
        if ($email !== null) {
            $sets[] = "email = ?";
            $params[] = $email;
        }
        if ($coord !== null) {
            $sets[] = "Coord = ?";
            $params[] = $coord;
            // Sync Coord2 with Coord for backward compatibility
            $sets[] = "Coord2 = ?";
            $params[] = $coord;
        }

        if (empty($sets)) {
            return $this->json(['error' => true, 'message' => 'No fields to update'], Response::HTTP_BAD_REQUEST);
        }

        $params[] = $code;
        $sql = "UPDATE kp_club SET " . implode(', ', $sets) . " WHERE Code = ?";
        $this->connection->executeStatement($sql, $params);

        $this->logActionForCompetition('CLUB_UPDATE', null, null, "Club $code updated");

        return $this->json(['success' => true]);
    }

    /**
     * Create a new club, optionally with a team
     */
    #[Route('/admin/clubs', name: 'admin_clubs_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $code = mb_substr(trim($data['code'] ?? ''), 0, 6);
        $libelle = mb_substr(trim($data['libelle'] ?? ''), 0, 100);
        $codeComiteDep = trim($data['codeComiteDep'] ?? '');
        $postal = mb_substr(trim($data['postal'] ?? ''), 0, 100);
        $www = mb_substr(trim($data['www'] ?? ''), 0, 60);
        $email = mb_substr(trim($data['email'] ?? ''), 0, 60);
        $coord = mb_substr(trim($data['coord'] ?? ''), 0, 50);
        $equipeLibelle = isset($data['equipe']['libelle']) ? mb_substr(trim($data['equipe']['libelle']), 0, 30) : null;

        // Validation
        if (empty($code) || empty($libelle)) {
            return $this->json(['error' => true, 'message' => 'Code and libelle are required', 'code' => 'VALIDATION_ERROR'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($codeComiteDep)) {
            return $this->json(['error' => true, 'message' => 'Departmental committee is required', 'code' => 'VALIDATION_ERROR'], Response::HTTP_BAD_REQUEST);
        }

        // Check CD exists
        $cdExists = $this->connection->fetchOne("SELECT Code FROM kp_cd WHERE Code = ?", [$codeComiteDep]);
        if (!$cdExists) {
            return $this->json(['error' => true, 'message' => 'Departmental committee not found', 'code' => 'CD_NOT_FOUND'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check club code uniqueness
        $clubExists = $this->connection->fetchOne("SELECT Code FROM kp_club WHERE Code = ?", [$code]);
        if ($clubExists) {
            return $this->json(['error' => true, 'message' => 'Club code already exists', 'code' => 'DUPLICATE_CODE'], Response::HTTP_CONFLICT);
        }

        $this->connection->beginTransaction();
        try {
            // Insert club
            $this->connection->executeStatement(
                "INSERT INTO kp_club (Code, Libelle, Code_comite_dep, Postal, www, email, Coord, Coord2)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$code, $libelle, $codeComiteDep, $postal, $www, $email, $coord, $coord]
            );

            // Optionally create team
            if (!empty($equipeLibelle)) {
                $this->connection->executeStatement(
                    "INSERT INTO kp_equipe (Libelle, Code_club) VALUES (?, ?)",
                    [$equipeLibelle, $code]
                );
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json(['error' => true, 'message' => 'Database error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->logActionForCompetition('CLUB_CREATE', null, null, "Club $code ($libelle) created");

        return $this->json(['success' => true, 'code' => $code], Response::HTTP_CREATED);
    }

    /**
     * Get all teams (kp_equipe) for a club, with last season and competition count
     */
    #[Route('/admin/clubs/{code}/teams', name: 'admin_clubs_teams', methods: ['GET'])]
    public function clubTeams(string $code): JsonResponse
    {
        // Check club exists
        $exists = $this->connection->fetchOne("SELECT Code FROM kp_club WHERE Code = ?", [$code]);
        if (!$exists) {
            return $this->json(['error' => true, 'message' => 'Club not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $sql = "SELECT e.Numero, e.Libelle, e.logo,
                       MAX(ce.Code_saison) AS derniereSaison,
                       COUNT(DISTINCT CONCAT(ce.Code_compet, '-', ce.Code_saison)) AS nbCompetitions
                FROM kp_equipe e
                LEFT JOIN kp_competition_equipe ce ON ce.Numero = e.Numero
                WHERE e.Code_club = ?
                GROUP BY e.Numero, e.Libelle, e.logo
                ORDER BY derniereSaison DESC, e.Libelle";

        $rows = $this->connection->fetchAllAssociative($sql, [$code]);

        $teams = array_map(fn(array $row) => [
            'numero' => (int) $row['Numero'],
            'libelle' => $row['Libelle'],
            'logo' => $row['logo'] ?? '',
            'derniereSaison' => $row['derniereSaison'],
            'nbCompetitions' => (int) $row['nbCompetitions'],
        ], $rows);

        return $this->json(['teams' => $teams]);
    }

    /**
     * Get team detail with competition history
     */
    #[Route('/admin/teams/{numero}', name: 'admin_teams_detail', methods: ['GET'])]
    public function teamDetail(int $numero): JsonResponse
    {
        $sql = "SELECT e.Numero, e.Libelle, e.Code_club, e.logo, e.color1, e.color2, e.colortext,
                       c.Libelle AS libelleClub
                FROM kp_equipe e
                LEFT JOIN kp_club c ON c.Code = e.Code_club
                WHERE e.Numero = ?";

        $row = $this->connection->fetchAssociative($sql, [$numero]);

        if (!$row) {
            return $this->json(['error' => true, 'message' => 'Team not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $sqlCompets = "SELECT ce.Code_compet, ce.Code_saison, ce.Libelle AS libelleEquipe,
                              comp.Libelle AS libelleCompet
                       FROM kp_competition_equipe ce
                       LEFT JOIN kp_competition comp ON comp.Code = ce.Code_compet AND comp.Code_saison = ce.Code_saison
                       WHERE ce.Numero = ?
                       ORDER BY ce.Code_saison DESC, comp.Libelle";

        $compets = $this->connection->fetchAllAssociative($sqlCompets, [$numero]);

        return $this->json([
            'numero' => (int) $row['Numero'],
            'libelle' => $row['Libelle'],
            'codeClub' => $row['Code_club'],
            'libelleClub' => $row['libelleClub'] ?? '',
            'logo' => $row['logo'] ?? '',
            'color1' => $row['color1'] ?? '',
            'color2' => $row['color2'] ?? '',
            'colortext' => $row['colortext'] ?? '',
            'competitions' => array_map(fn(array $r) => [
                'codeCompet' => $r['Code_compet'],
                'codeSaison' => $r['Code_saison'],
                'libelleEquipe' => $r['libelleEquipe'] ?? '',
                'libelleCompet' => $r['libelleCompet'] ?? '',
            ], $compets),
        ]);
    }

    /**
     * Create a new departmental committee
     */
    #[Route('/admin/departmental-committees', name: 'admin_cd_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createDepartmentalCommittee(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $code = mb_substr(trim($data['code'] ?? ''), 0, 6);
        $libelle = mb_substr(trim($data['libelle'] ?? ''), 0, 100);
        $codeComiteReg = trim($data['codeComiteReg'] ?? '');

        // Validation
        if (empty($code) || empty($libelle)) {
            return $this->json(['error' => true, 'message' => 'Code and libelle are required', 'code' => 'VALIDATION_ERROR'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($codeComiteReg)) {
            return $this->json(['error' => true, 'message' => 'Regional committee is required', 'code' => 'VALIDATION_ERROR'], Response::HTTP_BAD_REQUEST);
        }

        // Check CR exists
        $crExists = $this->connection->fetchOne("SELECT Code FROM kp_cr WHERE Code = ?", [$codeComiteReg]);
        if (!$crExists) {
            return $this->json(['error' => true, 'message' => 'Regional committee not found', 'code' => 'CR_NOT_FOUND'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check CD code uniqueness
        $cdExists = $this->connection->fetchOne("SELECT Code FROM kp_cd WHERE Code = ?", [$code]);
        if ($cdExists) {
            return $this->json(['error' => true, 'message' => 'Committee code already exists', 'code' => 'DUPLICATE_CODE'], Response::HTTP_CONFLICT);
        }

        $this->connection->executeStatement(
            "INSERT INTO kp_cd (Code, Libelle, Code_comite_reg) VALUES (?, ?, ?)",
            [$code, $libelle, $codeComiteReg]
        );

        $this->logActionForCompetition('CD_CREATE', null, null, "CD $code ($libelle) created for CR $codeComiteReg");

        return $this->json(['success' => true, 'code' => $code], Response::HTTP_CREATED);
    }
}
