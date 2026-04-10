<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Auth Mandate Controller
 *
 * Allows authenticated users to list and switch between their mandates.
 */
#[Route('/auth')]
#[IsGranted('ROLE_USER')]
#[OA\Tag(name: '20. App4 - Authentication')]
class AdminAuthMandateController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly JWTTokenManagerInterface $jwtManager
    ) {
    }

    /**
     * List mandates for the currently authenticated user
     */
    #[Route('/mandates', name: 'api_auth_mandates', methods: ['GET'])]
    #[OA\Get(
        path: '/auth/mandates',
        summary: 'List mandates for the current user',
        tags: ['20. App4 - Authentication']
    )]
    #[OA\Response(response: 200, description: 'Base profile and mandates')]
    public function listMandates(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $baseProfile = [
            'niveau' => $user->getNiveau(),
            'filters' => $user->toArray()['filters'],
        ];

        $mandates = $this->loadMandatesForUser($user->getCode());

        return $this->json([
            'baseProfile' => $baseProfile,
            'mandates' => $mandates,
        ]);
    }

    /**
     * Switch active mandate and get new JWT token
     */
    #[Route('/switch-mandate', name: 'api_auth_switch_mandate', methods: ['POST'])]
    #[OA\Post(
        path: '/auth/switch-mandate',
        summary: 'Switch active mandate and get new JWT',
        tags: ['20. App4 - Authentication']
    )]
    #[OA\Response(response: 200, description: 'New token with active mandate')]
    #[OA\Response(response: 400, description: 'Invalid mandate ID')]
    #[OA\Response(response: 404, description: 'Mandate not found')]
    public function switchMandate(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $mandateId = $data['mandateId'] ?? null;

        $userData = $user->toArray();
        $mandates = $this->loadMandatesForUser($user->getCode());
        $userData['mandates'] = $mandates;

        if ($mandateId === null) {
            // Return to base profile
            $userData['activeMandate'] = null;
            $userData['effectiveProfile'] = $user->getNiveau();
            $userData['effectiveFilters'] = $userData['filters'];
        } else {
            // Find the mandate
            $mandate = null;
            foreach ($mandates as $m) {
                if ($m['id'] === (int) $mandateId) {
                    $mandate = $m;
                    break;
                }
            }

            if (!$mandate) {
                return $this->json(['error' => true, 'message' => 'Mandate not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
            }

            $userData['activeMandate'] = [
                'id' => $mandate['id'],
                'libelle' => $mandate['libelle'],
            ];
            $userData['effectiveProfile'] = $mandate['niveau'];
            $userData['effectiveFilters'] = $mandate['filters'];
        }

        // Generate new JWT
        $token = $this->jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user' => $userData,
        ]);
    }

    private function loadMandatesForUser(string $userCode): array
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
}
