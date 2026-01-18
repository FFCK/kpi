<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class GroupController extends AbstractController
{
    private const SECTION_LABELS = [
        1 => 'Competitions_Internationales',
        2 => 'Competitions_Nationales',
        3 => 'Competitions_Regionales',
        4 => 'Tournois_Internationaux',
        5 => 'Continents',
        100 => 'Divers'
    ];

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/groups/{season}', name: 'groups', methods: ['GET'])]
    #[OA\Get(
        path: '/groups/{season}',
        summary: 'Get competition groups for a season',
        description: 'Returns groups with public competitions organized by section (for optgroup display)',
        tags: ['2. App2 - Public'],
        parameters: [
            new OA\Parameter(
                name: 'season',
                in: 'path',
                required: true,
                description: 'Season code (year, e.g. 2026)',
                schema: new OA\Schema(type: 'string', example: '2026')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns groups organized by section',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'season', type: 'string', example: '2026'),
                        new OA\Property(
                            property: 'sections',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'section', type: 'integer', example: 2),
                                    new OA\Property(property: 'label', type: 'string', example: 'Competitions_Nationales'),
                                    new OA\Property(
                                        property: 'groups',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'code', type: 'string', example: 'N1H'),
                                                new OA\Property(property: 'libelle', type: 'string', example: 'Nationale 1 Hommes')
                                            ]
                                        )
                                    )
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function getGroups(string $season): JsonResponse
    {
        $conn = $this->entityManager->getConnection();

        $sql = "SELECT g.Groupe as code, g.Libelle as libelle, g.section, g.ordre
            FROM kp_groupe g
            WHERE g.section < 100
            AND EXISTS (
                SELECT 1 FROM kp_competition c
                WHERE c.Code_ref = g.Groupe
                AND c.Publication = 'O'
                AND c.Code_saison = ?
            )
            ORDER BY g.section, g.ordre";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $season);
        $result = $stmt->executeQuery();
        $rows = $result->fetchAllAssociative();

        // Organize by section
        $sections = [];
        $currentSection = null;
        $sectionIndex = -1;

        foreach ($rows as $row) {
            if ($currentSection !== $row['section']) {
                $currentSection = $row['section'];
                $sectionIndex++;
                $sections[$sectionIndex] = [
                    'section' => (int) $row['section'],
                    'label' => self::SECTION_LABELS[$row['section']] ?? 'Unknown',
                    'groups' => []
                ];
            }
            $sections[$sectionIndex]['groups'][] = [
                'code' => $row['code'],
                'libelle' => $row['libelle']
            ];
        }

        return new JsonResponse([
            'season' => $season,
            'sections' => array_values($sections)
        ]);
    }

    #[Route('/group/{season}/{groupCode}/games', name: 'group_games', methods: ['GET'])]
    #[OA\Get(
        path: '/group/{season}/{groupCode}/games',
        summary: 'Get games for a competition group',
        description: 'Returns all games from competitions in the specified group for the given season',
        tags: ['2. App2 - Public'],
        parameters: [
            new OA\Parameter(
                name: 'season',
                in: 'path',
                required: true,
                description: 'Season code (year)',
                schema: new OA\Schema(type: 'string', example: '2026')
            ),
            new OA\Parameter(
                name: 'groupCode',
                in: 'path',
                required: true,
                description: 'Group code (e.g. N1H, N1F)',
                schema: new OA\Schema(type: 'string', example: 'N1H')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns list of games for the group',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'g_id', type: 'integer', example: 456),
                            new OA\Property(property: 'c_code', type: 'string', example: 'N1H'),
                            new OA\Property(property: 'd_phase', type: 'string', example: 'Poules'),
                            new OA\Property(property: 't_a_label', type: 'string', example: 'Team A'),
                            new OA\Property(property: 't_b_label', type: 'string', example: 'Team B'),
                            new OA\Property(property: 'g_score_a', type: 'integer', nullable: true, example: 5),
                            new OA\Property(property: 'g_score_b', type: 'integer', nullable: true, example: 3),
                            new OA\Property(property: 'g_date', type: 'string', example: '2025-01-15'),
                            new OA\Property(property: 'g_time', type: 'string', example: '10:00:00')
                        ]
                    )
                )
            )
        ]
    )]
    public function getGroupGames(string $season, string $groupCode): JsonResponse
    {
        $conn = $this->entityManager->getConnection();

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
            WHERE c.Code_ref = ?
            AND c.Code_saison = ?
            AND c.Publication = 'O'
            AND j.Publication = 'O'
            AND m.Publication = 'O'
            AND j.Phase != 'Break'
            AND j.Phase != 'Pause'
            ORDER BY m.Date_match DESC, m.Heure_match, m.Terrain";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $groupCode);
        $stmt->bindValue(2, $season);
        $result = $stmt->executeQuery();
        $games = $result->fetchAllAssociative();

        return new JsonResponse($games);
    }

    #[Route('/group/{season}/{groupCode}/charts', name: 'group_charts', methods: ['GET'])]
    #[OA\Get(
        path: '/group/{season}/{groupCode}/charts',
        summary: 'Get rankings and brackets for a competition group',
        description: 'Returns tournament structure with pools, brackets, rankings for all competitions in the group',
        tags: ['2. App2 - Public'],
        parameters: [
            new OA\Parameter(
                name: 'season',
                in: 'path',
                required: true,
                description: 'Season code (year)',
                schema: new OA\Schema(type: 'string', example: '2026')
            ),
            new OA\Parameter(
                name: 'groupCode',
                in: 'path',
                required: true,
                description: 'Group code (e.g. N1H, N1F)',
                schema: new OA\Schema(type: 'string', example: 'N1H')
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
                            new OA\Property(property: 'code', type: 'string', example: 'N1H'),
                            new OA\Property(property: 'libelle', type: 'string', example: 'Nationale 1 Hommes'),
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
    public function getGroupCharts(string $season, string $groupCode): JsonResponse
    {
        $conn = $this->entityManager->getConnection();
        $charts = [];
        $games = [];

        // First query: Get games grouped by day
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
            WHERE c.Code_ref = ?
            AND c.Code_saison = ?
            AND c.Publication = 'O'
            AND c.Statut != 'ATT'
            AND j.Publication = 'O'
            AND m.Publication = 'O'
            AND j.Phase != 'Break'
            AND j.Phase != 'Pause'
            ORDER BY m.Id_journee, m.Date_match, m.Heure_match, m.Terrain";

        $stmtGames = $conn->prepare($sqlGames);
        $stmtGames->bindValue(1, $groupCode);
        $stmtGames->bindValue(2, $season);
        $resultGames = $stmtGames->executeQuery();

        while ($row = $resultGames->fetchAssociative()) {
            if ($row['d_type'] === 'E' || $row['c_type'] === 'CHPT') {
                $games[$row['d_id']][] = $row;
            } elseif ($row['d_type'] === 'C' && $row['c_type'] === 'CP') {
                $games[$row['d_id']][] = $row['g_code'];
            }
        }

        // Second query: Get teams and rankings
        $sqlTeams = "SELECT j.Code_saison c_season, j.Code_competition c_code, c.Code_typeclt c_type,
            c.GroupOrder c_order, c.Code_tour c_tour, c.Soustitre2 c_category, c.Statut c_status,
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
            WHERE c.Code_ref = ?
            AND c.Code_saison = ?
            AND c.Publication = 'O'
            AND j.Phase != 'Break'
            AND j.Phase != 'Pause'
            ORDER BY c_season, c_tour, c_order, c_code, d_round, d_level DESC, d_phase, d_start DESC,
            t_clt ASC, t_diff DESC, t_plus ASC";

        $stmtTeams = $conn->prepare($sqlTeams);
        $stmtTeams->bindValue(1, $groupCode);
        $stmtTeams->bindValue(2, $season);
        $resultTeams = $stmtTeams->executeQuery();

        $arrayChpt = [];
        while ($row = $resultTeams->fetchAssociative()) {
            $phaseOrder = (100 - $row['d_level']) . '-' . $row['d_phase'];
            $charts[$row['c_code']]['type'] = $row['c_type'];
            $charts[$row['c_code']]['code'] = $row['c_code'];
            $charts[$row['c_code']]['libelle'] = $row['c_category'];
            $charts[$row['c_code']]['status'] = $row['c_status'];
            $charts[$row['c_code']]['season'] = $row['c_season'];
            $charts[$row['c_code']]['order'] = (int) $row['c_order'];
            $charts[$row['c_code']]['tour'] = (int) $row['c_tour'];

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
            $stmt2->bindValue(1, $compet['code']);
            $stmt2->bindValue(2, $compet['season']);
            $result2 = $stmt2->executeQuery();
            $charts[$compet['code']]['ranking'] = $result2->fetchAllAssociative();
        }

        // Sort charts by order ASC, then tour ASC (default for Team page)
        // Frontend can re-sort if needed (Charts page uses DESC)
        uasort($charts, function ($a, $b) {
            $orderCmp = ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
            if ($orderCmp !== 0) {
                return $orderCmp;
            }
            return ($a['tour'] ?? 0) <=> ($b['tour'] ?? 0);
        });

        return new JsonResponse(array_values($charts));
    }

    #[Route('/group/{season}/{groupCode}/teams', name: 'group_teams', methods: ['GET'])]
    #[OA\Get(
        path: '/group/{season}/{groupCode}/teams',
        summary: 'Get teams for a competition group',
        description: 'Returns all teams from competitions in the specified group (for scrutineering)',
        tags: ['2. App2 - Public'],
        parameters: [
            new OA\Parameter(
                name: 'season',
                in: 'path',
                required: true,
                description: 'Season code (year)',
                schema: new OA\Schema(type: 'string', example: '2026')
            ),
            new OA\Parameter(
                name: 'groupCode',
                in: 'path',
                required: true,
                description: 'Group code (e.g. N1H, N1F)',
                schema: new OA\Schema(type: 'string', example: 'N1H')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns list of teams for the group',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 't_id', type: 'integer', example: 123),
                            new OA\Property(property: 't_label', type: 'string', example: 'Team Name'),
                            new OA\Property(property: 't_club', type: 'string', example: 'CLUB01'),
                            new OA\Property(property: 't_logo', type: 'string', example: 'logo/team.png'),
                            new OA\Property(property: 'c_code', type: 'string', example: 'N1H'),
                            new OA\Property(property: 'c_category', type: 'string', example: 'Nationale 1 Hommes')
                        ]
                    )
                )
            )
        ]
    )]
    public function getGroupTeams(string $season, string $groupCode): JsonResponse
    {
        $conn = $this->entityManager->getConnection();

        $sql = "SELECT ce.Id t_id, ce.Libelle t_label, ce.Code_club t_club,
            CASE WHEN ce.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ce.logo END t_logo,
            c.Code c_code, c.Soustitre2 c_category
            FROM kp_competition_equipe ce
            INNER JOIN kp_competition c ON (ce.Code_compet = c.Code AND ce.Code_saison = c.Code_saison)
            WHERE c.Code_ref = ?
            AND c.Code_saison = ?
            AND c.Publication = 'O'
            ORDER BY c.Soustitre2, ce.Libelle";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $groupCode);
        $stmt->bindValue(2, $season);
        $result = $stmt->executeQuery();
        $teams = $result->fetchAllAssociative();

        return new JsonResponse($teams);
    }
}
