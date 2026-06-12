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
 * Admin Referees Pool Controller
 *
 * Management of the global referees pool, stored as a special "competition"
 * (kp_competition_equipe.Code_compet = 'POOL', Code_saison = '1000').
 *
 * Unlike regular competitions, the pool is NOT tied to the working season /
 * context: it uses a fixed season code (1000) and is accessible at any time.
 *
 * Structure:
 *  - Groups (nations / sub-pools) = rows in kp_competition_equipe with Code_compet='POOL'
 *  - Referees                     = rows in kp_competition_equipe_joueur attached to a group
 *  - Arbitration status           = kp_arbitre row (per Matric)
 *
 * Business rules:
 *  - Licensed athletes (Matric < 2_000_000) come from the federation import.
 *    Their identity AND their arbitration level are read-only here: they can
 *    only be added to / removed from a group.
 *  - Non-licensed referees (Matric >= 2_000_000) are fully editable
 *    (identity + arbitration status), as in the legacy GestionEquipeJoueur.
 *
 * Migrated from GestionEquipe.php / GestionEquipeJoueur.php (POOL branch).
 */
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '32. App4 - Referees Pool')]
class AdminRefereesPoolController extends AbstractController
{
    private const POOL_COMPET = 'POOL';
    private const POOL_SAISON = '1000';
    private const NON_LICENSED_THRESHOLD = 2_000_000;

    /** Valid arbitration codes -> [regional, interregional, national, international] flags. */
    private const ARBITRE_FLAGS = [
        'REG' => ['O', 'N', 'N', 'N'],
        'IR'  => ['N', 'O', 'N', 'N'],
        'NAT' => ['N', 'N', 'O', 'N'],
        'INT' => ['N', 'N', 'O', 'O'],
        'OTM' => ['N', 'N', 'N', 'N'],
        'JO'  => ['N', 'N', 'N', 'N'],
    ];

