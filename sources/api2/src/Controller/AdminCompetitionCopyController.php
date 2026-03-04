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
 * Admin Competition Copy Controller
 *
 * Search competition schemas and copy structures (gamedays + matches)
 * Migrated from GestionCopieCompetition.php
 */
#[Route('/admin/competitions')]
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '25. App4 - Competitions')]
class AdminCompetitionCopyController extends AbstractController
{
    use AdminLoggableTrait;

    private const SECTION_LABELS = [
        1 => 'International',
        2 => 'National',
        3 => 'Régional',
        4 => 'Tournoi',
        5 => 'Continental',
        100 => 'Divers',
    ];

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Search competition schemas by number of teams
     */
    #[Route('/-schemas', name: 'admin_competitions_schemas', methods: ['GET'])]
    public function searchSchemas(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $nbEquipes = (int) $request->query->get('nbEquipes', 0);
        if ($nbEquipes <= 0) {
            return $this->json(['message' => 'nbEquipes must be a positive integer'], Response::HTTP_BAD_REQUEST);
        }

        $type = $request->query->get('type', '');
        $tri = $request->query->get('tri', 'saison');

        // Build WHERE conditions
        $whereConditions = [
            'c.Nb_equipes = ?',
            'c.Nb_equipes > 0',
            'c.Code_ref = g.Groupe',
        ];
        $params = [$nbEquipes];

        if (in_array($type, ['CHPT', 'CP'], true)) {
            $whereConditions[] = 'c.Code_typeclt = ?';
            $params[] = $type;
        }

        $where = implode(' AND ', $whereConditions);

        $sql = "SELECT c.Code, c.Code_saison, c.Code_niveau, c.Libelle, c.Soustitre, c.Soustitre2,
                       c.Titre_actif, c.Code_typeclt, c.Code_tour, c.Nb_equipes, c.Qualifies, c.Elimines,
                       c.commentairesCompet
                FROM kp_competition c, kp_groupe g
                WHERE {$where}
                ORDER BY c.Code_saison DESC, g.Id, COALESCE(c.Code_ref, 'z'),
                         c.Code_tour, c.GroupOrder, c.Code";

        $stmt = $this->connection->prepare($sql);
        $rows = $stmt->executeQuery($params)->fetchAllAssociative();

        $schemas = [];
        foreach ($rows as $row) {
            $code = $row['Code'];
            $season = $row['Code_saison'];

            // Count matches (excluding Break/Pause phases), distinct terrains and etapes
            $statsSql = "SELECT
                            COUNT(m.Id) as nbMatchs,
                            COUNT(DISTINCT m.Terrain) as nbTerrains,
                            COUNT(DISTINCT j.Etape) as nbTours,
                            COUNT(DISTINCT j.Phase) as nbPhases,
                            SUM(CASE WHEN m.Libelle LIKE '%[%]%' THEN 1 ELSE 0 END) as nbEncoded
                         FROM kp_match m
                         JOIN kp_journee j ON j.Id = m.Id_journee
                         WHERE j.Code_competition = ? AND j.Code_saison = ?
                           AND j.Phase NOT IN ('Break', 'Pause')";

            $statsRow = $this->connection->prepare($statsSql)
                ->executeQuery([$code, $season])
                ->fetchAssociative();

            $nbMatchs = (int) ($statsRow['nbMatchs'] ?? 0);
            if ($nbMatchs === 0) {
                continue;
            }

            $nbEncoded = (int) ($statsRow['nbEncoded'] ?? 0);
            $nbTotal = $nbMatchs;

            $schemas[] = [
                'code' => $code,
                'season' => $season,
                'codeNiveau' => $row['Code_niveau'],
                'libelle' => $row['Libelle'],
                'soustitre' => $row['Soustitre'] ?: null,
                'soustitre2' => $row['Soustitre2'] ?: null,
                'titreActif' => $row['Titre_actif'] === 'O',
                'codeTypeclt' => $row['Code_typeclt'],
                'codeTour' => $row['Code_tour'] ?: null,
                'nbEquipes' => (int) $row['Nb_equipes'],
                'qualifies' => (int) $row['Qualifies'],
                'elimines' => (int) $row['Elimines'],
                'commentaires' => $row['commentairesCompet'] ?: null,
                'nbMatchs' => $nbMatchs,
                'nbTerrains' => (int) ($statsRow['nbTerrains'] ?? 0),
                'nbTours' => (int) ($statsRow['nbTours'] ?? 0),
                'nbPhases' => (int) ($statsRow['nbPhases'] ?? 0),
                'matchsEncodes' => $nbEncoded > ($nbTotal / 2),
            ];
        }

        // Sort by nbMatchs DESC then season DESC if requested
        if ($tri === 'matchs') {
            usort($schemas, function ($a, $b) {
                $cmp = $b['nbMatchs'] <=> $a['nbMatchs'];
                if ($cmp !== 0) return $cmp;
                return $b['season'] <=> $a['season'];
            });
        }

        return $this->json(['schemas' => $schemas]);
    }

