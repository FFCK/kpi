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
 * Admin TV Controller
 *
 * TV channel control panel for live streaming overlays and display screens.
 * Migrated from kptv.php + kptvscenario.php
 */
#[Route('/admin/tv')]
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '35. App4 - TV Control')]
class AdminTvController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $liveDocumentRoot
    ) {
    }

    // ─────────────────────────────────────────────
    // GET /admin/tv/events — Published events list
    // ─────────────────────────────────────────────

    #[Route('/events', name: 'admin_tv_events', methods: ['GET'])]
    public function events(): JsonResponse
    {
        $sql = "SELECT Id, Libelle, Lieu, Date_debut, Date_fin
                FROM kp_evenement
                WHERE Publication = 'O'
                ORDER BY Date_debut DESC";

        $rows = $this->connection->fetchAllAssociative($sql);

        return $this->json(array_map(fn(array $r) => [
            'id'        => (int) $r['Id'],
            'libelle'   => $r['Libelle'],
            'lieu'      => $r['Lieu'],
            'dateDebut' => $r['Date_debut'],
            'dateFin'   => $r['Date_fin'],
        ], $rows));
    }

    // ─────────────────────────────────────────────
    // GET /admin/tv/matches — Matches for an event
    // ─────────────────────────────────────────────

    #[Route('/matches', name: 'admin_tv_matches', methods: ['GET'])]
    public function matches(Request $request): JsonResponse
    {
        $eventId     = (int) $request->query->get('eventId', 0);
        $date        = $request->query->get('date', '');
        $competition = $request->query->get('competition', '');

        if ($eventId <= 0) {
            return $this->json(['message' => 'eventId is required'], Response::HTTP_BAD_REQUEST);
        }

        $params = [$eventId];
        $extraWhere = '';

        if (!empty($date)) {
            $extraWhere .= ' AND m.Date_match = ?';
            $params[] = $date;
        }
        if (!empty($competition)) {
            $extraWhere .= ' AND j.Code_competition = ?';
            $params[] = $competition;
        }

        $sql = "SELECT m.Id, m.Numero_ordre, m.Terrain, m.Heure_match, m.Date_match,
                       ce1.Libelle AS EquipeA, ce2.Libelle AS EquipeB,
                       ce1.Id AS IdEquipeA, ce2.Id AS IdEquipeB,
                       j.Phase, j.Code_competition AS CodeCompetition, j.Code_saison AS CodeSaison
                FROM kp_evenement_journee ej
                INNER JOIN kp_journee j ON ej.Id_journee = j.Id
                INNER JOIN kp_match m ON m.Id_journee = j.Id
                LEFT JOIN kp_competition_equipe ce1 ON m.Id_equipeA = ce1.Id
                LEFT JOIN kp_competition_equipe ce2 ON m.Id_equipeB = ce2.Id
                WHERE ej.Id_evenement = ?
                $extraWhere
                ORDER BY m.Date_match, m.Heure_match, m.Terrain, m.Numero_ordre";

        $rows = $this->connection->prepare($sql)->executeQuery($params)->fetchAllAssociative();

        $competitions = [];
        $dates = [];
        $teamsMap = [];
        $season = '';
        $matches = [];

        foreach ($rows as $r) {
            $comp = $r['CodeCompetition'];
            if (!in_array($comp, $competitions)) {
                $competitions[] = $comp;
            }

            $d = $r['Date_match'] ?? '';
            if ($d !== '' && !in_array($d, $dates)) {
                $dates[] = $d;
            }

            if (!empty($r['CodeSaison'])) {
                $season = $r['CodeSaison'];
            }

            foreach ([
                ['id' => (int) ($r['IdEquipeA'] ?? 0), 'l' => $r['EquipeA'] ?? ''],
                ['id' => (int) ($r['IdEquipeB'] ?? 0), 'l' => $r['EquipeB'] ?? ''],
            ] as $t) {
                if ($t['id'] > 0 && !isset($teamsMap[$t['id']])) {
                    $teamsMap[$t['id']] = ['idEquipe' => $t['id'], 'libelleEquipe' => $t['l']];
                }
            }

            $matches[] = [
                'id'              => (int) $r['Id'],
                'numeroOrdre'     => $r['Numero_ordre'] !== null ? (int) $r['Numero_ordre'] : null,
                'terrain'         => $r['Terrain'] ?? '',
                'heureMatch'      => $r['Heure_match'] ?? '',
                'dateMatch'       => $r['Date_match'] ?? '',
                'equipeA'         => $r['EquipeA'] ?? '',
                'equipeB'         => $r['EquipeB'] ?? '',
                'idEquipeA'       => (int) ($r['IdEquipeA'] ?? 0),
                'idEquipeB'       => (int) ($r['IdEquipeB'] ?? 0),
                'phase'           => $r['Phase'] ?? '',
                'codeCompetition' => $r['CodeCompetition'] ?? '',
                'codeSaison'      => $r['CodeSaison'] ?? '',
            ];
        }

        return $this->json([
            'matches'      => $matches,
            'competitions' => $competitions,
            'dates'        => $dates,
            'teams'        => array_values($teamsMap),
            'season'       => $season,
        ]);
    }

    // ─────────────────────────────────────────────
    // POST /admin/tv/activate — Activate a channel
    // ─────────────────────────────────────────────

    #[Route('/activate', name: 'admin_tv_activate', methods: ['POST'])]
    public function activate(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        if (!is_array($body)) {
            return $this->json(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $voie = (int) ($body['voie'] ?? 0);
        $url  = trim($body['url'] ?? '');

        if ($voie <= 0 || $url === '') {
            return $this->json(['message' => 'voie and url are required'], Response::HTTP_BAD_REQUEST);
        }

        $this->setChannelUrl($voie, $url);

        return $this->json(['success' => true, 'voie' => $voie, 'url' => $url]);
    }

    // ─────────────────────────────────────────────
    // POST /admin/tv/blank — Blank a channel
    // ─────────────────────────────────────────────

    #[Route('/blank', name: 'admin_tv_blank', methods: ['POST'])]
    public function blank(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        if (!is_array($body)) {
            return $this->json(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $voie = (int) ($body['voie'] ?? 0);
        $css  = trim($body['css'] ?? 'simply');

        if ($voie <= 0) {
            return $this->json(['message' => 'voie is required'], Response::HTTP_BAD_REQUEST);
        }

        $url = 'live/tv2.php?show=empty&css=' . urlencode($css);
        $this->setChannelUrl($voie, $url);

        return $this->json(['success' => true, 'voie' => $voie]);
    }

    // ─────────────────────────────────────────────
    // GET /admin/tv/labels — Get all labels
    // ─────────────────────────────────────────────

    #[Route('/labels', name: 'admin_tv_labels_get', methods: ['GET'])]
    public function getLabels(): JsonResponse
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT type, number, label FROM kp_tv_label ORDER BY type, number'
        );

        $channels = [];
        $scenarios = [];

        foreach ($rows as $r) {
            $item = ['number' => (int) $r['number'], 'label' => $r['label']];
            if ($r['type'] === 'channel') {
                $channels[] = $item;
            } else {
                $scenarios[] = $item;
            }
        }

        return $this->json(['channels' => $channels, 'scenarios' => $scenarios]);
    }

    // ─────────────────────────────────────────────
    // PUT /admin/tv/labels — Save labels
    // ─────────────────────────────────────────────

    #[Route('/labels', name: 'admin_tv_labels_put', methods: ['PUT'])]
    public function putLabels(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        if (!is_array($body)) {
            return $this->json(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $channels  = $body['channels']  ?? [];
        $scenarios = $body['scenarios'] ?? [];

        foreach ([['channel', $channels], ['scenario', $scenarios]] as [$type, $items]) {
            foreach ($items as $item) {
                $number = (int) ($item['number'] ?? 0);
                $label  = substr(trim($item['label'] ?? ''), 0, 100);
                if ($number <= 0) {
                    continue;
                }

                if ($label === '') {
                    // Remove empty labels
                    $this->connection->executeStatement(
                        'DELETE FROM kp_tv_label WHERE type = ? AND number = ?',
                        [$type, $number]
                    );
                } else {
                    $this->connection->executeStatement(
                        "INSERT INTO kp_tv_label (type, number, label) VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE label = VALUES(label)",
                        [$type, $number, $label]
                    );
                }
            }
        }

        return $this->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    // GET /admin/tv/scenario/{n} — Get scenario
    // ─────────────────────────────────────────────

    #[Route('/scenario/{scenarioNumber}', name: 'admin_tv_scenario_get', methods: ['GET'])]
    public function getScenario(int $scenarioNumber): JsonResponse
    {
        if ($scenarioNumber < 1 || $scenarioNumber > 9) {
            return $this->json(['message' => 'scenarioNumber must be 1-9'], Response::HTTP_BAD_REQUEST);
        }

        $base = $scenarioNumber * 100;
        $rows = $this->connection->fetchAllAssociative(
            'SELECT Voie, Url, intervalle FROM kp_tv WHERE Voie > ? AND Voie < ? ORDER BY Voie',
            [$base, $base + 100]
        );

        $indexed = [];
        foreach ($rows as $r) {
            $indexed[(int) $r['Voie']] = $r;
        }

        $scenes = [];
        for ($i = 1; $i <= 9; $i++) {
            $voie = $base + $i;
            $scenes[] = [
                'voie'       => $voie,
                'url'        => $indexed[$voie]['Url'] ?? '',
                'intervalle' => (int) ($indexed[$voie]['intervalle'] ?? 10000),
            ];
        }

        return $this->json(['scenario' => $scenarioNumber, 'scenes' => $scenes]);
    }

    // ─────────────────────────────────────────────
    // PUT /admin/tv/scenario/{n} — Update scenario
    // ─────────────────────────────────────────────

    #[Route('/scenario/{scenarioNumber}', name: 'admin_tv_scenario_put', methods: ['PUT'])]
    public function putScenario(int $scenarioNumber, Request $request): JsonResponse
    {
        if ($scenarioNumber < 1 || $scenarioNumber > 9) {
            return $this->json(['message' => 'scenarioNumber must be 1-9'], Response::HTTP_BAD_REQUEST);
        }

        $body = json_decode($request->getContent(), true);
        if (!is_array($body)) {
            return $this->json(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $scenes = $body['scenes'] ?? [];

        foreach ($scenes as $scene) {
            $voie       = (int) ($scene['voie'] ?? 0);
            $url        = trim($scene['url'] ?? '');
            $intervalle = (int) ($scene['intervalle'] ?? 10000);

            if ($voie <= 0) {
                continue;
            }

            $existing = $this->connection->fetchOne(
                'SELECT Voie FROM kp_tv WHERE Voie = ?',
                [$voie]
            );

            if ($existing !== false) {
                $this->connection->executeStatement(
                    'UPDATE kp_tv SET Url = ?, intervalle = ? WHERE Voie = ?',
                    [$url, $intervalle, $voie]
                );
            } else {
                $this->connection->executeStatement(
                    'INSERT INTO kp_tv (Voie, Url, intervalle) VALUES (?, ?, ?)',
                    [$voie, $url, $intervalle]
                );
            }

            $this->writeCacheFile($voie, $url, $intervalle);
        }

        return $this->json(['success' => true, 'scenario' => $scenarioNumber]);
    }

    // ─────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────

    private function setChannelUrl(int $voie, string $url): void
    {
        $existing = $this->connection->fetchOne(
            'SELECT Voie FROM kp_tv WHERE Voie = ?',
            [$voie]
        );

        if ($existing !== false) {
            $this->connection->executeStatement(
                'UPDATE kp_tv SET Url = ? WHERE Voie = ?',
                [$url, $voie]
            );
        } else {
            $this->connection->executeStatement(
                'INSERT INTO kp_tv (Voie, Url, intervalle) VALUES (?, ?, 10000)',
                [$voie, $url]
            );
        }

        $intervalle = (int) ($this->connection->fetchOne(
            'SELECT intervalle FROM kp_tv WHERE Voie = ?',
            [$voie]
        ) ?: 10000);

        $this->writeCacheFile($voie, $url, $intervalle);
    }

    private function writeCacheFile(int $voie, string $url, int $intervalle): void
    {
        $dir  = $this->liveDocumentRoot . '/live/cache';
        $path = $dir . '/voie_' . $voie . '.json';

        $data = json_encode([
            'voie'       => $voie,
            'url'        => $url,
            'intervalle' => $intervalle,
            'timestamp'  => date('YmdHis'),
        ]);

        if (is_dir($dir) && is_writable($dir)) {
            file_put_contents($path, $data);
        }
    }
}
