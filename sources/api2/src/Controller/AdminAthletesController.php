<?php

namespace App\Controller;

use App\Entity\User;
use App\Trait\AdminLoggableTrait;
use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin Athletes Controller
 *
 * Search, view, and edit athlete profiles (licence, arbitrage, participations).
 * Migrated from GestionAthlete.php
 */
#[IsGranted('ROLE_USER')]
#[OA\Tag(name: '28. App4 - Athletes')]
class AdminAthletesController extends AbstractController
{
    use AdminLoggableTrait;

    /** Paddle color code to label mapping */
    private const PADDLE_COLORS = [
        'NO' => 'Noire',
        'RO' => 'Rouge',
        'VE' => 'Verte',
        'BL' => 'Bleue',
        'BA' => 'Blanche',
        'JA' => 'Jaune',
    ];

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /admin/athletes/search  — Autocomplete search
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/athletes/search', name: 'admin_athletes_search', methods: ['GET'])]
    #[OA\Get(
        path: '/admin/athletes/search',
        summary: 'Search athletes by name, first name or licence number (autocomplete)',
        tags: ['28. App4 - Athletes']
    )]
    #[OA\Parameter(name: 'q', in: 'query', required: true, description: 'Search term (min 2 chars)', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Max results (default 20, max 50)', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Array of matching athletes')]
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query->get('q', ''));
        $limit = min(50, max(1, (int) $request->query->get('limit', 20)));

        if (mb_strlen($q) < 2) {
            return $this->json([]);
        }

        // If query is numeric, search by Matric or ICF number (Reserve); otherwise search by name
        if (ctype_digit($q)) {
            $sql = "SELECT l.Matric, l.Nom, l.Prenom, l.Sexe, l.Naissance,
                           c.Libelle AS clubLibelle, l.Numero_club AS codeClub
                    FROM kp_licence l
                    LEFT JOIN kp_club c ON c.Code = l.Numero_club
                    WHERE l.Matric = ? OR l.Reserve = ?
                    ORDER BY l.Nom, l.Prenom
                    LIMIT " . (int) $limit;
            $rows = $this->connection->fetchAllAssociative($sql, [(int) $q, (int) $q]);
        } else {
            $likeQ = "%$q%";
            $sql = "SELECT l.Matric, l.Nom, l.Prenom, l.Sexe, l.Naissance,
                           c.Libelle AS clubLibelle, l.Numero_club AS codeClub
                    FROM kp_licence l
                    LEFT JOIN kp_club c ON c.Code = l.Numero_club
                    WHERE l.Nom LIKE ?
                       OR l.Prenom LIKE ?
                       OR CONCAT(l.Nom, ' ', l.Prenom) LIKE ?
                       OR CONCAT(l.Prenom, ' ', l.Nom) LIKE ?
                    ORDER BY l.Nom, l.Prenom
                    LIMIT " . (int) $limit;
            $rows = $this->connection->fetchAllAssociative($sql, [$likeQ, $likeQ, $likeQ, $likeQ]);
        }

        return $this->json(array_map(fn(array $row) => [
            'matric' => (int) $row['Matric'],
            'nom' => $row['Nom'],
            'prenom' => $row['Prenom'],
            'sexe' => $row['Sexe'],
            'naissance' => $row['Naissance'],
            'club' => $row['clubLibelle'] ?? '',
            'codeClub' => $row['codeClub'] ?? '',
            'label' => sprintf(
                '%s %s (%d)%s',
                $row['Nom'],
                $row['Prenom'],
                (int) $row['Matric'],
                $row['clubLibelle'] ? ' - ' . $row['clubLibelle'] : ''
            ),
        ], $rows));
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /admin/athletes/{matric}  — Full athlete profile
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/athletes/{matric}', name: 'admin_athletes_detail', methods: ['GET'], requirements: ['matric' => '\d+'])]
    #[OA\Get(
        path: '/admin/athletes/{matric}',
        summary: 'Get full athlete profile (identity, club, paddle, certificates, refereeing)',
        tags: ['28. App4 - Athletes']
    )]
    #[OA\Parameter(name: 'matric', in: 'path', required: true, description: 'Athlete licence number', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Athlete profile')]
    #[OA\Response(response: 404, description: 'Athlete not found')]
    public function detail(int $matric): JsonResponse
    {
        // Fetch licence + club + CD + CR
        $sql = "SELECT l.*,
                       c.Libelle AS club_libelle,
                       cd.Code AS cd_code, cd.Libelle AS cd_libelle,
                       cr.Code AS cr_code, cr.Libelle AS cr_libelle
                FROM kp_licence l
                LEFT JOIN kp_club c ON c.Code = l.Numero_club
                LEFT JOIN kp_cd cd ON cd.Code = l.Numero_comite_dept
                LEFT JOIN kp_cr cr ON cr.Code = l.Numero_comite_reg
                WHERE l.Matric = ?";

        $row = $this->connection->fetchAssociative($sql, [$matric]);

        if (!$row) {
            return $this->json(['error' => true, 'message' => 'Athlete not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        // Fetch arbitrage info
        $arb = $this->connection->fetchAssociative(
            "SELECT * FROM kp_arbitre WHERE Matric = ?",
            [$matric]
        );

        // Fetch surclassement for current active season
        $activeSeason = $this->connection->fetchOne(
            "SELECT Code FROM kp_saison WHERE Etat = 'O' ORDER BY Code DESC LIMIT 1"
        );

        // Surclassement: use athlete's own Origine season (not active season)
        // An athlete can have a surclassement for a season different from the current active one
        $surclassement = null;
        $athleteSeason = $row['Origine'] ?: $activeSeason;
        if ($athleteSeason) {
            $surcl = $this->connection->fetchAssociative(
                "SELECT Date, Cat FROM kp_surclassement WHERE Matric = ? AND Saison = ?",
                [$matric, $athleteSeason]
            );
            if ($surcl) {
                $surclassement = ['date' => $surcl['Date'], 'cat' => $surcl['Cat']];
            }
        }

        // Compute age category: use athlete's Origine season for consistency
        // sexe in kp_categorie is 'T' (Tous/all) — no gender split at this level
        $categorieAge = null;
        $seasonForAge = $athleteSeason ?: $activeSeason;
        if ($seasonForAge && $row['Naissance']) {
            $anneeNaissance = (int) substr($row['Naissance'], 0, 4);
            $age = (int) $seasonForAge - $anneeNaissance;
            $cat = $this->connection->fetchAssociative(
                "SELECT id, libelle FROM kp_categorie WHERE age_min <= ? AND age_max >= ?",
                [$age, $age]
            );
            if ($cat) {
                $categorieAge = ['code' => $cat['id'], 'libelle' => $cat['libelle']];
            }
        }

        // Build qualification label from arbitre record
        $qualification = '';
        if ($arb) {
            if ($arb['arbitre']) {
                $qualification = $arb['arbitre'];
            } elseif ($arb['international'] === 'O') {
                $qualification = 'Int';
            } elseif ($arb['national'] === 'O') {
                $qualification = 'Nat';
            } elseif ($arb['interregional'] === 'O') {
                $qualification = 'IR';
            } elseif ($arb['regional'] === 'O') {
                $qualification = 'Reg';
            }
        }

        return $this->json([
            'matric' => (int) $row['Matric'],
            'nom' => $row['Nom'],
            'prenom' => $row['Prenom'],
            'sexe' => $row['Sexe'],
            'naissance' => $row['Naissance'],
            'icf' => $row['Reserve'] ? (int) $row['Reserve'] : null,
            'origine' => $row['Origine'],
            'club' => [
                'code' => $row['Numero_club'] ?? '',
                'libelle' => $row['club_libelle'] ?? '',
            ],
            'comiteDep' => [
                'code' => $row['cd_code'] ?? '',
                'libelle' => $row['cd_libelle'] ?? '',
            ],
            'comiteReg' => [
                'code' => $row['cr_code'] ?? '',
                'libelle' => $row['cr_libelle'] ?? '',
            ],
            'pagaie' => [
                'eauVive' => self::PADDLE_COLORS[$row['Pagaie_EVI'] ?? ''] ?? $row['Pagaie_EVI'] ?? '',
                'mer' => self::PADDLE_COLORS[$row['Pagaie_MER'] ?? ''] ?? $row['Pagaie_MER'] ?? '',
                'eauCalme' => self::PADDLE_COLORS[$row['Pagaie_ECA'] ?? ''] ?? $row['Pagaie_ECA'] ?? '',
            ],
            'certificats' => [
                'aps' => $row['Etat_certificat_APS'] ?? '',
                'ck' => $row['Etat_certificat_CK'] ?? '',
            ],
            'arbitrage' => [
                'qualification' => $qualification,
                'niveau' => $arb['niveau'] ?? '',
                'saison' => $arb['saison'] ?? '',
                'livret' => $arb['livret'] ?? '',
            ],
            'typeLicence' => $row['Type_licence'] ?? null,
            'categorieAge' => $categorieAge,
            'surclassement' => $surclassement,
            'editable' => (int) $row['Matric'] > 2000000,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /admin/athletes/{matric}/participations  — Participations for a season
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/athletes/{matric}/participations', name: 'admin_athletes_participations', methods: ['GET'], requirements: ['matric' => '\d+'])]
    #[OA\Get(
        path: '/admin/athletes/{matric}/participations',
        summary: 'Get athlete participations for a season (presence sheets, officials, matches)',
        tags: ['28. App4 - Athletes']
    )]
    #[OA\Parameter(name: 'matric', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'season', in: 'query', required: true, description: 'Season code', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Participations data')]
    #[OA\Response(response: 400, description: 'Missing season parameter')]
    public function participations(int $matric, Request $request): JsonResponse
    {
        $season = trim($request->query->get('season', ''));
        if (empty($season)) {
            return $this->json(['error' => true, 'message' => 'Season parameter is required'], Response::HTTP_BAD_REQUEST);
        }

        // Check athlete exists
        $exists = $this->connection->fetchOne("SELECT Matric FROM kp_licence WHERE Matric = ?", [$matric]);
        if (!$exists) {
            return $this->json(['error' => true, 'message' => 'Athlete not found'], Response::HTTP_NOT_FOUND);
        }

        // 1. Presence sheets
        $presences = $this->fetchPresences($matric, $season);

        // 2. Officials (unified: arbitrage + table de marque)
        $officiels = $this->fetchOfficiels($matric, $season);

        // 3. Matches played
        $matchs = $this->fetchMatchs($matric, $season);

        return $this->json([
            'season' => $season,
            'presences' => $presences,
            'officiels' => $officiels,
            'matchs' => $matchs,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // PUT /admin/athletes/{matric}  — Update athlete
    // ──────────────────────────────────────────────────────────────────────

    #[Route('/admin/athletes/{matric}', name: 'admin_athletes_update', methods: ['PUT'], requirements: ['matric' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        path: '/admin/athletes/{matric}',
        summary: 'Update athlete (identity, club, refereeing). Only non-federal athletes (Matric > 2000000)',
        tags: ['28. App4 - Athletes']
    )]
    #[OA\Parameter(name: 'matric', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Update successful')]
    #[OA\Response(response: 403, description: 'Modification forbidden (federal athlete or insufficient profile)')]
    #[OA\Response(response: 404, description: 'Athlete not found')]
    public function update(int $matric, Request $request): JsonResponse
    {
        // Check athlete exists
        $athlete = $this->connection->fetchAssociative(
            "SELECT Matric, Nom, Prenom FROM kp_licence WHERE Matric = ?",
            [$matric]
        );

        if (!$athlete) {
            return $this->json(['error' => true, 'message' => 'Athlete not found', 'code' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        // Only non-federal athletes can be modified
        if ($matric <= 2000000) {
            return $this->json([
                'error' => true,
                'message' => 'Modification interdite pour cet athlète (licencié fédéral)',
                'code' => 'FORBIDDEN_FEDERAL',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        // Validate required fields
        $nom = mb_substr(mb_strtoupper(trim($data['nom'] ?? '')), 0, 30);
        $prenom = mb_substr(mb_strtoupper(trim($data['prenom'] ?? '')), 0, 30);
        $sexe = trim($data['sexe'] ?? '');
        $naissance = trim($data['naissance'] ?? '');
        $origine = mb_substr(trim($data['origine'] ?? ''), 0, 6);
        $icf = isset($data['icf']) && $data['icf'] !== null && $data['icf'] !== '' ? (int) $data['icf'] : null;

        if (empty($nom) || empty($prenom)) {
            return $this->json(['error' => true, 'message' => 'Nom et prénom sont requis', 'code' => 'VALIDATION_ERROR'], Response::HTTP_BAD_REQUEST);
        }

        if (!in_array($sexe, ['M', 'F'], true)) {
            return $this->json(['error' => true, 'message' => 'Sexe invalide (M ou F)', 'code' => 'VALIDATION_ERROR'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($naissance)) {
            return $this->json(['error' => true, 'message' => 'Date de naissance requise', 'code' => 'VALIDATION_ERROR'], Response::HTTP_BAD_REQUEST);
        }

        // Arbitrage
        $arbQualification = trim($data['arbitrage']['qualification'] ?? '');
        $arbNiveau = trim($data['arbitrage']['niveau'] ?? '');

        $validQualifications = ['', '-', 'Reg', 'IR', 'Nat', 'Int', 'OTM', 'JO'];
        $validNiveaux = ['', '-', 'A', 'B', 'C', 'S'];

        if (!in_array($arbQualification, $validQualifications, true)) {
            return $this->json(['error' => true, 'message' => 'Qualification arbitrage invalide', 'code' => 'VALIDATION_ERROR'], Response::HTTP_BAD_REQUEST);
        }

        if (!in_array($arbNiveau, $validNiveaux, true)) {
            return $this->json(['error' => true, 'message' => 'Niveau arbitrage invalide', 'code' => 'VALIDATION_ERROR'], Response::HTTP_BAD_REQUEST);
        }

        // Club change
        $codeClub = isset($data['codeClub']) ? trim($data['codeClub']) : null;
        $clubData = null;

        if ($codeClub) {
            $clubData = $this->connection->fetchAssociative(
                "SELECT c.Code, cd.Code AS cd_code, cd.Code_comite_reg AS cr_code
                 FROM kp_club c
                 JOIN kp_cd cd ON cd.Code = c.Code_comite_dep
                 WHERE c.Code = ?",
                [$codeClub]
            );

            if (!$clubData) {
                return $this->json(['error' => true, 'message' => 'Club non trouvé', 'code' => 'CLUB_NOT_FOUND'], Response::HTTP_BAD_REQUEST);
            }
        }

        $this->connection->beginTransaction();
        try {
            // a. Update kp_licence
            $licenceSql = "UPDATE kp_licence SET Origine = ?, Nom = ?, Prenom = ?, Sexe = ?, Naissance = ?, Reserve = ?";
            $licenceParams = [$origine, $nom, $prenom, $sexe, $naissance, $icf];

            if ($clubData) {
                $licenceSql .= ", Numero_club = ?, Numero_comite_dept = ?, Numero_comite_reg = ?";
                $licenceParams[] = $clubData['Code'];
                $licenceParams[] = $clubData['cd_code'];
                $licenceParams[] = $clubData['cr_code'];
            }

            $licenceSql .= " WHERE Matric = ?";
            $licenceParams[] = $matric;

            $this->connection->executeStatement($licenceSql, $licenceParams);

            // b. Update kp_competition_equipe_joueur (denormalized name/sex)
            $this->connection->executeStatement(
                "UPDATE kp_competition_equipe_joueur SET Nom = ?, Prenom = ?, Sexe = ? WHERE Matric = ?",
                [$nom, $prenom, $sexe, $matric]
            );

            // c. REPLACE INTO kp_arbitre
            $this->updateArbitrage($matric, $arbQualification, $arbNiveau);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json([
                'error' => true,
                'message' => 'Erreur lors de la modification : ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->logActionForCompetition('ATHLETE_UPDATE', null, null, "Athlete $matric ($nom $prenom) updated");

        return $this->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // Private helpers
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Fetch presence sheets for an athlete in a given season.
     */
    private function fetchPresences(int $matric, string $season): array
    {
        $sql = "SELECT ce.Code_compet, ce.Libelle AS equipe,
                       cej.Numero, cej.Capitaine, cej.Categ
                FROM kp_competition_equipe_joueur cej
                JOIN kp_competition_equipe ce ON ce.Id = cej.Id_equipe
                WHERE cej.Matric = ?
                  AND ce.Code_saison = ?
                  AND ce.Code_compet != 'POOL'
                ORDER BY ce.Code_compet";

        $rows = $this->connection->fetchAllAssociative($sql, [$matric, $season]);

        return array_map(fn(array $row) => [
            'competition' => $row['Code_compet'],
            'equipe' => $row['equipe'],
            'numero' => $row['Numero'] !== null ? (int) $row['Numero'] : null,
            'capitaine' => $row['Capitaine'] ?? '-',
            'categorie' => $row['Categ'] ?? '',
        ], $rows);
    }

    /**
     * Fetch all official roles (referee + table) for an athlete in a given season.
     * Single unified query using OR conditions.
     */
    private function fetchOfficiels(int $matric, string $season): array
    {
        $matricStr = (string) $matric;
        $matricLike = "%($matricStr)%";

        $sql = "SELECT DISTINCT m.Id, m.Date_match, m.Heure_match, m.Numero_ordre,
                       m.ScoreA, m.ScoreB,
                       m.Matric_arbitre_principal, m.Matric_arbitre_secondaire,
                       m.Secretaire, m.Chronometre, m.Timeshoot, m.Ligne1, m.Ligne2,
                       j.Code_competition
                FROM kp_match m
                JOIN kp_journee j ON j.Id = m.Id_journee
                WHERE j.Code_saison = ?
                  AND (
                    m.Matric_arbitre_principal = ?
                    OR m.Matric_arbitre_secondaire = ?
                    OR m.Secretaire LIKE ?
                    OR m.Chronometre LIKE ?
                    OR m.Timeshoot LIKE ?
                    OR m.Ligne1 LIKE ?
                    OR m.Ligne2 LIKE ?
                  )
                ORDER BY m.Date_match DESC, m.Heure_match DESC";

        $rows = $this->connection->fetchAllAssociative($sql, [
            $season,
            $matric, $matric,
            $matricLike, $matricLike, $matricLike, $matricLike, $matricLike,
        ]);

        return array_map(function (array $row) use ($matric, $matricStr) {
            $scoreA = $row['ScoreA'];
            $scoreB = $row['ScoreB'];
            $scoreValide = $scoreA !== null && $scoreA !== '' && $scoreA !== '?'
                        && $scoreB !== null && $scoreB !== '' && $scoreB !== '?';

            return [
                'date' => $row['Date_match'],
                'heure' => $row['Heure_match'] ? substr($row['Heure_match'], 0, 5) : '',
                'competition' => $row['Code_competition'],
                'matchId' => (int) $row['Id'],
                'matchNumero' => $row['Numero_ordre'] !== null ? (int) $row['Numero_ordre'] : null,
                'arbitrePrincipal' => (int) $row['Matric_arbitre_principal'] === $matric,
                'arbitreSecondaire' => (int) $row['Matric_arbitre_secondaire'] === $matric,
                'secretaire' => $row['Secretaire'] && str_contains($row['Secretaire'], "($matricStr)"),
                'chronometreur' => $row['Chronometre'] && str_contains($row['Chronometre'], "($matricStr)"),
                'timekeeper' => $row['Timeshoot'] && str_contains($row['Timeshoot'], "($matricStr)"),
                'ligne' => ($row['Ligne1'] && str_contains($row['Ligne1'], "($matricStr)"))
                        || ($row['Ligne2'] && str_contains($row['Ligne2'], "($matricStr)")),
                'scoreValide' => $scoreValide,
            ];
        }, $rows);
    }

    /**
     * Fetch matches played by an athlete in a given season.
     */
    private function fetchMatchs(int $matric, string $season): array
    {
        $sql = "SELECT m.Id, m.Date_match, m.Heure_match, m.Numero_ordre,
                       m.ScoreA, m.ScoreB,
                       mj.Numero, mj.Equipe, mj.Capitaine,
                       ceA.Libelle AS equipeA, ceB.Libelle AS equipeB,
                       j.Code_competition,
                       SUM(CASE WHEN md.Id_evt_match = 'B' AND md.Competiteur = ? THEN 1 ELSE 0 END) AS buts,
                       SUM(CASE WHEN md.Id_evt_match = 'V' AND md.Competiteur = ? THEN 1 ELSE 0 END) AS verts,
                       SUM(CASE WHEN md.Id_evt_match = 'J' AND md.Competiteur = ? THEN 1 ELSE 0 END) AS jaunes,
                       SUM(CASE WHEN md.Id_evt_match = 'R' AND md.Competiteur = ? THEN 1 ELSE 0 END) AS rouges,
                       SUM(CASE WHEN md.Id_evt_match = 'D' AND md.Competiteur = ? THEN 1 ELSE 0 END) AS rougesDefinitifs,
                       SUM(CASE WHEN md.Id_evt_match = 'T' AND md.Competiteur = ? THEN 1 ELSE 0 END) AS tirs,
                       SUM(CASE WHEN md.Id_evt_match = 'A' AND md.Competiteur = ? THEN 1 ELSE 0 END) AS arrets
                FROM kp_match_joueur mj
                JOIN kp_match m ON m.Id = mj.Id_match
                JOIN kp_journee j ON j.Id = m.Id_journee
                LEFT JOIN kp_competition_equipe ceA ON ceA.Id = m.Id_equipeA
                LEFT JOIN kp_competition_equipe ceB ON ceB.Id = m.Id_equipeB
                LEFT JOIN kp_match_detail md ON md.Id_match = m.Id AND md.Competiteur = ?
                WHERE mj.Matric = ?
                  AND j.Code_saison = ?
                  AND mj.Capitaine != 'X'
                GROUP BY m.Id, m.Date_match, m.Heure_match, m.Numero_ordre,
                         m.ScoreA, m.ScoreB,
                         mj.Numero, mj.Equipe, mj.Capitaine,
                         ceA.Libelle, ceB.Libelle,
                         j.Code_competition
                ORDER BY m.Date_match DESC, m.Heure_match DESC";

        $rows = $this->connection->fetchAllAssociative($sql, [
            $matric, $matric, $matric, $matric, $matric, $matric, $matric,
            $matric,
            $matric, $season,
        ]);

        return array_map(function (array $row) {
            $scoreA = $row['ScoreA'];
            $scoreB = $row['ScoreB'];
            $scoreValide = $scoreA !== null && $scoreA !== '' && $scoreA !== '?'
                        && $scoreB !== null && $scoreB !== '' && $scoreB !== '?';

            return [
                'date' => $row['Date_match'],
                'competition' => $row['Code_competition'],
                'matchId' => (int) $row['Id'],
                'matchNumero' => $row['Numero_ordre'] !== null ? (int) $row['Numero_ordre'] : null,
                'equipeA' => $row['equipeA'] ?? '',
                'equipeB' => $row['equipeB'] ?? '',
                'scoreA' => $scoreA,
                'scoreB' => $scoreB,
                'equipe' => $row['Equipe'],
                'numero' => $row['Numero'] !== null ? (int) $row['Numero'] : null,
                'capitaine' => $row['Capitaine'] ?? '-',
                'buts' => (int) $row['buts'],
                'verts' => (int) $row['verts'],
                'jaunes' => (int) $row['jaunes'],
                'rouges' => (int) $row['rouges'],
                'rougesDefinitifs' => (int) $row['rougesDefinitifs'],
                'tirs' => (int) $row['tirs'],
                'arrets' => (int) $row['arrets'],
                'scoreValide' => $scoreValide,
            ];
        }, $rows);
    }

    /**
     * Update the kp_arbitre record using REPLACE INTO (upsert).
     */
    private function updateArbitrage(int $matric, string $qualification, string $niveau): void
    {
        // Normalize empty/dash to empty
        if ($qualification === '-') {
            $qualification = '';
        }
        if ($niveau === '-') {
            $niveau = '';
        }

        // Determine flags based on qualification
        $regional = 'N';
        $interregional = 'N';
        $national = 'N';
        $international = 'N';
        $arbitre = $qualification;

        switch ($qualification) {
            case 'Reg':
                $regional = 'O';
                break;
            case 'IR':
                $interregional = 'O';
                break;
            case 'Nat':
                $national = 'O';
                break;
            case 'Int':
                $national = 'O';
                $international = 'O';
                break;
            case 'OTM':
                $national = 'O';
                break;
            case 'JO':
                $national = 'O';
                break;
            default:
                $arbitre = '';
                break;
        }

        // Get active season for the saison field
        $activeSeason = $this->connection->fetchOne(
            "SELECT Code FROM kp_saison WHERE Etat = 'O' ORDER BY Code DESC LIMIT 1"
        ) ?: '';

        // Get existing livret to preserve it
        $existingLivret = $this->connection->fetchOne(
            "SELECT livret FROM kp_arbitre WHERE Matric = ?",
            [$matric]
        ) ?: '';

        $this->connection->executeStatement(
            "REPLACE INTO kp_arbitre (Matric, regional, interregional, national, international, arbitre, niveau, saison, livret)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$matric, $regional, $interregional, $national, $international, $arbitre, $niveau, $activeSeason, $existingLivret]
        );
    }
}
