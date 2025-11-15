<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class PublicController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/team-stats/{teamId}/{eventId}', name: 'team_stats', methods: ['GET'])]
    #[OA\Get(
        path: '/api/team-stats/{teamId}/{eventId}',
        summary: 'Get team statistics',
        description: 'Returns player statistics for a team in an event (goals, cards, etc.)',
        tags: ['Statistics'],
        parameters: [
            new OA\Parameter(
                name: 'teamId',
                in: 'path',
                required: true,
                description: 'Team ID',
                schema: new OA\Schema(type: 'integer', example: 456)
            ),
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
                description: 'Returns team player statistics',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'licence', type: 'string', example: '123456'),
                            new OA\Property(property: 'name', type: 'string', example: 'Dupont'),
                            new OA\Property(property: 'firstname', type: 'string', example: 'Jean'),
                            new OA\Property(property: 'number', type: 'integer', example: 5),
                            new OA\Property(property: 'goals', type: 'integer', example: 3),
                            new OA\Property(property: 'green_cards', type: 'integer', example: 1),
                            new OA\Property(property: 'yellow_cards', type: 'integer', example: 0),
                            new OA\Property(property: 'red_cards', type: 'integer', example: 0)
                        ]
                    )
                )
            )
        ]
    )]
    public function getTeamStats(int $teamId, int $eventId): JsonResponse
    {
        $conn = $this->entityManager->getConnection();

        $baseSql = "
            SELECT
                l.Matric AS licence, l.Nom AS name, l.Prenom AS firstname,
                l.Sexe AS gender, j.Numero AS number, j.Capitaine AS captain,
                CASE WHEN j.Capitaine = 'E' THEN 0 ELSE SUM(IF(md.Id_evt_match = 'B', 1, 0)) END AS goals,
                SUM(IF(md.Id_evt_match = 'V', 1, 0)) AS green_cards,
                CASE WHEN j.Capitaine = 'E' THEN 0 ELSE SUM(IF(md.Id_evt_match = 'J', 1, 0)) END AS yellow_cards,
                SUM(IF(md.Id_evt_match = 'R', 1, 0)) AS red_cards,
                SUM(IF(md.Id_evt_match = 'D', 1, 0)) AS final_red_cards
            FROM kp_competition_equipe_joueur j
            JOIN kp_licence l ON (j.Matric = l.Matric)
        ";

        if ($eventId < 3000) {
            $sql = $baseSql . "
                LEFT JOIN (
                    kp_match_detail md
                    JOIN kp_match m ON md.Id_match = m.Id
                    JOIN kp_evenement_journee ej ON m.Id_journee = ej.Id_journee AND ej.Id_evenement = ?
                ) ON l.Matric = md.Competiteur
                WHERE j.Id_equipe = ?
                  AND (j.Capitaine IS NULL OR j.Capitaine NOT IN ('A', 'X'))
                GROUP BY l.Matric, l.Nom, l.Prenom, l.Sexe, j.Numero, j.Capitaine
                ORDER BY CASE WHEN j.Capitaine = 'E' THEN 1 ELSE 0 END, j.Numero ASC
            ";
            $params = [$eventId, $teamId];
        } else {
            $sql = $baseSql . "
                LEFT JOIN (
                    kp_match_detail md
                    JOIN kp_match m ON md.Id_match = m.Id AND m.Id_journee = ?
                ) ON l.Matric = md.Competiteur
                WHERE j.Id_equipe = ?
                  AND (j.Capitaine IS NULL OR j.Capitaine NOT IN ('A', 'X'))
                GROUP BY l.Matric, l.Nom, l.Prenom, l.Sexe, j.Numero, j.Capitaine
                ORDER BY CASE WHEN j.Capitaine = 'E' THEN 1 ELSE 0 END, j.Numero ASC
            ";
            $params = [$eventId, $teamId];
        }

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery($params);
        $stats = $result->fetchAllAssociative();

        return new JsonResponse($stats);
    }

    #[Route('/stars', name: 'stars', methods: ['GET'])]
    #[OA\Get(
        path: '/api/stars',
        summary: 'Get app ratings statistics',
        tags: ['App Ratings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns average rating and count',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'average', type: 'number', format: 'float', example: 4.2),
                        new OA\Property(property: 'count', type: 'integer', example: 150)
                    ]
                )
            )
        ]
    )]
    public function getStars(): JsonResponse
    {
        $conn = $this->entityManager->getConnection();

        $sql = "SELECT AVG(stars) average, COUNT(id) count
            FROM kp_app_rating";

        $result = $conn->executeQuery($sql);
        $stars = $result->fetchAssociative();

        return new JsonResponse($stars);
    }

    #[Route('/rating', name: 'rating', methods: ['POST'])]
    #[OA\Post(
        path: '/api/rating',
        summary: 'Submit app rating',
        tags: ['App Ratings'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['uid', 'stars'],
                properties: [
                    new OA\Property(property: 'uid', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                    new OA\Property(property: 'stars', type: 'integer', minimum: 1, maximum: 5, example: 4)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Rating submitted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true)
                    ]
                )
            ),
            new OA\Response(response: 405, description: 'Invalid data')
        ]
    )]
    public function postRating(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());

        if (!$data || strlen($data->uid ?? '') !== 36 || ($data->stars ?? -1) < 0 || ($data->stars ?? 6) > 5) {
            return new JsonResponse(['error' => 'Invalid data'], 405);
        }

        $conn = $this->entityManager->getConnection();
        $sql = "INSERT INTO kp_app_rating (`uid`, `stars`)
            VALUES (?, ?)";

        $conn->executeStatement($sql, [$data->uid, $data->stars]);

        return new JsonResponse(['success' => true]);
    }
}
