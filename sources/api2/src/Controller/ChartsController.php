<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class ChartsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/charts/{eventId}', name: 'charts', methods: ['GET'])]
    #[OA\Get(
        path: '/api/charts/{eventId}',
        summary: 'Get rankings and brackets for an event',
        description: 'Returns complex tournament structure with pools, brackets, rankings, and game details',
        tags: ['Charts & Rankings'],
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
                description: 'Returns tournament charts and rankings',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'code', type: 'string', example: 'N1'),
                            new OA\Property(property: 'libelle', type: 'string', example: 'Nationale 1'),
                            new OA\Property(property: 'type', type: 'string', example: 'CHPT'),
                            new OA\Property(property: 'rounds', type: 'object', description: 'Tournament rounds structure'),
                            new OA\Property(
                                property: 'ranking',
                                type: 'array',
                                items: new OA\Items(type: 'object'),
                                description: 'Team rankings'
                            )
                        ]
                    )
                )
            )
        ]
    )]
    public function getCharts(int $eventId): JsonResponse
    {
        $conn = $this->entityManager->getConnection();
        $charts = [];
        $games = [];

        // First query: Get games grouped by day
        if ($eventId < 3000) {
            $sqlGames = "SELECT j.Phase d_phase, j.Niveau d_level, j.Type d_type,
                j.Lieu d_place, j.Libelle d_label, c.Soustitre2 c_label, c.Code_typeclt c_type,
                m.Id g_id, m.Id_journee d_id, m.Numero_ordre g_number, m.Date_match g_date,
                m.Heure_match g_time, m.Terrain g_pitch, m.Libelle g_code,
                m.Validation g_validation, m.Statut g_status, m.Periode g_period,
                m.ScoreA g_score_a, m.ScoreB g_score_b, m.CoeffA g_coef_a, m.CoeffB g_coef_b,
                m.ScoreDetailA g_score_detail_a, m.ScoreDetailB g_score_detail_b,
                m.Id_equipeA t_a_id, m.Id_equipeB t_b_id,
                cea.Libelle t_a_label, ceb.Libelle t_b_label, cea.Numero t_a_number, ceb.Numero t_b_number,
                cea.Code_club t_a_club, ceb.Code_club t_b_club,
                CASE WHEN cea.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE cea.logo END t_a_logo,
                CASE WHEN ceb.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ceb.logo END t_b_logo
                FROM kp_match m
                LEFT OUTER JOIN kp_competition_equipe cea ON (m.Id_equipeA = cea.Id)
                LEFT OUTER JOIN kp_competition_equipe ceb ON (m.Id_equipeB = ceb.Id)
                INNER JOIN kp_journee j ON (m.Id_journee = j.Id)
                INNER JOIN kp_evenement_journee ej ON (j.Id = ej.Id_journee)
                INNER JOIN kp_competition c ON (j.Code_competition = c.Code AND j.Code_saison = c.Code_saison)
                WHERE ej.Id_evenement = ?
                AND c.Publication = 'O'
                AND c.Statut != 'ATT'
                AND j.Publication = 'O'
                AND m.Publication = 'O'
                AND j.Phase != 'Break'
                AND j.Phase != 'Pause'
                ORDER BY m.Id_journee, m.Date_match, m.Heure_match, m.Terrain";
        } else {
            $sqlGames = "SELECT j.Phase d_phase, j.Niveau d_level, j.Type d_type,
                j.Lieu d_place, j.Libelle d_label, c.Soustitre2 c_label, c.Code_typeclt c_type,
                m.Id g_id, m.Id_journee d_id, m.Numero_ordre g_number, m.Date_match g_date,
                m.Heure_match g_time, m.Terrain g_pitch, m.Libelle g_code,
                m.Validation g_validation, m.Statut g_status, m.Periode g_period,
                m.ScoreA g_score_a, m.ScoreB g_score_b, m.CoeffA g_coef_a, m.CoeffB g_coef_b,
                m.ScoreDetailA g_score_detail_a, m.ScoreDetailB g_score_detail_b,
                m.Id_equipeA t_a_id, m.Id_equipeB t_b_id,
                cea.Libelle t_a_label, ceb.Libelle t_b_label, cea.Numero t_a_number, ceb.Numero t_b_number,
                cea.Code_club t_a_club, ceb.Code_club t_b_club,
                CASE WHEN cea.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE cea.logo END t_a_logo,
                CASE WHEN ceb.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ceb.logo END t_b_logo
                FROM kp_match m
                LEFT OUTER JOIN kp_competition_equipe cea ON (m.Id_equipeA = cea.Id)
                LEFT OUTER JOIN kp_competition_equipe ceb ON (m.Id_equipeB = ceb.Id)
                INNER JOIN kp_journee j ON (m.Id_journee = j.Id)
                INNER JOIN kp_competition c ON (j.Code_competition = c.Code AND j.Code_saison = c.Code_saison)
                WHERE j.Id = ?
                AND c.Publication = 'O'
                AND c.Statut != 'ATT'
                AND j.Publication = 'O'
                AND m.Publication = 'O'
                ORDER BY m.Id_journee, m.Date_match, m.Heure_match, m.Terrain";
        }

        $stmtGames = $conn->prepare($sqlGames);
        $resultGames = $stmtGames->executeQuery([$eventId]);

        while ($row = $resultGames->fetchAssociative()) {
            if ($row['d_type'] === 'E' || $row['c_type'] === 'CHPT') {
                $games[$row['d_id']][] = $row;
            } elseif ($row['d_type'] === 'C' && $row['c_type'] === 'CP') {
                $games[$row['d_id']][] = $row['g_code'];
            }
        }

        // Second query: Get teams and rankings
        if ($eventId < 3000) {
            $sqlTeams = "SELECT j.Code_saison c_season, j.Code_competition c_code, c.Code_typeclt c_type, c.GroupOrder c_order,
                c.Soustitre2 c_category, c.Statut c_status,
                j.Id d_id, j.Phase d_phase, j.Etape d_round, j.Nbequipes t_count,
                j.Niveau d_level, j.Type d_type, j.Date_debut d_start, j.Date_fin d_end,
                j.Lieu d_place, j.Departement d_dpt,
                ce.Id t_id, ce.Numero t_number, ce.Libelle t_label, ce.Code_club t_club,
                cej.Clt_publi t_clt, cej.Pts_publi t_pts, cej.J_publi t_pld, cej.G_publi t_won,
                cej.N_publi t_draw, cej.P_publi t_lost, cej.F_publi t_f, cej.Plus_publi t_plus, cej.Moins_publi t_minus,
                cej.Diff_publi t_diff, cej.PtsNiveau_publi t_ptslv, cej.CltNiveau_publi t_cltlv,
                CASE WHEN ce.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ce.logo END t_logo
                FROM kp_journee j
                LEFT JOIN kp_competition c ON (j.Code_saison = c.Code_saison AND j.Code_competition = c.Code)
                LEFT JOIN kp_evenement_journee ej ON ej.Id_journee = j.Id
                LEFT OUTER JOIN kp_competition_equipe_journee cej ON cej.Id_journee = j.Id
                LEFT OUTER JOIN kp_competition_equipe ce ON ce.Id = cej.Id
                WHERE ej.Id_evenement = ?
                AND j.Phase != 'Break'
                AND j.Phase != 'Pause'
                ORDER BY c_season, c_order, c_code, d_round, d_level DESC, d_phase, d_start DESC,
                t_clt ASC, t_diff DESC, t_plus ASC";
        } else {
            $sqlTeams = "SELECT j.Code_saison c_season, j.Code_competition c_code, c.Code_typeclt c_type, c.GroupOrder c_order,
                c.Soustitre2 c_category, c.Statut c_status,
                j.Id d_id, j.Phase d_phase, j.Etape d_round, j.Nbequipes t_count,
                j.Niveau d_level, j.Type d_type, j.Date_debut d_start, j.Date_fin d_end,
                j.Lieu d_place, j.Departement d_dpt,
                ce.Id t_id, ce.Numero t_number, ce.Libelle t_label, ce.Code_club t_club,
                cej.Clt_publi t_clt, cej.Pts_publi t_pts, cej.J_publi t_pld, cej.G_publi t_won,
                cej.N_publi t_draw, cej.P_publi t_lost, cej.F_publi t_f, cej.Plus_publi t_plus, cej.Moins_publi t_minus,
                cej.Diff_publi t_diff, cej.PtsNiveau_publi t_ptslv, cej.CltNiveau_publi t_cltlv,
                CASE WHEN ce.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ce.logo END t_logo
                FROM kp_journee j
                LEFT JOIN kp_competition c ON (j.Code_saison = c.Code_saison AND j.Code_competition = c.Code)
                LEFT OUTER JOIN kp_competition_equipe_journee cej ON cej.Id_journee = j.Id
                LEFT OUTER JOIN kp_competition_equipe ce ON ce.Id = cej.Id
                WHERE j.Id = ?
                ORDER BY c_season, c_order, c_code, d_round, d_level DESC, d_phase, d_start DESC,
                t_clt ASC, t_diff DESC, t_plus ASC";
        }

        $stmtTeams = $conn->prepare($sqlTeams);
        $resultTeams = $stmtTeams->executeQuery([$eventId]);

        $arrayChpt = [];
        while ($row = $resultTeams->fetchAssociative()) {
            $phaseOrder = (100 - $row['d_level']) . '-' . $row['d_phase'];
            $charts[$row['c_code']]['type'] = $row['c_type'];
            $charts[$row['c_code']]['code'] = $row['c_code'];
            $charts[$row['c_code']]['libelle'] = $row['c_category'];
            $charts[$row['c_code']]['status'] = $row['c_status'];
            $charts[$row['c_code']]['season'] = $row['c_season'];

            if (($row['c_type'] === 'CHPT' && $row['c_status'] !== 'ATT') || ($row['c_type'] === 'CP')) {
                $arrayChpt[$row['c_code']] = [
                    'code' => $row['c_code'],
                    'season' => $row['c_season'],
                    'type' => $row['c_type']
                ];
            }

            $charts[$row['c_code']]['rounds'][$row['d_round']]['type'] = $row['d_type'];
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['teams'][] = $row;
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['type'] = $row['d_type'];
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['libelle'] = $row['d_phase'];
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['level'] = $row['d_level'];
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['t_count'] = $row['t_count'];
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['games'] = $games[$row['d_id']] ?? null;
        }

        // Get overall rankings
        foreach ($arrayChpt as $compet) {
            if ($compet['type'] === 'CHPT') {
                $sql2 = "SELECT ce.Id t_id, ce.Numero t_number, ce.Libelle t_label, ce.Code_club t_club,
                    ce.Clt_publi t_clt, ROUND(ce.Pts_publi / 100, 0) t_pts, ce.J_publi t_pld, ce.G_publi t_won,
                    ce.N_publi t_draw, ce.P_publi t_lost, ce.F_publi t_f, ce.Plus_publi t_plus, ce.Moins_publi t_minus,
                    ce.Diff_publi t_diff, ce.CltNiveau_publi t_clt_cp,
                    CASE WHEN ce.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ce.logo END t_logo
                    FROM kp_competition_equipe ce
                    WHERE ce.Code_compet = ?
                    AND ce.Code_saison = ?
                    AND Clt_publi > 0
                    ORDER BY Clt_publi ASC, Diff_publi DESC";
            } else {
                $sql2 = "SELECT ce.Id t_id, ce.Numero t_number, ce.Libelle t_label, ce.Code_club t_club,
                    ce.CltNiveau_publi t_clt_cp, ce.Poule t_group, ce.Tirage t_order,
                    CASE WHEN ce.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ce.logo END t_logo
                    FROM kp_competition_equipe ce
                    WHERE ce.Code_compet = ?
                    AND ce.Code_saison = ?
                    ORDER BY CltNiveau_publi ASC, t_group, t_order";
            }

            $stmt2 = $conn->prepare($sql2);
            $result2 = $stmt2->executeQuery([$compet['code'], $compet['season']]);
            $charts[$compet['code']]['ranking'] = $result2->fetchAllAssociative();
        }

        return new JsonResponse(array_values($charts));
    }
}
