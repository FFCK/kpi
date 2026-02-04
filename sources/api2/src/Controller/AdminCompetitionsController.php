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
 * Admin Competitions Controller
 *
 * CRUD operations for competitions management (kp_competition table)
 * Migrated from GestionCompetition.php
 */
#[Route('/admin/competitions')]
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '25. App4 - Competitions')]
class AdminCompetitionsController extends AbstractController
{
    private const SECTION_LABELS = [
        1 => 'Championnat National',
        2 => 'Coupe de France',
        3 => 'Championnat Régional',
        4 => 'Championnat Départemental',
        5 => 'Autres compétitions',
        6 => 'International',
    ];

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * List all competitions with pagination and filters
     */
    #[Route('', name: 'admin_competitions_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $season = $request->query->get('season', '');
        if (empty($season)) {
            // Get active season
            $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery();
            $season = $result->fetchOne();
            if (!$season) {
                return $this->json(['message' => 'No active season found'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Apply user filters
        /** @var User|null $user */
        $user = $this->getUser();
        $allowedCompetitions = $user?->getAllowedCompetitions();
        $allowedSeasons = $user?->getAllowedSeasons();

        // Check if user can access this season
        if ($allowedSeasons !== null && !in_array($season, $allowedSeasons)) {
            return $this->json(['items' => [], 'total' => 0, 'page' => 1, 'limit' => 20, 'totalPages' => 0]);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 50)));
        $offset = ($page - 1) * $limit;

        // Optional filters
        $search = $request->query->get('search', '');
        $level = $request->query->get('level', ''); // INT, NAT, REG
        $type = $request->query->get('type', ''); // CHPT, CP, MULTI
        $codes = $request->query->get('codes', ''); // Comma-separated competition codes (from work context)
        $sortBy = $request->query->get('sortBy', 'section');
        $sortOrder = strtoupper($request->query->get('sortOrder', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        // Validate sortBy column
        $allowedSortColumns = ['Code', 'Code_niveau', 'Libelle', 'Code_typeclt', 'Statut', 'section', 'ordre'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'section';
        }

        // Build WHERE clause
        $whereConditions = ['c.Code_saison = ?'];
        $params = [$season];

        if (!empty($search)) {
            $whereConditions[] = '(c.Code LIKE ? OR c.Libelle LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($level)) {
            $whereConditions[] = 'c.Code_niveau = ?';
            $params[] = $level;
        }

        if (!empty($type)) {
            $whereConditions[] = 'c.Code_typeclt = ?';
            $params[] = $type;
        }

        // Filter by competition codes (from work context)
        if (!empty($codes)) {
            $codeList = array_filter(array_map('trim', explode(',', $codes)));
            if (count($codeList) > 0) {
                $placeholders = implode(',', array_fill(0, count($codeList), '?'));
                $whereConditions[] = "c.Code IN ($placeholders)";
                $params = array_merge($params, $codeList);
            }
        }

        // Apply competition filter from user
        if ($allowedCompetitions !== null && count($allowedCompetitions) > 0) {
            $placeholders = implode(',', array_fill(0, count($allowedCompetitions), '?'));
            $whereConditions[] = "c.Code IN ($placeholders)";
            $params = array_merge($params, $allowedCompetitions);
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

        // Count total
        $countSql = "SELECT COUNT(*) as total
                     FROM kp_competition c
                     LEFT JOIN kp_groupe g ON c.Code_ref = g.Groupe
                     $whereClause";
        $stmt = $this->connection->prepare($countSql);
        $result = $stmt->executeQuery($params);
        $total = (int) $result->fetchOne();

        // Build ORDER BY
        $orderBy = match ($sortBy) {
            'section' => "COALESCE(g.section, 999) $sortOrder, COALESCE(g.ordre, 999) ASC, c.Code_tour ASC, c.GroupOrder ASC, c.Code ASC",
            'ordre' => "COALESCE(g.ordre, 999) $sortOrder, c.Code ASC",
            default => "$sortBy $sortOrder"
        };

        // Get competitions with counts
        $sql = "SELECT c.Code, c.Code_saison, c.Code_niveau, c.Libelle, c.Soustitre, c.Soustitre2,
                       c.Web, c.BandeauLink, c.LogoLink, c.SponsorLink,
                       c.En_actif, c.Titre_actif, c.Bandeau_actif, c.Logo_actif, c.Sponsor_actif, c.Kpi_ffck_actif,
                       c.Code_ref, c.GroupOrder, c.Code_typeclt, c.Code_tour,
                       c.Qualifies, c.Elimines, c.Points, c.goalaverage, c.Statut, c.Verrou, c.Publication,
                       c.points_grid, c.multi_competitions, c.ranking_structure_type, c.commentairesCompet,
                       g.section, g.ordre,
                       (SELECT COUNT(*) FROM kp_competition_equipe ce WHERE ce.Code_compet = c.Code AND ce.Code_saison = c.Code_saison) as nbEquipes,
                       (SELECT COUNT(*) FROM kp_journee j WHERE j.Code_competition = c.Code AND j.Code_saison = c.Code_saison) as nbJournees,
                       (SELECT COUNT(*) FROM kp_match m
                        INNER JOIN kp_journee j ON m.Id_journee = j.Id
                        WHERE j.Code_competition = c.Code AND j.Code_saison = c.Code_saison) as nbMatchs,
                       (SELECT COUNT(*) FROM kp_rc rc WHERE rc.Code_competition = c.Code AND rc.Code_saison = c.Code_saison) as nbRc
                FROM kp_competition c
                LEFT JOIN kp_groupe g ON c.Code_ref = g.Groupe
                $whereClause
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $competitions = $result->fetchAllAssociative();

        // Format competitions
        $items = array_map(function ($row) use ($season) {
            $section = (int) ($row['section'] ?? 5);
            return [
                'code' => $row['Code'],
                'codeSaison' => $row['Code_saison'],
                'codeNiveau' => $row['Code_niveau'],
                'libelle' => $row['Libelle'],
                'soustitre' => $row['Soustitre'],
                'soustitre2' => $row['Soustitre2'],
                'codeRef' => $row['Code_ref'],
                'groupOrder' => $row['GroupOrder'] ? (int) $row['GroupOrder'] : null,
                'codeTypeclt' => $row['Code_typeclt'],
                'codeTour' => (int) $row['Code_tour'],
                'qualifies' => (int) $row['Qualifies'],
                'elimines' => (int) $row['Elimines'],
                'points' => $row['Points'],
                'goalaverage' => $row['goalaverage'],
                'statut' => $row['Statut'],
                'publication' => $row['Publication'] === 'O',
                'verrou' => $row['Verrou'] === 'O',
                'nbEquipes' => (int) $row['nbEquipes'],
                'nbJournees' => (int) $row['nbJournees'],
                'nbMatchs' => (int) $row['nbMatchs'],
                'hasRc' => (int) $row['nbRc'] > 0,
                'section' => $section,
                'sectionLabel' => self::SECTION_LABELS[$section] ?? 'Autres',
                'web' => $row['Web'],
                'enActif' => $row['En_actif'] === 'O',
                'titreActif' => $row['Titre_actif'] === 'O',
                'bandeauActif' => $row['Bandeau_actif'] === 'O',
                'logoActif' => $row['Logo_actif'] === 'O',
                'sponsorActif' => $row['Sponsor_actif'] === 'O',
                'kpiFfckActif' => $row['Kpi_ffck_actif'] === 'O',
                'bandeauLink' => $this->buildImagePath('B', $row['Code_ref'], $season),
                'logoLink' => $this->buildImagePath('L', $row['Code_ref'], $season),
                'sponsorLink' => $this->buildImagePath('S', $row['Code_ref'], $season),
                'pointsGrid' => $row['points_grid'] ? json_decode($row['points_grid'], true) : null,
                'multiCompetitions' => $row['multi_competitions'] ? json_decode($row['multi_competitions'], true) : null,
                'rankingStructureType' => $row['ranking_structure_type'],
                'commentairesCompet' => $row['commentairesCompet'],
            ];
        }, $competitions);

        return $this->json([
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int) ceil($total / $limit),
            'season' => $season,
        ]);
    }

    /**
     * Get a single competition by code
     */
    #[Route('/{code}', name: 'admin_competitions_get', methods: ['GET'])]
    public function get(string $code, Request $request): JsonResponse
    {
        $season = $request->query->get('season', '');
        if (empty($season)) {
            $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery();
            $season = $result->fetchOne();
        }

        $sql = "SELECT c.*, g.section, g.ordre,
                       (SELECT COUNT(*) FROM kp_competition_equipe ce WHERE ce.Code_compet = c.Code AND ce.Code_saison = c.Code_saison) as nbEquipes,
                       (SELECT COUNT(*) FROM kp_journee j WHERE j.Code_competition = c.Code AND j.Code_saison = c.Code_saison) as nbJournees,
                       (SELECT COUNT(*) FROM kp_match m
                        INNER JOIN kp_journee j ON m.Id_journee = j.Id
                        WHERE j.Code_competition = c.Code AND j.Code_saison = c.Code_saison) as nbMatchs,
                       (SELECT COUNT(*) FROM kp_rc rc WHERE rc.Code_competition = c.Code AND rc.Code_saison = c.Code_saison) as nbRc
                FROM kp_competition c
                LEFT JOIN kp_groupe g ON c.Code_ref = g.Groupe
                WHERE c.Code = ? AND c.Code_saison = ?";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$code, $season]);
        $row = $result->fetchAssociative();

        if (!$row) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        $section = (int) ($row['section'] ?? 5);
        return $this->json([
            'code' => $row['Code'],
            'codeSaison' => $row['Code_saison'],
            'codeNiveau' => $row['Code_niveau'],
            'libelle' => $row['Libelle'],
            'soustitre' => $row['Soustitre'],
            'soustitre2' => $row['Soustitre2'],
            'codeRef' => $row['Code_ref'],
            'groupOrder' => $row['GroupOrder'] ? (int) $row['GroupOrder'] : null,
            'codeTypeclt' => $row['Code_typeclt'],
            'codeTour' => (int) $row['Code_tour'],
            'qualifies' => (int) $row['Qualifies'],
            'elimines' => (int) $row['Elimines'],
            'points' => $row['Points'],
            'goalaverage' => $row['goalaverage'],
            'statut' => $row['Statut'],
            'publication' => $row['Publication'] === 'O',
            'verrou' => $row['Verrou'] === 'O',
            'nbEquipes' => (int) $row['nbEquipes'],
            'nbJournees' => (int) $row['nbJournees'],
            'nbMatchs' => (int) $row['nbMatchs'],
            'hasRc' => (int) $row['nbRc'] > 0,
            'section' => $section,
            'sectionLabel' => self::SECTION_LABELS[$section] ?? 'Autres',
            'web' => $row['Web'],
            'enActif' => $row['En_actif'] === 'O',
            'titreActif' => $row['Titre_actif'] === 'O',
            'bandeauActif' => $row['Bandeau_actif'] === 'O',
            'logoActif' => $row['Logo_actif'] === 'O',
            'sponsorActif' => $row['Sponsor_actif'] === 'O',
            'kpiFfckActif' => $row['Kpi_ffck_actif'] === 'O',
            'bandeauLink' => $this->buildImagePath('B', $row['Code_ref'], $season),
            'logoLink' => $this->buildImagePath('L', $row['Code_ref'], $season),
            'sponsorLink' => $this->buildImagePath('S', $row['Code_ref'], $season),
            'pointsGrid' => $row['points_grid'] ? json_decode($row['points_grid'], true) : null,
            'multiCompetitions' => $row['multi_competitions'] ? json_decode($row['multi_competitions'], true) : null,
            'rankingStructureType' => $row['ranking_structure_type'],
            'commentairesCompet' => $row['commentairesCompet'],
        ]);
    }

    /**
     * Create a new competition (profile <= 3)
     */
    #[Route('', name: 'admin_competitions_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        // Validate required fields
        $code = trim(strtoupper($data['code'] ?? ''));
        if (empty($code)) {
            return $this->json(['message' => 'Code is required'], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($code) > 12) {
            return $this->json(['message' => 'Code must be 12 characters or less'], Response::HTTP_BAD_REQUEST);
        }

        $libelle = trim($data['libelle'] ?? '');
        if (empty($libelle)) {
            return $this->json(['message' => 'Libelle is required'], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($libelle) > 80) {
            return $this->json(['message' => 'Libelle must be 80 characters or less'], Response::HTTP_BAD_REQUEST);
        }

        // Get active season
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $season = $result->fetchOne();
        if (!$season) {
            return $this->json(['message' => 'No active season found'], Response::HTTP_BAD_REQUEST);
        }

        // Check if competition already exists
        $sql = "SELECT Code FROM kp_competition WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$code, $season]);
        if ($result->fetchOne()) {
            return $this->json(['message' => 'Competition code already exists for this season'], Response::HTTP_CONFLICT);
        }

        // Prepare values
        $codeNiveau = $data['codeNiveau'] ?? 'NAT';
        $soustitre = trim($data['soustitre'] ?? '');
        $soustitre2 = trim($data['soustitre2'] ?? '');
        $codeRef = $data['codeRef'] ?: 'AUTRES';
        $groupOrder = isset($data['groupOrder']) && is_numeric($data['groupOrder']) ? (int) $data['groupOrder'] : 0;
        $codeTypeclt = $data['codeTypeclt'] ?? 'CHPT';
        $codeTour = isset($data['codeTour']) ? (int) $data['codeTour'] : 1;
        $qualifies = isset($data['qualifies']) ? (int) $data['qualifies'] : 3;
        $elimines = isset($data['elimines']) ? (int) $data['elimines'] : 0;
        $points = $data['points'] ?? '4-2-1-0';
        $goalaverage = $data['goalaverage'] ?? 'gen';
        $statut = $data['statut'] ?? 'ATT';
        $web = trim($data['web'] ?? '');
        $enActif = ($data['enActif'] ?? true) ? 'O' : 'N';
        $titreActif = ($data['titreActif'] ?? true) ? 'O' : 'N';
        $bandeauActif = ($data['bandeauActif'] ?? true) ? 'O' : 'N';
        $logoActif = ($data['logoActif'] ?? true) ? 'O' : 'N';
        $sponsorActif = ($data['sponsorActif'] ?? true) ? 'O' : 'N';
        $kpiFfckActif = ($data['kpiFfckActif'] ?? true) ? 'O' : 'N';
        $pointsGrid = isset($data['pointsGrid']) ? json_encode($data['pointsGrid']) : null;
        $multiCompetitions = isset($data['multiCompetitions']) && is_array($data['multiCompetitions']) ? json_encode($data['multiCompetitions']) : null;
        $rankingStructureType = $data['rankingStructureType'] ?? 'team';
        $commentairesCompet = trim($data['commentairesCompet'] ?? '');

        // Insert competition
        $sql = "INSERT INTO kp_competition
                (Code, Code_saison, Code_niveau, Libelle, Soustitre, Soustitre2, Web,
                 BandeauLink, LogoLink, SponsorLink, ToutGroup, TouteSaisons,
                 En_actif, Titre_actif, Bandeau_actif, Logo_actif, Sponsor_actif, Kpi_ffck_actif,
                 Code_ref, GroupOrder, Code_typeclt, points_grid, multi_competitions, ranking_structure_type,
                 Code_tour, Qualifies, Elimines, Points, goalaverage, Statut, Publication, Verrou, commentairesCompet)
                VALUES (?, ?, ?, ?, ?, ?, ?, '', '', '', '', '', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'N', '', ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement([
            $code, $season, $codeNiveau, $libelle, $soustitre, $soustitre2, $web,
            $enActif, $titreActif, $bandeauActif, $logoActif, $sponsorActif, $kpiFfckActif,
            $codeRef, $groupOrder, $codeTypeclt, $pointsGrid, $multiCompetitions, $rankingStructureType,
            $codeTour, $qualifies, $elimines, $points, $goalaverage, $statut, $commentairesCompet
        ]);

        // Log action
        $this->logAction('Ajout Compet', $season, $code);

        return $this->json([
            'code' => $code,
            'codeSaison' => $season,
            'message' => 'Competition created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * Update an existing competition (profile <= 3)
     */
    #[Route('/{code}', name: 'admin_competitions_update', methods: ['PUT'])]
    public function update(string $code, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Get active season
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $season = $result->fetchOne();

        // Check if competition exists
        $checkSql = "SELECT Code FROM kp_competition WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($checkSql);
        $result = $stmt->executeQuery([$code, $season]);
        if (!$result->fetchOne()) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Validate
        $libelle = trim($data['libelle'] ?? '');
        if (empty($libelle)) {
            return $this->json(['message' => 'Libelle is required'], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($libelle) > 80) {
            return $this->json(['message' => 'Libelle must be 80 characters or less'], Response::HTTP_BAD_REQUEST);
        }

        // Prepare values
        $codeNiveau = $data['codeNiveau'] ?? 'NAT';
        $soustitre = trim($data['soustitre'] ?? '');
        $soustitre2 = trim($data['soustitre2'] ?? '');
        $codeRef = $data['codeRef'] ?: 'AUTRES';
        $groupOrder = isset($data['groupOrder']) && is_numeric($data['groupOrder']) ? (int) $data['groupOrder'] : 0;
        $codeTypeclt = $data['codeTypeclt'] ?? 'CHPT';
        $codeTour = isset($data['codeTour']) ? (int) $data['codeTour'] : 1;
        $qualifies = isset($data['qualifies']) ? (int) $data['qualifies'] : 3;
        $elimines = isset($data['elimines']) ? (int) $data['elimines'] : 0;
        $points = $data['points'] ?? '4-2-1-0';
        $goalaverage = $data['goalaverage'] ?? 'gen';
        $statut = $data['statut'] ?? 'ATT';
        $web = trim($data['web'] ?? '');
        $enActif = ($data['enActif'] ?? true) ? 'O' : 'N';
        $titreActif = ($data['titreActif'] ?? true) ? 'O' : 'N';
        $bandeauActif = ($data['bandeauActif'] ?? true) ? 'O' : 'N';
        $logoActif = ($data['logoActif'] ?? true) ? 'O' : 'N';
        $sponsorActif = ($data['sponsorActif'] ?? true) ? 'O' : 'N';
        $kpiFfckActif = ($data['kpiFfckActif'] ?? true) ? 'O' : 'N';
        $pointsGrid = isset($data['pointsGrid']) ? json_encode($data['pointsGrid']) : null;
        $multiCompetitions = isset($data['multiCompetitions']) && is_array($data['multiCompetitions']) ? json_encode($data['multiCompetitions']) : null;
        $rankingStructureType = $data['rankingStructureType'] ?? 'team';
        $commentairesCompet = trim($data['commentairesCompet'] ?? '');
        $publication = ($data['publication'] ?? false) ? 'O' : 'N';

        // Update competition
        $sql = "UPDATE kp_competition SET
                Code_niveau = ?, Libelle = ?, Soustitre = ?, Soustitre2 = ?, Web = ?,
                En_actif = ?, Titre_actif = ?, Bandeau_actif = ?, Logo_actif = ?, Sponsor_actif = ?, Kpi_ffck_actif = ?,
                Code_ref = ?, GroupOrder = ?, Code_typeclt = ?, points_grid = ?, multi_competitions = ?, ranking_structure_type = ?,
                Code_tour = ?, Qualifies = ?, Elimines = ?, Points = ?, goalaverage = ?, Statut = ?, Publication = ?, commentairesCompet = ?
                WHERE Code = ? AND Code_saison = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement([
            $codeNiveau, $libelle, $soustitre, $soustitre2, $web,
            $enActif, $titreActif, $bandeauActif, $logoActif, $sponsorActif, $kpiFfckActif,
            $codeRef, $groupOrder, $codeTypeclt, $pointsGrid, $multiCompetitions, $rankingStructureType,
            $codeTour, $qualifies, $elimines, $points, $goalaverage, $statut, $publication, $commentairesCompet,
            $code, $season
        ]);

        // Log action
        $this->logAction('Modif Competition', $season, $code);

        return $this->json([
            'code' => $code,
            'codeSaison' => $season,
            'message' => 'Competition updated successfully'
        ]);
    }

    /**
     * Delete a competition (profile <= 2, Super Admin)
     */
    #[Route('/{code}', name: 'admin_competitions_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(string $code): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 2) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Get active season
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $season = $result->fetchOne();

        // Check if competition exists
        $checkSql = "SELECT Code FROM kp_competition WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($checkSql);
        $result = $stmt->executeQuery([$code, $season]);
        if (!$result->fetchOne()) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if there are teams
        $sql = "SELECT COUNT(*) FROM kp_competition_equipe WHERE Code_compet = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$code, $season]);
        if ((int) $result->fetchOne() > 0) {
            return $this->json(['message' => 'Cannot delete: competition has teams'], Response::HTTP_CONFLICT);
        }

        // Check if there are gamedays/phases
        $sql = "SELECT COUNT(*) FROM kp_journee WHERE Code_competition = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$code, $season]);
        if ((int) $result->fetchOne() > 0) {
            return $this->json(['message' => 'Cannot delete: competition has gamedays'], Response::HTTP_CONFLICT);
        }

        // Check if there are matches (through journées)
        $sql = "SELECT COUNT(*) FROM kp_match m
                INNER JOIN kp_journee j ON m.Id_journee = j.Id
                WHERE j.Code_competition = ? AND j.Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$code, $season]);
        if ((int) $result->fetchOne() > 0) {
            return $this->json(['message' => 'Cannot delete: competition has matches'], Response::HTTP_CONFLICT);
        }

        try {
            $sql = "DELETE FROM kp_competition WHERE Code = ? AND Code_saison = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$code, $season]);

            // Log action
            $this->logAction('Suppression Compet', $season, $code);

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception) {
            return $this->json([
                'message' => 'Cannot delete competition: it may have related data'
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Delete multiple competitions (profile <= 2, Super Admin)
     */
    #[Route('/bulk-delete', name: 'admin_competitions_bulk_delete', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function bulkDelete(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 2) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $codes = $data['codes'] ?? [];

        if (empty($codes) || !is_array($codes)) {
            return $this->json(['message' => 'No competition codes provided'], Response::HTTP_BAD_REQUEST);
        }

        // Get active season
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $season = $result->fetchOne();

        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $params = array_merge($codes, [$season]);

        // Check for teams
        $sql = "SELECT Code_compet FROM kp_competition_equipe WHERE Code_compet IN ($placeholders) AND Code_saison = ? GROUP BY Code_compet";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $competitionsWithTeams = $result->fetchFirstColumn();

        if (count($competitionsWithTeams) > 0) {
            return $this->json([
                'message' => 'Cannot delete: some competitions have teams',
                'blocked' => $competitionsWithTeams
            ], Response::HTTP_CONFLICT);
        }

        // Check for gamedays/phases
        $sql = "SELECT Code_competition FROM kp_journee WHERE Code_competition IN ($placeholders) AND Code_saison = ? GROUP BY Code_competition";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $competitionsWithGamedays = $result->fetchFirstColumn();

        if (count($competitionsWithGamedays) > 0) {
            return $this->json([
                'message' => 'Cannot delete: some competitions have gamedays',
                'blocked' => $competitionsWithGamedays
            ], Response::HTTP_CONFLICT);
        }

        // Check for matches
        $sql = "SELECT DISTINCT j.Code_competition FROM kp_match m
                INNER JOIN kp_journee j ON m.Id_journee = j.Id
                WHERE j.Code_competition IN ($placeholders) AND j.Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);
        $competitionsWithMatches = $result->fetchFirstColumn();

        if (count($competitionsWithMatches) > 0) {
            return $this->json([
                'message' => 'Cannot delete: some competitions have matches',
                'blocked' => $competitionsWithMatches
            ], Response::HTTP_CONFLICT);
        }

        try {
            $sql = "DELETE FROM kp_competition WHERE Code IN ($placeholders) AND Code_saison = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement($params);

            // Log action
            $this->logAction('Suppression Compets', $season, implode(',', $codes));

            return $this->json(['deleted' => count($codes)]);
        } catch (\Exception) {
            return $this->json([
                'message' => 'Cannot delete some competitions: they may have related data'
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Toggle competition publication status (profile <= 4)
     */
    #[Route('/{code}/publish', name: 'admin_competitions_toggle_publish', methods: ['PATCH'])]
    public function togglePublish(string $code): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Get active season
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $season = $result->fetchOne();

        // Get current value
        $sql = "SELECT Publication FROM kp_competition WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$code, $season]);
        $current = $result->fetchOne();

        if ($current === false) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        $newValue = $current === 'O' ? 'N' : 'O';

        $updateSql = "UPDATE kp_competition SET Publication = ? WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($updateSql);
        $stmt->executeStatement([$newValue, $code, $season]);

        // Log action
        $this->logAction('Publication competition', $season, "$code: $newValue");

        return $this->json([
            'code' => $code,
            'publication' => $newValue === 'O',
        ]);
    }

    /**
     * Toggle competition lock status (profile <= 3)
     */
    #[Route('/{code}/lock', name: 'admin_competitions_toggle_lock', methods: ['PATCH'])]
    public function toggleLock(string $code): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Get active season
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $season = $result->fetchOne();

        // Get current value
        $sql = "SELECT Verrou FROM kp_competition WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$code, $season]);
        $current = $result->fetchOne();

        if ($current === false) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        $newValue = $current === 'O' ? '' : 'O';

        $updateSql = "UPDATE kp_competition SET Verrou = ? WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($updateSql);
        $stmt->executeStatement([$newValue, $code, $season]);

        // Log action
        $this->logAction('Verrou Compet', $season, "$code: $newValue");

        return $this->json([
            'code' => $code,
            'verrou' => $newValue === 'O',
        ]);
    }

    /**
     * Change competition status (profile <= 3)
     */
    #[Route('/{code}/status', name: 'admin_competitions_change_status', methods: ['PATCH'])]
    public function changeStatus(string $code, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $newStatus = $data['statut'] ?? '';

        if (!in_array($newStatus, ['ATT', 'ON', 'END'])) {
            return $this->json(['message' => 'Invalid status. Must be ATT, ON or END'], Response::HTTP_BAD_REQUEST);
        }

        // Get active season
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $season = $result->fetchOne();

        // Check if competition exists
        $sql = "SELECT Code FROM kp_competition WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$code, $season]);
        if (!$result->fetchOne()) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        $updateSql = "UPDATE kp_competition SET Statut = ? WHERE Code = ? AND Code_saison = ?";
        $stmt = $this->connection->prepare($updateSql);
        $stmt->executeStatement([$newStatus, $code, $season]);

        // Log action
        $this->logAction('Statut Competition', $season, "$code: $newStatus");

        return $this->json([
            'code' => $code,
            'statut' => $newStatus,
        ]);
    }

    /**
     * List all groups for select dropdown
     */
    #[Route('-groups', name: 'admin_competitions_groups', methods: ['GET'])]
    public function listGroups(): JsonResponse
    {
        $sql = "SELECT id, Groupe, Libelle, Code_niveau, section, ordre
                FROM kp_groupe
                ORDER BY section, ordre, Groupe";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $groups = $result->fetchAllAssociative();

        $items = array_map(function ($row) {
            return [
                'id' => (int) $row['id'],
                'groupe' => $row['Groupe'],
                'libelle' => $row['Libelle'],
                'libelleEn' => null, // Not stored in DB
                'section' => (int) $row['section'],
                'ordre' => (int) $row['ordre'],
                'codeNiveau' => $row['Code_niveau'],
            ];
        }, $groups);

        return $this->json($items);
    }

    /**
     * List competitions for MULTI select (non-MULTI competitions)
     */
    #[Route('-for-multi', name: 'admin_competitions_for_multi', methods: ['GET'])]
    public function listForMulti(Request $request): JsonResponse
    {
        $season = $request->query->get('season', '');
        if (empty($season)) {
            $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery();
            $season = $result->fetchOne();
        }

        $sql = "SELECT c.Code, c.Libelle, c.Code_typeclt, c.Code_tour, c.GroupOrder,
                       g.section, g.ordre, g.Groupe as GroupeLibelle
                FROM kp_competition c
                LEFT JOIN kp_groupe g ON c.Code_ref = g.Groupe
                WHERE c.Code_saison = ?
                AND c.Code_typeclt != 'MULTI'
                ORDER BY
                    COALESCE(g.section, 999),
                    COALESCE(g.ordre, 999),
                    COALESCE(c.Code_tour, 999),
                    COALESCE(c.GroupOrder, 999),
                    c.Libelle";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$season]);
        $competitions = $result->fetchAllAssociative();

        // Group by section
        $bySection = [];
        foreach ($competitions as $row) {
            $section = (int) ($row['section'] ?? 5);
            $sectionLabel = self::SECTION_LABELS[$section] ?? 'Autres';

            if (!isset($bySection[$section])) {
                $bySection[$section] = [
                    'section' => $section,
                    'sectionLabel' => $sectionLabel,
                    'competitions' => []
                ];
            }

            $bySection[$section]['competitions'][] = [
                'code' => $row['Code'],
                'libelle' => $row['Libelle'],
                'type' => $row['Code_typeclt'],
                'tour' => $row['Code_tour'] ? (int) $row['Code_tour'] : null,
                'groupOrder' => $row['GroupOrder'] ? (int) $row['GroupOrder'] : null,
                'section' => $section,
                'sectionLabel' => $sectionLabel,
            ];
        }

        // Sort by section number and return as array
        ksort($bySection);
        return $this->json(array_values($bySection));
    }

    /**
     * Build image path for competition
     */
    private function buildImagePath(string $type, ?string $codeRef, string $season): ?string
    {
        if (empty($codeRef)) {
            return null;
        }
        return "/img/logo/{$type}-{$codeRef}-{$season}.jpg";
    }

    /**
     * Log admin action to journal table
     */
    private function logAction(string $action, ?string $season = null, ?string $details = null): void
    {
        try {
            $user = $this->getUser();
            $userId = $user?->getUserIdentifier() ?? 'system';

            $sql = "INSERT INTO kp_journal (Date, Heure, User, Action, Code_saison, Details)
                    VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?)";

            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$userId, $action, $season, $details]);
        } catch (\Exception) {
            // Log silently fails - don't break the main operation
        }
    }
}
