<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        path: '/login',
        summary: 'User authentication',
        description: 'Authenticates a user with HTTP Basic Auth and returns a token for subsequent requests',
        tags: ['1. App2 - Authentication'],
        security: [['BasicAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authentication successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'string', example: '123456'),
                                new OA\Property(property: 'name', type: 'string', example: 'Dupont'),
                                new OA\Property(property: 'firstname', type: 'string', example: 'Jean'),
                                new OA\Property(property: 'profile', type: 'string', example: 'O'),
                                new OA\Property(property: 'events', type: 'string', example: '123|456|789'),
                                new OA\Property(property: 'token', type: 'string', example: 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid credentials or no events access')
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        // Get HTTP Basic Auth credentials
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        // Decode Basic Auth
        $credentials = base64_decode(substr($authHeader, 6));
        [$user, $password] = explode(':', $credentials, 2);

        // Clean user code (remove leading zeros)
        $user = preg_replace('/^0+/', '', trim($user));

        $conn = $this->entityManager->getConnection();

        // Query user
        $sql = "SELECT u.Code, u.Pwd, u.Niveau, u.Id_Evenement,
            c.Nom, c.Prenom, c.Numero_club, ut.token, ut.generated_at
            FROM kp_user u
            JOIN kp_licence c ON (u.Code = c.Matric)
            LEFT OUTER JOIN kp_user_token ut ON (u.Code = ut.user)
            WHERE u.Code = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $user);
        $result = $stmt->executeQuery();
        $row = $result->fetchAssociative();

        if (!$row) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        // Verify password and events
        $events = trim($row['Id_Evenement'] ?? '', '|');
        if ($row['Pwd'] !== md5($password) || strlen($events) === 0) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        // Generate or reuse token
        $token = $row['token'] ?? bin2hex(random_bytes(16));

        // Save/update token
        $sqlToken = "INSERT INTO kp_user_token (user, token, generated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE token = VALUES(token), generated_at = NOW()";

        $conn->executeStatement($sqlToken, [$row['Code'], $token]);

        // Return user data
        return new JsonResponse([
            'user' => [
                'id' => $row['Code'],
                'name' => $row['Nom'],
                'firstname' => $row['Prenom'],
                'profile' => $row['Niveau'],
                'events' => $events,
                'token' => $token
            ]
        ]);
    }
}
