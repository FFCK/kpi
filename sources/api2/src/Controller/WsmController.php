<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/wsm', name: 'api_wsm_')]
class WsmController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/eventNetwork/{eventId}', name: 'event_network', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/wsm/eventNetwork/{eventId}',
        summary: 'Update event network configuration',
        tags: ['WSM - Web Score Management'],
        parameters: [
            new OA\Parameter(
                name: 'eventId',
                in: 'path',
                required: true,
                description: 'Event ID',
                schema: new OA\Schema(type: 'integer', example: 123)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                description: 'Network configuration JSON'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Network configuration updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true)
                    ]
                )
            ),
            new OA\Response(response: 405, description: 'Failed to write file')
        ]
    )]
    public function putEventNetwork(int $eventId, Request $request): JsonResponse
    {
        $network = $request->getContent();
        $fileName = 'event' . $eventId . '_network.json';

        // TODO: Configure proper path
        $filePath = $this->getParameter('kernel.project_dir') . '/var/cache/' . $fileName;

        if (!file_put_contents($filePath, $network)) {
            return new JsonResponse(['error' => 'Failed to write file'], 405);
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/gameParam/{matchId}', name: 'game_param', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/wsm/gameParam/{matchId}',
        summary: 'Update game parameters',
        description: 'Update match status, period, scores, etc.',
        tags: ['WSM - Web Score Management'],
        parameters: [
            new OA\Parameter(
                name: 'matchId',
                in: 'path',
                required: true,
                description: 'Match ID',
                schema: new OA\Schema(type: 'integer', example: 456)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['param', 'value'],
                properties: [
                    new OA\Property(
                        property: 'param',
                        type: 'string',
                        enum: ['Statut', 'Periode', 'ScoreA', 'ScoreB', 'ScoreDetailA', 'ScoreDetailB', 'Heure_fin'],
                        example: 'ScoreA'
                    ),
                    new OA\Property(property: 'value', type: 'string', example: '5')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Parameter updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true)
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Game is locked (validated)'),
            new OA\Response(response: 401, description: 'Invalid parameter')
        ]
    )]
    public function putGameParam(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());

        if (!in_array($data->param ?? '', ['Statut', 'Periode', 'ScoreA', 'ScoreB', 'ScoreDetailA', 'ScoreDetailB', 'Heure_fin'])) {
            return new JsonResponse(['error' => 'Invalid parameter'], 401);
        }

        $conn = $this->entityManager->getConnection();
        $sql = "UPDATE kp_match
            SET {$data->param} = ?
            WHERE Id = ?
            AND Validation != 'O'";

        try {
            $conn->executeStatement($sql, [$data->value, $matchId]);
            // TODO: Create cache here
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/gameEvent/{matchId}', name: 'game_event', methods: ['PUT'])]
    public function putGameEvent(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $conn = $this->entityManager->getConnection();

        // Check if match is locked
        $sql = "SELECT COUNT(Id)
            FROM kp_match
            WHERE Id = ?
            AND Validation != 'O'";

        $count = $conn->fetchOne($sql, [$matchId]);

        if ($count != 1) {
            return new JsonResponse(['error' => 'Game locked'], 400);
        }

        if ($data->params->action === 'add') {
            $uid = $data->params->uid ?? str_replace('-', '', uniqid('', true));

            $sql = "INSERT INTO kp_match_detail (Id, Id_match, Periode, Temps, Id_evt_match,
                Competiteur, Numero, Equipe_A_B, motif)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $conn->executeStatement($sql, [
                $uid, $matchId, $data->params->period, $data->params->tpsJeu, $data->params->code,
                $data->params->player, $data->params->number, $data->params->team, $data->params->reason
            ]);
        } elseif ($data->params->action === 'remove') {
            $sql = "DELETE FROM kp_match_detail
                WHERE Id_match = ?
                AND Periode = ?
                AND Competiteur = ?
                AND Id_evt_match = ?
                ORDER BY date_insert DESC
                LIMIT 1";

            $conn->executeStatement($sql, [
                $matchId, $data->params->period, $data->params->player, $data->params->code
            ]);
        }

        // TODO: Create cache here
        return new JsonResponse(['success' => true]);
    }

    #[Route('/playerStatus/{matchId}', name: 'player_status', methods: ['PUT'])]
    public function putPlayerStatus(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $conn = $this->entityManager->getConnection();

        // Check if match is locked
        $sql = "SELECT COUNT(Id)
            FROM kp_match
            WHERE Id = ?
            AND Validation != 'O'";

        $count = $conn->fetchOne($sql, [$matchId]);

        if ($count != 1) {
            return new JsonResponse(['error' => 'Game locked'], 400);
        }

        if ($data->params->team && $data->params->player && $data->params->status) {
            $sql = "UPDATE kp_match_joueur
                SET Capitaine = ?
                WHERE Id_match = ?
                AND Equipe = ?
                AND Matric = ?";

            $conn->executeStatement($sql, [
                $data->params->status, $matchId, $data->params->team, $data->params->player
            ]);

            // TODO: Create cache here
            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['error' => 'Invalid parameters'], 400);
    }

    #[Route('/gameTimer/{matchId}', name: 'game_timer', methods: ['PUT'])]
    public function putGameTimer(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $conn = $this->entityManager->getConnection();

        if (!in_array($data->params->action ?? '', ['run', 'stop', 'RAZ'])) {
            return new JsonResponse(['error' => 'Invalid action'], 401);
        }

        // Check if match is locked
        $sql = "SELECT COUNT(Id)
            FROM kp_match
            WHERE Id = ?
            AND Validation != 'O'";

        $count = $conn->fetchOne($sql, [$matchId]);

        if ($count != 1) {
            return new JsonResponse(['error' => 'Game locked'], 400);
        }

        if ($data->params->action === 'RAZ') {
            $sql = "DELETE FROM kp_chrono
                WHERE IdMatch = ?";
            $conn->executeStatement($sql, [$matchId]);
        } else {
            $data->params->startTimeServer = time() % 86400;
            $sql = "REPLACE kp_chrono
                SET IdMatch = ?,
                `action` = ?,
                start_time = ?,
                start_time_server = ?,
                run_time = ?,
                max_time = ?";

            $conn->executeStatement($sql, [
                $matchId, $data->params->action, $data->params->startTime, $data->params->startTimeServer,
                $data->params->runTime, $data->params->maxTime
            ]);
        }

        // TODO: Create cache here
        return new JsonResponse(['success' => true]);
    }

    #[Route('/stats', name: 'stats', methods: ['PUT'])]
    public function putStats(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $conn = $this->entityManager->getConnection();

        if (!in_array($data->action ?? '', ['pass', 'possession', 'kickoff', 'kickoff-ko', 'shot-in', 'shot-out', 'shot-stop'])) {
            return new JsonResponse(['error' => 'Invalid action'], 401);
        }

        // Check if match is locked
        $sql = "SELECT COUNT(Id)
            FROM kp_match
            WHERE Id = ?
            AND Validation != 'O'";

        $count = $conn->fetchOne($sql, [$data->game]);

        if ($count != 1) {
            return new JsonResponse(['error' => 'Game locked'], 400);
        }

        $sql = "INSERT INTO kp_stats
            SET user = ?,
            game = ?,
            team = ?,
            player = ?,
            `action` = ?,
            `period` = ?,
            timer = ?";

        $conn->executeStatement($sql, [
            $data->user, $data->game, $data->team, $data->player,
            $data->action, $data->period, rtrim($data->timer, '.')
        ]);

        return new JsonResponse(['success' => true]);
    }
}
