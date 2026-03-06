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
 * Admin Rankings Controller
 *
 * Ranking computation, publication, inline edit, consolidation, and team transfer.
 * Migrated from GestionClassement.php and GestionClassementInit.php
 */
#[Route('/admin/rankings')]
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: '30. App4 - Rankings')]
class AdminRankingsController extends AbstractController
{
    use AdminLoggableTrait;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    // ─────────────────────────────────────────────
    // 1. GET /admin/rankings — Read ranking data
    // ─────────────────────────────────────────────

    #[Route('', name: 'admin_rankings_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $season = $request->query->get('season', '');
        $competition = $request->query->get('competition', '');
        $typeOverride = $request->query->get('type', '');

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        $allowedCompetitions = $user?->getAllowedCompetitions();
        if ($allowedCompetitions !== null && !in_array($competition, $allowedCompetitions)) {
            return $this->json(['message' => 'Access denied to this competition'], Response::HTTP_FORBIDDEN);
        }

        // Load competition info
        $compInfo = $this->loadCompetitionInfo($competition, $season);
        if (!$compInfo) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }

        $effectiveType = !empty($typeOverride) && in_array($typeOverride, ['CHPT', 'CP', 'MULTI'])
            ? $typeOverride
            : $compInfo['codeTypeclt'];

        // Types list
        $types = [
            ['code' => 'CHPT', 'label' => 'Championnat', 'selected' => $effectiveType === 'CHPT'],
            ['code' => 'CP', 'label' => 'Tournoi à élimination', 'selected' => $effectiveType === 'CP'],
            ['code' => 'MULTI', 'label' => 'Multi-Compétition', 'selected' => $effectiveType === 'MULTI'],
        ];

        // Load teams ranking
        $ranking = $this->loadRanking($competition, $season, $effectiveType);

        // Load phases (CP only)
        $phases = [];
        if ($effectiveType === 'CP') {
            $phases = $this->loadPhases($competition, $season);
        }

        return $this->json([
            'competition' => $compInfo,
            'types' => $types,
            'ranking' => $ranking,
            'phases' => $phases,
        ]);
    }

    // ─────────────────────────────────────────────
    // 2. POST /admin/rankings/compute — Recalculate
    // ─────────────────────────────────────────────

    #[Route('/compute', name: 'admin_rankings_compute', methods: ['POST'])]
    public function compute(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $niveau = $user ? $user->getNiveau() : 99;

        if ($niveau > 6 && $niveau !== 9) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $season = $data['season'] ?? '';
        $competition = $data['competition'] ?? '';
        $includeUnlocked = (bool)($data['includeUnlocked'] ?? false);

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        // Check competition status = ON
        $compRow = $this->getCompetitionRow($competition, $season);
        if (!$compRow) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }
        if ($compRow['Statut'] !== 'ON') {
            return $this->json(['message' => 'Competition must be ON to recalculate'], Response::HTTP_FORBIDDEN);
        }

        $this->connection->beginTransaction();
        try {
            $type = $compRow['Code_typeclt'] ?: 'CHPT';
            $pointsStr = $compRow['Points'] ?: '4-2-1-0';
            $goalaverage = $compRow['goalaverage'] ?: 'gen';

            if ($type === 'MULTI') {
                $this->calculateMulti($competition, $season, $compRow);
            } else {
                // 1. RAZ
                $this->razRanking($competition, $season);
                $this->razJourneeRanking($competition, $season);
                $this->razNiveauRanking($competition, $season);

                // 2. Apply initial values
                $this->applyInitialValues($competition, $season);

                // 3. Process matches
                $this->processMatches($competition, $season, $includeUnlocked, $pointsStr);

                // 4. Finalize rankings (all types: CHPT and CP)
                $this->finalizeChptRanking($competition, $season, $goalaverage);
                $this->finalizeNiveauRanking($competition, $season);
                $this->finalizeNiveauNiveauRanking($competition, $season);
                $this->finalizeJourneeChptRanking($competition, $season, $goalaverage);
                $this->finalizeJourneeNiveauRanking($competition, $season);
            }

            // 5. Update metadata
            $mode = $includeUnlocked ? 'tous' : 'verr';
            $userCode = $user ? $user->getCode() : '';
            $sql = "UPDATE kp_competition
                    SET Date_calcul = NOW(), Mode_calcul = ?, Code_uti_calcul = ?
                    WHERE Code = ? AND Code_saison = ?";
            $this->connection->prepare($sql)->executeStatement([$mode, $userCode, $competition, $season]);

            $this->connection->commit();

            $this->logActionForCompetition('Calcul Classement', $season, $competition, "mode=$mode");
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Calculation error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return refreshed data
        return $this->list($request->duplicate(query: [
            'season' => $season,
            'competition' => $competition,
        ]));
    }

    // ─────────────────────────────────────────────
    // 3. POST /admin/rankings/publish — Publish
    // ─────────────────────────────────────────────

    #[Route('/publish', name: 'admin_rankings_publish', methods: ['POST'])]
    public function publish(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $season = $data['season'] ?? '';
        $competition = $data['competition'] ?? '';

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        $compRow = $this->getCompetitionRow($competition, $season);
        if (!$compRow) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }
        if ($compRow['Statut'] !== 'ON') {
            return $this->json(['message' => 'Competition must be ON to publish'], Response::HTTP_FORBIDDEN);
        }

        $this->connection->beginTransaction();
        try {
            $userCode = $user->getCode();

            // Update publication metadata
            $sql = "UPDATE kp_competition
                    SET Date_publication = NOW(),
                        Date_publication_calcul = Date_calcul,
                        Code_uti_publication = ?,
                        Mode_publication_calcul = Mode_calcul
                    WHERE Code = ? AND Code_saison = ?";
            $this->connection->prepare($sql)->executeStatement([$userCode, $competition, $season]);

            // Copy computed → published in kp_competition_equipe
            $sql = "UPDATE kp_competition_equipe
                    SET Pts_publi = Pts, Clt_publi = Clt, J_publi = J, G_publi = G,
                        N_publi = N, P_publi = P, F_publi = F, Plus_publi = Plus,
                        Moins_publi = Moins, Diff_publi = Diff,
                        PtsNiveau_publi = PtsNiveau, CltNiveau_publi = CltNiveau
                    WHERE Code_compet = ? AND Code_saison = ?";
            $this->connection->prepare($sql)->executeStatement([$competition, $season]);

            // Copy computed → published in kp_competition_equipe_journee
            $sql = "UPDATE kp_competition_equipe_journee a
                    INNER JOIN kp_competition_equipe b ON a.Id = b.Id
                    INNER JOIN kp_journee c ON c.Id = a.Id_journee
                    SET a.Pts_publi = a.Pts, a.Clt_publi = a.Clt, a.J_publi = a.J,
                        a.G_publi = a.G, a.N_publi = a.N, a.P_publi = a.P,
                        a.F_publi = a.F, a.Plus_publi = a.Plus, a.Moins_publi = a.Moins,
                        a.Diff_publi = a.Diff, a.PtsNiveau_publi = a.PtsNiveau,
                        a.CltNiveau_publi = a.CltNiveau
                    WHERE c.Code_competition = ? AND c.Code_saison = ?";
            $this->connection->prepare($sql)->executeStatement([$competition, $season]);

            // Copy computed → published in kp_competition_equipe_niveau
            $sql = "UPDATE kp_competition_equipe_niveau a
                    INNER JOIN kp_competition_equipe b ON a.Id = b.Id
                    SET a.Pts_publi = a.Pts, a.Clt_publi = a.Clt, a.J_publi = a.J,
                        a.G_publi = a.G, a.N_publi = a.N, a.P_publi = a.P,
                        a.F_publi = a.F, a.Plus_publi = a.Plus, a.Moins_publi = a.Moins,
                        a.Diff_publi = a.Diff, a.PtsNiveau_publi = a.PtsNiveau,
                        a.CltNiveau_publi = a.CltNiveau
                    WHERE b.Code_compet = ? AND b.Code_saison = ?";
            $this->connection->prepare($sql)->executeStatement([$competition, $season]);

            $this->connection->commit();

            $this->logActionForCompetition('Publication Classement', $season, $competition, null);
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Publication error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    // 4. DELETE /admin/rankings/publish — Unpublish
    // ─────────────────────────────────────────────

    #[Route('/publish', name: 'admin_rankings_unpublish', methods: ['DELETE'])]
    public function unpublish(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $season = $data['season'] ?? '';
        $competition = $data['competition'] ?? '';

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        $compRow = $this->getCompetitionRow($competition, $season);
        if (!$compRow) {
            return $this->json(['message' => 'Competition not found'], Response::HTTP_NOT_FOUND);
        }
        if ($compRow['Statut'] !== 'ON') {
            return $this->json(['message' => 'Competition must be ON to unpublish'], Response::HTTP_FORBIDDEN);
        }

        $this->connection->beginTransaction();
        try {
            $userCode = $user->getCode();

            // Reset publication metadata
            $sql = "UPDATE kp_competition
                    SET Date_publication = '0000-00-00 00:00:00',
                        Date_publication_calcul = '0000-00-00 00:00:00',
                        Code_uti_publication = '',
                        Mode_publication_calcul = NULL
                    WHERE Code = ? AND Code_saison = ?";
            $this->connection->prepare($sql)->executeStatement([$competition, $season]);

            // Reset published rankings in kp_competition_equipe
            $sql = "UPDATE kp_competition_equipe
                    SET Clt_publi = 0, CltNiveau_publi = 0,
                        Pts_publi = 0, J_publi = 0, G_publi = 0, N_publi = 0,
                        P_publi = 0, F_publi = 0, Plus_publi = 0, Moins_publi = 0,
                        Diff_publi = 0, PtsNiveau_publi = 0
                    WHERE Code_compet = ? AND Code_saison = ?";
            $this->connection->prepare($sql)->executeStatement([$competition, $season]);

            // Delete journee published data
            $sql = "DELETE cej FROM kp_competition_equipe_journee cej
                    INNER JOIN kp_journee j ON j.Id = cej.Id_journee
                    WHERE j.Code_competition = ? AND j.Code_saison = ?";
            $this->connection->prepare($sql)->executeStatement([$competition, $season]);

            // Delete niveau published data
            $sql = "DELETE cen FROM kp_competition_equipe_niveau cen
                    INNER JOIN kp_competition_equipe ce ON cen.Id = ce.Id
                    WHERE ce.Code_compet = ? AND ce.Code_saison = ?";
            $this->connection->prepare($sql)->executeStatement([$competition, $season]);

            $this->connection->commit();

            $this->logActionForCompetition('Publication Classement RAZ', $season, $competition, null);
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Unpublication error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // ─────────────────────────────────────────────
    // 5. PATCH /admin/rankings/{teamId}/inline
    // ─────────────────────────────────────────────

    #[Route('/{teamId}/inline', name: 'admin_rankings_inline', methods: ['PATCH'])]
    public function inlineEdit(int $teamId, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $field = $data['field'] ?? '';
        $value = $data['value'] ?? 0;
        $journeeId = $data['journeeId'] ?? null;

        $allowedFields = ['Clt', 'Pts', 'J', 'G', 'N', 'P', 'F', 'Plus', 'Moins', 'Diff', 'CltNiveau', 'PtsNiveau'];
        if (!in_array($field, $allowedFields)) {
            return $this->json(['message' => 'Invalid field'], Response::HTTP_BAD_REQUEST);
        }

        // Get team to check competition status
        $sql = "SELECT ce.Code_compet, ce.Code_saison FROM kp_competition_equipe ce WHERE ce.Id = ?";
        $row = $this->connection->prepare($sql)->executeQuery([$teamId])->fetchAssociative();
        if (!$row) {
            return $this->json(['message' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        $compRow = $this->getCompetitionRow($row['Code_compet'], $row['Code_saison']);
        if (!$compRow || $compRow['Statut'] !== 'ON') {
            return $this->json(['message' => 'Competition must be ON to edit rankings'], Response::HTTP_FORBIDDEN);
        }

        if ($journeeId !== null) {
            // Check phase is not consolidated
            $sql = "SELECT Consolidation FROM kp_journee WHERE Id = ?";
            $jRow = $this->connection->prepare($sql)->executeQuery([(int)$journeeId])->fetchAssociative();
            if ($jRow && $jRow['Consolidation'] === 'O') {
                return $this->json(['message' => 'Cannot edit a consolidated phase'], Response::HTTP_FORBIDDEN);
            }

            // Update kp_competition_equipe_journee
            $sql = "UPDATE kp_competition_equipe_journee SET `$field` = ? WHERE Id = ? AND Id_journee = ?";
            $this->connection->prepare($sql)->executeStatement([$value, $teamId, (int)$journeeId]);
        } else {
            // Update kp_competition_equipe
            $sql = "UPDATE kp_competition_equipe SET `$field` = ? WHERE Id = ?";
            $this->connection->prepare($sql)->executeStatement([$value, $teamId]);
        }

        return $this->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    // 6. PATCH /admin/rankings/consolidation/{journeeId}
    // ─────────────────────────────────────────────

    #[Route('/consolidation/{journeeId}', name: 'admin_rankings_consolidation', methods: ['PATCH'])]
    public function toggleConsolidation(int $journeeId, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $consolidation = (bool)($data['consolidation'] ?? false);

        // Check journee exists and competition is ON
        $sql = "SELECT j.Id, j.Code_competition, j.Code_saison, c.Statut
                FROM kp_journee j
                INNER JOIN kp_competition c ON c.Code = j.Code_competition AND c.Code_saison = j.Code_saison
                WHERE j.Id = ?";
        $row = $this->connection->prepare($sql)->executeQuery([$journeeId])->fetchAssociative();
        if (!$row) {
            return $this->json(['message' => 'Phase not found'], Response::HTTP_NOT_FOUND);
        }
        if ($row['Statut'] !== 'ON') {
            return $this->json(['message' => 'Competition must be ON'], Response::HTTP_FORBIDDEN);
        }

        $val = $consolidation ? 'O' : null;
        $sql = "UPDATE kp_journee SET Consolidation = ? WHERE Id = ?";
        $this->connection->prepare($sql)->executeStatement([$val, $journeeId]);

        $this->logActionForCompetition(
            'Consolidation Phase',
            $row['Code_saison'],
            $row['Code_competition'],
            "journee=$journeeId, consolidation=" . ($consolidation ? 'O' : 'N')
        );

        return $this->json(['success' => true, 'consolidation' => $consolidation]);
    }

    // ─────────────────────────────────────────────
    // 7. DELETE /admin/rankings/phase-team/{journeeId}/{teamId}
    // ─────────────────────────────────────────────

    #[Route('/phase-team/{journeeId}/{teamId}', name: 'admin_rankings_phase_team_delete', methods: ['DELETE'])]
    public function deletePhaseTeam(int $journeeId, int $teamId): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Check team has 0 matches in this phase
        $sql = "SELECT cej.J FROM kp_competition_equipe_journee cej WHERE cej.Id = ? AND cej.Id_journee = ?";
        $row = $this->connection->prepare($sql)->executeQuery([$teamId, $journeeId])->fetchAssociative();
        if (!$row) {
            return $this->json(['message' => 'Team not found in this phase'], Response::HTTP_NOT_FOUND);
        }
        if ((int)$row['J'] > 0) {
            return $this->json(['message' => 'Cannot remove a team with played matches'], Response::HTTP_CONFLICT);
        }

        // Check competition is ON
        $sql = "SELECT j.Code_competition, j.Code_saison, c.Statut
                FROM kp_journee j
                INNER JOIN kp_competition c ON c.Code = j.Code_competition AND c.Code_saison = j.Code_saison
                WHERE j.Id = ?";
        $jRow = $this->connection->prepare($sql)->executeQuery([$journeeId])->fetchAssociative();
        if (!$jRow || $jRow['Statut'] !== 'ON') {
            return $this->json(['message' => 'Competition must be ON'], Response::HTTP_FORBIDDEN);
        }

        $sql = "DELETE FROM kp_competition_equipe_journee WHERE Id = ? AND Id_journee = ?";
        $this->connection->prepare($sql)->executeStatement([$teamId, $journeeId]);

        // Also delete niveau entry for this team at this phase's niveau
        $sql = "SELECT Niveau FROM kp_journee WHERE Id = ?";
        $nRow = $this->connection->prepare($sql)->executeQuery([$journeeId])->fetchAssociative();
        if ($nRow && $nRow['Niveau'] !== null) {
            $sql = "DELETE FROM kp_competition_equipe_niveau WHERE Id = ? AND Niveau = ?";
            $this->connection->prepare($sql)->executeStatement([$teamId, (int)$nRow['Niveau']]);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // ─────────────────────────────────────────────
    // 8. POST /admin/rankings/transfer
    // ─────────────────────────────────────────────

    #[Route('/transfer', name: 'admin_rankings_transfer', methods: ['POST'])]
    public function transfer(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $teamIds = $data['teamIds'] ?? [];
        $targetSeason = $data['targetSeason'] ?? '';
        $targetCompetition = $data['targetCompetition'] ?? '';

        if (empty($teamIds) || empty($targetSeason) || empty($targetCompetition)) {
            return $this->json(['message' => 'teamIds, targetSeason and targetCompetition are required'], Response::HTTP_BAD_REQUEST);
        }

        // Verify target competition exists
        $targetRow = $this->getCompetitionRow($targetCompetition, $targetSeason);
        if (!$targetRow) {
            return $this->json(['message' => 'Target competition not found'], Response::HTTP_NOT_FOUND);
        }

        $this->connection->beginTransaction();
        try {
            $details = [];
            $transferred = 0;
            $skipped = 0;

            // Get target season year for age category calculation
            $targetYear = (int) $targetSeason;

            foreach ($teamIds as $teamId) {
                $teamId = (int) $teamId;

                // Get source team
                $sql = "SELECT Id, Libelle, Code_club, Numero, Code_compet, Code_saison
                        FROM kp_competition_equipe WHERE Id = ?";
                $srcTeam = $this->connection->prepare($sql)->executeQuery([$teamId])->fetchAssociative();
                if (!$srcTeam) {
                    continue;
                }

                // Check source != target
                if ($srcTeam['Code_compet'] === $targetCompetition && $srcTeam['Code_saison'] === $targetSeason) {
                    $details[] = ['teamId' => $teamId, 'libelle' => $srcTeam['Libelle'], 'status' => 'skipped'];
                    $skipped++;
                    continue;
                }

                // Check if team already exists in target (by Numero)
                $sql = "SELECT Id FROM kp_competition_equipe
                        WHERE Code_compet = ? AND Code_saison = ? AND Numero = ?";
                $existing = $this->connection->prepare($sql)
                    ->executeQuery([$targetCompetition, $targetSeason, $srcTeam['Numero']])
                    ->fetchAssociative();

                if ($existing) {
                    $details[] = ['teamId' => $teamId, 'libelle' => $srcTeam['Libelle'], 'status' => 'skipped'];
                    $skipped++;
                    continue;
                }

                // Clear previous Id_dupli pointing to this team
                $sql = "UPDATE kp_competition_equipe SET Id_dupli = NULL WHERE Id_dupli = ?";
                $this->connection->prepare($sql)->executeStatement([$teamId]);

                // Insert new team
                $sql = "INSERT INTO kp_competition_equipe (Code_compet, Code_saison, Libelle, Code_club, Numero, Id_dupli)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $this->connection->prepare($sql)->executeStatement([
                    $targetCompetition, $targetSeason,
                    $srcTeam['Libelle'], $srcTeam['Code_club'], $srcTeam['Numero'], $teamId
                ]);
                $newId = (int) $this->connection->lastInsertId();

                // Copy players with age recalculation
                $sql = "INSERT INTO kp_competition_equipe_joueur
                            (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine)
                        SELECT ?, a.Matric, a.Nom, a.Prenom, a.Sexe,
                               COALESCE(d.id, a.Categ), a.Numero, a.Capitaine
                        FROM kp_competition_equipe_joueur a
                        LEFT JOIN kp_licence e ON a.Matric = e.Matric
                        LEFT JOIN kp_categorie d ON (? - YEAR(e.Naissance)) BETWEEN d.age_min AND d.age_max
                                                    AND (d.sexe = '' OR d.sexe = a.Sexe)
                        WHERE a.Id_equipe = ?";
                $this->connection->prepare($sql)->executeStatement([$newId, $targetYear, $teamId]);

                $details[] = ['teamId' => $teamId, 'libelle' => $srcTeam['Libelle'], 'status' => 'created', 'newId' => $newId];
                $transferred++;
            }

            $this->connection->commit();

            // Log
            $srcCodes = implode(',', $teamIds);
            $this->logActionForCompetition(
                'Transfert Equipes',
                $targetSeason,
                $targetCompetition,
                "teams=$srcCodes, transferred=$transferred, skipped=$skipped"
            );

            return $this->json([
                'transferred' => $transferred,
                'skipped' => $skipped,
                'details' => $details,
            ]);
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            return $this->json(['message' => 'Transfer error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ─────────────────────────────────────────────
    // 9. GET /admin/rankings/transfer-competitions
    // ─────────────────────────────────────────────

    #[Route('/transfer-competitions', name: 'admin_rankings_transfer_competitions', methods: ['GET'])]
    public function transferCompetitions(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 4) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $season = $request->query->get('season', '');
        if (empty($season)) {
            return $this->json(['message' => 'Season is required'], Response::HTTP_BAD_REQUEST);
        }

        $sql = "SELECT Code, Libelle FROM kp_competition WHERE Code_saison = ? ORDER BY Code ASC";
        $rows = $this->connection->prepare($sql)->executeQuery([$season])->fetchAllAssociative();

        $result = array_map(fn($r) => ['code' => $r['Code'], 'libelle' => $r['Libelle']], $rows);

        return $this->json($result);
    }

    // ─────────────────────────────────────────────
    // 10. GET /admin/rankings/initial — Read initial ranking
    // ─────────────────────────────────────────────

    #[Route('/initial', name: 'admin_rankings_initial_list', methods: ['GET'])]
    public function initialList(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 6) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $season = $request->query->get('season', '');
        $competition = $request->query->get('competition', '');

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        // Ensure all teams have init rows (insert missing ones with zeros)
        $sql = "INSERT IGNORE INTO kp_competition_equipe_init (Id, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff)
                SELECT ce.Id, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
                FROM kp_competition_equipe ce
                WHERE ce.Code_compet = ? AND ce.Code_saison = ?
                AND NOT EXISTS (SELECT 1 FROM kp_competition_equipe_init i WHERE i.Id = ce.Id)";
        $this->connection->prepare($sql)->executeStatement([$competition, $season]);

        // Load data
        $sql = "SELECT ce.Id, ce.Libelle,
                       COALESCE(i.Clt, 0) AS Clt, COALESCE(i.Pts, 0) AS Pts,
                       COALESCE(i.J, 0) AS J, COALESCE(i.G, 0) AS G,
                       COALESCE(i.N, 0) AS N, COALESCE(i.P, 0) AS P,
                       COALESCE(i.F, 0) AS F, COALESCE(i.Plus, 0) AS Plus_val,
                       COALESCE(i.Moins, 0) AS Moins, COALESCE(i.Diff, 0) AS Diff
                FROM kp_competition_equipe ce
                LEFT JOIN kp_competition_equipe_init i ON ce.Id = i.Id
                WHERE ce.Code_compet = ? AND ce.Code_saison = ?
                ORDER BY COALESCE(i.Clt, 0) DESC, COALESCE(i.Pts, 0) DESC, COALESCE(i.Diff, 0) DESC, ce.Libelle ASC";
        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $teams = array_map(fn($r) => [
            'id' => (int) $r['Id'],
            'libelle' => $r['Libelle'],
            'clt' => (int) $r['Clt'],
            'pts' => (int) $r['Pts'],
            'j' => (int) $r['J'],
            'g' => (int) $r['G'],
            'n' => (int) $r['N'],
            'p' => (int) $r['P'],
            'f' => (int) $r['F'],
            'plus' => (int) $r['Plus_val'],
            'moins' => (int) $r['Moins'],
            'diff' => (int) $r['Diff'],
        ], $rows);

        return $this->json([
            'competition' => $competition,
            'season' => $season,
            'teams' => $teams,
        ]);
    }

    // ─────────────────────────────────────────────
    // 11. PATCH /admin/rankings/initial/{teamId}
    // ─────────────────────────────────────────────

    #[Route('/initial/{teamId}', name: 'admin_rankings_initial_edit', methods: ['PATCH'])]
    public function initialEdit(int $teamId, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $field = $data['field'] ?? '';
        $value = (int) ($data['value'] ?? 0);

        $allowedFields = ['Clt', 'Pts', 'J', 'G', 'N', 'P', 'F', 'Plus', 'Moins', 'Diff'];
        if (!in_array($field, $allowedFields)) {
            return $this->json(['message' => 'Invalid field'], Response::HTTP_BAD_REQUEST);
        }

        // Ensure init row exists
        $sql = "INSERT IGNORE INTO kp_competition_equipe_init (Id, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff)
                VALUES (?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
        $this->connection->prepare($sql)->executeStatement([$teamId]);

        $sql = "UPDATE kp_competition_equipe_init SET `$field` = ? WHERE Id = ?";
        $this->connection->prepare($sql)->executeStatement([$value, $teamId]);

        return $this->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    // 12. POST /admin/rankings/initial/reset
    // ─────────────────────────────────────────────

    #[Route('/initial/reset', name: 'admin_rankings_initial_reset', methods: ['POST'])]
    public function initialReset(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->getNiveau() > 3) {
            return $this->json(['message' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $season = $data['season'] ?? '';
        $competition = $data['competition'] ?? '';

        if (empty($season) || empty($competition)) {
            return $this->json(['message' => 'Season and competition are required'], Response::HTTP_BAD_REQUEST);
        }

        // Delete all init rows for this competition's teams
        $sql = "DELETE i FROM kp_competition_equipe_init i
                INNER JOIN kp_competition_equipe ce ON i.Id = ce.Id
                WHERE ce.Code_compet = ? AND ce.Code_saison = ?";
        $this->connection->prepare($sql)->executeStatement([$competition, $season]);

        // Re-create with zeros
        $sql = "INSERT INTO kp_competition_equipe_init (Id, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff)
                SELECT ce.Id, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
                FROM kp_competition_equipe ce
                WHERE ce.Code_compet = ? AND ce.Code_saison = ?";
        $this->connection->prepare($sql)->executeStatement([$competition, $season]);

        $this->logActionForCompetition('RAZ Classement Initial', $season, $competition, null);

        // Return fresh data
        return $this->initialList($request->duplicate(query: [
            'season' => $season,
            'competition' => $competition,
        ]));
    }

    // ═══════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════

    private function getCompetitionRow(string $code, string $season): ?array
    {
        $sql = "SELECT Code, Code_saison, Libelle, Code_niveau, Code_typeclt, Statut,
                       Qualifies, Elimines, Points, goalaverage,
                       Date_calcul, Mode_calcul, Code_uti_calcul,
                       Date_publication, Date_publication_calcul,
                       Code_uti_publication, Mode_publication_calcul,
                       ranking_structure_type, points_grid, multi_competitions
                FROM kp_competition
                WHERE Code = ? AND Code_saison = ?";
        return $this->connection->prepare($sql)->executeQuery([$code, $season])->fetchAssociative() ?: null;
    }

    private function loadCompetitionInfo(string $code, string $season): ?array
    {
        $row = $this->getCompetitionRow($code, $season);
        if (!$row) return null;

        // Resolve user names for calcul and publication
        $userNameCalcul = $this->resolveUserName($row['Code_uti_calcul'] ?? '');
        $userNamePublication = $this->resolveUserName($row['Code_uti_publication'] ?? '');

        $dateCalcul = $this->formatDatetime($row['Date_calcul'] ?? '');
        $datePublication = $this->formatDatetime($row['Date_publication'] ?? '');
        $datePublicationCalcul = $this->formatDatetime($row['Date_publication_calcul'] ?? '');

        return [
            'code' => $row['Code'],
            'codeSaison' => $row['Code_saison'],
            'libelle' => $row['Libelle'],
            'codeTypeclt' => $row['Code_typeclt'] ?: 'CHPT',
            'codeNiveau' => $row['Code_niveau'] ?: 'NAT',
            'statut' => $row['Statut'] ?: 'ATT',
            'qualifies' => (int) $row['Qualifies'],
            'elimines' => (int) $row['Elimines'],
            'points' => $row['Points'] ?: '4-2-1-0',
            'goalaverage' => $row['goalaverage'] ?: 'gen',
            'rankingStructureType' => $row['ranking_structure_type'] ?: null,
            'dateCalcul' => $dateCalcul,
            'modeCalcul' => $row['Mode_calcul'] ?: null,
            'codeUtiCalcul' => $row['Code_uti_calcul'] ?: '',
            'userNameCalcul' => $userNameCalcul,
            'datePublication' => $datePublication,
            'datePublicationCalcul' => $datePublicationCalcul,
            'codeUtiPublication' => $row['Code_uti_publication'] ?: '',
            'userNamePublication' => $userNamePublication,
            'modePublicationCalcul' => $row['Mode_publication_calcul'] ?: null,
        ];
    }

    private function resolveUserName(string $userCode): string
    {
        if (empty($userCode)) return '';
        $sql = "SELECT Identite FROM kp_user WHERE Code = ?";
        $row = $this->connection->prepare($sql)->executeQuery([$userCode])->fetchAssociative();
        return $row ? ($row['Identite'] ?: $userCode) : $userCode;
    }

    private function formatDatetime(?string $dt): ?string
    {
        if (!$dt || $dt === '0000-00-00 00:00:00') return null;
        return $dt;
    }

    private function loadRanking(string $competition, string $season, string $type): array
    {
        $sql = "SELECT ce.Id, ce.Libelle, ce.Code_club, COALESCE(ce.logo, '') AS logo, COALESCE(cl.Code_comite_dep, '') AS codeComiteDep,
                       ce.Clt, ce.Pts, ce.J, ce.G, ce.N, ce.P, ce.F,
                       ce.Plus, ce.Moins, ce.Diff, ce.PtsNiveau, ce.CltNiveau,
                       ce.Clt_publi, ce.Pts_publi, ce.J_publi, ce.G_publi, ce.N_publi,
                       ce.P_publi, ce.F_publi, ce.Plus_publi, ce.Moins_publi, ce.Diff_publi,
                       ce.PtsNiveau_publi, ce.CltNiveau_publi
                FROM kp_competition_equipe ce
                LEFT JOIN kp_club cl ON ce.Code_club = cl.Code
                WHERE ce.Code_compet = ? AND ce.Code_saison = ?";

        // Sorting depends on type
        if ($type === 'CP') {
            $sql .= " ORDER BY ce.CltNiveau ASC, ce.Diff DESC, ce.Plus DESC, ce.Libelle ASC";
        } elseif ($type === 'MULTI') {
            $sql .= " ORDER BY ce.Pts DESC, ce.J DESC, ce.Libelle ASC";
        } else {
            // CHPT
            $sql .= " ORDER BY ce.Clt ASC, ce.Pts DESC, ce.Diff DESC, ce.Plus DESC, ce.Libelle ASC";
        }

        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        return array_map(fn($r) => [
            'id' => (int) $r['Id'],
            'libelle' => $r['Libelle'],
            'codeClub' => $r['Code_club'] ?: '',
            'logo' => $r['logo'],
            'codeComiteDep' => $r['codeComiteDep'],
            'clt' => (int) $r['Clt'],
            'pts' => (int) $r['Pts'],
            'j' => (int) $r['J'],
            'g' => (int) $r['G'],
            'n' => (int) $r['N'],
            'p' => (int) $r['P'],
            'f' => (int) $r['F'],
            'plus' => (int) $r['Plus'],
            'moins' => (int) $r['Moins'],
            'diff' => (int) $r['Diff'],
            'ptsNiveau' => (float) $r['PtsNiveau'],
            'cltNiveau' => (int) $r['CltNiveau'],
            'cltPubli' => (int) $r['Clt_publi'],
            'ptsPubli' => (int) $r['Pts_publi'],
            'jPubli' => (int) $r['J_publi'],
            'gPubli' => (int) $r['G_publi'],
            'nPubli' => (int) $r['N_publi'],
            'pPubli' => (int) $r['P_publi'],
            'fPubli' => (int) $r['F_publi'],
            'plusPubli' => (int) $r['Plus_publi'],
            'moinsPubli' => (int) $r['Moins_publi'],
            'diffPubli' => (int) $r['Diff_publi'],
            'ptsNiveauPubli' => (float) $r['PtsNiveau_publi'],
            'cltNiveauPubli' => (int) $r['CltNiveau_publi'],
        ], $rows);
    }

    private function loadPhases(string $competition, string $season): array
    {
        // Load journées/phases for this competition
        $sql = "SELECT j.Id, j.Phase, j.Lieu, j.Type, j.Niveau, j.Consolidation
                FROM kp_journee j
                WHERE j.Code_competition = ? AND j.Code_saison = ?
                ORDER BY j.Niveau ASC, j.Id ASC";
        $journees = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $phases = [];
        foreach ($journees as $j) {
            $journeeId = (int) $j['Id'];

            // Load teams for this phase
            $sql = "SELECT cej.Id, ce.Libelle,
                           cej.Clt, cej.Pts, cej.J, cej.G, cej.N, cej.P, cej.F,
                           cej.Plus, cej.Moins, cej.Diff,
                           COALESCE(cej.Clt_publi, 0) AS Clt_publi,
                           COALESCE(cej.Pts_publi, 0) AS Pts_publi,
                           COALESCE(cej.J_publi, 0) AS J_publi,
                           COALESCE(cej.G_publi, 0) AS G_publi,
                           COALESCE(cej.N_publi, 0) AS N_publi,
                           COALESCE(cej.P_publi, 0) AS P_publi,
                           COALESCE(cej.F_publi, 0) AS F_publi,
                           COALESCE(cej.Plus_publi, 0) AS Plus_publi,
                           COALESCE(cej.Moins_publi, 0) AS Moins_publi,
                           COALESCE(cej.Diff_publi, 0) AS Diff_publi
                    FROM kp_competition_equipe_journee cej
                    INNER JOIN kp_competition_equipe ce ON cej.Id = ce.Id
                    WHERE cej.Id_journee = ?
                    ORDER BY cej.Clt ASC, cej.Pts DESC, cej.Diff DESC, ce.Libelle ASC";
            $teams = $this->connection->prepare($sql)->executeQuery([$journeeId])->fetchAllAssociative();

            $phaseTeams = array_map(fn($t) => [
                'id' => (int) $t['Id'],
                'libelle' => $t['Libelle'],
                'clt' => (int) ($t['Clt'] ?? 0),
                'pts' => (int) ($t['Pts'] ?? 0),
                'j' => (int) ($t['J'] ?? 0),
                'g' => (int) ($t['G'] ?? 0),
                'n' => (int) ($t['N'] ?? 0),
                'p' => (int) ($t['P'] ?? 0),
                'f' => (int) ($t['F'] ?? 0),
                'plus' => (int) ($t['Plus'] ?? 0),
                'moins' => (int) ($t['Moins'] ?? 0),
                'diff' => (int) ($t['Diff'] ?? 0),
                'cltPubli' => (int) $t['Clt_publi'],
                'ptsPubli' => (int) $t['Pts_publi'],
                'jPubli' => (int) $t['J_publi'],
                'gPubli' => (int) $t['G_publi'],
                'nPubli' => (int) $t['N_publi'],
                'pPubli' => (int) $t['P_publi'],
                'fPubli' => (int) $t['F_publi'],
                'plusPubli' => (int) $t['Plus_publi'],
                'moinsPubli' => (int) $t['Moins_publi'],
                'diffPubli' => (int) $t['Diff_publi'],
            ], $teams);

            // Load matches for elimination phases
            $phaseMatches = [];
            if (($j['Type'] ?: 'C') === 'E') {
                $sql = "SELECT m.Id, m.ScoreA, m.ScoreB, m.Id_equipeA, m.Id_equipeB,
                               ce1.Libelle AS EquipeA, ce2.Libelle AS EquipeB
                        FROM kp_match m
                        LEFT JOIN kp_competition_equipe ce1 ON m.Id_equipeA = ce1.Id
                        LEFT JOIN kp_competition_equipe ce2 ON m.Id_equipeB = ce2.Id
                        WHERE m.Id_journee = ?
                        ORDER BY m.Numero_ordre ASC";
                $matches = $this->connection->prepare($sql)->executeQuery([$journeeId])->fetchAllAssociative();
                $phaseMatches = array_map(fn($m) => [
                    'id' => (int) $m['Id'],
                    'equipeA' => $m['EquipeA'] ?? '',
                    'equipeB' => $m['EquipeB'] ?? '',
                    'idEquipeA' => (int) ($m['Id_equipeA'] ?? 0),
                    'idEquipeB' => (int) ($m['Id_equipeB'] ?? 0),
                    'scoreA' => $m['ScoreA'] !== null ? (int) $m['ScoreA'] : null,
                    'scoreB' => $m['ScoreB'] !== null ? (int) $m['ScoreB'] : null,
                ], $matches);
            }

            $phase = [
                'idJournee' => $journeeId,
                'phase' => $j['Phase'] ?? '',
                'lieu' => $j['Lieu'] ?? '',
                'type' => $j['Type'] ?: 'C',
                'niveau' => (int) ($j['Niveau'] ?? 0),
                'consolidation' => $j['Consolidation'] === 'O',
                'teams' => $phaseTeams,
            ];
            if (!empty($phaseMatches)) {
                $phase['matches'] = $phaseMatches;
            }
            $phases[] = $phase;
        }

        return $phases;
    }

    // ═══════════════════════════════════════════════
    //  RANKING CALCULATION ENGINE
    // ═══════════════════════════════════════════════

    private function razRanking(string $competition, string $season): void
    {
        $sql = "UPDATE kp_competition_equipe
                SET Clt = 0, Pts = 0, J = 0, G = 0, N = 0, P = 0, F = 0,
                    Plus = 0, Moins = 0, Diff = 0, PtsNiveau = 0, CltNiveau = 0
                WHERE Code_compet = ? AND Code_saison = ?";
        $this->connection->prepare($sql)->executeStatement([$competition, $season]);
    }

    private function razJourneeRanking(string $competition, string $season): void
    {
        // Reset non-consolidated phases only
        $sql = "UPDATE kp_competition_equipe_journee cej
                INNER JOIN kp_journee j ON j.Id = cej.Id_journee
                INNER JOIN kp_competition_equipe ce ON cej.Id = ce.Id
                SET cej.Clt = 0, cej.Pts = 0, cej.J = 0, cej.G = 0, cej.N = 0,
                    cej.P = 0, cej.F = 0, cej.Plus = 0, cej.Moins = 0, cej.Diff = 0,
                    cej.PtsNiveau = 0, cej.CltNiveau = 0
                WHERE j.Code_competition = ? AND j.Code_saison = ?
                AND (j.Consolidation IS NULL OR j.Consolidation != 'O')";
        $this->connection->prepare($sql)->executeStatement([$competition, $season]);
    }

    private function razNiveauRanking(string $competition, string $season): void
    {
        // Delete non-consolidated niveau rows
        // We need to figure out which niveaux are consolidated
        $sql = "DELETE cen FROM kp_competition_equipe_niveau cen
                INNER JOIN kp_competition_equipe ce ON cen.Id = ce.Id
                WHERE ce.Code_compet = ? AND ce.Code_saison = ?
                AND cen.Niveau NOT IN (
                    SELECT DISTINCT j.Niveau FROM kp_journee j
                    WHERE j.Code_competition = ? AND j.Code_saison = ?
                    AND j.Consolidation = 'O' AND j.Niveau IS NOT NULL
                )";
        $this->connection->prepare($sql)->executeStatement([$competition, $season, $competition, $season]);
    }

    private function applyInitialValues(string $competition, string $season): void
    {
        // Copy initial values (Pts * 100) to ranking
        $sql = "UPDATE kp_competition_equipe ce
                INNER JOIN kp_competition_equipe_init i ON ce.Id = i.Id
                SET ce.Pts = i.Pts * 100,
                    ce.Clt = i.Clt, ce.J = i.J, ce.G = i.G, ce.N = i.N,
                    ce.P = i.P, ce.F = i.F, ce.Plus = i.Plus,
                    ce.Moins = i.Moins, ce.Diff = i.Diff
                WHERE ce.Code_compet = ? AND ce.Code_saison = ?";
        $this->connection->prepare($sql)->executeStatement([$competition, $season]);
    }

    private function processMatches(string $competition, string $season, bool $includeUnlocked, string $pointsStr): void
    {
        $points = explode('-', $pointsStr);
        if (count($points) < 4) $points = [4, 2, 1, 0];

        // Fetch matches, excluding consolidated phases
        $sql = "SELECT m.Id, m.Id_equipeA, m.Id_equipeB, m.ScoreA, m.ScoreB,
                       m.CoeffA, m.CoeffB, m.Validation, m.Id_journee,
                       j.Niveau, j.Consolidation
                FROM kp_match m
                INNER JOIN kp_journee j ON j.Id = m.Id_journee
                WHERE j.Code_competition = ? AND j.Code_saison = ?
                AND (j.Consolidation IS NULL OR j.Consolidation != 'O')";

        if (!$includeUnlocked) {
            $sql .= " AND m.Validation = 'O'";
        }

        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        // Build increments per team
        $teamInc = [];     // teamId -> [Pts, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau]
        $journeeInc = [];  // teamId_journeeId -> same
        $niveauInc = [];   // teamId_niveau -> same

        foreach ($rows as $match) {
            $idA = (int) ($match['Id_equipeA'] ?? 0);
            $idB = (int) ($match['Id_equipeB'] ?? 0);
            if ($idA === 0 || $idB === 0) continue;

            $scoreA = $match['ScoreA'];
            $scoreB = $match['ScoreB'];
            $coeffA = (float) ($match['CoeffA'] ?: 1.0);
            $coeffB = (float) ($match['CoeffB'] ?: 1.0);
            if ($coeffA == 0) $coeffA = 1.0;
            if ($coeffB == 0) $coeffB = 1.0;
            $niveau = (int) ($match['Niveau'] ?? 0);
            $journeeId = (int) $match['Id_journee'];

            $cltA = $this->emptyStats();
            $cltB = $this->emptyStats();
            $this->calculateMatchResult($scoreA, $scoreB, $niveau, $cltA, $cltB, $coeffA, $coeffB, $points);

            // Accumulate for team
            $this->accumulate($teamInc, (string) $idA, $cltA);
            $this->accumulate($teamInc, (string) $idB, $cltB);

            // Accumulate for journee
            $this->accumulate($journeeInc, "{$idA}_{$journeeId}", $cltA);
            $this->accumulate($journeeInc, "{$idB}_{$journeeId}", $cltB);

            // Accumulate for niveau
            $this->accumulate($niveauInc, "{$idA}_{$niveau}", $cltA);
            $this->accumulate($niveauInc, "{$idB}_{$niveau}", $cltB);
        }

        // Apply team increments
        foreach ($teamInc as $teamId => $inc) {
            $sql = "UPDATE kp_competition_equipe
                    SET Pts = Pts + ?, J = J + ?, G = G + ?, N = N + ?,
                        P = P + ?, F = F + ?, Plus = Plus + ?, Moins = Moins + ?,
                        Diff = Diff + ?, PtsNiveau = PtsNiveau + ?
                    WHERE Id = ?";
            $this->connection->prepare($sql)->executeStatement([
                $inc['Pts'], $inc['J'], $inc['G'], $inc['N'],
                $inc['P'], $inc['F'], $inc['Plus'], $inc['Moins'],
                $inc['Diff'], $inc['PtsNiveau'], (int) $teamId
            ]);
        }

        // Apply journee increments (ensure rows exist first)
        foreach ($journeeInc as $key => $inc) {
            [$teamId, $journeeId] = explode('_', $key);
            $teamId = (int) $teamId;
            $journeeId = (int) $journeeId;

            // Ensure row exists
            $sql = "INSERT IGNORE INTO kp_competition_equipe_journee
                    (Id, Id_journee, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau)
                    VALUES (?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
            $this->connection->prepare($sql)->executeStatement([$teamId, $journeeId]);

            $sql = "UPDATE kp_competition_equipe_journee
                    SET Pts = Pts + ?, J = J + ?, G = G + ?, N = N + ?,
                        P = P + ?, F = F + ?, Plus = Plus + ?, Moins = Moins + ?,
                        Diff = Diff + ?, PtsNiveau = PtsNiveau + ?
                    WHERE Id = ? AND Id_journee = ?";
            $this->connection->prepare($sql)->executeStatement([
                $inc['Pts'], $inc['J'], $inc['G'], $inc['N'],
                $inc['P'], $inc['F'], $inc['Plus'], $inc['Moins'],
                $inc['Diff'], $inc['PtsNiveau'], $teamId, $journeeId
            ]);
        }

        // Apply niveau increments
        foreach ($niveauInc as $key => $inc) {
            [$teamId, $niveau] = explode('_', $key);
            $teamId = (int) $teamId;
            $niveau = (int) $niveau;

            $sql = "INSERT IGNORE INTO kp_competition_equipe_niveau
                    (Id, Niveau, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau)
                    VALUES (?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
            $this->connection->prepare($sql)->executeStatement([$teamId, $niveau]);

            $sql = "UPDATE kp_competition_equipe_niveau
                    SET Pts = Pts + ?, J = J + ?, G = G + ?, N = N + ?,
                        P = P + ?, F = F + ?, Plus = Plus + ?, Moins = Moins + ?,
                        Diff = Diff + ?, PtsNiveau = PtsNiveau + ?
                    WHERE Id = ? AND Niveau = ?";
            $this->connection->prepare($sql)->executeStatement([
                $inc['Pts'], $inc['J'], $inc['G'], $inc['N'],
                $inc['P'], $inc['F'], $inc['Plus'], $inc['Moins'],
                $inc['Diff'], $inc['PtsNiveau'], $teamId, $niveau
            ]);
        }
    }

    private function emptyStats(): array
    {
        return ['Pts' => 0, 'J' => 0, 'G' => 0, 'N' => 0, 'P' => 0, 'F' => 0,
                'Plus' => 0, 'Moins' => 0, 'Diff' => 0, 'PtsNiveau' => 0];
    }

    private function accumulate(array &$storage, string $key, array $inc): void
    {
        if (!isset($storage[$key])) {
            $storage[$key] = $this->emptyStats();
        }
        foreach ($inc as $k => $v) {
            $storage[$key][$k] += $v;
        }
    }

    private function calculateMatchResult(
        ?string $scoreA, ?string $scoreB, int $niveau,
        array &$cltA, array &$cltB,
        float $coeffA, float $coeffB, array $points
    ): void {
        $ptsV = (int) $points[0] * 100;
        $ptsN = (int) ($points[1] ?? 2) * 100;
        $ptsP = (int) ($points[2] ?? 1) * 100;
        $ptsF = (int) ($points[3] ?? 0) * 100;

        $nV = 4; $nN = 3; $nP = 2; $nF = 1;

        $isForfeitA = ($scoreA === 'F');
        $isForfeitB = ($scoreB === 'F');

        if (!$isForfeitA && !$isForfeitB) {
            // Normal match
            if ($scoreA === '' || $scoreA === null || $scoreA === '?' ||
                $scoreB === '' || $scoreB === null || $scoreB === '?') {
                return; // No score yet
            }

            $sA = (int) $scoreA;
            $sB = (int) $scoreB;

            $cltA['J'] = 1; $cltB['J'] = 1;
            $cltA['Plus'] = $sA; $cltA['Moins'] = $sB; $cltA['Diff'] = $sA - $sB;
            $cltB['Plus'] = $sB; $cltB['Moins'] = $sA; $cltB['Diff'] = $sB - $sA;

            if ($sA > $sB) {
                $cltA['Pts'] = (int)($ptsV * $coeffA); $cltA['G'] = 1;
                $cltA['PtsNiveau'] = pow(64, $niveau) * $nV;
                $cltB['Pts'] = (int)($ptsP * $coeffB); $cltB['P'] = 1;
                $cltB['PtsNiveau'] = pow(64, $niveau) * $nP;
            } elseif ($sB > $sA) {
                $cltA['Pts'] = (int)($ptsP * $coeffA); $cltA['P'] = 1;
                $cltA['PtsNiveau'] = pow(64, $niveau) * $nP;
                $cltB['Pts'] = (int)($ptsV * $coeffB); $cltB['G'] = 1;
                $cltB['PtsNiveau'] = pow(64, $niveau) * $nV;
            } else {
                // Draw
                $cltA['Pts'] = (int)($ptsN * $coeffA); $cltA['N'] = 1;
                $cltA['PtsNiveau'] = pow(64, $niveau) * $nN;
                $cltB['Pts'] = (int)($ptsN * $coeffB); $cltB['N'] = 1;
                $cltB['PtsNiveau'] = pow(64, $niveau) * $nN;
            }
        } elseif (!$isForfeitA && $isForfeitB) {
            // B forfeits, A wins
            $sA = is_numeric($scoreA) ? (int) $scoreA : 0;
            $cltA['Pts'] = (int)($ptsV * $coeffA); $cltA['J'] = 1; $cltA['G'] = 1;
            $cltA['Plus'] = $sA; $cltA['Diff'] = $sA;
            $cltA['PtsNiveau'] = pow(64, $niveau) * $nV;
            $cltB['Pts'] = (int)($ptsF * $coeffB); $cltB['J'] = 1; $cltB['F'] = 1;
            $cltB['Moins'] = $sA; $cltB['Diff'] = -$sA;
            $cltB['PtsNiveau'] = pow(64, $niveau) * $nF;
        } elseif ($isForfeitA && !$isForfeitB) {
            // A forfeits, B wins
            $sB = is_numeric($scoreB) ? (int) $scoreB : 0;
            $cltB['Pts'] = (int)($ptsV * $coeffB); $cltB['J'] = 1; $cltB['G'] = 1;
            $cltB['Plus'] = $sB; $cltB['Diff'] = $sB;
            $cltB['PtsNiveau'] = pow(64, $niveau) * $nV;
            $cltA['Pts'] = (int)($ptsF * $coeffA); $cltA['J'] = 1; $cltA['F'] = 1;
            $cltA['Moins'] = $sB; $cltA['Diff'] = -$sB;
            $cltA['PtsNiveau'] = pow(64, $niveau) * $nF;
        } else {
            // Double forfeit
            $cltA['Pts'] = (int)($ptsF * $coeffA); $cltA['J'] = 1; $cltA['F'] = 1;
            $cltA['PtsNiveau'] = pow(64, $niveau) * $nF;
            $cltB['Pts'] = (int)($ptsF * $coeffB); $cltB['J'] = 1; $cltB['F'] = 1;
            $cltB['PtsNiveau'] = pow(64, $niveau) * $nF;
        }
    }

    private function finalizeChptRanking(string $competition, string $season, string $goalaverage): void
    {
        $sql = "SELECT Id, Pts, Diff, Plus FROM kp_competition_equipe
                WHERE Code_compet = ? AND Code_saison = ?
                ORDER BY Pts DESC, Diff DESC, Plus DESC";
        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $clt = 1;
        $oldPts = -1;
        $oldClt = 1;
        $ties = []; // clt -> [ids] for H2H resolution
        $oldId = 0;

        foreach ($rows as $i => $row) {
            $id = (int) $row['Id'];
            if ($row['Pts'] == $oldPts) {
                if ($goalaverage === 'gen') {
                    // General goal-average: each team gets sequential rank
                    $clt = $i + 1;
                } else {
                    // Particular goal-average: teams share rank, collect for H2H
                    $clt = $oldClt;
                    $ties[$clt][$oldId] = $oldId;
                    $ties[$clt][$id] = $id;
                }
            } else {
                $clt = $i + 1;
            }

            $sql = "UPDATE kp_competition_equipe SET Clt = ? WHERE Id = ?";
            $this->connection->prepare($sql)->executeStatement([$clt, $id]);

            $oldPts = $row['Pts'];
            $oldClt = $clt;
            $oldId = $id;
        }

        // Head-to-head tie-breaking if goal-average = 'part'
        if ($goalaverage === 'part' && !empty($ties)) {
            $this->resolveHeadToHead($competition, $season);
        }
    }

    private function resolveHeadToHead(string $competition, string $season): void
    {
        // Find groups of teams with same Pts
        $sql = "SELECT Pts, GROUP_CONCAT(Id) AS ids
                FROM kp_competition_equipe
                WHERE Code_compet = ? AND Code_saison = ?
                GROUP BY Pts
                HAVING COUNT(*) > 1";
        $groups = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        foreach ($groups as $group) {
            $ids = array_map('intval', explode(',', $group['ids']));
            if (count($ids) < 2) continue;

            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            // Calculate head-to-head stats between tied teams
            $sql = "SELECT m.Id_equipeA, m.Id_equipeB, m.ScoreA, m.ScoreB
                    FROM kp_match m
                    INNER JOIN kp_journee j ON j.Id = m.Id_journee
                    WHERE j.Code_competition = ? AND j.Code_saison = ?
                    AND m.Validation = 'O'
                    AND m.Id_equipeA IN ($placeholders)
                    AND m.Id_equipeB IN ($placeholders)";
            $params = array_merge([$competition, $season], $ids, $ids);
            $matches = $this->connection->prepare($sql)->executeQuery($params)->fetchAllAssociative();

            // Build head-to-head diff for each tied team
            $h2h = [];
            foreach ($ids as $id) {
                $h2h[$id] = ['diff' => 0, 'plus' => 0];
            }

            foreach ($matches as $m) {
                $idA = (int) $m['Id_equipeA'];
                $idB = (int) $m['Id_equipeB'];
                $sA = $m['ScoreA'];
                $sB = $m['ScoreB'];
                if (!is_numeric($sA) || !is_numeric($sB)) continue;
                $sA = (int) $sA; $sB = (int) $sB;

                if (isset($h2h[$idA])) { $h2h[$idA]['diff'] += ($sA - $sB); $h2h[$idA]['plus'] += $sA; }
                if (isset($h2h[$idB])) { $h2h[$idB]['diff'] += ($sB - $sA); $h2h[$idB]['plus'] += $sB; }
            }

            // Sort tied teams by h2h diff DESC, h2h plus DESC
            usort($ids, function($a, $b) use ($h2h) {
                if ($h2h[$a]['diff'] !== $h2h[$b]['diff']) {
                    return $h2h[$b]['diff'] - $h2h[$a]['diff'];
                }
                return $h2h[$b]['plus'] - $h2h[$a]['plus'];
            });

            // Get the min Clt for these teams and re-assign sequentially
            $sql = "SELECT MIN(Clt) AS minClt FROM kp_competition_equipe WHERE Id IN ($placeholders)";
            $minCltRow = $this->connection->prepare($sql)->executeQuery($ids)->fetchAssociative();
            $startClt = (int) ($minCltRow['minClt'] ?? 1);

            foreach ($ids as $i => $id) {
                $sql = "UPDATE kp_competition_equipe SET Clt = ? WHERE Id = ?";
                $this->connection->prepare($sql)->executeStatement([$startClt + $i, $id]);
            }
        }
    }

    private function finalizeNiveauRanking(string $competition, string $season): void
    {
        // Assign CltNiveau based on PtsNiveau DESC, Diff DESC
        // Teams with same PtsNiveau AND same Diff share the same rank
        $sql = "SELECT Id, PtsNiveau, Diff FROM kp_competition_equipe
                WHERE Code_compet = ? AND Code_saison = ?
                ORDER BY PtsNiveau DESC, Diff DESC";
        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $clt = 1;
        $oldPts = 0;
        $oldDiff = 9999;
        foreach ($rows as $i => $row) {
            if (abs($row['PtsNiveau'] - $oldPts) >= 1 || $row['Diff'] != $oldDiff) {
                $clt = $i + 1;
                $oldPts = $row['PtsNiveau'];
                $oldDiff = $row['Diff'];
            }
            $sql = "UPDATE kp_competition_equipe SET CltNiveau = ? WHERE Id = ?";
            $this->connection->prepare($sql)->executeStatement([$clt, (int) $row['Id']]);
        }
    }

    private function finalizeNiveauNiveauRanking(string $competition, string $season): void
    {
        // Assign Clt/CltNiveau within each Niveau group in kp_competition_equipe_niveau
        // Teams with same PtsNiveau AND same Diff share the same rank
        $sql = "SELECT cen.Id, cen.Niveau, cen.PtsNiveau, cen.Diff
                FROM kp_competition_equipe_niveau cen
                INNER JOIN kp_competition_equipe ce ON cen.Id = ce.Id
                WHERE ce.Code_compet = ? AND ce.Code_saison = ?
                ORDER BY cen.Niveau, cen.PtsNiveau DESC, cen.Diff DESC";
        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $oldNiveau = -1;
        $clt = 1;
        $oldPts = 0;
        $oldDiff = 9999;
        $j = 0;

        foreach ($rows as $row) {
            $niveau = (int) $row['Niveau'];

            if ($niveau !== $oldNiveau) {
                // New niveau: reset
                $oldNiveau = $niveau;
                $clt = 1;
                $oldPts = $row['PtsNiveau'];
                $oldDiff = $row['Diff'];
                $j = 0;
            } else {
                if (abs($row['PtsNiveau'] - $oldPts) >= 1 || $row['Diff'] != $oldDiff) {
                    $clt = $j + 1;
                    $oldPts = $row['PtsNiveau'];
                    $oldDiff = $row['Diff'];
                }
            }

            $sql = "UPDATE kp_competition_equipe_niveau SET Clt = ?, CltNiveau = ? WHERE Id = ? AND Niveau = ?";
            $this->connection->prepare($sql)->executeStatement([$clt, $clt, (int) $row['Id'], $niveau]);
            $j++;
        }
    }

    private function finalizeJourneeChptRanking(string $competition, string $season, string $goalaverage): void
    {
        // Assign Clt within each journee, grouped by journee
        $sql = "SELECT cej.Id, cej.Id_journee, cej.Pts, cej.Diff, cej.Plus, j.Type
                FROM kp_competition_equipe_journee cej
                INNER JOIN kp_competition_equipe ce ON cej.Id = ce.Id
                INNER JOIN kp_journee j ON j.Id = cej.Id_journee
                WHERE j.Code_competition = ? AND j.Code_saison = ?
                AND (j.Consolidation IS NULL OR j.Consolidation != 'O')
                ORDER BY cej.Id_journee, cej.Pts DESC, cej.Diff DESC, cej.Plus DESC";
        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $oldJourneeId = -1;
        $clt = 1;
        $oldPts = -1;
        $oldClt = 1;
        $oldId = 0;
        $i = 0;

        foreach ($rows as $row) {
            $journeeId = (int) $row['Id_journee'];
            $id = (int) $row['Id'];
            $type = $row['Type'] ?? 'C';

            if ($journeeId !== $oldJourneeId) {
                // New journee: reset
                $oldJourneeId = $journeeId;
                $clt = 1;
                $oldPts = $row['Pts'];
                $oldClt = 1;
                $i = 0;
            } else {
                if ($row['Pts'] == $oldPts) {
                    if ($goalaverage === 'gen') {
                        $clt = $i + 1;
                    } else {
                        $clt = $oldClt;
                    }
                } else {
                    $clt = $i + 1;
                }
            }

            $sql = "UPDATE kp_competition_equipe_journee SET Clt = ? WHERE Id = ? AND Id_journee = ?";
            $this->connection->prepare($sql)->executeStatement([$clt, $id, $journeeId]);

            $oldPts = $row['Pts'];
            $oldClt = $clt;
            $oldId = $id;
            $i++;
        }
    }

    private function finalizeJourneeNiveauRanking(string $competition, string $season): void
    {
        // For CP: assign CltNiveau within each journee
        // Teams with same PtsNiveau AND same Diff share the same rank
        $sql = "SELECT cej.Id, cej.Id_journee, cej.PtsNiveau, cej.Diff
                FROM kp_competition_equipe_journee cej
                INNER JOIN kp_competition_equipe ce ON cej.Id = ce.Id
                INNER JOIN kp_journee j ON j.Id = cej.Id_journee
                WHERE j.Code_competition = ? AND j.Code_saison = ?
                AND (j.Consolidation IS NULL OR j.Consolidation != 'O')
                ORDER BY cej.Id_journee, cej.PtsNiveau DESC, cej.Diff DESC";
        $rows = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $oldJourneeId = -1;
        $clt = 1;
        $oldPts = 0;
        $oldDiff = 9999;
        $j = 0;

        foreach ($rows as $row) {
            $journeeId = (int) $row['Id_journee'];

            if ($journeeId !== $oldJourneeId) {
                // New journee: reset
                $oldJourneeId = $journeeId;
                $clt = 1;
                $oldPts = $row['PtsNiveau'];
                $oldDiff = $row['Diff'];
                $j = 0;
            } else {
                if (abs($row['PtsNiveau'] - $oldPts) >= 1 || $row['Diff'] != $oldDiff) {
                    $clt = $j + 1;
                    $oldPts = $row['PtsNiveau'];
                    $oldDiff = $row['Diff'];
                }
            }

            $sql = "UPDATE kp_competition_equipe_journee SET CltNiveau = ? WHERE Id = ? AND Id_journee = ?";
            $this->connection->prepare($sql)->executeStatement([$clt, (int) $row['Id'], $journeeId]);
            $j++;
        }
    }

    private function calculateMulti(string $competition, string $season, array $compRow): void
    {
        $multiComps = json_decode($compRow['multi_competitions'] ?? '[]', true) ?: [];
        $pointsGrid = json_decode($compRow['points_grid'] ?? '{}', true) ?: [];
        $structureType = $compRow['ranking_structure_type'] ?? 'team';
        $defaultPts = (int)($pointsGrid['default'] ?? 0);

        if (empty($multiComps)) return;

        // RAZ
        $this->razRanking($competition, $season);

        // Get teams in this MULTI competition
        $sql = "SELECT Id, Numero, Code_club FROM kp_competition_equipe
                WHERE Code_compet = ? AND Code_saison = ?";
        $teams = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        foreach ($teams as $team) {
            $teamId = (int) $team['Id'];
            $totalPts = 0;
            $totalJ = 0;

            foreach ($multiComps as $srcComp) {
                // Find same team in source competition
                $cltValue = 0;

                if ($structureType === 'team') {
                    // By team numero
                    $sql = "SELECT CltNiveau_publi, Clt_publi FROM kp_competition_equipe
                            WHERE Code_compet = ? AND Code_saison = ? AND Numero = ?";
                    $srcRow = $this->connection->prepare($sql)
                        ->executeQuery([$srcComp, $season, $team['Numero']])
                        ->fetchAssociative();
                    if ($srcRow) {
                        $cltValue = (int)($srcRow['CltNiveau_publi'] ?: $srcRow['Clt_publi']);
                    }
                } elseif ($structureType === 'club') {
                    // Best result from any team of same club
                    $sql = "SELECT MIN(CASE WHEN CltNiveau_publi > 0 THEN CltNiveau_publi ELSE Clt_publi END) AS bestClt
                            FROM kp_competition_equipe
                            WHERE Code_compet = ? AND Code_saison = ? AND Code_club = ?
                            AND (CltNiveau_publi > 0 OR Clt_publi > 0)";
                    $srcRow = $this->connection->prepare($sql)
                        ->executeQuery([$srcComp, $season, $team['Code_club']])
                        ->fetchAssociative();
                    if ($srcRow) {
                        $cltValue = (int)($srcRow['bestClt'] ?? 0);
                    }
                }
                // Other structure types (cd, cr, nation) follow similar patterns

                if ($cltValue > 0) {
                    $pts = (int)($pointsGrid[(string)$cltValue] ?? $defaultPts);
                    $totalPts += $pts * 100; // Store × 100
                    $totalJ++;
                }
            }

            $sql = "UPDATE kp_competition_equipe SET Pts = ?, J = ? WHERE Id = ?";
            $this->connection->prepare($sql)->executeStatement([$totalPts, $totalJ, $teamId]);
        }

        // Finalize ranking: sort by Pts DESC
        $sql = "SELECT Id FROM kp_competition_equipe
                WHERE Code_compet = ? AND Code_saison = ?
                ORDER BY Pts DESC, J DESC";
        $sorted = $this->connection->prepare($sql)->executeQuery([$competition, $season])->fetchAllAssociative();

        $clt = 1;
        foreach ($sorted as $row) {
            $sql = "UPDATE kp_competition_equipe SET Clt = ? WHERE Id = ?";
            $this->connection->prepare($sql)->executeStatement([$clt, (int) $row['Id']]);
            $clt++;
        }
    }
}
