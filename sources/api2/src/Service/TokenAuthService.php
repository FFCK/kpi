<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class TokenAuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get and validate token from request
     *
     * Checks for token in:
     * 1. X-Auth-Token header
     * 2. Cookie (kpi_app)
     * 3. URL parameter (token)
     *
     * @param Request $request
     * @param string|null $tokenFromUrl Token from URL parameter (optional)
     * @param int|null $eventId Event ID to check access (optional)
     * @return array{user: string, token: string}|null Returns user data or null if invalid
     */
    public function validateToken(Request $request, ?string $tokenFromUrl = null, ?int $eventId = null): ?array
    {
        // Get token from multiple sources (priority order)
        $token = $request->headers->get('X-Auth-Token')
            ?? $request->cookies->get('kpi_app')
            ?? $tokenFromUrl;

        if (!$token) {
            return null;
        }

        $conn = $this->entityManager->getConnection();

        // Query user by token
        $sql = "SELECT ut.user, ut.token, ut.generated_at, u.Id_Evenement
            FROM kp_user_token ut
            INNER JOIN kp_user u ON (ut.user = u.Code)
            WHERE ut.token = ?";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([$token]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        // Check token expiration (10 days)
        $generatedAt = new \DateTime($row['generated_at']);
        $generatedAt->add(new \DateInterval('P10D'));
        $now = new \DateTime();

        if ($generatedAt < $now) {
            return null; // Token expired
        }

        // Check event access if eventId provided
        if ($eventId !== null) {
            $grantedEvents = array_filter(explode('|', trim($row['Id_Evenement'] ?? '', '|')));
            if (!in_array((string)$eventId, $grantedEvents, true)) {
                return null; // User doesn't have access to this event
            }
        }

        return [
            'user' => $row['user'],
            'token' => $row['token']
        ];
    }

    /**
     * Create unauthorized response
     */
    public function createUnauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return new JsonResponse(['error' => $message], 401);
    }

    /**
     * Create forbidden response
     */
    public function createForbiddenResponse(string $message = 'Forbidden - Access denied to this event'): JsonResponse
    {
        return new JsonResponse(['error' => $message], 403);
    }
}
