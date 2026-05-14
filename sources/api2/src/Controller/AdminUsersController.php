<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\NotificationService;
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
 * Admin Users Controller
 *
 * CRUD operations for user accounts (kp_user table).
 * Migrated from GestionUtilisateur.php
 */
#[IsGranted('ROLE_USER')]
#[OA\Tag(name: '30. App4 - Users')]
class AdminUsersController extends AbstractController
{
    use AdminLoggableTrait;

    public function __construct(
        private readonly Connection $connection,
        private readonly NotificationService $notificationService
    ) {
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /admin/users  — Paginated list
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users', name: 'admin_users_list', methods: ['GET'])]
    #[OA\Get(
        path: '/admin/users',
        summary: 'List users with pagination and filters',
        tags: ['30. App4 - Users']
    )]
    #[OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20))]
    #[OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'profile', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'season', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'competition', in: 'query', required: false, schema: new OA\Schema(type: 'string', description: 'Comma-separated competition codes'))]
    #[OA\Response(response: 200, description: 'Paginated list of users')]
    #[OA\Response(response: 403, description: 'Profile insufficient')]
    public function list(Request $request): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 4) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limitParam = (int) $request->query->get('limit', 20);
        $limit = $limitParam > 0 ? min(100, $limitParam) : 0;
        $search = trim($request->query->get('search', ''));
        $profileFilter = $request->query->get('profile');
        $seasonFilter = trim($request->query->get('season', ''));
        $competitionFilter = trim($request->query->get('competition', ''));

        $adminNiveau = $currentUser->getEffectiveNiveau();

        $whereConditions = [];
        $params = [];

        // An admin only sees users with profile >= their own (except super admin)
        if ($adminNiveau > 1) {
            $whereConditions[] = 'u.Niveau >= ?';
            $params[] = $adminNiveau;
        }

        if (!empty($search)) {
            $whereConditions[] = '(u.Code LIKE ? OR u.Identite LIKE ? OR u.Mail LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Profile/season/competition filters: base profile OR a single mandate must satisfy ALL active filters simultaneously
        $hasProfileFilter = $profileFilter !== null && $profileFilter !== '';
        $hasSeasonFilter = !empty($seasonFilter);
        $hasCompFilter = !empty($competitionFilter);
        $compCodes = $hasCompFilter ? array_filter(array_map('trim', explode(',', $competitionFilter))) : [];

        if ($hasProfileFilter || $hasSeasonFilter || $hasCompFilter) {
            $baseParts = [];
            if ($hasProfileFilter) { $baseParts[] = 'u.Niveau = ?'; $params[] = (int) $profileFilter; }
            if ($hasSeasonFilter) { $baseParts[] = "(u.Filtre_saison = '' OR u.Filtre_saison LIKE ?)"; $params[] = "%|$seasonFilter|%"; }
            if (!empty($compCodes)) {
                $orParts = [];
                foreach ($compCodes as $c) { $orParts[] = "u.Filtre_competition = '' OR u.Filtre_competition LIKE ?"; $params[] = "%|$c|%"; }
                $baseParts[] = '(' . implode(' OR ', $orParts) . ')';
            }
            $mandateParts = [];
            if ($hasProfileFilter) { $mandateParts[] = 'm.niveau = ?'; $params[] = (int) $profileFilter; }
            if ($hasSeasonFilter) { $mandateParts[] = "(m.filtre_saison = '' OR m.filtre_saison LIKE ?)"; $params[] = "%|$seasonFilter|%"; }
            if (!empty($compCodes)) {
                $orParts = [];
                foreach ($compCodes as $c) { $orParts[] = "m.filtre_competition = '' OR m.filtre_competition LIKE ?"; $params[] = "%|$c|%"; }
                $mandateParts[] = '(' . implode(' OR ', $orParts) . ')';
            }
            $whereConditions[] = '(' . implode(' AND ', $baseParts)
                . ' OR EXISTS (SELECT 1 FROM kp_user_mandat m WHERE m.user_code = u.Code AND '
                . implode(' AND ', $mandateParts) . '))';
        }

        $whereClause = count($whereConditions) > 0 ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Count total
        $countSql = "SELECT COUNT(*) FROM kp_user u $whereClause";
        $total = (int) $this->connection->fetchOne($countSql, $params);

        // Fetch items
        $offset = $limit > 0 ? ($page - 1) * $limit : 0;
        $sql = "SELECT u.Code, u.Identite, u.Mail, u.Tel, u.Fonction, u.Niveau,
                       u.Filtre_saison, u.Filtre_competition, u.Id_Evenement,
                       u.Filtre_journee, u.Limitation_equipe_club,
                       (SELECT COUNT(*) FROM kp_user_mandat m WHERE m.user_code = u.Code) AS mandateCount
                FROM kp_user u
                $whereClause
                ORDER BY u.Niveau ASC, u.Identite ASC"
                . ($limit > 0 ? " LIMIT " . (int) $limit . " OFFSET " . (int) $offset : '');

        $rows = $this->connection->fetchAllAssociative($sql, $params);

        // Load mandates for all users in this page in one query
        $pageCodes = array_column($rows, 'Code');
        $mandatesByCode = [];
        if (!empty($pageCodes)) {
            $placeholders = implode(',', array_fill(0, count($pageCodes), '?'));
            $mandateRows = $this->connection->fetchAllAssociative(
                "SELECT user_code, id, libelle, niveau, filtre_saison, filtre_competition, limitation_equipe_club, filtre_journee, id_evenement
                 FROM kp_user_mandat WHERE user_code IN ($placeholders) ORDER BY user_code, id",
                $pageCodes
            );
            foreach ($mandateRows as $m) {
                $mandatesByCode[$m['user_code']][] = [
                    'id' => (int) $m['id'],
                    'libelle' => $m['libelle'],
                    'niveau' => (int) $m['niveau'],
                    'filtreSaison' => $m['filtre_saison'] ?? '',
                    'filtreCompetition' => $m['filtre_competition'] ?? '',
                    'limitClubs' => $m['limitation_equipe_club'] ?? '',
                    'filtreJournee' => $m['filtre_journee'] ?? '',
                    'idEvenement' => $m['id_evenement'] ?? '',
                ];
            }
        }

        $items = array_map(fn(array $row) => [
            'code' => $row['Code'],
            'identite' => $row['Identite'] ?? '',
            'mail' => $row['Mail'] ?? '',
            'tel' => $row['Tel'] ?? '',
            'fonction' => $row['Fonction'] ?? '',
            'niveau' => (int) $row['Niveau'],
            'filtreSaison' => $row['Filtre_saison'] ?? '',
            'filtreCompetition' => $row['Filtre_competition'] ?? '',
            'idEvenement' => $row['Id_Evenement'] ?? '',
            'filtreJournee' => $row['Filtre_journee'] ?? '',
            'limitClubs' => $row['Limitation_equipe_club'] ?? '',
            'mandateCount' => (int) $row['mandateCount'],
            'mandates' => $mandatesByCode[$row['Code']] ?? [],
        ], $rows);

        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 1;

        return $this->json([
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /admin/users/{code}  — Single user detail
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users/{code}', name: 'admin_users_get', methods: ['GET'], priority: -1)]
    #[OA\Get(
        path: '/admin/users/{code}',
        summary: 'Get single user details',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 200, description: 'User details')]
    #[OA\Response(response: 403, description: 'Profile insufficient')]
    #[OA\Response(response: 404, description: 'User not found')]
    public function get(string $code): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 4) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $sql = "SELECT u.Code, u.Identite, u.Mail, u.Tel, u.Fonction, u.Niveau,
                       u.Filtre_saison, u.Filtre_competition, u.Id_Evenement,
                       u.Filtre_journee, u.Limitation_equipe_club,
                       u.Date_debut, u.Date_fin,
                       l.Numero_club AS club, c.Libelle AS clubLabel
                FROM kp_user u
                LEFT JOIN kp_licence l ON u.Code = l.Matric
                LEFT JOIN kp_club c ON l.Numero_club = c.Code
                WHERE u.Code = ?";

        $row = $this->connection->fetchAssociative($sql, [$code]);

        if (!$row) {
            return $this->json(['error' => true, 'message' => 'User not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        // Check that admin can see this user
        if ($currentUser->getEffectiveNiveau() > 1 && (int) $row['Niveau'] < $currentUser->getEffectiveNiveau()) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        return $this->json([
            'code' => $row['Code'],
            'identite' => $row['Identite'] ?? '',
            'mail' => $row['Mail'] ?? '',
            'tel' => $row['Tel'] ?? '',
            'fonction' => $row['Fonction'] ?? '',
            'niveau' => (int) $row['Niveau'],
            'filtreSaison' => $row['Filtre_saison'] ?? '',
            'filtreCompetition' => $row['Filtre_competition'] ?? '',
            'idEvenement' => $row['Id_Evenement'] ?? '',
            'filtreJournee' => $row['Filtre_journee'] ?? '',
            'limitClubs' => $row['Limitation_equipe_club'] ?? '',
            'dateDebut' => $row['Date_debut'],
            'dateFin' => $row['Date_fin'],
            'club' => $row['club'] ?? null,
            'clubLabel' => $row['clubLabel'] ?? null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // POST /admin/users  — Create user
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users', name: 'admin_users_create', methods: ['POST'])]
    #[OA\Post(
        path: '/admin/users',
        summary: 'Create a new user',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 201, description: 'User created')]
    #[OA\Response(response: 400, description: 'Invalid data')]
    #[OA\Response(response: 403, description: 'Profile insufficient')]
    #[OA\Response(response: 409, description: 'User code already exists')]
    public function create(Request $request): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 3) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => true, 'message' => 'Invalid JSON', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        $code = trim($data['code'] ?? '');
        $identite = trim($data['identite'] ?? '');
        $mail = trim($data['mail'] ?? '');
        $tel = trim($data['tel'] ?? '');
        $fonction = trim($data['fonction'] ?? '');
        $niveau = (int) ($data['niveau'] ?? 7);
        $filtreSaison = $data['filtreSaison'] ?? '';
        $filtreCompetition = $data['filtreCompetition'] ?? '';
        $idEvenement = $data['idEvenement'] ?? '';
        $filtreJournee = $data['filtreJournee'] ?? '';
        $limitClubs = $data['limitClubs'] ?? '';
        $sendResetEmail = (bool) ($data['sendResetEmail'] ?? false);
        $includeDocLink = (bool) ($data['includeDocLink'] ?? false);
        $complementaryMessage = trim($data['complementaryMessage'] ?? '');

        // Validation
        if (empty($code)) {
            return $this->json(['error' => true, 'message' => 'User code is required', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }
        if (strlen($code) > 8) {
            return $this->json(['error' => true, 'message' => 'User code must be 8 characters or less', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($mail) || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => true, 'message' => 'Valid email is required', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        // Profile restrictions
        $profileError = $this->validateProfileAssignment($currentUser->getEffectiveNiveau(), $niveau);
        if ($profileError) {
            return $this->json(['error' => true, 'message' => $profileError, 'code' => 'PROFILE_RESTRICTED'], Response::HTTP_FORBIDDEN);
        }

        // Filter requirements per profile
        $filterError = $this->validateProfileFilters($niveau, $filtreCompetition, $filtreSaison, $filtreJournee, $limitClubs);
        if ($filterError) {
            return $this->json(['error' => true, 'message' => $filterError, 'code' => 'FILTER_REQUIRED'], Response::HTTP_BAD_REQUEST);
        }

        // Check uniqueness
        $exists = $this->connection->fetchOne('SELECT COUNT(*) FROM kp_user WHERE Code = ?', [$code]);
        if ((int) $exists > 0) {
            return $this->json(['error' => true, 'message' => 'User code already exists', 'code' => 'CODE_EXISTS'], Response::HTTP_CONFLICT);
        }

        // Generate random bcrypt password (user will use reset link)
        $randomPassword = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($randomPassword, PASSWORD_BCRYPT);

        $sql = "INSERT INTO kp_user (Code, Pwd, Identite, Mail, Tel, Fonction, Niveau,
                    Type_filtre_competition, Filtre_competition, Filtre_saison,
                    Filtre_competition_sql, Filtre_journee, Limitation_equipe_club, Id_Evenement)
                VALUES (?, ?, ?, ?, ?, ?, ?, 2, ?, ?, '', ?, ?, ?)";

        $this->connection->executeStatement($sql, [
            $code, $hashedPassword, $identite, $mail, $tel, $fonction, $niveau,
            $filtreCompetition, $filtreSaison, $filtreJournee, $limitClubs, $idEvenement,
        ]);

        $this->logActionForSeason(
            'Création utilisateur',
            null,
            "Création utilisateur $code ($identite), profil $niveau par " . $currentUser->getUserIdentifier()
        );

        if ($sendResetEmail) {
            $token = bin2hex(random_bytes(32));
            $this->connection->executeStatement('DELETE FROM kp_user_token WHERE user = ?', [$code]);
            $this->connection->executeStatement(
                'INSERT INTO kp_user_token (user, token, generated_at) VALUES (?, ?, NOW())',
                [$code, $token]
            );
            $this->notificationService->sendPasswordReset($mail, $token, $includeDocLink, $complementaryMessage);
        }

        return $this->json([
            'code' => $code,
            'identite' => $identite,
            'message' => 'User created',
        ], Response::HTTP_CREATED);
    }

    // ──────────────────────────────────────────────────────────────────────
    // PUT /admin/users/{code}  — Update user
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users/{code}', name: 'admin_users_update', methods: ['PUT'], priority: -1)]
    #[OA\Put(
        path: '/admin/users/{code}',
        summary: 'Update an existing user',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 200, description: 'User updated')]
    #[OA\Response(response: 400, description: 'Invalid data')]
    #[OA\Response(response: 403, description: 'Profile insufficient')]
    #[OA\Response(response: 404, description: 'User not found')]
    public function update(string $code, Request $request): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $adminNiveau = $currentUser?->getEffectiveNiveau() ?? 99;

        // Profiles 3-4 cannot use this endpoint — they can only manage mandates
        if (!$currentUser || $adminNiveau > 2) {
            return $this->json(['error' => true, 'message' => 'Access denied — profiles 3 and 4 can only manage mandates', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        // Check target user exists and admin can modify them
        $existing = $this->connection->fetchAssociative('SELECT Niveau, Identite FROM kp_user WHERE Code = ?', [$code]);
        if (!$existing) {
            return $this->json(['error' => true, 'message' => 'User not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $targetNiveau = (int) $existing['Niveau'];

        // Cannot modify user with higher or equal privilege (except super admin)
        if ($adminNiveau > 1 && $targetNiveau < $adminNiveau) {
            return $this->json(['error' => true, 'message' => 'Cannot modify user with higher privilege', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => true, 'message' => 'Invalid JSON', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        $niveau = (int) ($data['niveau'] ?? $targetNiveau);
        $mail = trim($data['mail'] ?? '');

        if (!empty($mail) && !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => true, 'message' => 'Valid email is required', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        // Profile restrictions
        $profileError = $this->validateProfileAssignment($adminNiveau, $niveau);
        if ($profileError) {
            return $this->json(['error' => true, 'message' => $profileError, 'code' => 'PROFILE_RESTRICTED'], Response::HTTP_FORBIDDEN);
        }

        // Filter requirements per profile
        $filterError = $this->validateProfileFilters(
            $niveau,
            $data['filtreCompetition'] ?? '',
            $data['filtreSaison'] ?? '',
            $data['filtreJournee'] ?? '',
            $data['limitClubs'] ?? ''
        );
        if ($filterError) {
            return $this->json(['error' => true, 'message' => $filterError, 'code' => 'FILTER_REQUIRED'], Response::HTTP_BAD_REQUEST);
        }

        $setClauses = [
            'Mail = ?', 'Tel = ?', 'Fonction = ?', 'Niveau = ?',
            'Type_filtre_competition = 2', 'Filtre_competition_sql = ?',
            'Filtre_competition = ?', 'Filtre_saison = ?',
            'Filtre_journee = ?', 'Limitation_equipe_club = ?',
        ];
        $params = [
            $mail,
            trim($data['tel'] ?? ''),
            trim($data['fonction'] ?? ''),
            $niveau,
            '', // Filtre_competition_sql always empty for app4
            $data['filtreCompetition'] ?? '',
            $data['filtreSaison'] ?? '',
            $data['filtreJournee'] ?? '',
            $data['limitClubs'] ?? '',
        ];

        // Only profile 1 can modify identity
        if ($adminNiveau <= 1) {
            $setClauses[] = 'Identite = ?';
            $params[] = trim($data['identite'] ?? $existing['Identite']);
        }

        // Only profile <= 2 can modify events filter
        if ($adminNiveau <= 2) {
            $setClauses[] = 'Id_Evenement = ?';
            $params[] = $data['idEvenement'] ?? '';
        }

        $params[] = $code; // WHERE clause
        $sql = "UPDATE kp_user SET " . implode(', ', $setClauses) . " WHERE Code = ?";
        $this->connection->executeStatement($sql, $params);

        $this->logActionForSeason(
            'Modification utilisateur',
            null,
            "Modification utilisateur $code, profil $niveau par " . $currentUser->getUserIdentifier()
        );

        return $this->json(['message' => 'User updated', 'code' => $code]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // DELETE /admin/users/{code}  — Delete user
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users/{code}', name: 'admin_users_delete', methods: ['DELETE'], priority: -1)]
    #[OA\Delete(
        path: '/admin/users/{code}',
        summary: 'Delete a user',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 204, description: 'User deleted')]
    #[OA\Response(response: 403, description: 'Profile insufficient')]
    #[OA\Response(response: 404, description: 'User not found')]
    public function delete(string $code): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 2) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $existing = $this->connection->fetchAssociative('SELECT Niveau FROM kp_user WHERE Code = ?', [$code]);
        if (!$existing) {
            return $this->json(['error' => true, 'message' => 'User not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        // Cannot delete user with profile <= own (except self-evidently)
        if ((int) $existing['Niveau'] <= $currentUser->getEffectiveNiveau()) {
            return $this->json(['error' => true, 'message' => 'Cannot delete user with equal or higher privilege', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $this->connection->executeStatement('DELETE FROM kp_user_token WHERE user = ?', [$code]);
        $this->connection->executeStatement('DELETE FROM kp_user WHERE Code = ?', [$code]);

        $this->logActionForSeason(
            'Suppression utilisateur',
            null,
            "Suppression utilisateur $code par " . $currentUser->getUserIdentifier()
        );

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /admin/users/export  — CSV export
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users/export', name: 'admin_users_export', methods: ['GET'])]
    #[OA\Get(
        path: '/admin/users/export',
        summary: 'Export filtered users as CSV',
        tags: ['30. App4 - Users']
    )]
    #[OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'profile', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'season', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'competition', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'CSV file')]
    #[OA\Response(response: 403, description: 'Profile insufficient')]
    public function export(Request $request): StreamedResponse|JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 2) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $search = trim($request->query->get('search', ''));
        $profileFilter = $request->query->get('profile');
        $seasonFilter = trim($request->query->get('season', ''));
        $competitionFilter = trim($request->query->get('competition', ''));

        $adminNiveau = $currentUser->getEffectiveNiveau();

        [$whereClause, $params] = $this->buildUserWhereClause($adminNiveau, $search, $profileFilter, $seasonFilter, $competitionFilter);

        // Fetch matching users (identity + base profile)
        $users = $this->connection->fetchAllAssociative(
            "SELECT u.Code, u.Identite, u.Mail, u.Tel, u.Fonction, u.Niveau,
                    u.Filtre_saison, u.Filtre_competition, u.Limitation_equipe_club, u.Filtre_journee
             FROM kp_user u $whereClause ORDER BY u.Niveau ASC, u.Identite ASC LIMIT 1000",
            $params
        );

        [$mandateConditions, $mandateParams] = $this->buildMandateFilterConditions($profileFilter, $seasonFilter, $competitionFilter);
        $userCodes = array_column($users, 'Code');
        $mandatesByCode = $this->fetchMandatesForUsers($userCodes, $mandateConditions, $mandateParams);

        $baseMatchesProfile = $profileFilter === null || $profileFilter === '';
        $baseMatchesSeason = empty($seasonFilter);
        $baseMatchesCompetition = empty($competitionFilter);

        // Build export rows: one line per matching scope (base profile and/or each matching mandate)
        $exportRows = [];
        foreach ($users as $u) {
            $baseNiveauMatches = $baseMatchesProfile || (int) $u['Niveau'] === (int) $profileFilter;
            $baseSaisonMatches = $baseMatchesSeason
                || $u['Filtre_saison'] === ''
                || str_contains($u['Filtre_saison'], "|$seasonFilter|");
            $baseCompMatches = $this->baseCompetitionMatches($u['Filtre_competition'] ?? '', $baseMatchesCompetition, $competitionFilter);

            if ($baseNiveauMatches && $baseSaisonMatches && $baseCompMatches) {
                $exportRows[] = [
                    'code'        => $u['Code'],
                    'identite'    => $u['Identite'] ?? '',
                    'mail'        => $u['Mail'] ?? '',
                    'tel'         => $u['Tel'] ?? '',
                    'fonction'    => $u['Fonction'] ?? '',
                    'niveau'      => $u['Niveau'],
                    'saison'      => str_replace('|', ', ', trim($u['Filtre_saison'] ?? '', '|')),
                    'competition' => str_replace('|', ', ', trim($u['Filtre_competition'] ?? '', '|')),
                    'clubs'       => $u['Limitation_equipe_club'] ?? '',
                    'journee'     => $u['Filtre_journee'] ?? '',
                    'mandat'      => '',
                ];
            }

            foreach ($mandatesByCode[$u['Code']] ?? [] as $m) {
                $exportRows[] = [
                    'code'        => $u['Code'],
                    'identite'    => $u['Identite'] ?? '',
                    'mail'        => $u['Mail'] ?? '',
                    'tel'         => $u['Tel'] ?? '',
                    'fonction'    => $u['Fonction'] ?? '',
                    'niveau'      => $m['niveau'],
                    'saison'      => str_replace('|', ', ', trim($m['filtre_saison'] ?? '', '|')),
                    'competition' => str_replace('|', ', ', trim($m['filtre_competition'] ?? '', '|')),
                    'clubs'       => $m['limitation_equipe_club'] ?? '',
                    'journee'     => $m['filtre_journee'] ?? '',
                    'mandat'      => $m['libelle'],
                ];
            }
        }

        $filename = 'utilisateurs_' . date('Ymd') . '.csv';

        $response = new StreamedResponse(function () use ($exportRows) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'Licence', 'Identité', 'Email', 'Téléphone', 'Fonction',
                'Profil', 'Saisons', 'Compétitions', 'Clubs', 'Journées', 'Mandat',
            ], ';');

            foreach ($exportRows as $row) {
                fputcsv($handle, [
                    $row['code'],
                    $row['identite'],
                    $row['mail'],
                    $row['tel'],
                    $row['fonction'],
                    $row['niveau'],
                    $row['saison'],
                    $row['competition'],
                    $row['clubs'],
                    $row['journee'],
                    $row['mandat'],
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');

        return $response;
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /admin/users/mandate-scopes  — Paginated scopes (mode Mandats)
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users/mandate-scopes', name: 'admin_users_mandate_scopes', methods: ['GET'])]
    #[OA\Get(
        path: '/admin/users/mandate-scopes',
        summary: 'List access scopes (base profiles + mandates) with pagination',
        tags: ['30. App4 - Users']
    )]
    #[OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20))]
    #[OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'profile', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'season', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'competition', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Paginated list of access scopes')]
    #[OA\Response(response: 403, description: 'Profile insufficient')]
    public function mandateScopes(Request $request): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 4) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limitParam = (int) $request->query->get('limit', 20);
        $limit = $limitParam > 0 ? min(100, $limitParam) : 20;
        $search = trim($request->query->get('search', ''));
        $profileFilter = $request->query->get('profile');
        $seasonFilter = trim($request->query->get('season', ''));
        $competitionFilter = trim($request->query->get('competition', ''));

        $adminNiveau = $currentUser->getEffectiveNiveau();

        [$whereClause, $params] = $this->buildUserWhereClause($adminNiveau, $search, $profileFilter, $seasonFilter, $competitionFilter);

        $users = $this->connection->fetchAllAssociative(
            "SELECT u.Code, u.Identite, u.Niveau, u.Filtre_saison, u.Filtre_competition,
                    u.Limitation_equipe_club, u.Filtre_journee
             FROM kp_user u $whereClause ORDER BY u.Niveau ASC, u.Identite ASC LIMIT 1000",
            $params
        );

        [$mandateConditions, $mandateParams] = $this->buildMandateFilterConditions($profileFilter, $seasonFilter, $competitionFilter);

        $userCodes = array_column($users, 'Code');
        $mandatesByCode = $this->fetchMandatesForUsers($userCodes, $mandateConditions, $mandateParams, true);

        $baseMatchesProfile = $profileFilter === null || $profileFilter === '';
        $baseMatchesSeason = empty($seasonFilter);
        $baseMatchesCompetition = empty($competitionFilter);

        // Build all scopes
        $allScopes = [];
        foreach ($users as $u) {
            $baseNiveauMatches = $baseMatchesProfile || (int) $u['Niveau'] === (int) $profileFilter;
            $baseSaisonMatches = $baseMatchesSeason || $u['Filtre_saison'] === '' || str_contains($u['Filtre_saison'], "|$seasonFilter|");
            $baseCompMatches = $this->baseCompetitionMatches($u['Filtre_competition'] ?? '', $baseMatchesCompetition, $competitionFilter);

            if ($baseNiveauMatches && $baseSaisonMatches && $baseCompMatches) {
                $allScopes[] = [
                    'userCode' => $u['Code'],
                    'identite' => $u['Identite'] ?? '',
                    'scopeType' => 'base',
                    'mandateId' => null,
                    'mandateLabel' => null,
                    'niveau' => (int) $u['Niveau'],
                    'filtreSaison' => $u['Filtre_saison'] ?? '',
                    'filtreCompetition' => $u['Filtre_competition'] ?? '',
                    'limitClubs' => $u['Limitation_equipe_club'] ?? '',
                    'filtreJournee' => $u['Filtre_journee'] ?? '',
                ];
            }

            foreach ($mandatesByCode[$u['Code']] ?? [] as $m) {
                $allScopes[] = [
                    'userCode' => $u['Code'],
                    'identite' => $u['Identite'] ?? '',
                    'scopeType' => 'mandate',
                    'mandateId' => (int) $m['id'],
                    'mandateLabel' => $m['libelle'],
                    'niveau' => (int) $m['niveau'],
                    'filtreSaison' => $m['filtre_saison'] ?? '',
                    'filtreCompetition' => $m['filtre_competition'] ?? '',
                    'limitClubs' => $m['limitation_equipe_club'] ?? '',
                    'filtreJournee' => $m['filtre_journee'] ?? '',
                ];
            }
        }

        $total = count($allScopes);
        $offset = ($page - 1) * $limit;
        $items = array_slice($allScopes, $offset, $limit);
        $totalPages = (int) ceil($total / $limit);

        return $this->json([
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // POST /admin/users/bulk-add-season  — Add season to selected scopes
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users/bulk-add-season', name: 'admin_users_bulk_add_season', methods: ['POST'])]
    #[OA\Post(
        path: '/admin/users/bulk-add-season',
        summary: 'Add a season to selected scopes (base profiles and/or mandates)',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 200, description: 'Report of processed scopes')]
    #[OA\Response(response: 400, description: 'Missing season or empty scope list')]
    #[OA\Response(response: 403, description: 'Profile insufficient')]
    public function bulkAddSeason(Request $request): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 2) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => true, 'message' => 'Invalid JSON', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        $season = trim($data['season'] ?? '');
        $scopes = $data['scopes'] ?? [];

        if (empty($season)) {
            return $this->json(['error' => true, 'message' => 'Season is required', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }
        if (!is_array($scopes) || count($scopes) === 0) {
            return $this->json(['error' => true, 'message' => 'No scopes provided', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        $updated = 0;
        $alreadyPresent = 0;
        $restricted = 0;
        $restrictedDetails = [];

        foreach ($scopes as $scope) {
            $type = $scope['type'] ?? '';
            $userCode = trim($scope['userCode'] ?? '');
            if (empty($userCode) || !in_array($type, ['base', 'mandate'], true)) {
                continue;
            }

            if ($type === 'base') {
                $row = $this->connection->fetchAssociative(
                    'SELECT Filtre_saison, Identite FROM kp_user WHERE Code = ?',
                    [$userCode]
                );
                if (!$row) continue;

                $current = $row['Filtre_saison'] ?? '';
                if (str_contains($current, "|$season|")) {
                    $alreadyPresent++;
                    continue;
                }

                $wasEmpty = ($current === '');
                $newValue = $wasEmpty ? "|$season|" : rtrim($current, '|') . "|$season|";
                $this->connection->executeStatement(
                    'UPDATE kp_user SET Filtre_saison = ? WHERE Code = ?',
                    [$newValue, $userCode]
                );
                $updated++;
                if ($wasEmpty) {
                    $restricted++;
                    $restrictedDetails[] = [
                        'type' => 'base',
                        'userCode' => $userCode,
                        'identite' => $row['Identite'] ?? '',
                    ];
                }
            } else {
                $mandateId = (int) ($scope['mandateId'] ?? 0);
                if ($mandateId <= 0) continue;

                $row = $this->connection->fetchAssociative(
                    'SELECT filtre_saison, libelle FROM kp_user_mandat WHERE id = ? AND user_code = ?',
                    [$mandateId, $userCode]
                );
                if (!$row) continue;

                $current = $row['filtre_saison'] ?? '';
                if (str_contains($current, "|$season|")) {
                    $alreadyPresent++;
                    continue;
                }

                $wasEmpty = ($current === '');
                $newValue = $wasEmpty ? "|$season|" : rtrim($current, '|') . "|$season|";
                $this->connection->executeStatement(
                    'UPDATE kp_user_mandat SET filtre_saison = ? WHERE id = ? AND user_code = ?',
                    [$newValue, $mandateId, $userCode]
                );
                $updated++;
                if ($wasEmpty) {
                    $restricted++;
                    $restrictedDetails[] = [
                        'type' => 'mandate',
                        'userCode' => $userCode,
                        'mandateId' => $mandateId,
                        'mandateLabel' => $row['libelle'],
                    ];
                }
            }
        }

        $this->logActionForSeason(
            'Ajout saison en masse',
            null,
            "Saison $season ajoutée sur $updated périmètre(s) par " . $currentUser->getUserIdentifier()
        );

        return $this->json([
            'season' => $season,
            'updated' => $updated,
            'alreadyPresent' => $alreadyPresent,
            'restricted' => $restricted,
            'details' => ['restricted' => $restrictedDetails],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // POST /admin/users/bulk-delete  — Bulk delete
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users/bulk-delete', name: 'admin_users_bulk_delete', methods: ['POST'])]
    #[OA\Post(
        path: '/admin/users/bulk-delete',
        summary: 'Bulk delete users',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 200, description: 'Users deleted')]
    #[OA\Response(response: 403, description: 'Profile insufficient')]
    public function bulkDelete(Request $request): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 2) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $codes = $data['codes'] ?? [];
        if (!is_array($codes) || count($codes) === 0) {
            return $this->json(['error' => true, 'message' => 'No codes provided', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        $adminNiveau = $currentUser->getEffectiveNiveau();
        $deleted = 0;

        foreach ($codes as $code) {
            $code = trim((string) $code);
            if (empty($code)) {
                continue;
            }

            $existing = $this->connection->fetchAssociative('SELECT Niveau FROM kp_user WHERE Code = ?', [$code]);
            if (!$existing) {
                continue;
            }

            // Skip users with profile <= admin
            if ((int) $existing['Niveau'] <= $adminNiveau) {
                continue;
            }

            $this->connection->executeStatement('DELETE FROM kp_user_token WHERE user = ?', [$code]);
            $this->connection->executeStatement('DELETE FROM kp_user WHERE Code = ?', [$code]);
            $deleted++;
        }

        $this->logActionForSeason(
            'Suppression en masse utilisateurs',
            null,
            "Suppression de $deleted utilisateur(s) par " . $currentUser->getUserIdentifier()
        );

        return $this->json(['deleted' => $deleted]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // POST /admin/users/{code}/reset-password  — Send password reset
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users/{code}/reset-password', name: 'admin_users_reset_password', methods: ['POST'])]
    #[OA\Post(
        path: '/admin/users/{code}/reset-password',
        summary: 'Generate a password reset token for a user',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 200, description: 'Reset token generated')]
    #[OA\Response(response: 403, description: 'Profile insufficient')]
    #[OA\Response(response: 404, description: 'User not found')]
    public function resetPassword(string $code): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 3) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $existing = $this->connection->fetchAssociative('SELECT Code, Mail FROM kp_user WHERE Code = ?', [$code]);
        if (!$existing) {
            return $this->json(['error' => true, 'message' => 'User not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        // Generate a reset token (64 hex chars)
        $token = bin2hex(random_bytes(32));

        // Store in kp_user_token (upsert: replace if exists)
        $this->connection->executeStatement('DELETE FROM kp_user_token WHERE user = ?', [$code]);
        $this->connection->executeStatement(
            'INSERT INTO kp_user_token (user, token, generated_at) VALUES (?, ?, NOW())',
            [$code, $token]
        );

        $data = json_decode($request->getContent(), true) ?? [];
        $includeDocLink = (bool) ($data['includeDocLink'] ?? false);
        $complementaryMessage = trim($data['complementaryMessage'] ?? '');

        $this->notificationService->sendPasswordReset($existing['Mail'], $token, $includeDocLink, $complementaryMessage);

        $this->logActionForSeason(
            'Reset mot de passe',
            null,
            "Token de réinitialisation généré pour $code par " . $currentUser->getUserIdentifier()
        );

        return $this->json([
            'message' => 'Reset token generated',
            'email' => $existing['Mail'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // MANDATES CRUD
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/users/{code}/mandats', name: 'admin_users_mandats_list', methods: ['GET'])]
    #[OA\Get(
        path: '/admin/users/{code}/mandats',
        summary: 'List mandates for a user',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 200, description: 'List of mandates')]
    public function listMandats(string $code): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 3) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        // Check user exists
        $exists = $this->connection->fetchOne('SELECT COUNT(*) FROM kp_user WHERE Code = ?', [$code]);
        if ((int) $exists === 0) {
            return $this->json(['error' => true, 'message' => 'User not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, libelle, niveau, filtre_saison, filtre_competition, limitation_equipe_club, filtre_journee, id_evenement FROM kp_user_mandat WHERE user_code = ? ORDER BY id',
            [$code]
        );

        $mandats = array_map(fn(array $row) => [
            'id' => (int) $row['id'],
            'libelle' => $row['libelle'],
            'niveau' => (int) $row['niveau'],
            'filtreSaison' => $row['filtre_saison'] ?? '',
            'filtreCompetition' => $row['filtre_competition'] ?? '',
            'limitClubs' => $row['limitation_equipe_club'] ?? '',
            'filtreJournee' => $row['filtre_journee'] ?? '',
            'idEvenement' => $row['id_evenement'] ?? '',
        ], $rows);

        return $this->json(['mandats' => $mandats]);
    }

    #[Route('/admin/users/{code}/mandats', name: 'admin_users_mandats_create', methods: ['POST'])]
    #[OA\Post(
        path: '/admin/users/{code}/mandats',
        summary: 'Create a mandate for a user',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 201, description: 'Mandate created')]
    public function createMandat(string $code, Request $request): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 3) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $exists = $this->connection->fetchOne('SELECT COUNT(*) FROM kp_user WHERE Code = ?', [$code]);
        if ((int) $exists === 0) {
            return $this->json(['error' => true, 'message' => 'User not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => true, 'message' => 'Invalid JSON', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        $libelle = trim($data['libelle'] ?? '');
        $niveau = (int) ($data['niveau'] ?? 7);

        if (empty($libelle)) {
            return $this->json(['error' => true, 'message' => 'Mandate label is required', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        $profileError = $this->validateProfileAssignment($currentUser->getEffectiveNiveau(), $niveau);
        if ($profileError) {
            return $this->json(['error' => true, 'message' => $profileError, 'code' => 'PROFILE_RESTRICTED'], Response::HTTP_FORBIDDEN);
        }

        // Filter requirements per profile (mandate: season is mandatory)
        $filterError = $this->validateProfileFilters(
            $niveau,
            $data['filtreCompetition'] ?? '',
            $data['filtreSaison'] ?? '',
            $data['filtreJournee'] ?? '',
            $data['limitClubs'] ?? '',
            true
        );
        if ($filterError) {
            return $this->json(['error' => true, 'message' => $filterError, 'code' => 'FILTER_REQUIRED'], Response::HTTP_BAD_REQUEST);
        }

        $sql = "INSERT INTO kp_user_mandat (user_code, libelle, niveau, filtre_saison, filtre_competition, limitation_equipe_club, filtre_journee, id_evenement)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $this->connection->executeStatement($sql, [
            $code,
            $libelle,
            $niveau,
            $data['filtreSaison'] ?? '',
            $data['filtreCompetition'] ?? '',
            $data['limitClubs'] ?? '',
            $data['filtreJournee'] ?? '',
            $data['idEvenement'] ?? '',
        ]);

        $id = (int) $this->connection->lastInsertId();

        $this->logActionForSeason(
            'Création mandat',
            null,
            "Mandat '$libelle' (profil $niveau) créé pour $code par " . $currentUser->getUserIdentifier()
        );

        return $this->json(['id' => $id, 'message' => 'Mandate created'], Response::HTTP_CREATED);
    }

    #[Route('/admin/users/{code}/mandats/{id}', name: 'admin_users_mandats_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/admin/users/{code}/mandats/{id}',
        summary: 'Update a mandate',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 200, description: 'Mandate updated')]
    public function updateMandat(string $code, int $id, Request $request): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 3) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $existing = $this->connection->fetchAssociative(
            'SELECT id FROM kp_user_mandat WHERE id = ? AND user_code = ?',
            [$id, $code]
        );
        if (!$existing) {
            return $this->json(['error' => true, 'message' => 'Mandate not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => true, 'message' => 'Invalid JSON', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        $libelle = trim($data['libelle'] ?? '');
        $niveau = (int) ($data['niveau'] ?? 7);

        if (empty($libelle)) {
            return $this->json(['error' => true, 'message' => 'Mandate label is required', 'code' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        $profileError = $this->validateProfileAssignment($currentUser->getEffectiveNiveau(), $niveau);
        if ($profileError) {
            return $this->json(['error' => true, 'message' => $profileError, 'code' => 'PROFILE_RESTRICTED'], Response::HTTP_FORBIDDEN);
        }

        // Filter requirements per profile (mandate: season is mandatory)
        $filterError = $this->validateProfileFilters(
            $niveau,
            $data['filtreCompetition'] ?? '',
            $data['filtreSaison'] ?? '',
            $data['filtreJournee'] ?? '',
            $data['limitClubs'] ?? '',
            true
        );
        if ($filterError) {
            return $this->json(['error' => true, 'message' => $filterError, 'code' => 'FILTER_REQUIRED'], Response::HTTP_BAD_REQUEST);
        }

        $sql = "UPDATE kp_user_mandat SET libelle = ?, niveau = ?, filtre_saison = ?, filtre_competition = ?,
                    limitation_equipe_club = ?, filtre_journee = ?, id_evenement = ?
                WHERE id = ? AND user_code = ?";

        $this->connection->executeStatement($sql, [
            $libelle,
            $niveau,
            $data['filtreSaison'] ?? '',
            $data['filtreCompetition'] ?? '',
            $data['limitClubs'] ?? '',
            $data['filtreJournee'] ?? '',
            $data['idEvenement'] ?? '',
            $id,
            $code,
        ]);

        $this->logActionForSeason(
            'Modification mandat',
            null,
            "Mandat #$id '$libelle' modifié pour $code par " . $currentUser->getUserIdentifier()
        );

        return $this->json(['message' => 'Mandate updated']);
    }

    #[Route('/admin/users/{code}/mandats/{id}', name: 'admin_users_mandats_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/admin/users/{code}/mandats/{id}',
        summary: 'Delete a mandate',
        tags: ['30. App4 - Users']
    )]
    #[OA\Response(response: 204, description: 'Mandate deleted')]
    public function deleteMandat(string $code, int $id): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || $currentUser->getEffectiveNiveau() > 3) {
            return $this->json(['error' => true, 'message' => 'Access denied', 'code' => 'ACCESS_DENIED'], Response::HTTP_FORBIDDEN);
        }

        $existing = $this->connection->fetchAssociative(
            'SELECT id FROM kp_user_mandat WHERE id = ? AND user_code = ?',
            [$id, $code]
        );
        if (!$existing) {
            return $this->json(['error' => true, 'message' => 'Mandate not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $this->connection->executeStatement('DELETE FROM kp_user_mandat WHERE id = ? AND user_code = ?', [$id, $code]);

        $this->logActionForSeason(
            'Suppression mandat',
            null,
            "Mandat #$id supprimé pour $code par " . $currentUser->getUserIdentifier()
        );

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Shared helpers for scope-based queries (export + mandate-scopes)
    // ──────────────────────────────────────────────────────────────────────

    /** @return array{string, array<int, mixed>} [$whereClause, $params] */
    private function buildUserWhereClause(
        int $adminNiveau,
        string $search,
        ?string $profileFilter,
        string $seasonFilter,
        string $competitionFilter
    ): array {
        $conditions = [];
        $params = [];

        if ($adminNiveau > 1) {
            $conditions[] = 'u.Niveau >= ?';
            $params[] = $adminNiveau;
        }
        if (!empty($search)) {
            $conditions[] = '(u.Code LIKE ? OR u.Identite LIKE ? OR u.Mail LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // For profile/season/competition filters: an user matches if its BASE PROFILE satisfies
        // ALL active filters simultaneously, OR a SINGLE MANDATE satisfies ALL active filters simultaneously.
        $hasProfileFilter = $profileFilter !== null && $profileFilter !== '';
        $hasSeasonFilter = !empty($seasonFilter);
        $hasCompFilter = !empty($competitionFilter);
        $compCodes = $hasCompFilter
            ? array_filter(array_map('trim', explode(',', $competitionFilter)))
            : [];

        if ($hasProfileFilter || $hasSeasonFilter || $hasCompFilter) {
            // Build base-profile match: all active filters must hold on u.*
            $baseParts = [];
            if ($hasProfileFilter) {
                $baseParts[] = 'u.Niveau = ?';
                $params[] = (int) $profileFilter;
            }
            if ($hasSeasonFilter) {
                $baseParts[] = "(u.Filtre_saison = '' OR u.Filtre_saison LIKE ?)";
                $params[] = "%|$seasonFilter|%";
            }
            if (!empty($compCodes)) {
                $compOrParts = [];
                foreach ($compCodes as $code) {
                    $compOrParts[] = "u.Filtre_competition = '' OR u.Filtre_competition LIKE ?";
                    $params[] = "%|$code|%";
                }
                $baseParts[] = '(' . implode(' OR ', $compOrParts) . ')';
            }
            $baseMatch = implode(' AND ', $baseParts);

            // Build mandate match: a single mandate must satisfy ALL active filters
            $mandateParts = [];
            if ($hasProfileFilter) {
                $mandateParts[] = 'm.niveau = ?';
                $params[] = (int) $profileFilter;
            }
            if ($hasSeasonFilter) {
                $mandateParts[] = "(m.filtre_saison = '' OR m.filtre_saison LIKE ?)";
                $params[] = "%|$seasonFilter|%";
            }
            if (!empty($compCodes)) {
                $compOrParts = [];
                foreach ($compCodes as $code) {
                    $compOrParts[] = "m.filtre_competition = '' OR m.filtre_competition LIKE ?";
                    $params[] = "%|$code|%";
                }
                $mandateParts[] = '(' . implode(' OR ', $compOrParts) . ')';
            }
            $mandateMatch = 'EXISTS (SELECT 1 FROM kp_user_mandat m WHERE m.user_code = u.Code AND ' . implode(' AND ', $mandateParts) . ')';

            $conditions[] = "($baseMatch OR $mandateMatch)";
        }

        $whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$whereClause, $params];
    }

    /** @return array{array<string>, array<mixed>} [$conditions, $params] */
    private function buildMandateFilterConditions(?string $profileFilter, string $seasonFilter, string $competitionFilter): array
    {
        $conditions = [];
        $params = [];

        if ($profileFilter !== null && $profileFilter !== '') {
            $conditions[] = 'm.niveau = ?';
            $params[] = (int) $profileFilter;
        }
        if (!empty($seasonFilter)) {
            $conditions[] = "(m.filtre_saison = '' OR m.filtre_saison LIKE ?)";
            $params[] = "%|$seasonFilter|%";
        }
        if (!empty($competitionFilter)) {
            $codes = array_filter(array_map('trim', explode(',', $competitionFilter)));
            if (!empty($codes)) {
                $orParts = [];
                foreach ($codes as $code) {
                    $orParts[] = "m.filtre_competition = '' OR m.filtre_competition LIKE ?";
                    $params[] = "%|$code|%";
                }
                $conditions[] = '(' . implode(' OR ', $orParts) . ')';
            }
        }

        return [$conditions, $params];
    }

    /**
     * @param string[] $userCodes
     * @param string[] $mandateConditions
     * @param mixed[]  $mandateParams
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function fetchMandatesForUsers(array $userCodes, array $mandateConditions, array $mandateParams, bool $includeId = false): array
    {
        if (empty($userCodes)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userCodes), '?'));
        $mandateWhere = "WHERE m.user_code IN ($placeholders)";
        $sqlParams = $userCodes;

        if (!empty($mandateConditions)) {
            $mandateWhere .= ' AND ' . implode(' AND ', $mandateConditions);
            $sqlParams = array_merge($sqlParams, $mandateParams);
        }

        $idCol = $includeId ? 'm.id,' : '';
        $rows = $this->connection->fetchAllAssociative(
            "SELECT m.user_code, {$idCol} m.libelle, m.niveau, m.filtre_saison, m.filtre_competition,
                    m.limitation_equipe_club, m.filtre_journee
             FROM kp_user_mandat m $mandateWhere ORDER BY m.user_code, m.id",
            $sqlParams
        );

        $byCode = [];
        foreach ($rows as $m) {
            $byCode[$m['user_code']][] = $m;
        }
        return $byCode;
    }

    private function baseCompetitionMatches(string $filtreCompetition, bool $baseMatchesCompetition, string $competitionFilter): bool
    {
        if ($baseMatchesCompetition) {
            return true;
        }
        foreach (array_filter(array_map('trim', explode(',', $competitionFilter))) as $code) {
            if ($filtreCompetition === '' || str_contains($filtreCompetition, "|$code|")) {
                return true;
            }
        }
        return false;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helper: validate profile assignment
    // ──────────────────────────────────────────────────────────────────────

    private function validateProfileAssignment(int $adminNiveau, int $targetNiveau): ?string
    {
        if ($targetNiveau < 1 || $targetNiveau > 10) {
            return 'Profile must be between 1 and 10';
        }

        // Profile 1 can assign any profile
        if ($adminNiveau <= 1) {
            return null;
        }

        // Profile 2 can assign profiles 3-10
        if ($adminNiveau <= 2 && $targetNiveau >= 3) {
            return null;
        }

        // Profile 3 can assign profiles 4-10
        if ($adminNiveau <= 3 && $targetNiveau >= 4) {
            return null;
        }

        // Profile 4 can assign profiles 5-10
        if ($adminNiveau <= 4 && $targetNiveau >= 5) {
            return null;
        }

        return 'You cannot assign this profile level';
    }

    /**
     * Validate that mandatory filters are set for a given profile level.
     *
     * Rules:
     *  - niveau >= 3 : at least one season required (not "all")
     *  - niveau >= 3 : at least one competition required (not "all")
     *  - niveau 5 or 6 : at least one journée required
     *  - niveau 7 : at least one club required
     *  - isMandat = true : at least one season required (not "all") — already covered by first rule
     *
     * @return string|null Error message key, or null if valid
     */
    private function validateProfileFilters(
        int $niveau,
        string $filtreCompetition,
        string $filtreSaison,
        string $filtreJournee,
        string $limitClubs,
        bool $isMandat = false
    ): ?string {
        if ($niveau >= 3 && trim($filtreSaison) === '') {
            return 'At least one season must be selected for profiles 3 and above';
        }

        if ($niveau >= 3 && trim($filtreCompetition) === '') {
            return 'At least one competition must be selected for profiles 3 and above';
        }

        if (in_array($niveau, [5, 6]) && trim($filtreJournee) === '') {
            return 'At least one gameday must be selected for profiles 5 and 6';
        }

        if ($niveau === 7 && trim($limitClubs) === '') {
            return 'At least one club must be selected for profile 7';
        }

        if ($isMandat && trim($filtreSaison) === '') {
            return 'At least one season must be selected for a mandate';
        }

        return null;
    }
}
