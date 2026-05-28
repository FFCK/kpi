<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ChartsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/event/{eventId}/charts', name: 'charts', methods: ['GET'])]
    #[OA\Get(
        path: '/event/{eventId}/charts',
        summary: 'Get rankings and brackets for an event',
        description: 'Returns complex tournament structure with pools, brackets, rankings, and game details',
        tags: ['2. App2 - Public'],
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
        $stmtGames->bindValue(1, $eventId);
        $resultGames = $stmtGames->executeQuery();

        while ($row = $resultGames->fetchAssociative()) {
            if ($row['d_type'] === 'E' || $row['c_type'] === 'CHPT') {
                // Resolve placeholder team labels for elimination phases
                if (!$row['t_a_label'] && $row['g_code']) {
                    $parsed = $this->parseMatchLabel($row['g_code']);
                    if (isset($parsed[0])) $row['t_a_label'] = $parsed[0];
                    if (isset($parsed[1])) $row['t_b_label'] = $parsed[1];
                }
                $games[$row['d_id']][] = $row;
            } elseif ($row['d_type'] === 'C' && $row['c_type'] === 'CP') {
                // Store full row so we can extract team names from matches
                $games[$row['d_id']][] = $row;
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
        $stmtTeams->bindValue(1, $eventId);
        $resultTeams = $stmtTeams->executeQuery();

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
            if ($row['t_id'] !== null) {
                $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['teams'][] = $row;
            }
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['type'] = $row['d_type'];
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['libelle'] = $row['d_phase'];
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['level'] = $row['d_level'];
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['t_count'] = $row['t_count'];
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['d_id'] = $row['d_id'];
            $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$phaseOrder]['games'] = $games[$row['d_id']] ?? null;
        }

        // For CP pool phases (type C) without ranking data, derive teams from matches
        foreach ($charts as &$chart) {
            if ($chart['type'] !== 'CP') continue;
            foreach ($chart['rounds'] as &$round) {
                foreach ($round['phases'] as &$phase) {
                    if ($phase['type'] !== 'C') continue;
                    if (!empty($phase['teams'])) continue;
                    $dId = $phase['d_id'] ?? null;
                    if (!$dId || empty($games[$dId])) continue;
                    $seen = [];
                    foreach ($games[$dId] as $g) {
                        if (!is_array($g)) continue;
                        $idA = (int)($g['t_a_id'] ?? 0);
                        $idB = (int)($g['t_b_id'] ?? 0);
                        // Resolve placeholder labels from g_code when teams not yet assigned
                        $labelA = $g['t_a_label'] ?? null;
                        $labelB = $g['t_b_label'] ?? null;
                        if ((!$labelA || !$labelB) && !empty($g['g_code'])) {
                            $parsed = $this->parseMatchLabel($g['g_code']);
                            if (!$labelA && isset($parsed[0])) $labelA = $parsed[0];
                            if (!$labelB && isset($parsed[1])) $labelB = $parsed[1];
                        }
                        // Deduplicate by id (real teams) or by label (placeholders)
                        $keyA = $idA > 1 ? "id:$idA" : "lbl:$labelA";
                        $keyB = $idB > 1 ? "id:$idB" : "lbl:$labelB";
                        if ($labelA && !isset($seen[$keyA])) {
                            $seen[$keyA] = true;
                            $phase['teams'][] = [
                                't_id' => $idA > 1 ? $idA : null,
                                't_number' => $g['t_a_number'] ?? null,
                                't_label' => $labelA, 't_club' => $g['t_a_club'] ?? null,
                                't_clt' => null, 't_pts' => null, 't_pld' => null,
                                't_diff' => null, 't_logo' => $g['t_a_logo'] ?? null,
                            ];
                        }
                        if ($labelB && !isset($seen[$keyB])) {
                            $seen[$keyB] = true;
                            $phase['teams'][] = [
                                't_id' => $idB > 1 ? $idB : null,
                                't_number' => $g['t_b_number'] ?? null,
                                't_label' => $labelB, 't_club' => $g['t_b_club'] ?? null,
                                't_clt' => null, 't_pts' => null, 't_pld' => null,
                                't_diff' => null, 't_logo' => $g['t_b_logo'] ?? null,
                            ];
                        }
                    }
                    if (!empty($phase['teams'])) {
                        $hasNumbers = array_filter($phase['teams'], fn($t) => $t['t_number'] !== null);
                        if ($hasNumbers) {
                            usort($phase['teams'], fn($a, $b) => ((int)($a['t_number'] ?? 0)) <=> ((int)($b['t_number'] ?? 0)));
                        } else {
                            usort($phase['teams'], fn($a, $b) => strnatcasecmp($a['t_label'] ?? '', $b['t_label'] ?? ''));
                        }
                    }
                }
            }
        }
        unset($chart, $round, $phase);

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
            $stmt2->bindValue(1, $compet['code']);
            $stmt2->bindValue(2, $compet['season']);
            $result2 = $stmt2->executeQuery();
            $charts[$compet['code']]['ranking'] = $result2->fetchAllAssociative();
        }

        $response = new JsonResponse(array_values($charts));
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_UNESCAPED_UNICODE);
        return $response;
    }

    private function parseMatchLabel(string $libelle): array
    {
        $result = [];
        $parts = preg_split('/\[/', $libelle);
        if (!isset($parts[1]) || $parts[1] === '') return $result;
        $inner = preg_split('/\]/', $parts[1]);
        if ($inner[0] === '') return $result;
        $codes = preg_split('/[-\/*,;]/', $inner[0]);
        for ($j = 0; $j < 4; $j++) {
            if (!isset($codes[$j])) continue;
            $code = trim($codes[$j]);
            preg_match('/([A-Z_]+)/', $code, $codeLettres);
            preg_match('/([0-9]+)/', $code, $codeNumero);
            if (!isset($codeLettres[1], $codeNumero[1])) continue;
            $posL = strpos($code, $codeLettres[1]);
            $posN = strpos($code, $codeNumero[1]);
            if ($posN > $posL) {
                $result[$j] = match ($codeLettres[1]) {
                    'T', 'D' => '(Team ' . $codeNumero[1] . ')',
                    'V', 'G', 'W' => '(Winner game #' . $codeNumero[1] . ')',
                    'P', 'L' => '(Loser game #' . $codeNumero[1] . ')',
                    default => $code,
                };
            } else {
                $n = (int)$codeNumero[1];
                $ord = match ($n) { 1 => '1st', 2 => '2nd', 3 => '3rd', default => $n . 'th' };
                $result[$j] = "($ord Group {$codeLettres[1]})";
            }
        }
        return $result;
    }
}