    /** Stored short label per arbitration code (kp_arbitre.arbitre, char(3)). */
    private const ARBITRE_LABEL = [
        'REG' => 'Reg',
        'IR'  => 'IR',
        'NAT' => 'Nat',
        'INT' => 'Int',
        'OTM' => 'OTM',
        'JO'  => 'JO',
    ];

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * List all pool groups with their referees.
     */
    #[Route('/admin/referees-pool', name: 'admin_referees_pool_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $groups = $this->connection->fetchAllAssociative(
            "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.logo
             FROM kp_competition_equipe ce
             WHERE ce.Code_compet = ? AND ce.Code_saison = ?
             ORDER BY ce.Libelle ASC",
            [self::POOL_COMPET, self::POOL_SAISON]
        );

        if (empty($groups)) {
            return $this->json(['groups' => []]);
        }

        $ids = array_map(static fn ($g) => (int) $g['Id'], $groups);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $referees = $this->connection->fetchAllAssociative(
            "SELECT j.Id_equipe, j.Matric, j.Nom, j.Prenom, j.Sexe, j.Categ, j.Capitaine,
                    a.arbitre, a.niveau, a.regional, a.interregional, a.national, a.international
             FROM kp_competition_equipe_joueur j
             LEFT JOIN kp_arbitre a ON j.Matric = a.Matric
             WHERE j.Id_equipe IN ($placeholders)
             ORDER BY (j.Capitaine = 'X') ASC, j.Nom ASC, j.Prenom ASC",
            $ids
        );

        $byGroup = [];
        foreach ($referees as $r) {
            $byGroup[(int) $r['Id_equipe']][] = $this->serializeReferee($r);
        }

        $result = array_map(function ($g) use ($byGroup) {
            $id = (int) $g['Id'];
            return [
                'id' => $id,
                'libelle' => $g['Libelle'],
                'codeClub' => $g['Code_club'],
                'numero' => (int) $g['Numero'],
                'logo' => $g['logo'] ?: null,
                'referees' => $byGroup[$id] ?? [],
                'refereeCount' => count($byGroup[$id] ?? []),
            ];
        }, $groups);

        return $this->json(['groups' => $result]);
    }

    /**
     * Create a new pool group.
     */
    #[Route('/admin/referees-pool/groups', name: 'admin_referees_pool_group_create', methods: ['POST'])]
    public function createGroup(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $libelle = trim((string) ($data['libelle'] ?? ''));
        $codeClub = trim((string) ($data['codeClub'] ?? ''));

        if ($libelle === '') {
            return $this->json(['message' => 'Group name is required'], Response::HTTP_BAD_REQUEST);
        }

        // Default club to ICF (international) like existing nation groups.
        if ($codeClub === '') {
            $codeClub = 'ICF';
        }
        if (!$this->connection->fetchOne("SELECT Code FROM kp_club WHERE Code = ?", [$codeClub])) {
            return $this->json(['message' => 'Club not found'], Response::HTTP_BAD_REQUEST);
        }

        $exists = $this->connection->fetchOne(
            "SELECT Id FROM kp_competition_equipe
             WHERE Code_compet = ? AND Code_saison = ? AND Libelle = ?",
            [self::POOL_COMPET, self::POOL_SAISON, $libelle]
        );
        if ($exists) {
            return $this->json(['message' => 'A group with this name already exists'], Response::HTTP_CONFLICT);
        }

        $this->connection->beginTransaction();
        try {
            // Create backing kp_equipe row to obtain a team number.
            $this->connection->executeStatement(
                "INSERT INTO kp_equipe (Libelle, Code_club) VALUES (?, ?)",
                [$libelle, $codeClub]
            );
            $numero = (int) $this->connection->lastInsertId();

            $this->connection->executeStatement(
                "INSERT INTO kp_competition_equipe (Code_compet, Code_saison, Libelle, Code_club, Numero)
                 VALUES (?, ?, ?, ?, ?)",
                [self::POOL_COMPET, self::POOL_SAISON, $libelle, $codeClub, $numero]
            );
            $id = (int) $this->connection->lastInsertId();

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Could not create group: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['id' => $id, 'libelle' => $libelle, 'codeClub' => $codeClub], Response::HTTP_CREATED);
    }

    /**
     * Rename a pool group.
     */
    #[Route('/admin/referees-pool/groups/{id}', name: 'admin_referees_pool_group_update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function updateGroup(int $id, Request $request): JsonResponse
    {
        $group = $this->getPoolGroup($id);
        if (!$group) {
            return $this->json(['message' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $libelle = trim((string) ($data['libelle'] ?? ''));
        if ($libelle === '') {
            return $this->json(['message' => 'Group name is required'], Response::HTTP_BAD_REQUEST);
        }

        $exists = $this->connection->fetchOne(
            "SELECT Id FROM kp_competition_equipe
             WHERE Code_compet = ? AND Code_saison = ? AND Libelle = ? AND Id <> ?",
            [self::POOL_COMPET, self::POOL_SAISON, $libelle, $id]
        );
        if ($exists) {
            return $this->json(['message' => 'A group with this name already exists'], Response::HTTP_CONFLICT);
        }

        $this->connection->executeStatement(
            "UPDATE kp_competition_equipe SET Libelle = ? WHERE Id = ?",
            [$libelle, $id]
        );

        return $this->json(['id' => $id, 'libelle' => $libelle]);
    }

    /**
     * Delete a pool group and detach its referees.
     */
    #[Route('/admin/referees-pool/groups/{id}', name: 'admin_referees_pool_group_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deleteGroup(int $id): JsonResponse
    {
        $group = $this->getPoolGroup($id);
        if (!$group) {
            return $this->json(['message' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }

        $this->connection->beginTransaction();
        try {
            $this->connection->executeStatement(
                "DELETE FROM kp_competition_equipe_joueur WHERE Id_equipe = ?",
                [$id]
            );
            $this->connection->executeStatement(
                "DELETE FROM kp_competition_equipe WHERE Id = ?",
                [$id]
            );
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Could not delete group: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['message' => 'Group deleted']);
    }

    /**
     * Search licensed athletes to add to the pool (kp_licence).
     */
    #[Route('/admin/referees-pool/search-licence', name: 'admin_referees_pool_search_licence', methods: ['GET'])]
    public function searchLicence(Request $request): JsonResponse
    {
        $q = trim($request->query->get('q', ''));
        if (mb_strlen($q) < 2) {
            return $this->json([]);
        }
        $likeQ = '%' . $q . '%';

        $rows = $this->connection->fetchAllAssociative(
            "SELECT l.Matric, l.Nom, l.Prenom, l.Sexe, l.Numero_club, c.Libelle AS club_libelle,
                    a.arbitre, a.niveau, a.regional, a.interregional, a.national, a.international
             FROM kp_licence l
             LEFT JOIN kp_club c ON l.Numero_club = c.Code
             LEFT JOIN kp_arbitre a ON l.Matric = a.Matric
             WHERE l.Matric LIKE ?
                OR UPPER(CONCAT_WS(' ', l.Nom, l.Prenom)) LIKE UPPER(?)
                OR UPPER(CONCAT_WS(' ', l.Prenom, l.Nom)) LIKE UPPER(?)
             ORDER BY l.Nom, l.Prenom
             LIMIT 30",
            [$likeQ, $likeQ, $likeQ]
        );

        $results = array_map(function ($r) {
            $ref = $this->serializeReferee($r);
            $ref['clubLibelle'] = $r['club_libelle'] ?? '';
            $ref['numeroClub'] = $r['Numero_club'] ?? '';
            return $ref;
        }, $rows);

        return $this->json($results);
    }

    /**
     * Add a referee to a group.
     *
     * Two modes:
     *  - 'licence': attach an existing licensed athlete (matric provided).
     *  - 'manual' : create a non-licensed referee (matric auto-generated >= 2M),
     *               with optional arbitration status.
     */
    #[Route('/admin/referees-pool/groups/{id}/referees', name: 'admin_referees_pool_referee_add', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addReferee(int $id, Request $request): JsonResponse
    {
        $group = $this->getPoolGroup($id);
        if (!$group) {
            return $this->json(['message' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $mode = $data['mode'] ?? 'licence';

        if ($mode === 'licence') {
            return $this->addLicensedReferee($id, $data);
        }
        if ($mode === 'manual') {
            return $this->addManualReferee($id, $data);
        }

        return $this->json(['message' => 'Invalid mode'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update a non-licensed referee (identity + arbitration status).
     * Licensed athletes (Matric < 2M) are read-only and cannot be edited here.
     */
    #[Route('/admin/referees-pool/groups/{id}/referees/{matric}', name: 'admin_referees_pool_referee_update', methods: ['PATCH'], requirements: ['id' => '\d+', 'matric' => '\d+'])]
    public function updateReferee(int $id, int $matric, Request $request): JsonResponse
    {
        $group = $this->getPoolGroup($id);
        if (!$group) {
            return $this->json(['message' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }

        if ($matric < self::NON_LICENSED_THRESHOLD) {
            return $this->json(
                ['message' => 'Licensed athletes are read-only; edit them via the licence import'],
                Response::HTTP_FORBIDDEN
            );
        }

        $exists = $this->connection->fetchOne(
            "SELECT Matric FROM kp_competition_equipe_joueur WHERE Id_equipe = ? AND Matric = ?",
            [$id, $matric]
        );
        if (!$exists) {
            return $this->json(['message' => 'Referee not found in this group'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $nom = mb_strtoupper(trim((string) ($data['nom'] ?? '')), 'UTF-8');
        $prenom = mb_convert_case(trim((string) ($data['prenom'] ?? '')), MB_CASE_TITLE, 'UTF-8');
        $sexe = strtoupper(trim((string) ($data['sexe'] ?? '')));
        $arbitre = isset($data['arbitre']) ? strtoupper(trim((string) $data['arbitre'])) : null;
        $niveau = strtoupper(trim((string) ($data['niveau'] ?? '')));

        if ($nom === '') {
            return $this->json(['message' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }
        if ($arbitre !== null && $arbitre !== '' && !isset(self::ARBITRE_FLAGS[$arbitre])) {
            return $this->json(['message' => 'Invalid arbitration code'], Response::HTTP_BAD_REQUEST);
        }

        $this->connection->beginTransaction();
        try {
            $this->connection->executeStatement(
                "UPDATE kp_competition_equipe_joueur SET Nom = ?, Prenom = ?, Sexe = ? WHERE Id_equipe = ? AND Matric = ?",
                [$nom, $prenom, $sexe, $id, $matric]
            );
            // Keep the (non-licensed) backing licence row in sync.
            $this->connection->executeStatement(
                "UPDATE kp_licence SET Nom = ?, Prenom = ?, Sexe = ? WHERE Matric = ?",
                [$nom, $prenom, $sexe, $matric]
            );
            $this->applyArbitrationStatus($matric, $arbitre, $niveau);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Could not update referee: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['message' => 'Referee updated']);
    }

    /**
     * Remove a referee from a group.
     */
    #[Route('/admin/referees-pool/groups/{id}/referees/{matric}', name: 'admin_referees_pool_referee_remove', methods: ['DELETE'], requirements: ['id' => '\d+', 'matric' => '\d+'])]
    public function removeReferee(int $id, int $matric): JsonResponse
    {
        $group = $this->getPoolGroup($id);
        if (!$group) {
            return $this->json(['message' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }

        $this->connection->executeStatement(
            "DELETE FROM kp_competition_equipe_joueur WHERE Id_equipe = ? AND Matric = ?",
            [$id, $matric]
        );

        return $this->json(['message' => 'Referee removed']);
    }

    /**
     * Update a referee's pool membership status (active 'A' / inactive 'X').
     *
     * Allowed for ALL referees, including licensed ones: the status reflects
     * pool membership, not federation data.
     */
    #[Route('/admin/referees-pool/groups/{id}/referees/{matric}/status', name: 'admin_referees_pool_referee_status', methods: ['PATCH'], requirements: ['id' => '\d+', 'matric' => '\d+'])]
    public function updateRefereeStatus(int $id, int $matric, Request $request): JsonResponse
    {
        $group = $this->getPoolGroup($id);
        if (!$group) {
            return $this->json(['message' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }

        $exists = $this->connection->fetchOne(
            "SELECT Matric FROM kp_competition_equipe_joueur WHERE Id_equipe = ? AND Matric = ?",
            [$id, $matric]
        );
        if (!$exists) {
            return $this->json(['message' => 'Referee not found in this group'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $status = strtoupper(trim((string) ($data['status'] ?? '')));
        if (!in_array($status, ['A', 'X'], true)) {
            return $this->json(['message' => 'Invalid status'], Response::HTTP_BAD_REQUEST);
        }

        $this->connection->executeStatement(
            "UPDATE kp_competition_equipe_joueur SET Capitaine = ? WHERE Id_equipe = ? AND Matric = ?",
            [$status, $id, $matric]
        );

        return $this->json(['message' => 'Status updated', 'status' => $status]);
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    /**
     * @return array<string, mixed>|false
     */
    private function getPoolGroup(int $id): array|false
    {
        return $this->connection->fetchAssociative(
            "SELECT Id, Libelle, Code_club, Numero FROM kp_competition_equipe
             WHERE Id = ? AND Code_compet = ? AND Code_saison = ?",
            [$id, self::POOL_COMPET, self::POOL_SAISON]
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function addLicensedReferee(int $groupId, array $data): JsonResponse
    {
        $matric = (int) ($data['matric'] ?? 0);
        if ($matric <= 0) {
            return $this->json(['message' => 'Matric is required'], Response::HTTP_BAD_REQUEST);
        }

        $licence = $this->connection->fetchAssociative(
            "SELECT Matric, Nom, Prenom, Sexe, Naissance FROM kp_licence WHERE Matric = ?",
            [$matric]
        );
        if (!$licence) {
            return $this->json(['message' => 'Licence not found'], Response::HTTP_NOT_FOUND);
        }

        $already = $this->connection->fetchOne(
            "SELECT Matric FROM kp_competition_equipe_joueur WHERE Id_equipe = ? AND Matric = ?",
            [$groupId, $matric]
        );
        if ($already) {
            return $this->json(['message' => 'Referee already in this group'], Response::HTTP_CONFLICT);
        }

        $categ = $this->computeCategory($licence['Naissance'] ?? null);

        $this->connection->executeStatement(
            "INSERT INTO kp_competition_equipe_joueur (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Capitaine)
             VALUES (?, ?, ?, ?, ?, ?, 'A')",
            [$groupId, $matric, $licence['Nom'], $licence['Prenom'], $licence['Sexe'], $categ]
        );

        return $this->json(['message' => 'Referee added', 'matric' => $matric], Response::HTTP_CREATED);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function addManualReferee(int $groupId, array $data): JsonResponse
    {
        $nom = mb_strtoupper(trim((string) ($data['nom'] ?? '')), 'UTF-8');
        $prenom = mb_convert_case(trim((string) ($data['prenom'] ?? '')), MB_CASE_TITLE, 'UTF-8');
        $sexe = strtoupper(trim((string) ($data['sexe'] ?? '')));
        $naissance = $this->normalizeDate((string) ($data['naissance'] ?? ''));
        $arbitre = isset($data['arbitre']) ? strtoupper(trim((string) $data['arbitre'])) : null;
        $niveau = strtoupper(trim((string) ($data['niveau'] ?? '')));

        if ($nom === '') {
            return $this->json(['message' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }
        if ($arbitre !== null && $arbitre !== '' && !isset(self::ARBITRE_FLAGS[$arbitre])) {
            return $this->json(['message' => 'Invalid arbitration code'], Response::HTTP_BAD_REQUEST);
        }

        $codeClub = $this->getPoolGroup($groupId)['Code_club'] ?? '';
        $categ = $this->computeCategory($naissance);
        $saison = $this->getActiveSeason();

        $this->connection->beginTransaction();
        try {
            $matric = $this->nextNonLicensedMatric();

            // Backing licence row for the non-licensed referee.
            // Origine = active season, as done in the legacy GestionEquipeJoueur::Add2().
            $this->connection->executeStatement(
                "INSERT INTO kp_licence (Matric, Origine, Nom, Prenom, Sexe, Naissance, Numero_club)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$matric, $saison, $nom, $prenom, $sexe, $naissance, $codeClub]
            );

            $this->connection->executeStatement(
                "INSERT INTO kp_competition_equipe_joueur (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Capitaine)
                 VALUES (?, ?, ?, ?, ?, ?, 'A')",
                [$groupId, $matric, $nom, $prenom, $sexe, $categ]
            );

            $this->applyArbitrationStatus($matric, $arbitre, $niveau);

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Could not create referee: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['message' => 'Referee created', 'matric' => $matric], Response::HTTP_CREATED);
    }

    /**
     * Insert/update/remove the kp_arbitre row for a non-licensed referee.
     */
    private function applyArbitrationStatus(int $matric, ?string $arbitre, string $niveau): void
    {
        // null = leave untouched; '' = clear the status.
        if ($arbitre === null) {
            return;
        }

        if ($arbitre === '') {
            $this->connection->executeStatement("DELETE FROM kp_arbitre WHERE Matric = ?", [$matric]);
            return;
        }

        [$reg, $ir, $nat, $int] = self::ARBITRE_FLAGS[$arbitre];
        $label = self::ARBITRE_LABEL[$arbitre];
        $saison = $this->getActiveSeason();

        // niveau is char(1) NOT NULL.
        $niveau = mb_substr($niveau, 0, 1);

        $this->connection->executeStatement(
            "REPLACE INTO kp_arbitre
                (Matric, regional, interregional, national, international, arbitre, livret, niveau, saison)
             VALUES (?, ?, ?, ?, ?, ?, '', ?, ?)",
            [$matric, $reg, $ir, $nat, $int, $label, $niveau, $saison]
        );
    }

    private function nextNonLicensedMatric(): int
    {
        $max = (int) $this->connection->fetchOne(
            "SELECT MAX(Matric) FROM kp_licence WHERE Matric >= ?",
            [self::NON_LICENSED_THRESHOLD]
        );
        return $max > 0 ? $max + 1 : self::NON_LICENSED_THRESHOLD;
    }

    private function getActiveSeason(): string
    {
        return (string) ($this->connection->fetchOne(
            "SELECT Code FROM kp_saison WHERE Etat = 'O' ORDER BY Code DESC LIMIT 1"
        ) ?: '');
    }

    private function computeCategory(?string $naissance): ?string
    {
        if (!$naissance) {
            return null;
        }
        $season = $this->getActiveSeason();
        if ($season === '') {
            return null;
        }
        $year = (int) substr($naissance, 0, 4);
        if ($year <= 0) {
            return null;
        }
        $age = (int) $season - $year;
        return $this->connection->fetchOne(
            "SELECT id FROM kp_categorie WHERE age_min <= ? AND age_max >= ?",
            [$age, $age]
        ) ?: null;
    }

    /**
     * Accept dates as YYYY-MM-DD or DD/MM/YYYY, return YYYY-MM-DD or null.
     */
    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
            return $value;
        }
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $value, $m)) {
            return "$m[3]-$m[2]-$m[1]";
        }
        return null;
    }

    /**
     * Build the API representation of a referee row.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function serializeReferee(array $row): array
    {
        $matric = (int) $row['Matric'];
        $arbitre = $row['arbitre'] ?? null;
        $niveau = $row['niveau'] ?? null;

        $arbLabel = '';
        if ($arbitre) {
            $arbLabel = strtoupper($arbitre);
            if ($niveau !== null && $niveau !== '') {
                $arbLabel .= '-' . $niveau;
            }
        }

        // Pool membership status, stored in Capitaine: 'A' = active referee, 'X' = inactive.
        $status = ($row['Capitaine'] ?? 'A') === 'X' ? 'X' : 'A';

        return [
            'matric' => $matric,
            'nom' => mb_strtoupper((string) ($row['Nom'] ?? '')),
            'prenom' => mb_convert_case(mb_strtolower((string) ($row['Prenom'] ?? '')), MB_CASE_TITLE, 'UTF-8'),
            'sexe' => $row['Sexe'] ?? '',
            'categ' => $row['Categ'] ?? '',
            'licensed' => $matric < self::NON_LICENSED_THRESHOLD,
            'status' => $status,
            'arbitre' => $arbitre ? strtoupper($arbitre) : '',
            'niveau' => $niveau ?? '',
            'arbitreLabel' => $arbLabel,
        ];
    }
}