    /**
     * Get competition copy detail (origin info with journees and prefill data)
     */
    #[Route('/{season}/{code}/copy-detail', name: 'admin_competitions_copy_detail', methods: ['GET'])]
    public function getCopyDetail(string $season, string $code): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Get competition info
        $sql = "SELECT Code, Code_saison, Code_typeclt, Nb_equipes, Qualifies, Elimines,
                       Soustitre, Soustitre2, commentairesCompet
                FROM kp_competition
                WHERE Code = ? AND Code_saison = ?";
        $comp = $this->connection->prepare($sql)->executeQuery([$code, $season])->fetchAssociative();

        if (!$comp) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        // Get journees
        $journeeSql = "SELECT Id, Phase, Niveau, Date_debut, Date_fin, Nom, Libelle, Lieu,
                               Plan_eau, Departement, Responsable_insc, Responsable_R1,
                               Organisateur, Delegue
                        FROM kp_journee
                        WHERE Code_competition = ? AND Code_saison = ?
                        ORDER BY Id";
        $journees = $this->connection->prepare($journeeSql)
            ->executeQuery([$code, $season])
            ->fetchAllAssociative();

        // Count total matches
        $matchCountSql = "SELECT COUNT(m.Id) as cnt
                          FROM kp_match m
                          JOIN kp_journee j ON j.Id = m.Id_journee
                          WHERE j.Code_competition = ? AND j.Code_saison = ?
                            AND j.Phase NOT IN ('Break', 'Pause')";
        $nbMatchs = (int) $this->connection->prepare($matchCountSql)
            ->executeQuery([$code, $season])
            ->fetchOne();

        // Build prefill from first journee
        $prefill = [
            'dateDebut' => null,
            'dateFin' => null,
            'nom' => null,
            'libelle' => null,
            'lieu' => null,
            'planEau' => null,
            'departement' => null,
            'responsableInsc' => null,
            'responsableR1' => null,
            'organisateur' => null,
            'delegue' => null,
        ];

        if (!empty($journees)) {
            $first = $journees[0];
            $prefill = [
                'dateDebut' => $first['Date_debut'] ?: null,
                'dateFin' => $first['Date_fin'] ?: null,
                'nom' => $first['Nom'] ?: null,
                'libelle' => $first['Libelle'] ?: null,
                'lieu' => $first['Lieu'] ?: null,
                'planEau' => $first['Plan_eau'] ?: null,
                'departement' => $first['Departement'] ?: null,
                'responsableInsc' => $first['Responsable_insc'] ?: null,
                'responsableR1' => $first['Responsable_R1'] ?: null,
                'organisateur' => $first['Organisateur'] ?: null,
                'delegue' => $first['Delegue'] ?: null,
            ];
        }

