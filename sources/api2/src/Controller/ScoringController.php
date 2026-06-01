<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Scoring — live match console backend (manual KPI scoring).
 *
 * Replaces the former WsmController ("Web Score Management" — an erroneous translation
 * of WebSocket Manager). The hardware relay keeps the WSM/broker naming; this controller
 * serves the human scoring console (app4 /games/[id]/scoring). See DOC/specs/PAGE_SCORING.md.
 *
 * Routes are under /admin/scoring so they sit behind the existing JWT firewall (^/admin).
 *
 * ⚠️ Experimentation phase: access restricted to ROLE_ADMIN (profile <= 2). Open up to
 * ROLE_SCORER (profile 9 "Table de marque") once validated — see spec §6.3.
 */
#[Route('/admin/scoring', name: 'scoring_')]
#[IsGranted('ROLE_ADMIN')]
class ScoringController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/gameParam/{matchId}', name: 'game_param', methods: ['PUT'])]
    #[OA\Put(
        path: '/admin/scoring/gameParam/{matchId}',
        summary: 'Update game parameters (status, period, scores)',
        tags: ['6. Scoring'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['param', 'value'],
                properties: [
                    new OA\Property(
                        property: 'param',
                        type: 'string',
                        enum: ['Statut', 'Periode', 'ScoreA', 'ScoreB', 'ScoreDetailA', 'ScoreDetailB', 'Heure_fin']
                    ),
                    new OA\Property(property: 'value', type: 'string', example: '5')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Parameter updated'),
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
            // TODO (Phase 3): generate broadcast cache here
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/gameEvent/{matchId}', name: 'game_event', methods: ['PUT'])]
    #[OA\Put(
        path: '/admin/scoring/gameEvent/{matchId}',
        summary: 'Add or remove match events (goals, cards)',
        tags: ['6. Scoring'],
        responses: [
            new OA\Response(response: 200, description: 'Event added/removed'),
            new OA\Response(response: 400, description: 'Game is locked')
        ]
    )]
    public function putGameEvent(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $conn = $this->entityManager->getConnection();

        $sql = "SELECT COUNT(Id) FROM kp_match WHERE Id = ? AND Validation != 'O'";
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

        // TODO (Phase 3): generate broadcast cache here
        return new JsonResponse(['success' => true]);
    }

    #[Route('/playerStatus/{matchId}', name: 'player_status', methods: ['PUT'])]
    #[OA\Put(
        path: '/admin/scoring/playerStatus/{matchId}',
        summary: 'Update player status (Captain, Coach)',
        tags: ['6. Scoring'],
        responses: [
            new OA\Response(response: 200, description: 'Player status updated'),
            new OA\Response(response: 400, description: 'Game is locked or invalid parameters')
        ]
    )]
    public function putPlayerStatus(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $conn = $this->entityManager->getConnection();

        $sql = "SELECT COUNT(Id) FROM kp_match WHERE Id = ? AND Validation != 'O'";
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

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['error' => 'Invalid parameters'], 400);
    }

    #[Route('/gameTimer/{matchId}', name: 'game_timer', methods: ['PUT'])]
    #[OA\Put(
        path: '/admin/scoring/gameTimer/{matchId}',
        summary: 'Control match timer (run/stop/RAZ)',
        tags: ['6. Scoring'],
        responses: [
            new OA\Response(response: 200, description: 'Timer updated'),
            new OA\Response(response: 400, description: 'Game is locked'),
            new OA\Response(response: 401, description: 'Invalid action')
        ]
    )]
    public function putGameTimer(int $matchId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $conn = $this->entityManager->getConnection();

        if (!in_array($data->params->action ?? '', ['run', 'stop', 'RAZ'])) {
            return new JsonResponse(['error' => 'Invalid action'], 401);
        }

        $sql = "SELECT COUNT(Id) FROM kp_match WHERE Id = ? AND Validation != 'O'";
        $count = $conn->fetchOne($sql, [$matchId]);

        if ($count != 1) {
            return new JsonResponse(['error' => 'Game locked'], 400);
        }

        if ($data->params->action === 'RAZ') {
            $sql = "DELETE FROM kp_chrono WHERE IdMatch = ?";
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

        // TODO (Phase 3): generate broadcast cache here
        return new JsonResponse(['success' => true]);
    }

    #[Route('/stats', name: 'stats', methods: ['PUT'])]
    #[OA\Put(
        path: '/admin/scoring/stats',
        summary: 'Record match statistics',
        tags: ['6. Scoring'],
        responses: [
            new OA\Response(response: 200, description: 'Statistic recorded'),
            new OA\Response(response: 400, description: 'Game is locked'),
            new OA\Response(response: 401, description: 'Invalid action')
        ]
    )]
    public function putStats(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $conn = $this->entityManager->getConnection();

        if (!in_array($data->action ?? '', ['pass', 'possession', 'kickoff', 'kickoff-ko', 'shot-in', 'shot-out', 'shot-stop'])) {
            return new JsonResponse(['error' => 'Invalid action'], 401);
        }

        $sql = "SELECT COUNT(Id) FROM kp_match WHERE Id = ? AND Validation != 'O'";
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
