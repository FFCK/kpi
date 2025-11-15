<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class GamesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/games/{eventId}', name: 'games', methods: ['GET'])]
    public function getGames(int $eventId): JsonResponse
    {
        $conn = $this->entityManager->getConnection();

        if ($eventId < 3000) {
            $sql = "SELECT j.Code_competition c_code, c.Code_saison c_season, j.Phase d_phase, j.Niveau d_level,
                j.Lieu d_place, j.Libelle d_label, c.Soustitre2 c_label,
                m.Id g_id, m.Id_journee d_id, m.Numero_ordre g_number, m.Date_match g_date,
                m.Heure_match g_time, m.Terrain g_pitch, m.Libelle g_code,
                m.Validation g_validation, m.Statut g_status, m.Periode g_period,
                m.ScoreA g_score_a, m.ScoreB g_score_b, m.CoeffA g_coef_a, m.CoeffB g_coef_b,
                m.ScoreDetailA g_score_detail_a, m.ScoreDetailB g_score_detail_b,
                m.Id_equipeA t_a_id, m.Id_equipeB t_b_id,
                cea.Libelle t_a_label, ceb.Libelle t_b_label, cea.Numero t_a_number, ceb.Numero t_b_number,
                cea.Code_club t_a_club, ceb.Code_club t_b_club,
                CASE WHEN cea.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE cea.logo END t_a_logo,
                CASE WHEN ceb.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ceb.logo END t_b_logo,
                m.Arbitre_principal r_1, m.Arbitre_secondaire r_2,
                m.Matric_arbitre_principal r_1_id, m.Matric_arbitre_secondaire r_2_id,
                CONCAT(lcp.Nom, ' ', lcp.Prenom) r_1_name,
                CONCAT(lcs.Nom, ' ', lcs.Prenom) r_2_name
                FROM kp_match m
                LEFT OUTER JOIN kp_competition_equipe cea ON (m.Id_equipeA = cea.Id)
                LEFT OUTER JOIN kp_competition_equipe ceb ON (m.Id_equipeB = ceb.Id)
                LEFT OUTER JOIN kp_licence lcp ON (m.Matric_arbitre_principal = lcp.Matric)
                LEFT OUTER JOIN kp_licence lcs ON (m.Matric_arbitre_secondaire = lcs.Matric)
                INNER JOIN kp_journee j ON (m.Id_journee = j.Id)
                INNER JOIN kp_evenement_journee ej ON (j.Id = ej.Id_journee)
                INNER JOIN kp_competition c ON (j.Code_competition = c.Code AND j.Code_saison = c.Code_saison)
                WHERE ej.Id_evenement = ?
                AND c.Publication = 'O'
                AND j.Publication = 'O'
                AND m.Publication = 'O'
                AND j.Phase != 'Break'
                AND j.Phase != 'Pause'
                ORDER BY m.Date_match, m.Heure_match, m.Terrain";
        } else {
            $sql = "SELECT j.Code_competition c_code, c.Code_saison c_season, j.Phase d_phase, j.Niveau d_level,
                j.Lieu d_place, j.Libelle d_label, c.Soustitre2 c_label,
                m.Id g_id, m.Id_journee d_id, m.Numero_ordre g_number, m.Date_match g_date,
                m.Heure_match g_time, m.Terrain g_pitch, m.Libelle g_code,
                m.Validation g_validation, m.Statut g_status, m.Periode g_period,
                m.ScoreA g_score_a, m.ScoreB g_score_b, m.CoeffA g_coef_a, m.CoeffB g_coef_b,
                m.ScoreDetailA g_score_detail_a, m.ScoreDetailB g_score_detail_b,
                m.Id_equipeA t_a_id, m.Id_equipeB t_b_id,
                cea.Libelle t_a_label, ceb.Libelle t_b_label, cea.Numero t_a_number, ceb.Numero t_b_number,
                cea.Code_club t_a_club, ceb.Code_club t_b_club,
                CASE WHEN cea.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE cea.logo END t_a_logo,
                CASE WHEN ceb.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ceb.logo END t_b_logo,
                m.Arbitre_principal r_1, m.Arbitre_secondaire r_2,
                m.Matric_arbitre_principal r_1_id, m.Matric_arbitre_secondaire r_2_id,
                CONCAT(lcp.Nom, ' ', lcp.Prenom) r_1_name,
                CONCAT(lcs.Nom, ' ', lcs.Prenom) r_2_name
                FROM kp_match m
                LEFT OUTER JOIN kp_competition_equipe cea ON (m.Id_equipeA = cea.Id)
                LEFT OUTER JOIN kp_competition_equipe ceb ON (m.Id_equipeB = ceb.Id)
                LEFT OUTER JOIN kp_licence lcp ON (m.Matric_arbitre_principal = lcp.Matric)
                LEFT OUTER JOIN kp_licence lcs ON (m.Matric_arbitre_secondaire = lcs.Matric)
                INNER JOIN kp_journee j ON (m.Id_journee = j.Id)
                INNER JOIN kp_competition c ON (j.Code_competition = c.Code AND j.Code_saison = c.Code_saison)
                WHERE j.Id = ?
                AND c.Publication = 'O'
                AND j.Publication = 'O'
                AND m.Publication = 'O'
                ORDER BY m.Date_match, m.Heure_match, m.Terrain";
        }

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([$eventId]);
        $games = $result->fetchAllAssociative();

        return new JsonResponse($games);
    }
}