        // Format journees for response
        $journeeList = [];
        foreach ($journees as $j) {
            $journeeList[] = [
                'id' => (int) $j['Id'],
                'phase' => $j['Phase'],
                'niveau' => (int) $j['Niveau'],
                'lieu' => $j['Lieu'] ?: null,
            ];
        }

        return $this->json([
            'code' => $comp['Code'],
            'season' => $comp['Code_saison'],
            'codeTypeclt' => $comp['Code_typeclt'],
            'nbEquipes' => (int) $comp['Nb_equipes'],
            'qualifies' => (int) $comp['Qualifies'],
            'elimines' => (int) $comp['Elimines'],
            'nbMatchs' => $nbMatchs,
            'soustitre' => $comp['Soustitre'] ?: null,
            'soustitre2' => $comp['Soustitre2'] ?: null,
            'commentaires' => $comp['commentairesCompet'] ?: null,
            'journees' => $journeeList,
            'prefill' => $prefill,
        ]);
    }

    /**
     * List competitions grouped by section for destination dropdown
     */
    #[Route('/-options', name: 'admin_competitions_options', methods: ['GET'])]
    public function getCompetitionOptions(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $season = $request->query->get('season', '');
        if (empty($season)) {
            return $this->json(['message' => 'Season is required'], Response::HTTP_BAD_REQUEST);
        }

        // Apply user competition filter
        $allowedCompetitions = $user?->getAllowedCompetitions();
        $filterSql = '';
        $params = [$season];

        if ($allowedCompetitions !== null && count($allowedCompetitions) > 0) {
            $placeholders = implode(',', array_fill(0, count($allowedCompetitions), '?'));
            $filterSql = "AND c.Code IN ($placeholders)";
            $params = array_merge($params, $allowedCompetitions);
        }

        $sql = "SELECT c.Code, c.Libelle, c.Code_typeclt, c.Nb_equipes, c.Qualifies, c.Elimines,
                       g.section, g.ordre
                FROM kp_competition c
                LEFT JOIN kp_groupe g ON c.Code_ref = g.Groupe
                WHERE c.Code_saison = ?
                {$filterSql}
                ORDER BY COALESCE(g.section, 999), COALESCE(g.ordre, 999),
                         c.Code_tour, c.GroupOrder, c.Code";

        $rows = $this->connection->prepare($sql)->executeQuery($params)->fetchAllAssociative();

        // Group by section
        $bySection = [];
        foreach ($rows as $row) {
            $section = (int) ($row['section'] ?? 100);
            $label = self::SECTION_LABELS[$section] ?? 'Autres';

            if (!isset($bySection[$section])) {
                $bySection[$section] = [
                    'label' => $label,
                    'options' => [],
                ];
            }

            $bySection[$section]['options'][] = [
                'code' => $row['Code'],
                'libelle' => $row['Libelle'],
                'codeTypeclt' => $row['Code_typeclt'],
                'nbEquipes' => (int) $row['Nb_equipes'],
                'qualifies' => (int) $row['Qualifies'],
                'elimines' => (int) $row['Elimines'],
            ];
        }

        ksort($bySection);
        return $this->json(['groups' => array_values($bySection)]);
    }

    /**
     * Copy competition structure (gamedays + matches) from origin to destination
     */
    #[Route('/-copy', name: 'admin_competitions_copy', methods: ['POST'])]
    public function copyCompetition(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $originSeason = trim($data['originSeason'] ?? '');
        $originCompetition = trim($data['originCompetition'] ?? '');
        $destSeason = trim($data['destinationSeason'] ?? '');
        $destCompetition = trim($data['destinationCompetition'] ?? '');

        if (empty($originSeason) || empty($originCompetition) || empty($destSeason) || empty($destCompetition)) {
            return $this->json(['message' => 'Origin and destination are required'], Response::HTTP_BAD_REQUEST);
        }

        // Verify destination competition exists
        $destCheck = $this->connection->prepare(
            "SELECT Code FROM kp_competition WHERE Code = ? AND Code_saison = ?"
        )->executeQuery([$destCompetition, $destSeason])->fetchOne();

        if (!$destCheck) {
            return $this->json(['message' => 'Destination competition not found'], Response::HTTP_NOT_FOUND);
        }

        // Form fields (null = use individual values from origin journee)
        $dateDebut = $data['dateDebut'] ?? null;
        $dateFin = $data['dateFin'] ?? null;
        $nom = $data['nom'] ?? null;
        $libelle = $data['libelle'] ?? null;
        $lieu = $data['lieu'] ?? null;
        $planEau = $data['planEau'] ?? null;
        $departement = $data['departement'] ?? null;
        $responsableInsc = $data['responsableInsc'] ?? null;
        $responsableR1 = $data['responsableR1'] ?? null;
        $organisateur = $data['organisateur'] ?? null;
        $delegue = $data['delegue'] ?? null;
        $initPremierTour = (bool) ($data['initPremierTour'] ?? false);

        // Get origin journees ordered by Id
        $journeeSql = "SELECT Id, Phase, Niveau, Etape, Nbequipes, `Type`,
                               Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, Departement,
                               Responsable_insc, Responsable_R1, Organisateur, Delegue
                        FROM kp_journee
                        WHERE Code_competition = ? AND Code_saison = ?
                        ORDER BY Id";
        $journees = $this->connection->prepare($journeeSql)
            ->executeQuery([$originCompetition, $originSeason])
            ->fetchAllAssociative();

        if (empty($journees)) {
            return $this->json(['message' => 'Origin competition has no gamedays'], Response::HTTP_BAD_REQUEST);
        }

        // Calculate date offset
        // Calculate date offset: diffDays = formDate - originDate (in days)
        $diffDays = 0;
        if ($dateDebut !== null && $dateDebut !== '') {
            $originDate = $journees[0]['Date_debut'] ?? null;
            if ($originDate) {
                $d1 = new \DateTime($dateDebut);
                $d2 = new \DateTime($originDate);
                $interval = $d2->diff($d1); // d1 - d2
                $diffDays = (int) $interval->format('%r%a');
            }
        }

        // Prepare team draw mapping if initPremierTour
        $teamMapping = [];
        if ($initPremierTour) {
            $teamSql = "SELECT Id
                         FROM kp_competition_equipe
                         WHERE Code_compet = ? AND Code_saison = ?
                         ORDER BY Poule, Tirage, Libelle";
            $teams = $this->connection->prepare($teamSql)
                ->executeQuery([$originCompetition, $originSeason])
                ->fetchAllAssociative();

            $pos = 1;
            foreach ($teams as $team) {
                $teamMapping[(int) $team['Id']] = $pos;
                $pos++;
            }
        }

        $this->connection->beginTransaction();
        try {
            $journeesCreated = 0;
            $matchsCreated = 0;

            foreach ($journees as $j) {
                // Get next journee ID
                $nextJourneeId = (int) $this->connection->executeQuery(
                    "SELECT COALESCE(MAX(Id), 0) + 1 FROM kp_journee WHERE Id < 19000001"
                )->fetchOne();

                // Resolve field values: use form value if provided, else origin value
                $jDateDebut = ($dateDebut !== null && $dateDebut !== '') ? $dateDebut : ($j['Date_debut'] ?: null);
                $jDateFin = ($dateFin !== null && $dateFin !== '') ? $dateFin : ($j['Date_fin'] ?: null);
                $jNom = ($nom !== null && $nom !== '') ? $nom : ($j['Nom'] ?: null);
                $jLibelle = ($libelle !== null && $libelle !== '') ? $libelle : ($j['Libelle'] ?: null);
                $jLieu = ($lieu !== null && $lieu !== '') ? $lieu : ($j['Lieu'] ?: null);
                $jPlanEau = ($planEau !== null && $planEau !== '') ? $planEau : ($j['Plan_eau'] ?: null);
                $jDepartement = ($departement !== null && $departement !== '') ? $departement : ($j['Departement'] ?: null);
                $jRespInsc = ($responsableInsc !== null && $responsableInsc !== '') ? $responsableInsc : ($j['Responsable_insc'] ?: null);
                $jRespR1 = ($responsableR1 !== null && $responsableR1 !== '') ? $responsableR1 : ($j['Responsable_R1'] ?: null);
                $jOrganisateur = ($organisateur !== null && $organisateur !== '') ? $organisateur : ($j['Organisateur'] ?: null);
                $jDelegue = ($delegue !== null && $delegue !== '') ? $delegue : ($j['Delegue'] ?: null);

                // Insert new journee
                $insertSql = "INSERT INTO kp_journee
                    (Id, Code_competition, Code_saison, Phase, Niveau, Etape, Nbequipes, `Type`,
                     Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, Departement,
                     Responsable_insc, Responsable_R1, Organisateur, Delegue)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $this->connection->prepare($insertSql)->executeStatement([
                    $nextJourneeId, $destCompetition, $destSeason,
                    $j['Phase'], $j['Niveau'], $j['Etape'], $j['Nbequipes'], $j['Type'],
                    $jDateDebut, $jDateFin, $jNom, $jLibelle, $jLieu, $jPlanEau, $jDepartement,
                    $jRespInsc, $jRespR1, $jOrganisateur, $jDelegue,
                ]);
                $journeesCreated++;

                // Copy matches for this journee
                $matchSql = "SELECT Id_equipeA, Id_equipeB, Libelle, Date_match, Heure_match,
                                    Terrain, Numero_ordre, `Type`
                             FROM kp_match
                             WHERE Id_journee = ?";
                $matches = $this->connection->prepare($matchSql)
                    ->executeQuery([(int) $j['Id']])
                    ->fetchAllAssociative();

                foreach ($matches as $match) {
                    // Determine match label
                    $matchLabel = $match['Libelle'];
                    if ($initPremierTour && (int) $j['Niveau'] <= 1) {
                        $posA = $teamMapping[(int) $match['Id_equipeA']] ?? '?';
                        $posB = $teamMapping[(int) $match['Id_equipeB']] ?? '?';
                        $matchLabel = "[T{$posA}/T{$posB}]";
                    }

                    // Calculate match date with offset
                    $matchDate = $match['Date_match'];
                    if ($diffDays !== 0 && $matchDate) {
                        $dt = new \DateTime($matchDate);
                        $dt->modify("{$diffDays} days");
                        $matchDate = $dt->format('Y-m-d');
                    }

                    $insertMatchSql = "INSERT INTO kp_match
                        (Id_journee, Libelle, Date_match, Heure_match, Terrain, Numero_ordre, `Type`)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

                    $this->connection->prepare($insertMatchSql)->executeStatement([
                        $nextJourneeId,
                        $matchLabel,
                        $matchDate,
                        $match['Heure_match'],
                        $match['Terrain'],
                        $match['Numero_ordre'],
                        $match['Type'],
                    ]);
                    $matchsCreated++;
                }

                $this->logActionForSeason(
                    'Ajout journee',
                    $destSeason,
                    "{$destCompetition}: copie depuis {$originCompetition}/{$originSeason}, Id {$nextJourneeId}"
                );
            }

            $this->connection->commit();

            return $this->json([
                'success' => true,
                'journeesCreated' => $journeesCreated,
                'matchsCreated' => $matchsCreated,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return $this->json([
                'error' => 'La requête ne peut pas être exécutée',
                'detail' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update competition comments
     */
    #[Route('/{season}/{code}/comments', name: 'admin_competitions_update_comments', methods: ['PATCH'])]
    public function updateComments(string $season, string $code, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $commentaires = $data['commentaires'] ?? '';

        $sql = "UPDATE kp_competition SET commentairesCompet = ? WHERE Code = ? AND Code_saison = ?";
        $affected = $this->connection->prepare($sql)->executeStatement([$commentaires, $code, $season]);

        if ($affected === 0) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['success' => true]);
    }
}
