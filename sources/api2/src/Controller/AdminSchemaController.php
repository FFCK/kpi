<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin Schema Controller
 *
 * Read-only visualization of competition phases: pool rankings and elimination brackets.
 * Migrated from GestionSchema.php
 */
#[Route('/admin/schema')]
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '31. App4 - Schema')]
class AdminSchemaController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    // ─────────────────────────────────────────────
    // GET /admin/schema — Read competition schema
    // ─────────────────────────────────────────────

    #[Route('', name: 'admin_schema_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $season = $request->query->get('season', '');
        $competition = $request->query->get('competition', '');
        $lang = $request->query->get('lang', 'fr');

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        $allowedCompetitions = $user?->getAllowedCompetitions();
        if ($allowedCompetitions !== null && !in_array($competition, $allowedCompetitions)) {
            return $this->json(['message' => 'Access denied to this competition'], Response::HTTP_FORBIDDEN);
        }

        // 1. Load competition info
        $compInfo = $this->loadCompetitionInfo($competition, $season);
        if (!$compInfo) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        // 2. Load phases (journées with match counts)
        $phases = $this->loadPhases($competition, $season);

        // 3. Load published rankings per phase
        $phaseRankings = $this->loadPhaseRankings($competition, $season);

        // 4. Load matches per phase
        $phaseMatches = $this->loadPhaseMatches($competition, $season, $lang);

        // 5. Load pool team compositions (fallback for phases without rankings)
        $poolTeams = $this->loadPoolTeams($competition, $season, $lang);

        // 6. Assemble response
        $totalMatches = 0;
        $stages = 0;
        $stageSet = [];
        $result = [];

        foreach ($phases as $phase) {
            $jId = (int) $phase['Id_journee'];
            $etape = (int) $phase['Etape'];
            $stageSet[$etape] = true;

            $ranking = $phaseRankings[$jId] ?? null;
            $pool = $poolTeams[$jId] ?? null;
            $matches = $phaseMatches[$jId] ?? [];

            // Compute start/end times from matches
            $startTime = null;
            $endTime = null;
            foreach ($matches as $m) {
                $heure = $m['heureMatch'] ?? null;
                if ($heure) {
                    if ($startTime === null) {
                        $startTime = $heure;
                    }
                    $endTime = $heure;
                }
            }

            $nbMatchs = (int) $phase['nb_matchs'];
            $totalMatches += $nbMatchs;

            $result[] = [
                'idJournee' => $jId,
                'phase' => $phase['Phase'] ?? '',
                'etape' => $etape,
                'niveau' => (int) ($phase['Niveau'] ?? 0),
                'type' => $phase['Type'] ?? 'C',
                'nbequipes' => (int) ($phase['Nbequipes'] ?? 0),
                'dateDebut' => $phase['Date_debut'],
                'dateFin' => $phase['Date_fin'],
                'lieu' => $phase['Lieu'],
                'departement' => $phase['Departement'],
                'nbMatchs' => $nbMatchs,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'ranking' => $ranking,
                'poolTeams' => $ranking === null ? $pool : null,
                'matches' => array_map(fn($m) => [
                    'id' => (int) $m['Id'],
                    'numeroOrdre' => $m['Numero_ordre'] !== null ? (int) $m['Numero_ordre'] : null,
                    'equipeA' => $m['EquipeA'] ?? '',
                    'equipeB' => $m['EquipeB'] ?? '',
                    'scoreA' => $m['Validation'] === 'O' ? $m['ScoreA'] : null,
                    'scoreB' => $m['Validation'] === 'O' ? $m['ScoreB'] : null,
                    'idEquipeA' => (int) ($m['Id_equipeA'] ?? 0),
                    'idEquipeB' => (int) ($m['Id_equipeB'] ?? 0),
                ], $matches),
            ];
        }

        $stages = count($stageSet);

        return $this->json([
            'competition' => $compInfo,
            'stages' => $stages,
            'totalMatches' => $totalMatches,
            'phases' => $result,
        ]);
    }

    // ═══════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════

    private function loadCompetitionInfo(string $code, string $season): ?array
    {
        $sql = "SELECT Code, Code_saison, Libelle, Soustitre, Soustitre2,
                       Code_typeclt, Code_niveau, Code_ref, Titre_actif,
                       Qualifies, Elimines,
                       BandeauLink, Bandeau_actif,
                       LogoLink, Logo_actif,
                       SponsorLink, Sponsor_actif
                FROM kp_competition
                WHERE Code = ? AND Code_saison = ?";
        $row = $this->connection->prepare($sql)->executeQuery([$code, $season])->fetchAssociative();
        if (!$row) {
            return null;
        }

        return [
            'code' => $row['Code'],
            'season' => $row['Code_saison'],
            'libelle' => $row['Libelle'],
            'soustitre' => $row['Soustitre'] ?: null,
            'soustitre2' => $row['Soustitre2'] ?: null,
            'codeTypeclt' => $row['Code_typeclt'] ?: 'CHPT',
            'codeNiveau' => $row['Code_niveau'] ?: 'NAT',
            'codeRef' => $row['Code_ref'] ?: '',
            'titreActif' => $row['Titre_actif'] === 'O',
            'qualifies' => (int) $row['Qualifies'],
            'elimines' => (int) $row['Elimines'],
            'bandeauLink' => $this->buildImageLink($row['BandeauLink']),
            'bandeauActif' => ($row['Bandeau_actif'] ?? 'N') === 'O',
            'logoLink' => $this->buildImageLink($row['LogoLink']),
            'logoActif' => ($row['Logo_actif'] ?? 'N') === 'O',
            'sponsorLink' => $this->buildImageLink($row['SponsorLink']),
            'sponsorActif' => ($row['Sponsor_actif'] ?? 'N') === 'O',
        ];
    }

    /**
     * Load journées/phases with match counts, excluding Break/Pause and empty phases.
     */
    private function loadPhases(string $competition, string $season): array
    {
        $sql = "SELECT j.Id AS Id_journee, j.Phase, j.Etape, j.Nbequipes, j.Niveau,
                       j.Type, j.Date_debut, j.Date_fin, j.Lieu, j.Departement,
                       COUNT(m.Id) AS nb_matchs
                FROM kp_journee j
                LEFT JOIN kp_match m ON j.Id = m.Id_journee
                WHERE j.Code_competition = ?
                AND j.Code_saison = ?
                AND j.Phase != 'Break'
                AND j.Phase != 'Pause'
                GROUP BY j.Id
                HAVING nb_matchs > 0
                ORDER BY j.Etape ASC, j.Niveau DESC, j.Date_debut DESC, j.Phase ASC";

        return $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();
    }

    /**
     * Load published rankings per phase, keyed by journée ID.
     */
    private function loadPhaseRankings(string $competition, string $season): array
    {
        $sql = "SELECT ce.Id, ce.Libelle, ce.Code_club, cej.Id_journee,
                       cej.Clt_publi, cej.Pts_publi, cej.J_publi, cej.Diff_publi
                FROM kp_competition_equipe ce
                INNER JOIN kp_competition_equipe_journee cej ON ce.Id = cej.Id
                INNER JOIN kp_journee j ON cej.Id_journee = j.Id
                WHERE j.Code_competition = ?
                AND j.Code_saison = ?
                ORDER BY j.Id, cej.Clt_publi ASC, cej.Diff_publi DESC, cej.Plus_publi ASC";

        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $result = [];
        foreach ($rows as $r) {
            $jId = (int) $r['Id_journee'];
            $result[$jId][] = [
                'id' => (int) $r['Id'],
                'libelle' => $r['Libelle'],
                'codeClub' => $r['Code_club'] ?: '',
                'clt' => (int) $r['Clt_publi'],
                'pts' => (int) $r['Pts_publi'] / 100,
                'j' => (int) $r['J_publi'],
                'diff' => (int) $r['Diff_publi'],
            ];
        }

        return $result;
    }

    /**
     * Load matches per phase, keyed by journée ID.
     */
    private function loadPhaseMatches(string $competition, string $season, string $lang): array
    {
        $sql = "SELECT m.Id, m.Id_journee, m.Numero_ordre, m.Date_match, m.Heure_match,
                       m.Libelle, m.Validation, m.ScoreA, m.ScoreB,
                       m.Id_equipeA, m.Id_equipeB,
                       ce1.Libelle AS EquipeA, ce2.Libelle AS EquipeB
                FROM kp_journee j
                INNER JOIN kp_match m ON m.Id_journee = j.Id
                LEFT JOIN kp_competition_equipe ce1 ON m.Id_equipeA = ce1.Id
                LEFT JOIN kp_competition_equipe ce2 ON m.Id_equipeB = ce2.Id
                WHERE j.Code_competition = ?
                AND j.Code_saison = ?
                ORDER BY j.Etape ASC, j.Niveau DESC, m.Date_match, m.Heure_match, m.Numero_ordre";

        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $result = [];
        foreach ($rows as $r) {
            $jId = (int) $r['Id_journee'];
            $idA = (int) ($r['Id_equipeA'] ?? 0);
            $idB = (int) ($r['Id_equipeB'] ?? 0);

            // Resolve placeholder team names
            if ($idA <= 1 || $idB <= 1) {
                $parsed = $this->parseMatchLabel($r['Libelle'] ?? '', $lang);
                if ($idA <= 1 && isset($parsed[0])) {
                    $r['EquipeA'] = $parsed[0];
                }
                if ($idB <= 1 && isset($parsed[1])) {
                    $r['EquipeB'] = $parsed[1];
                }
            }

            $r['heureMatch'] = $r['Heure_match'];
            $result[$jId][] = $r;
        }

        return $result;
    }

    /**
     * Load pool team compositions for type C phases (fallback when no ranking exists).
     * Teams are extracted from matches and deduplicated.
     */
    private function loadPoolTeams(string $competition, string $season, string $lang): array
    {
        $sql = "SELECT j.Id AS Id_journee, m.Id_equipeA, m.Id_equipeB, m.Libelle,
                       ce1.Libelle AS EquipeA, ce2.Libelle AS EquipeB,
                       ce1.Tirage AS TirageA, ce2.Tirage AS TirageB
                FROM kp_journee j
                INNER JOIN kp_match m ON m.Id_journee = j.Id
                LEFT JOIN kp_competition_equipe ce1 ON m.Id_equipeA = ce1.Id
                LEFT JOIN kp_competition_equipe ce2 ON m.Id_equipeB = ce2.Id
                WHERE j.Type = 'C'
                AND j.Code_competition = ?
                AND j.Code_saison = ?
                ORDER BY j.Etape ASC, j.Niveau DESC, j.Id ASC";

        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $result = [];
        foreach ($rows as $r) {
            $jId = (int) $r['Id_journee'];
            $idA = (int) ($r['Id_equipeA'] ?? 0);
            $idB = (int) ($r['Id_equipeB'] ?? 0);

            // Resolve placeholders
            if ($idA <= 1 || $idB <= 1) {
                $parsed = $this->parseMatchLabel($r['Libelle'] ?? '', $lang);
                if ($idA <= 1 && isset($parsed[0])) {
                    $r['EquipeA'] = $parsed[0];
                }
                if ($idB <= 1 && isset($parsed[1])) {
                    $r['EquipeB'] = $parsed[1];
                }
            }

            if (!isset($result[$jId])) {
                $result[$jId] = [];
            }

            // Deduplicate by team name
            if ($idA > 1 && !isset($result[$jId][$r['EquipeA']])) {
                $result[$jId][$r['EquipeA']] = [
                    'id' => $idA,
                    'libelle' => $r['EquipeA'],
                    'tirage' => (int) ($r['TirageA'] ?? 0),
                ];
            }
            if ($idB > 1 && !isset($result[$jId][$r['EquipeB']])) {
                $result[$jId][$r['EquipeB']] = [
                    'id' => $idB,
                    'libelle' => $r['EquipeB'],
                    'tirage' => (int) ($r['TirageB'] ?? 0),
                ];
            }
        }

        // Convert to indexed arrays sorted by tirage
        $final = [];
        foreach ($result as $jId => $teams) {
            $arr = array_values($teams);
            usort($arr, fn($a, $b) => $a['tirage'] <=> $b['tirage']);
            $final[$jId] = $arr;
        }

        return $final;
    }

    /**
     * Parse match label placeholders (e.g. "[V125-P126]") into team names.
     * Replicates utyEquipesAffectAuto / utyEquipesAffectAutoFR from MyTools.php.
     */
    private function parseMatchLabel(string $libelle, string $lang = 'fr'): array
    {
        $result = [];

        // Extract content between brackets [...]
        $parts = preg_split('/\[/', $libelle);
        if (!isset($parts[1]) || $parts[1] === '') {
            return $result;
        }
        $inner = preg_split('/\]/', $parts[1]);
        if ($inner[0] === '') {
            return $result;
        }

        // Split by separators: -, /, *, ,, ;
        $codes = preg_split('/[-\/*,;]/', $inner[0]);

        for ($j = 0; $j < 4; $j++) {
            if (!isset($codes[$j])) {
                continue;
            }

            $code = trim($codes[$j]);
            $resultat = '';

            preg_match('/([A-Z_]+)/', $code, $codeLettres);
            preg_match('/([0-9]+)/', $code, $codeNumero);

            $posNumero = isset($codeNumero[1]) ? strpos($code, $codeNumero[1]) : null;
            $posLettres = isset($codeLettres[1]) ? strpos($code, $codeLettres[1]) : null;

            if (isset($codeLettres[1], $codeNumero[1]) && $posNumero !== null && $posLettres !== null && $posNumero > $posLettres) {
                // Letter then number: match/draw code
                $letter = $codeLettres[1];
                $num = $codeNumero[1];
                if ($lang === 'fr') {
                    $resultat = match ($letter) {
                        'T', 'D' => "(Equipe $num)",
                        'V', 'G', 'W' => "(Vainqueur match $num)",
                        'P', 'L' => "(Perdant match $num)",
                        default => $code,
                    };
                } else {
                    $resultat = match ($letter) {
                        'T', 'D' => "(Team $num)",
                        'V', 'G', 'W' => "(Winner game #$num)",
                        'P', 'L' => "(Loser game #$num)",
                        default => $code,
                    };
                }
            } elseif (isset($codeLettres[1], $codeNumero[1]) && $posNumero !== null && $posLettres !== null && $posNumero < $posLettres) {
                // Number then letter: pool ranking code
                $num = (int) $codeNumero[1];
                $letter = $codeLettres[1];
                if ($lang === 'fr') {
                    $ord = match ($num) {
                        1 => '1er',
                        2 => '2nd',
                        default => $num . 'e',
                    };
                    $resultat = "($ord Poule $letter)";
                } else {
                    $ord = match ($num) {
                        1 => '1st',
                        2 => '2nd',
                        3 => '3rd',
                        default => $num . 'th',
                    };
                    $resultat = "($ord Group $letter)";
                }
            }

            $result[$j] = $resultat;
        }

        return $result;
    }

    private function buildImageLink(?string $dbValue): ?string
    {
        if (empty($dbValue)) {
            return null;
        }
        if (str_starts_with($dbValue, 'http://') || str_starts_with($dbValue, 'https://')) {
            return $dbValue;
        }
        return "/img/logo/{$dbValue}";
    }
}
