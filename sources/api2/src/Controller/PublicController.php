<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PublicController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/team-stats/{teamId}/{eventId}', name: 'team_stats', methods: ['GET'])]
    #[OA\Get(
        path: '/team-stats/{teamId}/{eventId}',
        summary: 'Get team statistics',
        description: 'Returns player statistics for a team in an event (goals, cards, etc.)',
        tags: ['2. App2 - Public'],
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
        path: '/stars',
        summary: 'Get app ratings statistics',
        tags: ['2. App2 - Public'],
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
        path: '/rating',
        summary: 'Submit app rating',
        tags: ['2. App2 - Public'],
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

    #[Route('/game-sheet/{gameId}', name: 'game_sheet', methods: ['GET'])]
    #[OA\Get(
        path: '/game-sheet/{gameId}',
        summary: 'Get game sheet data',
        description: 'Returns complete game sheet data for a finished and validated game. Includes game info, team compositions, events timeline, and player statistics. Publicly accessible for sharing match results.',
        tags: ['2. App2 - Public'],
        parameters: [
            new OA\Parameter(
                name: 'gameId',
                in: 'path',
                required: true,
                description: 'Game ID',
                schema: new OA\Schema(type: 'integer', example: 456)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns complete match sheet data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'game', type: 'object', description: 'Game information'),
                        new OA\Property(property: 'team_a', type: 'object', description: 'Team A composition and stats'),
                        new OA\Property(property: 'team_b', type: 'object', description: 'Team B composition and stats'),
                        new OA\Property(property: 'events', type: 'array', items: new OA\Items(type: 'object'), description: 'Match events timeline'),
                        new OA\Property(property: 'stats', type: 'object', description: 'Match statistics summary')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Game not found or not available')
        ]
    )]
    public function getGameSheet(string $gameId): JsonResponse
    {
        $conn = $this->entityManager->getConnection();
        $gameId = (int) $gameId;

        // Get game info - if in progress (ON) or finished (END), not pending (ATT)
        $gameSql = "
            SELECT c.Code c_code, c.Code_saison c_season, j.Phase d_phase, j.Niveau d_level, j.Type d_type,
                j.Lieu d_place, j.Libelle d_label, c.Soustitre2 c_label, c.Code_typeclt c_type,
                m.Id g_id, m.Id_journee d_id, m.Numero_ordre g_number, m.Date_match g_date,
                m.Heure_match g_time, m.Heure_fin g_time_end, m.Terrain g_pitch, m.Libelle g_code,
                m.Validation g_validation, m.Statut g_status, m.Periode g_period,
                m.ScoreA g_score_a, m.ScoreB g_score_b, m.CoeffA g_coef_a, m.CoeffB g_coef_b,
                m.ScoreDetailA g_score_detail_a, m.ScoreDetailB g_score_detail_b,
                m.Id_equipeA t_a_id, m.Id_equipeB t_b_id,
                m.Arbitre_principal g_referee_1, m.Arbitre_secondaire g_referee_2,
                cea.Libelle t_a_label, ceb.Libelle t_b_label, cea.Numero t_a_number, ceb.Numero t_b_number,
                cea.Code_club t_a_club, ceb.Code_club t_b_club,
                cea.color1 t_a_color1, cea.color2 t_a_color2, cea.colortext t_a_colortext,
                ceb.color1 t_b_color1, ceb.color2 t_b_color2, ceb.colortext t_b_colortext,
                CASE WHEN cea.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE cea.logo END t_a_logo,
                CASE WHEN ceb.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ceb.logo END t_b_logo
            FROM kp_match m
            LEFT OUTER JOIN kp_competition_equipe cea ON (m.Id_equipeA = cea.Id)
            LEFT OUTER JOIN kp_competition_equipe ceb ON (m.Id_equipeB = ceb.Id)
            INNER JOIN kp_journee j ON (m.Id_journee = j.Id)
            INNER JOIN kp_competition c ON (j.Code_competition = c.Code AND j.Code_saison = c.Code_saison)
            WHERE m.Id = ?
            AND m.Statut IN ('ON', 'END')
            AND c.Publication = 'O'
            AND j.Publication = 'O'
            AND m.Publication = 'O'
        ";

        $stmt = $conn->prepare($gameSql);
        $result = $stmt->executeQuery([$gameId]);
        $game = $result->fetchAssociative();

        if (!$game) {
            return new JsonResponse(['error' => 'Game not found or not available'], 404);
        }

        // Get match events
        $eventsSql = "
            SELECT md.Id e_id, md.Equipe_A_B e_team, md.Id_evt_match e_type,
                md.Periode e_period, md.Temps e_time, md.motif e_motif,
                l.Matric e_licence, md.Numero e_number, l.Nom e_name, l.Prenom e_firstname
            FROM kp_match_detail md
            LEFT OUTER JOIN kp_licence l ON (md.Competiteur = l.Matric)
            WHERE md.Id_match = ?
            ORDER BY md.Periode ASC, md.Temps ASC, md.Id ASC
        ";

        $stmt = $conn->prepare($eventsSql);
        $result = $stmt->executeQuery([$gameId]);
        $events = $result->fetchAllAssociative();

        // Get team members with their stats
        $membersSql = "
            SELECT mj.Equipe team, mj.Matric licence, mj.Numero number, mj.Capitaine captain,
                l.Nom name, l.Prenom firstname, l.Sexe gender
            FROM kp_match_joueur mj
            LEFT OUTER JOIN kp_licence l ON (mj.Matric = l.Matric)
            WHERE mj.Id_match = ?
            ORDER BY mj.Equipe, CASE WHEN mj.Capitaine = 'E' THEN 999 ELSE mj.Numero END
        ";

        $stmt = $conn->prepare($membersSql);
        $result = $stmt->executeQuery([$gameId]);
        $members = $result->fetchAllAssociative();

        // Calculate player stats from events
        $playerStats = [];
        foreach ($events as $event) {
            $licence = $event['e_licence'] ?? null;
            if (!$licence) continue;

            if (!isset($playerStats[$licence])) {
                $playerStats[$licence] = [
                    'goals' => 0,
                    'green_cards' => 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                    'exclusions' => 0
                ];
            }

            switch ($event['e_type']) {
                case 'B': $playerStats[$licence]['goals']++; break;
                case 'V': $playerStats[$licence]['green_cards']++; break;
                case 'J': $playerStats[$licence]['yellow_cards']++; break;
                case 'R': $playerStats[$licence]['red_cards']++; break;
                case 'D': $playerStats[$licence]['exclusions']++; break;
            }
        }

        // Organize team members with stats
        $teamA = [];
        $teamB = [];
        foreach ($members as $member) {
            $licence = $member['licence'];
            $memberData = [
                'licence' => $licence,
                'number' => $member['number'],
                'name' => $member['name'],
                'firstname' => $member['firstname'],
                'captain' => $member['captain'],
                'gender' => $member['gender'],
                'stats' => $playerStats[$licence] ?? [
                    'goals' => 0,
                    'green_cards' => 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                    'exclusions' => 0
                ]
            ];

            if ($member['team'] === 'A') {
                $teamA[] = $memberData;
            } else {
                $teamB[] = $memberData;
            }
        }

        // Calculate team stats summary and halftime score
        $teamAStats = ['goals' => 0, 'green_cards' => 0, 'yellow_cards' => 0, 'red_cards' => 0, 'exclusions' => 0];
        $teamBStats = ['goals' => 0, 'green_cards' => 0, 'yellow_cards' => 0, 'red_cards' => 0, 'exclusions' => 0];
        $halftimeScoreA = 0;
        $halftimeScoreB = 0;

        foreach ($events as $event) {
            $isTeamA = $event['e_team'] === 'A';
            switch ($event['e_type']) {
                case 'B':
                    if ($isTeamA) {
                        $teamAStats['goals']++;
                        if ($event['e_period'] === 'M1') $halftimeScoreA++;
                    } else {
                        $teamBStats['goals']++;
                        if ($event['e_period'] === 'M1') $halftimeScoreB++;
                    }
                    break;
                case 'V':
                    if ($isTeamA) $teamAStats['green_cards']++;
                    else $teamBStats['green_cards']++;
                    break;
                case 'J':
                    if ($isTeamA) $teamAStats['yellow_cards']++;
                    else $teamBStats['yellow_cards']++;
                    break;
                case 'R':
                    if ($isTeamA) $teamAStats['red_cards']++;
                    else $teamBStats['red_cards']++;
                    break;
                case 'D':
                    if ($isTeamA) $teamAStats['exclusions']++;
                    else $teamBStats['exclusions']++;
                    break;
            }
        }

        return new JsonResponse([
            'game' => $game,
            'team_a' => [
                'id' => $game['t_a_id'],
                'label' => $game['t_a_label'],
                'logo' => $game['t_a_logo'],
                'club' => $game['t_a_club'],
                'color1' => $game['t_a_color1'],
                'color2' => $game['t_a_color2'],
                'players' => $teamA,
                'stats' => $teamAStats
            ],
            'team_b' => [
                'id' => $game['t_b_id'],
                'label' => $game['t_b_label'],
                'logo' => $game['t_b_logo'],
                'club' => $game['t_b_club'],
                'color1' => $game['t_b_color1'],
                'color2' => $game['t_b_color2'],
                'players' => $teamB,
                'stats' => $teamBStats
            ],
            'events' => $events,
            'halftime_score' => [
                'team_a' => $halftimeScoreA,
                'team_b' => $halftimeScoreB
            ],
            'stats' => [
                'total_goals' => $teamAStats['goals'] + $teamBStats['goals'],
                'total_cards' => $teamAStats['green_cards'] + $teamBStats['green_cards'] +
                                $teamAStats['yellow_cards'] + $teamBStats['yellow_cards'] +
                                $teamAStats['red_cards'] + $teamBStats['red_cards']
            ]
        ]);
    }
}
