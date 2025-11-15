<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
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
