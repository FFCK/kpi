<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\UserProvider;
use Doctrine\DBAL\Connection;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

/**
 * JWT Authentication controller for App4 admin interface
 */
#[Route('/auth')]
#[OA\Tag(name: '20. App4 - Authentication')]
class AdminAuthController extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Connection $connection
    ) {
    }

    /**
     * Login endpoint - authenticates user and returns JWT token
     */
    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'message' => 'Invalid JSON payload'
            ], Response::HTTP_BAD_REQUEST);
        }

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            return $this->json([
                'message' => 'Username and password are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var User $user */
            $user = $this->userProvider->loadUserByIdentifier($username);
        } catch (UserNotFoundException) {
            return $this->json([
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Dual password verification: bcrypt first, then MD5 fallback
        if (!$this->verifyPassword($user, $password)) {
            return $this->json([
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Load mandates for the user
        $mandates = $this->loadMandates($user->getCode());

        // Generate JWT token
        $token = $this->jwtManager->create($user);

        $userData = $user->toArray();
        $userData['mandates'] = $mandates;
        $userData['activeMandate'] = null;
        $userData['effectiveProfile'] = $user->getNiveau();
        $userData['effectiveFilters'] = $userData['filters'];

        return $this->json([
            'token' => $token,
            'user' => $userData,
            'hasMandates' => count($mandates) > 0,
        ]);
    }

    /**
     * Get current authenticated user info
     */
    #[Route('/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $mandates = $this->loadMandates($user->getCode());
        $userData = $user->toArray();
        $userData['mandates'] = $mandates;
        $userData['activeMandate'] = null;
        $userData['effectiveProfile'] = $user->getNiveau();
        $userData['effectiveFilters'] = $userData['filters'];

        return $this->json([
            'user' => $userData
        ]);
    }

    /**
     * Refresh token endpoint
     */
    #[Route('/refresh', name: 'api_auth_refresh', methods: ['POST'])]
    public function refresh(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $mandates = $this->loadMandates($user->getCode());

        // Generate new JWT token
        $token = $this->jwtManager->create($user);

        $userData = $user->toArray();
        $userData['mandates'] = $mandates;
        $userData['activeMandate'] = null;
        $userData['effectiveProfile'] = $user->getNiveau();
        $userData['effectiveFilters'] = $userData['filters'];

        return $this->json([
            'token' => $token,
            'user' => $userData
        ]);
    }

    /**
     * Validate a password reset token and change password
     */
    #[Route('/reset-password', name: 'api_auth_reset_password', methods: ['POST'])]
    #[OA\Post(
        path: '/auth/reset-password',
        summary: 'Reset password using a token',
        tags: ['20. App4 - Authentication']
    )]
    #[OA\Response(response: 200, description: 'Password changed')]
    #[OA\Response(response: 400, description: 'Invalid password')]
    #[OA\Response(response: 401, description: 'Invalid or expired token')]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($token) || empty($password)) {
            return $this->json(['message' => 'Token and password are required'], Response::HTTP_BAD_REQUEST);
        }

        // Validate password complexity
        $complexityError = $this->validatePasswordComplexity($password);
        if ($complexityError) {
            return $this->json(['message' => $complexityError, 'code' => 'WEAK_PASSWORD'], Response::HTTP_BAD_REQUEST);
        }

        // Find token (valid for 48h)
        $row = $this->connection->fetchAssociative(
            'SELECT user, generated_at FROM kp_user_token WHERE token = ?',
            [$token]
        );

        if (!$row) {
            return $this->json(['message' => 'Invalid or expired token', 'code' => 'INVALID_TOKEN'], Response::HTTP_UNAUTHORIZED);
        }

        $generatedAt = new \DateTimeImmutable($row['generated_at']);
        $expiresAt = $generatedAt->modify('+48 hours');
        if (new \DateTimeImmutable() > $expiresAt) {
            // Clean up expired token
            $this->connection->executeStatement('DELETE FROM kp_user_token WHERE token = ?', [$token]);
            return $this->json(['message' => 'Invalid or expired token', 'code' => 'INVALID_TOKEN'], Response::HTTP_UNAUTHORIZED);
        }

        $userCode = $row['user'];

        // Hash with bcrypt and update
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->connection->executeStatement('UPDATE kp_user SET Pwd = ? WHERE Code = ?', [$hashedPassword, $userCode]);

        // Delete the used token
        $this->connection->executeStatement('DELETE FROM kp_user_token WHERE user = ?', [$userCode]);

        return $this->json(['message' => 'Password changed successfully']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Verify password: try bcrypt first, then MD5 fallback.
     * If MD5 matches, upgrade to bcrypt automatically.
     */
    private function verifyPassword(User $user, string $plainPassword): bool
    {
        $storedHash = $user->getPassword();

        // Try bcrypt first (new passwords)
        if (password_verify($plainPassword, $storedHash)) {
            return true;
        }

        // Fallback: MD5 (legacy passwords)
        if (md5($plainPassword) === $storedHash) {
            // Auto-upgrade to bcrypt
            $bcryptHash = password_hash($plainPassword, PASSWORD_BCRYPT);
            $this->connection->executeStatement(
                'UPDATE kp_user SET Pwd = ? WHERE Code = ?',
                [$bcryptHash, $user->getUserIdentifier()]
            );
            return true;
        }

        return false;
    }

    /**
     * Load mandates for a user, formatted for API response
     */
    private function loadMandates(string $userCode): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, libelle, niveau, filtre_saison, filtre_competition, limitation_equipe_club, filtre_journee, id_evenement
             FROM kp_user_mandat WHERE user_code = ? ORDER BY id',
            [$userCode]
        );

        return array_map(fn(array $row) => [
            'id' => (int) $row['id'],
            'libelle' => $row['libelle'],
            'niveau' => (int) $row['niveau'],
            'filters' => [
                'seasons' => self::parsePipeFilter($row['filtre_saison']),
                'competitions' => self::parsePipeFilter($row['filtre_competition']),
                'clubs' => self::parseCommaFilter($row['limitation_equipe_club']),
                'journees' => self::parseCommaFilterInt($row['filtre_journee']),
                'events' => self::parsePipeFilterInt($row['id_evenement']),
            ],
        ], $rows);
    }

    private static function parsePipeFilter(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        return array_values(array_filter(explode('|', trim($value, '|')), fn($v) => $v !== ''));
    }

    private static function parseCommaFilter(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        return array_values(array_filter(explode(',', $value), fn($v) => trim($v) !== ''));
    }

    private static function parsePipeFilterInt(?string $value): ?array
    {
        $values = self::parsePipeFilter($value);
        return $values !== null ? array_map('intval', $values) : null;
    }

    private static function parseCommaFilterInt(?string $value): ?array
    {
        $values = self::parseCommaFilter($value);
        return $values !== null ? array_map('intval', $values) : null;
    }

    private function validatePasswordComplexity(string $password): ?string
    {
        if (mb_strlen($password) < 10) {
            return 'Password must be at least 10 characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least 1 uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must contain at least 1 lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least 1 digit';
        }
        if (!preg_match('/[!@#$%^&*()\-_=+\[\]{}\\\\|;:\'",.<>?\/~`]/', $password)) {
            return 'Password must contain at least 1 special character';
        }
        return null;
    }
}
