<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/staff', name: 'api_staff_')]
class StaffController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/{token}/test', name: 'test', methods: ['GET'])]
    public function test(string $token): JsonResponse
    {
        // TODO: Implement token authentication
        return new JsonResponse(['result' => 'OK']);
    }

    #[Route('/{token}/teams/{eventId}', name: 'teams', methods: ['GET'])]
    public function getTeams(string $token, int $eventId): JsonResponse
    {
        // TODO: Implement token authentication
        $conn = $this->entityManager->getConnection();

        $sql = "SELECT ce.Id team_id, ce.Libelle label, ce.Code_club club, ce.logo
            FROM kp_competition_equipe ce
            INNER JOIN kp_journee j ON (ce.Code_compet = j.Code_competition AND ce.Code_saison = j.Code_saison)
            INNER JOIN kp_evenement_journee ej ON (j.Id = ej.Id_journee)
            WHERE ej.Id_evenement = ?
            GROUP BY team_id
            ORDER BY club, label";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([$eventId]);
        $teams = $result->fetchAllAssociative();

        return new JsonResponse($teams);
    }

    #[Route('/{token}/players/{teamId}', name: 'players', methods: ['GET'])]
    public function getPlayers(string $token, int $teamId): JsonResponse
    {
        // TODO: Implement token authentication
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
        $result = $stmt->executeQuery([$teamId]);
        $players = $result->fetchAllAssociative();

        return new JsonResponse($players);
    }

    #[Route('/{token}/player/{playerId}/team/{teamId}/{parameter}/{value}', name: 'update_player', methods: ['PUT'], defaults: ['value' => null])]
    #[Route('/{token}/player/{playerId}/team/{teamId}/comment', name: 'update_player_comment', methods: ['PUT'])]
    public function updatePlayer(string $token, int $playerId, int $teamId, string $parameter, ?int $value = null, Request $request): JsonResponse
    {
        // TODO: Implement token authentication
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
