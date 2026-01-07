<?php

namespace App\Controller;

use App\Service\TokenAuthService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/staff', name: 'staff_')]
class StaffController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenAuthService $tokenAuthService
    ) {
    }

    #[Route('/test', name: 'test', methods: ['GET'])]
    #[OA\Get(
        path: '/staff/test',
        summary: 'Test endpoint for staff authentication',
        tags: ['3. App2 - Staff'],
        security: [['ApiToken' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Test successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'result', type: 'string', example: 'OK'),
                        new OA\Property(property: 'user', type: 'string', example: '123456')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token')
        ]
    )]
    public function test(Request $request): JsonResponse
    {
        $auth = $this->tokenAuthService->validateToken($request);
        if (!$auth) {
            return $this->tokenAuthService->createUnauthorizedResponse();
        }

        return new JsonResponse([
            'result' => 'OK',
            'user' => $auth['user']
        ]);
    }

    #[Route('/{eventId}/teams', name: 'teams', methods: ['GET'])]
    #[OA\Get(
        path: '/staff/{eventId}/teams',
        summary: 'Get teams for scrutineering',
        tags: ['3. App2 - Staff'],
        security: [['ApiToken' => []]],
        parameters: [
            new OA\Parameter(
                name: 'eventId',
                in: 'path',
                required: true,
                description: 'Event ID',
                schema: new OA\Schema(type: 'integer', example: 123)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns list of teams',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'team_id', type: 'integer', example: 456),
                            new OA\Property(property: 'label', type: 'string', example: 'Team A'),
                            new OA\Property(property: 'club', type: 'string', example: 'CLUB01'),
                            new OA\Property(property: 'logo', type: 'string', nullable: true)
                        ]
                    )
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
            new OA\Response(response: 403, description: 'Forbidden - No access to this event')
        ]
    )]
    public function getTeams(Request $request, int $eventId): JsonResponse
    {
        $auth = $this->tokenAuthService->validateToken($request, null, $eventId);
        if (!$auth) {
            return $this->tokenAuthService->createUnauthorizedResponse();
        }

        $conn = $this->entityManager->getConnection();

        $sql = "SELECT ce.Id team_id, ce.Libelle label, ce.Code_club club, ce.logo
            FROM kp_competition_equipe ce
            INNER JOIN kp_journee j ON (ce.Code_compet = j.Code_competition AND ce.Code_saison = j.Code_saison)
            INNER JOIN kp_evenement_journee ej ON (j.Id = ej.Id_journee)
            WHERE ej.Id_evenement = ?
            GROUP BY team_id
            ORDER BY club, label";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $result = $stmt->executeQuery();
        $teams = $result->fetchAllAssociative();

        return new JsonResponse($teams);
    }

    #[Route('/{eventId}/team/{teamId}/players', name: 'team_players', methods: ['GET'])]
    #[OA\Get(
        path: '/staff/{eventId}/team/{teamId}/players',
        summary: 'Get players for a team',
        description: 'Get list of players with scrutineering data.',
        tags: ['3. App2 - Staff'],
        security: [['ApiToken' => []]],
        parameters: [
            new OA\Parameter(
                name: 'eventId',
                in: 'path',
                required: true,
                description: 'Event ID',
                schema: new OA\Schema(type: 'integer', example: 222)
            ),
            new OA\Parameter(
                name: 'teamId',
                in: 'path',
                required: true,
                description: 'Team ID',
                schema: new OA\Schema(type: 'integer', example: 456)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns list of players with scrutineering data (fresh data)',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'licence', type: 'string'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'number', type: 'integer'),
                            new OA\Property(property: 'kayak_status', type: 'string', nullable: true),
                            new OA\Property(property: 'vest_status', type: 'string', nullable: true),
                            new OA\Property(property: 'helmet_status', type: 'string', nullable: true),
                            new OA\Property(property: 'paddle_count', type: 'integer', nullable: true)
                        ]
                    )
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token')
        ]
    )]
    public function getPlayers(Request $request, int $eventId, int $teamId): JsonResponse
    {
        $auth = $this->tokenAuthService->validateToken($request);
        if (!$auth) {
            return $this->tokenAuthService->createUnauthorizedResponse();
        }

        $conn = $this->entityManager->getConnection();

        $sql = "SELECT cej.Matric player_id, cej.Nom last_name, cej.Prenom first_name,
            cej.Sexe gender, cej.Numero num, cej.Capitaine cap,
            sc.kayak_status, sc.kayak_print, sc.vest_status, sc.vest_print, sc.helmet_status,
            sc.helmet_print, sc.paddle_count, sc.paddle_print, sc.comment
            FROM kp_competition_equipe_joueur cej
            LEFT OUTER JOIN kp_scrutineering sc ON (cej.Id_equipe = sc.id_equipe AND cej.Matric = sc.matric)
            WHERE cej.Id_equipe = ?
            AND cej.Capitaine != 'A'
            AND cej.Capitaine != 'X'
            ORDER BY FIELD(IF(cej.Capitaine='C', '-', IF(cej.Capitaine='', '-', cej.Capitaine)), '-', 'E', 'A', 'X'), num, last_name, first_name";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $teamId);
        $result = $stmt->executeQuery();
        $players = $result->fetchAllAssociative();

        $response = new JsonResponse($players);

        // If force parameter is present, add no-cache headers
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    #[Route('/{eventId}/team/{teamId}/player/{playerId}/{parameter}/{value}', name: 'update_player', methods: ['PUT'], defaults: ['value' => null])]
    #[OA\Put(
        path: '/staff/{eventId}/team/{teamId}/player/{playerId}/{parameter}/{value}',
        summary: 'Update player scrutineering data',
        tags: ['3. App2 - Staff'],
        security: [['ApiToken' => []]],
        parameters: [
            new OA\Parameter(name: 'eventId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 222)),
            new OA\Parameter(name: 'playerId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'teamId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'parameter', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['kayak_status', 'vest_status', 'helmet_status', 'paddle_count'])),
            new OA\Parameter(name: 'value', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Player data updated'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 405, description: 'Invalid parameter')
        ]
    )]
    #[Route('/{eventId}/team/{teamId}/player/{playerId}/comment', name: 'update_player_comment', methods: ['PUT'])]
    #[OA\Put(
        path: '/staff/{eventId}/team/{teamId}/player/{playerId}/comment',
        summary: 'Update player comment',
        tags: ['3. App2 - Staff'],
        security: [['ApiToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'comment', type: 'string', example: 'Equipment OK', maxLength: 255)
                ]
            )
        ),
        parameters: [
            new OA\Parameter(name: 'eventId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 222)),
            new OA\Parameter(name: 'playerId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'teamId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Comment updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'comment', type: 'string', example: 'Equipment OK')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function updatePlayer(Request $request, int $eventId, int $playerId, int $teamId, string $parameter, ?int $value = null): JsonResponse
    {
        $auth = $this->tokenAuthService->validateToken($request);
        if (!$auth) {
            return $this->tokenAuthService->createUnauthorizedResponse();
        }

        $conn = $this->entityManager->getConnection();

        if ($parameter === 'comment') {
            $input = json_decode($request->getContent(), true);
            $comment = isset($input['comment']) ? htmlspecialchars(substr($input['comment'], 0, 255), ENT_QUOTES, 'UTF-8') : '';

            $sql = "INSERT INTO kp_scrutineering (id_equipe, matric, comment)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE comment = ?";

            $conn->executeStatement($sql, [$teamId, $playerId, $comment, $comment]);

            return new JsonResponse(['comment' => $comment]);
        }

        if (!in_array($parameter, ['kayak_status', 'vest_status', 'helmet_status', 'paddle_count'])) {
            return new JsonResponse(['error' => 'Invalid parameter'], 405);
        }

        if ($value === null) {
            return new JsonResponse(['error' => 'Value is required'], 405);
        }

        $sql = "INSERT INTO kp_scrutineering (id_equipe, matric, $parameter)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE $parameter = ?";

        try {
            $conn->executeStatement($sql, [$teamId, $playerId, $value, $value]);
            return new JsonResponse(['value' => $value]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 401);
        }
    }
}
