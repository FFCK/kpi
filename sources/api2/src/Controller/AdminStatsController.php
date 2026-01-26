<?php

namespace App\Controller;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin Stats Controller
 *
 * Read-only statistics endpoints for admin dashboard
 * Replicates GestionStats.php functionality
 */
#[Route('/admin/stats')]
#[IsGranted('ROLE_ADMIN')]
class AdminStatsController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Get available filters (seasons, competitions)
     */
    #[Route('/filters', name: 'admin_stats_filters', methods: ['GET'])]
    public function getFilters(Request $request): JsonResponse
    {
        $codeSaison = $request->query->get('season');

        // Get seasons
        $sql = "SELECT Code FROM kp_saison WHERE Code > '1900' ORDER BY Code DESC";
        $result = $this->connection->executeQuery($sql);
        $seasons = $result->fetchAllAssociative();

        // If no season provided, use active season
        if (!$codeSaison) {
            $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
            $result = $this->connection->executeQuery($sql);
            $codeSaison = $result->fetchOne() ?: $seasons[0]['Code'] ?? date('Y');
        }

        // Get competitions for season
        $sql = "SELECT c.Code, c.Libelle, c.Code_ref, g.section, g.ordre
                FROM kp_competition c
                LEFT JOIN kp_groupe g ON c.Code_ref = g.Groupe
                WHERE c.Code_saison = ?
                ORDER BY g.section, g.ordre, c.Code";
        $result = $this->connection->executeQuery($sql, [$codeSaison]);
        $competitions = $result->fetchAllAssociative();

        // Group competitions by section
        $sections = $this->getSections();
        $groupedCompetitions = [];
        foreach ($competitions as $comp) {
            $section = $comp['section'] ?? 0;
            $label = $sections[$section] ?? 'Autres';
            if (!isset($groupedCompetitions[$section])) {
                $groupedCompetitions[$section] = [
                    'labelKey' => $label,
                    'options' => []
                ];
            }
            $groupedCompetitions[$section]['options'][] = [
                'code' => $comp['Code'],
                'libelle' => $comp['Libelle']
            ];
        }

        // Stat types available
        $statTypes = $this->getStatTypes();

        return $this->json([
            'seasons' => array_column($seasons, 'Code'),
            'activeSeason' => $codeSaison,
            'competitions' => array_values($groupedCompetitions),
            'statTypes' => $statTypes
        ]);
    }

    /**
     * Get statistics data based on type
     */
    #[Route('/data', name: 'admin_stats_data', methods: ['GET'])]
    public function getData(Request $request): JsonResponse
    {
        $codeSaison = $request->query->get('season');
        // Handle array (?competitions[]=X), single value (?competitions=X), or comma-separated (?competitions=X,Y,Z)
        $compets = $request->query->all()['competitions'] ?? [];
        if (is_string($compets)) {
            $compets = $compets !== '' ? array_map('trim', explode(',', $compets)) : [];
        }
        $statType = $request->query->get('type', 'Buteurs');
        $limit = min(500, max(1, (int) $request->query->get('limit', 30)));

        // Get active season if not provided
        if (!$codeSaison) {
            $sql = "SELECT Code FROM kp_saison WHERE Actif = 'O' LIMIT 1";
            $result = $this->connection->executeQuery($sql);
            $codeSaison = $result->fetchOne() ?: date('Y');
        }

        // If no competitions, return empty
        if (empty($compets)) {
            return $this->json([
                'type' => $statType,
                'columns' => $this->getColumnsForType($statType),
                'data' => [],
                'meta' => ['season' => $codeSaison, 'competitions' => [], 'limit' => $limit]
            ]);
        }

        // Check profile restrictions
        $user = $this->getUser();
        $profile = $user instanceof \App\Entity\User ? $user->getNiveau() : 10;
        $restrictedTypes = ['CJouees3', 'LicenciesNationaux', 'CoherenceMatchs'];
        if ($profile > 6 && in_array($statType, $restrictedTypes)) {
            return $this->json(['message' => 'Access denied for this stat type'], 403);
        }

        // Get data based on stat type
        $data = match($statType) {
            'Buteurs' => $this->getButeurs($compets, $codeSaison, $limit),
            'Attaque' => $this->getAttaque($compets, $codeSaison, $limit),
            'Defense' => $this->getDefense($compets, $codeSaison, $limit),
            'Cartons' => $this->getCartons($compets, $codeSaison, $limit),
            'CartonsEquipe' => $this->getCartonsEquipe($compets, $codeSaison, $limit),
            'CartonsCompetition' => $this->getCartonsCompetition($compets, $codeSaison, $limit),
            'Fairplay' => $this->getFairplay($compets, $codeSaison, $limit),
            'FairplayEquipe' => $this->getFairplayEquipe($compets, $codeSaison, $limit),
            'Arbitrage' => $this->getArbitrage($compets, $codeSaison, $limit),
            'ArbitrageEquipe' => $this->getArbitrageEquipe($compets, $codeSaison, $limit),
            'CJouees' => $this->getCJouees($compets, $codeSaison, $limit),
            'CJouees2' => $this->getCJouees2($compets, $codeSaison, $limit),
            'CJouees3' => $this->getCJouees3($compets, $codeSaison, $limit),
            'CJoueesN' => $this->getCJoueesN($codeSaison, $limit),
            'CJoueesCF' => $this->getCJoueesCF($codeSaison, $limit),
            'OfficielsJournees' => $this->getOfficielsJournees($compets, $codeSaison, $limit),
            'OfficielsMatchs' => $this->getOfficielsMatchs($compets, $codeSaison, $limit),
            'ListeArbitres' => $this->getListeArbitres($limit),
            'ListeEquipes' => $this->getListeEquipes($compets, $codeSaison, $limit),
            'ListeJoueurs' => $this->getListeJoueurs($compets, $codeSaison, $limit),
            'ListeJoueurs2' => $this->getListeJoueurs2($compets, $codeSaison, $limit),
            'LicenciesNationaux' => $this->getLicenciesNationaux($compets, $codeSaison),
            'CoherenceMatchs' => $this->getCoherenceMatchs($compets, $codeSaison),
            default => []
        };

        return $this->json([
            'type' => $statType,
            'columns' => $this->getColumnsForType($statType),
            'data' => $data,
            'meta' => [
                'season' => $codeSaison,
                'competitions' => $compets,
                'limit' => $limit,
                'count' => count($data)
            ]
        ]);
    }

    /**
     * Buteurs - Top scorers
     */
    private function getButeurs(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT d.Code_competition AS competition, a.Matric AS licence, a.Nom AS nom,
                a.Prenom AS prenom, a.Sexe AS sexe, b.Numero AS numero, f.Libelle AS equipe,
                COUNT(*) AS buts
                FROM kp_licence a, kp_match_detail b, kp_match c,
                kp_journee d, kp_competition_equipe f
                WHERE a.Matric = b.Competiteur
                AND b.Id_match = c.Id
                AND c.Id_journee = d.Id
                AND d.Code_competition = f.Code_compet
                AND d.Code_saison = f.Code_saison
                AND f.Id = IF(b.Equipe_A_B='A', c.Id_equipeA, c.Id_equipeB)
                AND d.Code_competition IN (:compets)
                AND d.Code_saison = :saison
                AND b.Id_evt_match = 'B'
                GROUP BY a.Matric
                ORDER BY buts DESC, a.Nom
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'licence' => $row['licence'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'sexe' => $row['sexe'],
            'numero' => $row['numero'],
            'equipe' => $row['equipe'],
            'buts' => (int)$row['buts']
        ], $result->fetchAllAssociative());
    }

    /**
     * Attaque - Team attack stats
     */
    private function getAttaque(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT d.Code_competition AS competition, f.Libelle AS equipe, COUNT(*) AS buts
                FROM kp_match_detail b, kp_match c, kp_journee d, kp_competition_equipe f
                WHERE b.Id_match = c.Id
                AND c.Id_journee = d.Id
                AND d.Code_competition = f.Code_compet
                AND d.Code_saison = f.Code_saison
                AND f.Id = IF(b.Equipe_A_B='A', c.Id_equipeA, c.Id_equipeB)
                AND d.Code_competition IN (:compets)
                AND d.Code_saison = :saison
                AND b.Id_evt_match = 'B'
                GROUP BY equipe
                ORDER BY buts DESC, equipe
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'equipe' => $row['equipe'],
            'buts' => (int)$row['buts']
        ], $result->fetchAllAssociative());
    }

    /**
     * Defense - Team defense stats (goals conceded)
     */
    private function getDefense(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT d.Code_competition AS competition, f.Libelle AS equipe, COUNT(*) AS buts
                FROM kp_match_detail b, kp_match c, kp_journee d, kp_competition_equipe f
                WHERE b.Id_match = c.Id
                AND c.Id_journee = d.Id
                AND d.Code_competition = f.Code_compet
                AND d.Code_saison = f.Code_saison
                AND f.Id = IF(b.Equipe_A_B='B', c.Id_equipeA, c.Id_equipeB)
                AND d.Code_competition IN (:compets)
                AND d.Code_saison = :saison
                AND b.Id_evt_match = 'B'
                GROUP BY equipe
                ORDER BY buts ASC, equipe
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'equipe' => $row['equipe'],
            'buts' => (int)$row['buts']
        ], $result->fetchAllAssociative());
    }

    /**
     * Cartons - Individual cards
     */
    private function getCartons(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT d.Code_competition AS competition, a.Matric AS licence, a.Nom AS nom,
                a.Prenom AS prenom, a.Sexe AS sexe, b.Numero AS numero, f.Libelle AS equipe,
                SUM(IF(b.Id_evt_match='V',1,0)) AS vert,
                SUM(IF(b.Id_evt_match='J',1,0)) AS jaune,
                SUM(IF(b.Id_evt_match='R',1,0)) AS rouge,
                SUM(IF(b.Id_evt_match='D',1,0)) AS rougeDefinitif
                FROM kp_licence a, kp_match_detail b, kp_match c,
                kp_journee d, kp_competition_equipe f
                WHERE a.Matric = b.Competiteur
                AND b.Id_match = c.Id
                AND c.Id_journee = d.Id
                AND d.Code_competition = f.Code_compet
                AND d.Code_saison = f.Code_saison
                AND f.Id = IF(b.Equipe_A_B='A', c.Id_equipeA, c.Id_equipeB)
                AND d.Code_competition IN (:compets)
                AND d.Code_saison = :saison
                AND b.Id_evt_match IN ('V','J','R','D')
                GROUP BY a.Matric
                ORDER BY rougeDefinitif DESC, rouge DESC, jaune DESC, vert DESC, equipe, a.Nom
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'licence' => $row['licence'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'sexe' => $row['sexe'],
            'numero' => $row['numero'],
            'equipe' => $row['equipe'],
            'vert' => (int)$row['vert'],
            'jaune' => (int)$row['jaune'],
            'rouge' => (int)$row['rouge'],
            'rougeDefinitif' => (int)$row['rougeDefinitif']
        ], $result->fetchAllAssociative());
    }

    /**
     * CartonsEquipe - Team cards
     */
    private function getCartonsEquipe(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT d.Code_competition AS competition, f.Libelle AS equipe,
                SUM(IF(b.Id_evt_match='V',1,0)) AS vert,
                SUM(IF(b.Id_evt_match='J',1,0)) AS jaune,
                SUM(IF(b.Id_evt_match='R',1,0)) AS rouge,
                SUM(IF(b.Id_evt_match='D',1,0)) AS rougeDefinitif
                FROM kp_match_detail b, kp_match c, kp_journee d, kp_competition_equipe f
                WHERE b.Id_match = c.Id
                AND c.Id_journee = d.Id
                AND d.Code_competition = f.Code_compet
                AND d.Code_saison = f.Code_saison
                AND d.Code_competition IN (:compets)
                AND d.Code_saison = :saison
                AND f.Id = IF(b.Equipe_A_B='A', c.Id_equipeA, c.Id_equipeB)
                AND b.Id_evt_match IN ('V','J','R','D')
                GROUP BY equipe
                ORDER BY rougeDefinitif DESC, rouge DESC, jaune DESC, vert DESC, equipe
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'equipe' => $row['equipe'],
            'vert' => (int)$row['vert'],
            'jaune' => (int)$row['jaune'],
            'rouge' => (int)$row['rouge'],
            'rougeDefinitif' => (int)$row['rougeDefinitif']
        ], $result->fetchAllAssociative());
    }

    /**
     * CartonsCompetition - Competition-wide cards
     */
    private function getCartonsCompetition(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT c.Soustitre2 AS competition,
                COUNT(DISTINCT(m.Id)) AS matchs,
                SUM(IF(md.Id_evt_match='B',1,0)) AS buts,
                SUM(IF(md.Id_evt_match='V',1,0)) AS vert,
                SUM(IF(md.Id_evt_match='J',1,0)) AS jaune,
                SUM(IF(md.Id_evt_match='R',1,0)) AS rouge,
                SUM(IF(md.Id_evt_match='D',1,0)) AS rougeDefinitif
                FROM kp_journee j, kp_competition c,
                kp_match m
                LEFT OUTER JOIN kp_match_detail md
                    ON (m.Id = md.Id_match AND md.Id_evt_match IN ('B','V','J','R','D'))
                WHERE m.Id_journee = j.Id
                AND j.Code_competition = c.Code
                AND j.Code_saison = c.Code_saison
                AND j.Code_competition IN (:compets)
                AND j.Code_saison = :saison
                AND j.Phase != 'Break'
                AND j.Phase != 'Pause'
                AND m.Statut = 'END'
                AND m.Validation = 'O'
                GROUP BY j.Code_competition
                ORDER BY rougeDefinitif DESC, rouge DESC, jaune DESC, vert DESC, c.Code
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'matchs' => (int)$row['matchs'],
            'buts' => (int)$row['buts'],
            'vert' => (int)$row['vert'],
            'jaune' => (int)$row['jaune'],
            'rouge' => (int)$row['rouge'],
            'rougeDefinitif' => (int)$row['rougeDefinitif']
        ], $result->fetchAllAssociative());
    }

    /**
     * Fairplay - Individual fairplay score
     */
    private function getFairplay(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT d.Code_competition AS competition, a.Matric AS licence, a.Nom AS nom,
                a.Prenom AS prenom, a.Sexe AS sexe, b.Numero AS numero, f.Libelle AS equipe,
                SUM(IF(b.Id_evt_match='V',1, IF(b.Id_evt_match='J',2,
                    IF(b.Id_evt_match='R' OR b.Id_evt_match='D',4,0)))) AS fairplay
                FROM kp_licence a, kp_match_detail b,
                kp_match c, kp_journee d, kp_competition_equipe f
                WHERE a.Matric = b.Competiteur
                AND b.Id_match = c.Id
                AND c.Id_journee = d.Id
                AND d.Code_competition = f.Code_compet
                AND d.Code_saison = f.Code_saison
                AND d.Code_competition IN (:compets)
                AND d.Code_saison = :saison
                AND f.Id = IF(b.Equipe_A_B='A', c.Id_equipeA, c.Id_equipeB)
                AND b.Id_evt_match IN ('V','J','R','D')
                GROUP BY a.Matric
                ORDER BY fairplay ASC, a.Nom
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'licence' => $row['licence'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'sexe' => $row['sexe'],
            'numero' => $row['numero'],
            'equipe' => $row['equipe'],
            'fairplay' => (int)$row['fairplay']
        ], $result->fetchAllAssociative());
    }

    /**
     * FairplayEquipe - Team fairplay score
     */
    private function getFairplayEquipe(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT d.Code_competition AS competition, f.Libelle AS equipe,
                SUM(IF(b.Id_evt_match='V',1, IF(b.Id_evt_match='J',2,
                    IF(b.Id_evt_match='R' OR b.Id_evt_match='D',4,0)))) AS fairplay
                FROM kp_match_detail b, kp_match c, kp_journee d, kp_competition_equipe f
                WHERE b.Id_match = c.Id
                AND c.Id_journee = d.Id
                AND d.Code_competition = f.Code_compet
                AND d.Code_saison = f.Code_saison
                AND d.Code_competition IN (:compets)
                AND d.Code_saison = :saison
                AND f.Id = IF(b.Equipe_A_B='A', c.Id_equipeA, c.Id_equipeB)
                AND b.Id_evt_match IN ('V','J','R','D')
                GROUP BY equipe
                ORDER BY fairplay ASC, equipe
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'equipe' => $row['equipe'],
            'fairplay' => (int)$row['fairplay']
        ], $result->fetchAllAssociative());
    }

    /**
     * Arbitrage - Individual refereeing stats
     */
    private function getArbitrage(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT j.Code_competition AS competition, a.Matric AS licence, lc.Nom AS nom,
                lc.Prenom AS prenom, lc.Sexe AS sexe, c.Code AS codeClub, c.Libelle AS club,
                a.arbitre, a.niveau, a.saison, a.livret,
                SUM(IF(m.Matric_arbitre_principal=a.Matric,1,0)) AS principal,
                SUM(IF(m.Matric_arbitre_secondaire=a.Matric,1,0)) AS secondaire,
                COUNT(*) AS total
                FROM kp_licence lc, kp_arbitre a, kp_club c, kp_match m, kp_journee j
                WHERE a.Matric = lc.Matric
                AND c.Code = lc.Numero_club
                AND m.Id_journee = j.Id
                AND j.Code_competition IN (:compets)
                AND j.Code_saison = :saison
                AND (m.Matric_arbitre_principal = a.Matric OR m.Matric_arbitre_secondaire = a.Matric)
                GROUP BY licence
                ORDER BY total DESC, principal DESC, lc.Nom
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'licence' => $row['licence'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'sexe' => $row['sexe'],
            'principal' => (int)$row['principal'],
            'secondaire' => (int)$row['secondaire'],
            'total' => (int)$row['total']
        ], $result->fetchAllAssociative());
    }

    /**
     * ArbitrageEquipe - Team refereeing stats
     */
    private function getArbitrageEquipe(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT d.Code_competition AS competition, f.Libelle AS equipe,
                SUM(IF((c.Arbitre_principal=f.Libelle)
                    OR (c.Arbitre_principal LIKE CONCAT('%',f.Libelle,')%')),1,0)) AS principal,
                SUM(IF((c.Arbitre_secondaire=f.Libelle)
                    OR (c.Arbitre_secondaire LIKE CONCAT('%',f.Libelle,')%')),1,0)) AS secondaire
                FROM kp_match c, kp_journee d, kp_competition_equipe f
                WHERE c.Id_journee = d.Id
                AND d.Code_competition = f.Code_compet
                AND d.Code_saison = f.Code_saison
                AND d.Code_competition IN (:compets)
                AND d.Code_saison = :saison
                GROUP BY equipe
                ORDER BY principal DESC, secondaire DESC, equipe
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'equipe' => $row['equipe'],
            'principal' => (int)$row['principal'],
            'secondaire' => (int)$row['secondaire'],
            'total' => (int)$row['principal'] + (int)$row['secondaire']
        ], $result->fetchAllAssociative());
    }

    /**
     * CJouees - Matches played (by club)
     */
    private function getCJouees(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT lc.Matric AS matric, lc.Nom AS nom, lc.Prenom AS prenom,
                lc.Numero_club AS numeroClub, clubs.Libelle AS nomClub,
                j.Code_competition AS competition, COUNT(DISTINCT mj.Id_match) AS nbMatchs
                FROM kp_match_joueur mj, kp_match m, kp_journee j, kp_licence lc, kp_club clubs
                WHERE lc.Matric = mj.Matric
                AND mj.Capitaine NOT IN ('E','A','X')
                AND lc.Numero_club = clubs.Code
                AND mj.Id_match = m.Id
                AND m.Id_journee = j.Id
                AND j.Code_competition IN (:compets)
                AND j.Code_saison = :saison
                AND m.Date_match <= CURDATE()
                AND m.Validation = 'O'
                GROUP BY mj.Matric, j.Code_competition
                ORDER BY lc.Numero_club, lc.Nom, lc.Prenom, competition DESC
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'matric' => $row['matric'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'numeroClub' => $row['numeroClub'],
            'nomClub' => $row['nomClub'],
            'nbMatchs' => (int)$row['nbMatchs']
        ], $result->fetchAllAssociative());
    }

    /**
     * CJouees2 - Matches played (by team)
     */
    private function getCJouees2(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT ce.Libelle AS nomEquipe, lc.Matric AS matric, lc.Nom AS nom, lc.Prenom AS prenom,
                j.Code_competition AS competition, COUNT(DISTINCT mj.Id_match) AS nbMatchs
                FROM kp_match_joueur mj, kp_match m, kp_journee j, kp_licence lc, kp_competition_equipe ce
                WHERE lc.Matric = mj.Matric
                AND mj.Capitaine NOT IN ('E','A','X')
                AND mj.Id_match = m.Id
                AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id
                AND m.Id_journee = j.Id
                AND j.Code_competition IN (:compets)
                AND j.Code_saison = :saison
                AND m.Date_match <= CURDATE()
                AND m.Validation = 'O'
                GROUP BY nomEquipe, mj.Matric, j.Code_competition
                ORDER BY lc.Nom, lc.Prenom, competition
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'matric' => $row['matric'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'nomEquipe' => $row['nomEquipe'],
            'nbMatchs' => (int)$row['nbMatchs']
        ], $result->fetchAllAssociative());
    }

    /**
     * CJouees3 - Irregularities (restricted to profile <= 6)
     */
    private function getCJouees3(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT ce.Libelle AS nomEquipe, lc.Matric AS matric, lc.Nom AS nom, lc.Prenom AS prenom,
                j.Code_competition AS competition, COUNT(DISTINCT mj.Id_match) AS nbMatchs,
                lc.Origine AS origine, lc.Pagaie_ECA AS pagaieEca,
                lc.Etat_certificat_CK AS etatCertificatCk, lc.Etat_certificat_APS AS etatCertificatAps
                FROM kp_match_joueur mj, kp_match m, kp_journee j, kp_licence lc, kp_competition_equipe ce
                WHERE lc.Matric = mj.Matric
                AND mj.Capitaine NOT IN ('E','A','X')
                AND mj.Id_match = m.Id
                AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id
                AND m.Id_journee = j.Id
                AND j.Code_competition IN (:compets)
                AND j.Code_saison = :saison
                AND m.Date_match <= CURDATE()
                AND m.Validation = 'O'
                AND (lc.Origine <> :saison
                    OR lc.Pagaie_ECA = '' OR lc.Pagaie_ECA = 'PAGJ'
                    OR lc.Pagaie_ECA = 'PAGB' OR lc.Etat_certificat_CK = 'NON')
                GROUP BY nomEquipe, mj.Matric, j.Code_competition
                ORDER BY lc.Nom, lc.Prenom, competition
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(function($row) use ($codeSaison) {
            $irreg = '';
            if ($row['origine'] != $codeSaison) {
                $irreg = 'Licence ' . $row['origine'];
            }
            if ($row['pagaieEca'] == '' || $row['pagaieEca'] == 'PAGJ' || $row['pagaieEca'] == 'PAGB') {
                if ($irreg != '') $irreg .= ' | ';
                $irreg .= $row['pagaieEca'] != '' ? $row['pagaieEca'] : 'PAG ?';
            }
            if ($row['etatCertificatCk'] == 'NON') {
                if ($irreg != '') $irreg .= ' | ';
                $irreg .= 'Certif CK';
            }

            return [
                'competition' => $row['competition'],
                'matric' => $row['matric'],
                'nom' => mb_strtoupper($row['nom']),
                'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
                'nomEquipe' => $row['nomEquipe'],
                'nbMatchs' => (int)$row['nbMatchs'],
                'irregularite' => $irreg
            ];
        }, $result->fetchAllAssociative());
    }

    /**
     * CJoueesN - National competitions
     */
    private function getCJoueesN(string $codeSaison, int $limit): array
    {
        $sql = "SELECT ce.Libelle AS nomEquipe, lc.Matric AS matric, lc.Nom AS nom, lc.Prenom AS prenom,
                j.Code_competition AS competition, COUNT(DISTINCT mj.Id_match) AS nbMatchs
                FROM kp_match_joueur mj, kp_match m, kp_journee j, kp_licence lc, kp_competition_equipe ce
                WHERE lc.Matric = mj.Matric
                AND mj.Capitaine NOT IN ('E','A','X')
                AND mj.Id_match = m.Id
                AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id
                AND m.Id_journee = j.Id
                AND j.Code_competition LIKE 'N%'
                AND j.Code_saison = ?
                AND m.Date_match <= CURDATE()
                AND m.Validation = 'O'
                GROUP BY nomEquipe, mj.Matric, j.Code_competition
                ORDER BY lc.Nom, lc.Prenom, competition
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [$codeSaison]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'matric' => $row['matric'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'nomEquipe' => $row['nomEquipe'],
            'nbMatchs' => (int)$row['nbMatchs']
        ], $result->fetchAllAssociative());
    }

    /**
     * CJoueesCF - French Cup competitions
     */
    private function getCJoueesCF(string $codeSaison, int $limit): array
    {
        $sql = "SELECT ce.Libelle AS nomEquipe, lc.Matric AS matric, lc.Nom AS nom, lc.Prenom AS prenom,
                j.Code_competition AS competition, COUNT(DISTINCT mj.Id_match) AS nbMatchs
                FROM kp_match_joueur mj, kp_match m, kp_journee j, kp_licence lc, kp_competition_equipe ce
                WHERE lc.Matric = mj.Matric
                AND mj.Capitaine NOT IN ('E','A','X')
                AND mj.Id_match = m.Id
                AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id
                AND m.Id_journee = j.Id
                AND j.Code_competition LIKE 'CF%'
                AND j.Code_saison = ?
                AND m.Date_match <= CURDATE()
                AND m.Validation = 'O'
                GROUP BY nomEquipe, mj.Matric, j.Code_competition
                ORDER BY lc.Nom, lc.Prenom, competition
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [$codeSaison]);

        return array_map(fn($row) => [
            'competition' => $row['competition'],
            'matric' => $row['matric'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'nomEquipe' => $row['nomEquipe'],
            'nbMatchs' => (int)$row['nbMatchs']
        ], $result->fetchAllAssociative());
    }

    /**
     * OfficielsJournees - Officials per matchday
     */
    private function getOfficielsJournees(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT j.Id, j.Code_competition AS competition, j.Libelle AS libelle,
                j.Lieu AS lieu, j.Departement AS departement,
                j.Date_debut AS dateDebut, j.Date_fin AS dateFin,
                j.Responsable_insc AS responsableInsc, j.Responsable_R1 AS responsableR1,
                j.Delegue AS delegue, j.ChefArbitre AS chefArbitre
                FROM kp_journee j
                WHERE j.Code_competition IN (:compets)
                AND j.Code_saison = :saison
                GROUP BY j.Code_competition, j.Date_debut, j.Lieu
                ORDER BY j.Code_competition, j.Date_debut, j.Lieu
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'id' => (int)$row['Id'],
            'competition' => $row['competition'],
            'libelle' => $row['libelle'],
            'lieu' => $row['lieu'],
            'departement' => $row['departement'],
            'dateDebut' => $row['dateDebut'],
            'dateFin' => $row['dateFin'],
            'responsableInsc' => $row['responsableInsc'],
            'responsableR1' => $row['responsableR1'],
            'delegue' => $row['delegue'],
            'chefArbitre' => $row['chefArbitre']
        ], $result->fetchAllAssociative());
    }

    /**
     * OfficielsMatchs - Officials per match
     */
    private function getOfficielsMatchs(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT j.Code_competition AS competition, j.Lieu AS lieu, j.Departement AS departement,
                m.Id, m.Numero_ordre AS numeroOrdre, m.Date_match AS dateMatch, m.Heure_match AS heureMatch,
                a.Libelle AS equipeA, b.Libelle AS equipeB,
                m.Arbitre_principal AS arbitrePrincipal, m.Arbitre_secondaire AS arbitreSecondaire,
                m.Ligne1 AS ligne1, m.Ligne2 AS ligne2, m.Secretaire AS secretaire,
                m.Chronometre AS chronometre, m.Timeshoot AS timeshoot
                FROM kp_journee j, kp_match m, kp_competition_equipe a, kp_competition_equipe b
                WHERE j.Code_competition IN (:compets)
                AND j.Code_saison = :saison
                AND j.Id = m.Id_journee
                AND m.Id_equipeA = a.Id
                AND m.Id_equipeB = b.Id
                ORDER BY j.Code_competition, m.Date_match, m.Heure_match, m.Numero_ordre
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'id' => (int)$row['Id'],
            'competition' => $row['competition'],
            'lieu' => $row['lieu'],
            'dateMatch' => $row['dateMatch'],
            'heureMatch' => $row['heureMatch'],
            'numeroOrdre' => (int)$row['numeroOrdre'],
            'equipeA' => $row['equipeA'],
            'equipeB' => $row['equipeB'],
            'arbitrePrincipal' => $row['arbitrePrincipal'],
            'arbitreSecondaire' => $row['arbitreSecondaire'],
            'ligne1' => $row['ligne1'],
            'ligne2' => $row['ligne2'],
            'secretaire' => $row['secretaire'],
            'chronometre' => $row['chronometre'],
            'timeshoot' => $row['timeshoot']
        ], $result->fetchAllAssociative());
    }

    /**
     * ListeArbitres - List of referees
     */
    private function getListeArbitres(int $limit): array
    {
        $sql = "SELECT lc.Matric AS matric, lc.Nom AS nom, lc.Prenom AS prenom, lc.Sexe AS sexe,
                c.Code AS codeClub, c.Libelle AS club, c.Code_comite_dep AS codeCd,
                cd.Code_comite_reg AS codeCr, a.arbitre, a.niveau, a.saison, a.livret
                FROM kp_arbitre a, kp_licence lc, kp_club c
                LEFT JOIN kp_cd cd ON c.Code_comite_dep = cd.Code
                WHERE a.Matric = lc.Matric
                AND c.Code = lc.Numero_club
                AND a.Matric < 2000000
                AND a.arbitre != ''
                ORDER BY a.arbitre, a.niveau, a.saison, lc.Nom, lc.Prenom
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql);

        return array_map(fn($row) => [
            'matric' => $row['matric'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'sexe' => $row['sexe'],
            'codeClub' => $row['codeClub'],
            'club' => $row['club'],
            'codeCd' => $row['codeCd'],
            'codeCr' => $row['codeCr'],
            'arbitre' => $row['arbitre'],
            'niveau' => $row['niveau'],
            'saison' => $row['saison'],
            'livret' => $row['livret']
        ], $result->fetchAllAssociative());
    }

    /**
     * ListeEquipes - List of teams
     */
    private function getListeEquipes(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT ce.Libelle AS equipe, ce.Code_club AS club, c.Code_comite_dep AS cd,
                kp_cd.Code_comite_reg AS cr, GROUP_CONCAT(DISTINCT l.Numero_club) AS clubActuelJoueurs
                FROM kp_competition_equipe ce
                LEFT JOIN kp_club c ON ce.Code_club = c.Code
                LEFT JOIN kp_cd ON c.Code_comite_dep = kp_cd.Code
                LEFT JOIN kp_competition_equipe_joueur cej ON cej.Id_equipe = ce.Id
                LEFT JOIN kp_licence l ON cej.Matric = l.Matric
                WHERE ce.Code_compet IN (:compets)
                AND ce.Code_saison = :saison
                AND cej.Capitaine != 'E'
                GROUP BY ce.Numero
                ORDER BY ce.Code_club, ce.Libelle
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'equipe' => $row['equipe'],
            'club' => $row['club'],
            'cd' => $row['cd'],
            'cr' => $row['cr'],
            'clubActuelJoueurs' => $row['clubActuelJoueurs']
        ], $result->fetchAllAssociative());
    }

    /**
     * ListeJoueurs - List of players
     */
    private function getListeJoueurs(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT l.Matric AS matric, l.Nom AS nom, l.Prenom AS prenom, l.Sexe AS sexe,
                l.Naissance AS naissance, l.Numero_club AS clubActuel, cej.Categ AS categorie,
                ce.Code_club AS club
                FROM kp_competition_equipe_joueur cej
                LEFT JOIN kp_competition_equipe ce ON cej.Id_equipe = ce.Id
                LEFT JOIN kp_licence l ON cej.Matric = l.Matric
                WHERE cej.Capitaine != 'A'
                AND cej.Capitaine != 'E'
                AND ce.Code_compet IN (:compets)
                AND ce.Code_saison = :saison
                GROUP BY cej.Matric
                ORDER BY ce.Code_club, ce.Libelle
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'matric' => $row['matric'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'sexe' => $row['sexe'],
            'naissance' => $row['naissance'],
            'clubActuel' => $row['clubActuel'],
            'categorie' => $row['categorie'],
            'club' => $row['club']
        ], $result->fetchAllAssociative());
    }

    /**
     * ListeJoueurs2 - List of players & coaches
     */
    private function getListeJoueurs2(array $compets, string $codeSaison, int $limit): array
    {
        $sql = "SELECT l.Matric AS matric, l.Nom AS nom, l.Prenom AS prenom, l.Sexe AS sexe,
                l.Naissance AS naissance, l.Numero_club AS clubActuel, cej.Categ AS categorie,
                ce.Code_club AS club
                FROM kp_competition_equipe_joueur cej
                LEFT JOIN kp_competition_equipe ce ON cej.Id_equipe = ce.Id
                LEFT JOIN kp_licence l ON cej.Matric = l.Matric
                WHERE ce.Code_compet IN (:compets)
                AND ce.Code_saison = :saison
                GROUP BY cej.Matric
                ORDER BY ce.Code_club, ce.Libelle
                LIMIT $limit";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);

        return array_map(fn($row) => [
            'matric' => $row['matric'],
            'nom' => mb_strtoupper($row['nom']),
            'prenom' => mb_convert_case(strtolower($row['prenom']), MB_CASE_TITLE, "UTF-8"),
            'sexe' => $row['sexe'],
            'naissance' => $row['naissance'],
            'clubActuel' => $row['clubActuel'],
            'categorie' => $row['categorie'],
            'club' => $row['club']
        ], $result->fetchAllAssociative());
    }

    /**
     * LicenciesNationaux - National licenses by category (restricted to profile <= 6)
     */
    private function getLicenciesNationaux(array $compets, string $codeSaison): array
    {
        $sql = "SELECT
                'KAP' AS codeActivite,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'M' AND :saison - YEAR(l.Naissance) < 16 THEN l.Matric END) AS hommesU16,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'M' AND :saison - YEAR(l.Naissance) BETWEEN 16 AND 17 THEN l.Matric END) AS hommesU18,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'M' AND :saison - YEAR(l.Naissance) BETWEEN 18 AND 22 THEN l.Matric END) AS hommesU23,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'M' AND :saison - YEAR(l.Naissance) BETWEEN 23 AND 34 THEN l.Matric END) AS hommesU35,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'M' AND :saison - YEAR(l.Naissance) >= 35 THEN l.Matric END) AS hommesPlus35,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'M' THEN l.Matric END) AS hommesTotal,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'F' AND :saison - YEAR(l.Naissance) < 16 THEN l.Matric END) AS femmesU16,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'F' AND :saison - YEAR(l.Naissance) BETWEEN 16 AND 17 THEN l.Matric END) AS femmesU18,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'F' AND :saison - YEAR(l.Naissance) BETWEEN 18 AND 22 THEN l.Matric END) AS femmesU23,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'F' AND :saison - YEAR(l.Naissance) BETWEEN 23 AND 34 THEN l.Matric END) AS femmesU35,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'F' AND :saison - YEAR(l.Naissance) >= 35 THEN l.Matric END) AS femmesPlus35,
                COUNT(DISTINCT CASE WHEN l.Sexe = 'F' THEN l.Matric END) AS femmesTotal,
                COUNT(DISTINCT l.Matric) AS totalActivite
                FROM kp_journee j
                INNER JOIN kp_match m ON m.Id_journee = j.Id
                INNER JOIN kp_match_joueur mj ON mj.Id_match = m.Id
                INNER JOIN kp_licence l ON l.Matric = mj.Matric
                WHERE j.Code_competition IN (:compets)
                AND j.Code_saison = :saison
                AND l.Matric < 2000000
                AND mj.Capitaine NOT IN ('A','X')";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return [];
        }

        return [[
            'saison' => $codeSaison,
            'codeActivite' => $row['codeActivite'],
            'hommesU16' => (int)$row['hommesU16'],
            'hommesU18' => (int)$row['hommesU18'],
            'hommesU23' => (int)$row['hommesU23'],
            'hommesU35' => (int)$row['hommesU35'],
            'hommesPlus35' => (int)$row['hommesPlus35'],
            'hommesTotal' => (int)$row['hommesTotal'],
            'femmesU16' => (int)$row['femmesU16'],
            'femmesU18' => (int)$row['femmesU18'],
            'femmesU23' => (int)$row['femmesU23'],
            'femmesU35' => (int)$row['femmesU35'],
            'femmesPlus35' => (int)$row['femmesPlus35'],
            'femmesTotal' => (int)$row['femmesTotal'],
            'totalActivite' => (int)$row['totalActivite']
        ]];
    }

    /**
     * CoherenceMatchs - Match consistency check (restricted to profile <= 6)
     */
    private function getCoherenceMatchs(array $compets, string $codeSaison): array
    {
        // Get all matches with date, time, teams and referees
        $sql = "SELECT m.Id, m.Date_match AS dateMatch, m.Heure_match AS heureMatch, m.Numero_ordre AS numeroOrdre,
                j.Code_competition AS competition, j.Lieu AS lieu,
                ea.Id AS idEquipeA, ea.Libelle AS equipeA,
                eb.Id AS idEquipeB, eb.Libelle AS equipeB,
                m.Arbitre_principal AS arbitrePrincipal, m.Arbitre_secondaire AS arbitreSecondaire
                FROM kp_match m
                INNER JOIN kp_journee j ON m.Id_journee = j.Id
                INNER JOIN kp_competition_equipe ea ON m.Id_equipeA = ea.Id
                INNER JOIN kp_competition_equipe eb ON m.Id_equipeB = eb.Id
                WHERE j.Code_competition IN (:compets)
                AND j.Code_saison = :saison
                AND m.Date_match IS NOT NULL
                AND m.Heure_match IS NOT NULL
                ORDER BY m.Date_match, m.Heure_match";

        $result = $this->connection->executeQuery($sql, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);
        $matches = $result->fetchAllAssociative();

        // Get all teams for referee lookup
        $sqlTeams = "SELECT Id, Libelle FROM kp_competition_equipe
                     WHERE Code_compet IN (:compets) AND Code_saison = :saison";
        $resultTeams = $this->connection->executeQuery($sqlTeams, [
            'compets' => $compets,
            'saison' => $codeSaison,
        ], [
            'compets' => ArrayParameterType::STRING,
        ]);
        $allTeams = [];
        foreach ($resultTeams->fetchAllAssociative() as $team) {
            $allTeams[$team['Libelle']] = $team['Id'];
        }

        // Build event list per team
        $eventsPerTeam = [];

        foreach ($matches as $row) {
            $datetime = $row['dateMatch'] . ' ' . $row['heureMatch'];

            // Event for team A (match played)
            $eventsPerTeam[$row['idEquipeA']][] = [
                'type' => 'match',
                'datetime' => $datetime,
                'equipe' => $row['equipeA'],
                'matchId' => $row['Id'],
                'competition' => $row['competition'],
                'lieu' => $row['lieu'],
                'role' => 'Équipe A',
                'adversaire' => $row['equipeB']
            ];

            // Event for team B (match played)
            $eventsPerTeam[$row['idEquipeB']][] = [
                'type' => 'match',
                'datetime' => $datetime,
                'equipe' => $row['equipeB'],
                'matchId' => $row['Id'],
                'competition' => $row['competition'],
                'lieu' => $row['lieu'],
                'role' => 'Équipe B',
                'adversaire' => $row['equipeA']
            ];

            // Events for referees (extract team name from referee field)
            foreach (['principal' => $row['arbitrePrincipal'], 'secondaire' => $row['arbitreSecondaire']] as $typeArb => $arbitre) {
                if (!empty($arbitre)) {
                    // Extract team name (format: "Nom Prénom (Équipe)")
                    if (preg_match('/\(([^)]+)\)/', $arbitre, $matches2)) {
                        $equipeArbitre = trim($matches2[1]);

                        // Find team ID
                        $equipeId = null;
                        $equipeNom = null;

                        // Check if it's team A or B
                        if (str_contains($row['equipeA'], $equipeArbitre)) {
                            $equipeId = $row['idEquipeA'];
                            $equipeNom = $row['equipeA'];
                        } elseif (str_contains($row['equipeB'], $equipeArbitre)) {
                            $equipeId = $row['idEquipeB'];
                            $equipeNom = $row['equipeB'];
                        } else {
                            // Search in all teams
                            foreach ($allTeams as $libelle => $id) {
                                if (str_contains($libelle, $equipeArbitre) || $libelle === $equipeArbitre) {
                                    $equipeId = $id;
                                    $equipeNom = $libelle;
                                    break;
                                }
                            }
                        }

                        if ($equipeId !== null) {
                            $eventsPerTeam[$equipeId][] = [
                                'type' => 'arbitrage',
                                'datetime' => $datetime,
                                'equipe' => $equipeNom,
                                'matchId' => $row['Id'],
                                'competition' => $row['competition'],
                                'lieu' => $row['lieu'],
                                'role' => 'Arbitre ' . $typeArb,
                                'match' => $row['equipeA'] . ' vs ' . $row['equipeB']
                            ];
                        }
                    }
                }
            }
        }

        // Analyze inconsistencies
        $inconsistencies = [];

        foreach ($eventsPerTeam as $events) {
            usort($events, fn($a, $b) => strcmp($a['datetime'], $b['datetime']));

            for ($i = 0; $i < count($events); $i++) {
                $evt = $events[$i];
                $datetimeEvt = strtotime($evt['datetime']);
                $dateJour = date('Y-m-d', $datetimeEvt);

                // 1. Check if refereeing less than 1h after a match
                if ($evt['type'] == 'arbitrage' && $i > 0) {
                    for ($j = $i - 1; $j >= 0; $j--) {
                        $evtPrev = $events[$j];
                        if ($evtPrev['type'] == 'match') {
                            $datetimePrev = strtotime($evtPrev['datetime']);
                            $diffMinutes = ($datetimeEvt - $datetimePrev) / 60;
                            if ($diffMinutes > 0 && $diffMinutes < 60) {
                                $inconsistencies[] = [
                                    'type' => 'Arbitrage < 1h après match',
                                    'equipe' => $evt['equipe'],
                                    'competition' => $evt['competition'],
                                    'date' => date('d/m/Y', $datetimeEvt),
                                    'details' => 'Match ' . $evtPrev['role'] . ' vs ' . $evtPrev['adversaire'] .
                                        ' à ' . date('H:i', $datetimePrev) . ', puis ' . $evt['role'] .
                                        ' à ' . date('H:i', $datetimeEvt) . ' (' . round($diffMinutes) . ' min)',
                                    'lieu' => $evt['lieu']
                                ];
                            }
                            break;
                        }
                    }
                }

                // 2. Check if match less than 1h after refereeing
                if ($evt['type'] == 'match' && $i > 0) {
                    for ($j = $i - 1; $j >= 0; $j--) {
                        $evtPrev = $events[$j];
                        if ($evtPrev['type'] == 'arbitrage') {
                            $datetimePrev = strtotime($evtPrev['datetime']);
                            $diffMinutes = ($datetimeEvt - $datetimePrev) / 60;
                            if ($diffMinutes > 0 && $diffMinutes < 60) {
                                $inconsistencies[] = [
                                    'type' => 'Match < 1h après arbitrage',
                                    'equipe' => $evt['equipe'],
                                    'competition' => $evt['competition'],
                                    'date' => date('d/m/Y', $datetimeEvt),
                                    'details' => $evtPrev['role'] . ' à ' . date('H:i', $datetimePrev) .
                                        ', puis match ' . $evt['role'] . ' vs ' . $evt['adversaire'] .
                                        ' à ' . date('H:i', $datetimeEvt) . ' (' . round($diffMinutes) . ' min)',
                                    'lieu' => $evt['lieu']
                                ];
                            }
                            break;
                        }
                    }
                }

                // 3. Count matches played same day
                if ($evt['type'] == 'match') {
                    $matchsJour = array_filter($events, fn($e) =>
                        $e['type'] == 'match' && date('Y-m-d', strtotime($e['datetime'])) == $dateJour
                    );

                    if (count($matchsJour) > 6) {
                        $alreadyAdded = false;
                        foreach ($inconsistencies as $inc) {
                            if ($inc['type'] == 'Plus de 6 matchs/jour' &&
                                $inc['equipe'] == $evt['equipe'] &&
                                $inc['date'] == date('d/m/Y', $datetimeEvt)) {
                                $alreadyAdded = true;
                                break;
                            }
                        }

                        if (!$alreadyAdded) {
                            $inconsistencies[] = [
                                'type' => 'Plus de 6 matchs/jour',
                                'equipe' => $evt['equipe'],
                                'competition' => $evt['competition'],
                                'date' => date('d/m/Y', $datetimeEvt),
                                'details' => count($matchsJour) . ' matchs joués le ' . date('d/m/Y', $datetimeEvt),
                                'lieu' => $evt['lieu']
                            ];
                        }
                    }
                }

                // 4. Check more than 3 matches in 4h period
                if ($evt['type'] == 'match') {
                    $datetimeLimit = $datetimeEvt + (4 * 3600);
                    $matchs4h = [$evt];

                    for ($k = $i + 1; $k < count($events); $k++) {
                        $evtNext = $events[$k];
                        $datetimeNext = strtotime($evtNext['datetime']);

                        if ($datetimeNext > $datetimeLimit) break;
                        if ($evtNext['type'] == 'match') $matchs4h[] = $evtNext;
                    }

                    if (count($matchs4h) > 3) {
                        $alreadyAdded = false;
                        foreach ($inconsistencies as $inc) {
                            if ($inc['type'] == 'Plus de 3 matchs/4h' &&
                                $inc['equipe'] == $evt['equipe'] &&
                                $inc['date'] == date('d/m/Y', $datetimeEvt) &&
                                $inc['details'] == count($matchs4h) . ' matchs de ' . date('H:i', $datetimeEvt) . ' à ' . date('H:i', strtotime($matchs4h[count($matchs4h) - 1]['datetime']))) {
                                $alreadyAdded = true;
                                break;
                            }
                        }

                        if (!$alreadyAdded) {
                            $heureFin = date('H:i', strtotime($matchs4h[count($matchs4h) - 1]['datetime']));
                            $inconsistencies[] = [
                                'type' => 'Plus de 3 matchs/4h',
                                'equipe' => $evt['equipe'],
                                'competition' => $evt['competition'],
                                'date' => date('d/m/Y', $datetimeEvt),
                                'details' => count($matchs4h) . ' matchs de ' . date('H:i', $datetimeEvt) . ' à ' . $heureFin,
                                'lieu' => $evt['lieu']
                            ];
                        }
                    }
                }
            }
        }

        usort($inconsistencies, function($a, $b) {
            $cmp = strcmp($a['equipe'], $b['equipe']);
            return $cmp == 0 ? strcmp($a['date'], $b['date']) : $cmp;
        });

        return $inconsistencies;
    }

    /**
     * Get column definitions for each stat type
     */
    private function getColumnsForType(string $type): array
    {
        return match($type) {
            'Buteurs' => ['competition', 'licence', 'nom', 'prenom', 'sexe', 'numero', 'equipe', 'buts'],
            'Attaque', 'Defense' => ['competition', 'equipe', 'buts'],
            'Cartons' => ['competition', 'licence', 'nom', 'prenom', 'sexe', 'numero', 'equipe', 'vert', 'jaune', 'rouge', 'rougeDefinitif'],
            'CartonsEquipe' => ['competition', 'equipe', 'vert', 'jaune', 'rouge', 'rougeDefinitif'],
            'CartonsCompetition' => ['competition', 'matchs', 'buts', 'vert', 'jaune', 'rouge', 'rougeDefinitif'],
            'Fairplay' => ['competition', 'licence', 'nom', 'prenom', 'sexe', 'numero', 'equipe', 'fairplay'],
            'FairplayEquipe' => ['competition', 'equipe', 'fairplay'],
            'Arbitrage' => ['competition', 'licence', 'nom', 'prenom', 'sexe', 'principal', 'secondaire', 'total'],
            'ArbitrageEquipe' => ['competition', 'equipe', 'principal', 'secondaire', 'total'],
            'CJouees' => ['competition', 'matric', 'nom', 'prenom', 'numeroClub', 'nomClub', 'nbMatchs'],
            'CJouees2', 'CJoueesN', 'CJoueesCF' => ['competition', 'matric', 'nom', 'prenom', 'nomEquipe', 'nbMatchs'],
            'CJouees3' => ['competition', 'matric', 'nom', 'prenom', 'nomEquipe', 'nbMatchs', 'irregularite'],
            'OfficielsJournees' => ['id', 'competition', 'libelle', 'lieu', 'departement', 'dateDebut', 'dateFin', 'responsableInsc', 'responsableR1', 'delegue', 'chefArbitre'],
            'OfficielsMatchs' => ['id', 'competition', 'lieu', 'dateMatch', 'heureMatch', 'numeroOrdre', 'equipeA', 'equipeB', 'arbitrePrincipal', 'arbitreSecondaire', 'ligne1', 'ligne2', 'secretaire', 'chronometre', 'timeshoot'],
            'ListeArbitres' => ['matric', 'nom', 'prenom', 'sexe', 'codeClub', 'club', 'arbitre', 'niveau', 'saison'],
            'ListeEquipes' => ['equipe', 'club', 'cd', 'cr', 'clubActuelJoueurs'],
            'ListeJoueurs', 'ListeJoueurs2' => ['matric', 'nom', 'prenom', 'sexe', 'naissance', 'clubActuel', 'categorie', 'club'],
            'LicenciesNationaux' => ['saison', 'hommesU16', 'hommesU18', 'hommesU23', 'hommesU35', 'hommesPlus35', 'hommesTotal', 'femmesU16', 'femmesU18', 'femmesU23', 'femmesU35', 'femmesPlus35', 'femmesTotal', 'totalActivite'],
            'CoherenceMatchs' => ['type', 'equipe', 'competition', 'date', 'lieu', 'details'],
            default => []
        };
    }

    /**
     * Get all available stat types
     */
    private function getStatTypes(): array
    {
        return [
            ['value' => 'Buteurs', 'labelKey' => 'stats.types.buteurs', 'restricted' => false],
            ['value' => 'Attaque', 'labelKey' => 'stats.types.attaque', 'restricted' => false],
            ['value' => 'Defense', 'labelKey' => 'stats.types.defense', 'restricted' => false],
            ['value' => 'Cartons', 'labelKey' => 'stats.types.cartons_joueurs', 'restricted' => false],
            ['value' => 'CartonsEquipe', 'labelKey' => 'stats.types.cartons_equipes', 'restricted' => false],
            ['value' => 'CartonsCompetition', 'labelKey' => 'stats.types.cartons_competitions', 'restricted' => false],
            ['value' => 'Fairplay', 'labelKey' => 'stats.types.fairplay_joueurs', 'restricted' => false],
            ['value' => 'FairplayEquipe', 'labelKey' => 'stats.types.fairplay_equipes', 'restricted' => false],
            ['value' => 'Arbitrage', 'labelKey' => 'stats.types.arbitrage_arbitres', 'restricted' => false],
            ['value' => 'ArbitrageEquipe', 'labelKey' => 'stats.types.arbitrage_equipes', 'restricted' => false],
            ['value' => 'CJouees', 'labelKey' => 'stats.types.competitions_jouees_clubs', 'restricted' => false],
            ['value' => 'CJouees2', 'labelKey' => 'stats.types.competitions_jouees_equipes', 'restricted' => false],
            ['value' => 'CJoueesN', 'labelKey' => 'stats.types.competitions_nationales', 'restricted' => false],
            ['value' => 'CJoueesCF', 'labelKey' => 'stats.types.coupe_france', 'restricted' => false],
            ['value' => 'OfficielsJournees', 'labelKey' => 'stats.types.officiels_journees', 'restricted' => false],
            ['value' => 'OfficielsMatchs', 'labelKey' => 'stats.types.officiels_matchs', 'restricted' => false],
            ['value' => 'CJouees3', 'labelKey' => 'stats.types.irregularites', 'restricted' => true],
            ['value' => 'ListeArbitres', 'labelKey' => 'stats.types.liste_arbitres', 'restricted' => false],
            ['value' => 'ListeEquipes', 'labelKey' => 'stats.types.liste_equipes', 'restricted' => false],
            ['value' => 'ListeJoueurs', 'labelKey' => 'stats.types.liste_joueurs', 'restricted' => false],
            ['value' => 'ListeJoueurs2', 'labelKey' => 'stats.types.liste_joueurs_coachs', 'restricted' => false],
            ['value' => 'LicenciesNationaux', 'labelKey' => 'stats.types.licencies_nationaux', 'restricted' => true],
            ['value' => 'CoherenceMatchs', 'labelKey' => 'stats.types.coherence_matchs', 'restricted' => true],
        ];
    }

    /**
     * Get competition sections labels
     */
    private function getSections(): array
    {
        return [
            1 => 'stats.sections.championnat_national',
            2 => 'stats.sections.coupe_france',
            3 => 'stats.sections.championnat_regional',
            4 => 'stats.sections.championnat_departemental',
            5 => 'stats.sections.autres_competitions',
            6 => 'stats.sections.international',
        ];
    }
}
