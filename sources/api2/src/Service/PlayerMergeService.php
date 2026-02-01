<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Player Merge Service
 *
 * Handles player merge operations including manual merge and auto-merge of non-federal players.
 * Migrated from GestionOperations.php FusionJoueurs and FusionAutomatiqueLicenciesNonFederaux.
 */
class PlayerMergeService
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Merge two players (source into target)
     *
     * All references to source player will be transferred to target player,
     * then source player will be deleted.
     *
     * @throws \Exception if merge fails
     */
    public function mergePlayers(int $sourceMatric, int $targetMatric): void
    {
        if ($sourceMatric <= 0 || $targetMatric <= 0) {
            throw new \Exception('Invalid matric values');
        }

        if ($sourceMatric === $targetMatric) {
            throw new \Exception('Source and target cannot be the same');
        }

        // Verify both players exist
        $this->verifyPlayerExists($sourceMatric);
        $this->verifyPlayerExists($targetMatric);

        $this->connection->beginTransaction();

        try {
            // 1. Update match details (goals and cards)
            $sql = "UPDATE kp_match_detail SET Competiteur = ? WHERE Competiteur = ?";
            $this->connection->executeStatement($sql, [$targetMatric, $sourceMatric]);

            // 2. Update match players (compositions)
            // First delete duplicates if source and target are in the same match
            $sql = "DELETE mj_source FROM kp_match_joueur mj_source
                    INNER JOIN kp_match_joueur mj_target
                        ON mj_source.Id_match = mj_target.Id_match
                    WHERE mj_source.Matric = ?
                    AND mj_target.Matric = ?";
            $this->connection->executeStatement($sql, [$sourceMatric, $targetMatric]);

            // Then update remaining entries
            $sql = "UPDATE kp_match_joueur SET Matric = ? WHERE Matric = ?";
            $this->connection->executeStatement($sql, [$targetMatric, $sourceMatric]);

            // 3. Handle scrutineering data
            $this->mergeScrutineeringData($sourceMatric, $targetMatric);

            // 4. Update match referees
            $sql = "UPDATE kp_match SET Matric_arbitre_principal = ? WHERE Matric_arbitre_principal = ?";
            $this->connection->executeStatement($sql, [$targetMatric, $sourceMatric]);

            $sql = "UPDATE kp_match SET Matric_arbitre_secondaire = ? WHERE Matric_arbitre_secondaire = ?";
            $this->connection->executeStatement($sql, [$targetMatric, $sourceMatric]);

            // 5. Update match officials (stored as "(matric)")
            $sourcePattern = "($sourceMatric)";
            $targetPattern = "($targetMatric)";
            $likePattern = "%($sourceMatric)%";

            $officialFields = ['Secretaire', 'Chronometre', 'Timeshoot', 'Ligne1', 'Ligne2'];
            foreach ($officialFields as $field) {
                $sql = "UPDATE kp_match
                        SET $field = REPLACE($field, ?, ?)
                        WHERE $field LIKE ?";
                $this->connection->executeStatement($sql, [$sourcePattern, $targetPattern, $likePattern]);
            }

            // 6. Delete from child tables before deleting license (FK constraints)
            $sql = "DELETE FROM kp_arbitre WHERE Matric = ?";
            $this->connection->executeStatement($sql, [$sourceMatric]);

            $sql = "DELETE FROM kp_recherche_licence WHERE Matric = ?";
            $this->connection->executeStatement($sql, [$sourceMatric]);

            // 7. Delete source license
            $sql = "DELETE FROM kp_licence WHERE Matric = ?";
            $this->connection->executeStatement($sql, [$sourceMatric]);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new \Exception('Player merge failed: ' . $e->getMessage());
        }
    }

    /**
     * Auto-merge non-federal players (Matric > 2000000) with same Nom, Prenom, Numero_club
     *
     * Returns array with count and details of merges performed.
     */
    public function autoMergeNonFederalPlayers(): array
    {
        // Find all duplicate groups of non-federal players
        $sql = "SELECT
                    l.Nom,
                    l.Prenom,
                    l.Numero_club,
                    l.Club,
                    GROUP_CONCAT(l.Matric ORDER BY l.Matric SEPARATOR ',') as Matricules,
                    COUNT(*) as NbDoublons
                FROM kp_licence l
                WHERE l.Matric > 2000000
                GROUP BY l.Nom, l.Prenom, l.Numero_club
                HAVING COUNT(*) > 1
                ORDER BY l.Nom, l.Prenom";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        $duplicateGroups = $result->fetchAllAssociative();

        if (empty($duplicateGroups)) {
            return [
                'count' => 0,
                'message' => 'No duplicates found',
                'details' => [],
            ];
        }

        $mergeCount = 0;
        $mergeDetails = [];

        foreach ($duplicateGroups as $group) {
            $matricules = explode(',', $group['Matricules']);

            // Find the best target (has ICF number, birth date, referee qualification)
            $targetMatric = $this->findBestTargetPlayer($matricules);
            if (!$targetMatric) {
                continue;
            }

            // Get source players (all except target)
            $sources = array_filter($matricules, fn($m) => (int)$m !== $targetMatric);

            // Update target with any missing information from sources
            $this->enrichTargetFromSources($targetMatric, $sources);

            // Merge each source into target
            foreach ($sources as $sourceMatric) {
                try {
                    $this->mergePlayers((int)$sourceMatric, $targetMatric);
                    $mergeCount++;
                    $mergeDetails[] = [
                        'source' => (int)$sourceMatric,
                        'target' => $targetMatric,
                        'name' => "{$group['Nom']} {$group['Prenom']}",
                        'club' => $group['Club'],
                    ];
                } catch (\Exception $e) {
                    // Log error but continue with other merges
                    $mergeDetails[] = [
                        'source' => (int)$sourceMatric,
                        'target' => $targetMatric,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        return [
            'count' => $mergeCount,
            'message' => "$mergeCount players merged successfully",
            'details' => $mergeDetails,
        ];
    }

    /**
     * Verify a player exists in the database
     */
    private function verifyPlayerExists(int $matric): void
    {
        $sql = "SELECT COUNT(*) FROM kp_licence WHERE Matric = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$matric]);

        if ((int)$result->fetchOne() === 0) {
            throw new \Exception("Player with matric $matric does not exist");
        }
    }

    /**
     * Merge scrutineering data from source to target
     *
     * Uses a temporary table to handle the complex FK constraints and data merging.
     */
    private function mergeScrutineeringData(int $sourceMatric, int $targetMatric): void
    {
        // Create temporary table
        $this->connection->executeStatement("DROP TEMPORARY TABLE IF EXISTS temp_scrutineering_fusion");

        $sql = "CREATE TEMPORARY TABLE temp_scrutineering_fusion (
            id_equipe INT PRIMARY KEY,
            kayak_status INT,
            kayak_print INT,
            vest_status INT,
            vest_print INT,
            helmet_status INT,
            helmet_print INT,
            paddle_count INT,
            paddle_print INT,
            comment TEXT
        )";
        $this->connection->executeStatement($sql);

        // Copy target data first
        $sql = "INSERT INTO temp_scrutineering_fusion
                (id_equipe, kayak_status, kayak_print, vest_status, vest_print,
                 helmet_status, helmet_print, paddle_count, paddle_print, comment)
                SELECT id_equipe, kayak_status, kayak_print, vest_status, vest_print,
                       helmet_status, helmet_print, paddle_count, paddle_print, comment
                FROM kp_scrutineering
                WHERE matric = ?";
        $this->connection->executeStatement($sql, [$targetMatric]);

        // Merge source data, keeping non-null target values
        $sql = "INSERT INTO temp_scrutineering_fusion
                (id_equipe, kayak_status, kayak_print, vest_status, vest_print,
                 helmet_status, helmet_print, paddle_count, paddle_print, comment)
                SELECT id_equipe, kayak_status, kayak_print, vest_status, vest_print,
                       helmet_status, helmet_print, paddle_count, paddle_print, comment
                FROM kp_scrutineering
                WHERE matric = ?
                ON DUPLICATE KEY UPDATE
                    kayak_status = CASE WHEN temp_scrutineering_fusion.kayak_status IS NULL OR temp_scrutineering_fusion.kayak_status = 0 THEN VALUES(kayak_status) ELSE temp_scrutineering_fusion.kayak_status END,
                    kayak_print = CASE WHEN temp_scrutineering_fusion.kayak_print IS NULL OR temp_scrutineering_fusion.kayak_print = 0 THEN VALUES(kayak_print) ELSE temp_scrutineering_fusion.kayak_print END,
                    vest_status = CASE WHEN temp_scrutineering_fusion.vest_status IS NULL OR temp_scrutineering_fusion.vest_status = 0 THEN VALUES(vest_status) ELSE temp_scrutineering_fusion.vest_status END,
                    vest_print = CASE WHEN temp_scrutineering_fusion.vest_print IS NULL OR temp_scrutineering_fusion.vest_print = 0 THEN VALUES(vest_print) ELSE temp_scrutineering_fusion.vest_print END,
                    helmet_status = CASE WHEN temp_scrutineering_fusion.helmet_status IS NULL OR temp_scrutineering_fusion.helmet_status = 0 THEN VALUES(helmet_status) ELSE temp_scrutineering_fusion.helmet_status END,
                    helmet_print = CASE WHEN temp_scrutineering_fusion.helmet_print IS NULL OR temp_scrutineering_fusion.helmet_print = 0 THEN VALUES(helmet_print) ELSE temp_scrutineering_fusion.helmet_print END,
                    paddle_count = CASE WHEN temp_scrutineering_fusion.paddle_count IS NULL OR temp_scrutineering_fusion.paddle_count = 0 THEN VALUES(paddle_count) ELSE temp_scrutineering_fusion.paddle_count END,
                    paddle_print = CASE WHEN temp_scrutineering_fusion.paddle_print IS NULL OR temp_scrutineering_fusion.paddle_print = 0 THEN VALUES(paddle_print) ELSE temp_scrutineering_fusion.paddle_print END,
                    comment = CASE
                        WHEN temp_scrutineering_fusion.comment IS NULL OR temp_scrutineering_fusion.comment = '' THEN VALUES(comment)
                        WHEN VALUES(comment) IS NULL OR VALUES(comment) = '' THEN temp_scrutineering_fusion.comment
                        ELSE CONCAT(temp_scrutineering_fusion.comment, ' | ', VALUES(comment))
                    END";
        $this->connection->executeStatement($sql, [$sourceMatric]);

        // Delete scrutineering entries for both source and target
        $sql = "DELETE FROM kp_scrutineering WHERE matric IN (?, ?)";
        $this->connection->executeStatement($sql, [$sourceMatric, $targetMatric]);

        // Delete competition team player duplicates
        $sql = "DELETE cej_source FROM kp_competition_equipe_joueur cej_source
                INNER JOIN kp_competition_equipe_joueur cej_target
                    ON cej_source.Id_equipe = cej_target.Id_equipe
                WHERE cej_source.Matric = ?
                AND cej_target.Matric = ?";
        $this->connection->executeStatement($sql, [$sourceMatric, $targetMatric]);

        // Update remaining competition team player entries
        $sql = "UPDATE kp_competition_equipe_joueur cej, kp_licence lc
                SET cej.Matric = ?, cej.Nom = lc.Nom, cej.Prenom = lc.Prenom, cej.Sexe = lc.Sexe
                WHERE cej.Matric = ?
                AND lc.Matric = ?";
        $this->connection->executeStatement($sql, [$targetMatric, $sourceMatric, $targetMatric]);

        // Re-insert merged scrutineering data
        $sql = "INSERT INTO kp_scrutineering
                (id_equipe, matric, kayak_status, kayak_print, vest_status, vest_print,
                 helmet_status, helmet_print, paddle_count, paddle_print, comment)
                SELECT t.id_equipe, ?, t.kayak_status, t.kayak_print, t.vest_status, t.vest_print,
                       t.helmet_status, t.helmet_print, t.paddle_count, t.paddle_print, t.comment
                FROM temp_scrutineering_fusion t
                ON DUPLICATE KEY UPDATE
                    kayak_status = VALUES(kayak_status),
                    kayak_print = VALUES(kayak_print),
                    vest_status = VALUES(vest_status),
                    vest_print = VALUES(vest_print),
                    helmet_status = VALUES(helmet_status),
                    helmet_print = VALUES(helmet_print),
                    paddle_count = VALUES(paddle_count),
                    paddle_print = VALUES(paddle_print),
                    comment = CASE
                        WHEN kp_scrutineering.comment IS NULL OR kp_scrutineering.comment = '' THEN VALUES(comment)
                        WHEN VALUES(comment) IS NULL OR VALUES(comment) = '' THEN kp_scrutineering.comment
                        ELSE CONCAT(kp_scrutineering.comment, ' | ', VALUES(comment))
                    END";
        $this->connection->executeStatement($sql, [$targetMatric]);

        // Clean up temporary table
        $this->connection->executeStatement("DROP TEMPORARY TABLE IF EXISTS temp_scrutineering_fusion");
    }

    /**
     * Find the best player to use as merge target based on:
     * 1. Has ICF number
     * 2. Has valid birth date
     * 3. Has referee qualification
     * 4. Most recent season
     * 5. Lowest matric number
     */
    private function findBestTargetPlayer(array $matricules): ?int
    {
        $placeholders = implode(',', array_fill(0, count($matricules), '?'));

        $sql = "SELECT
                    l.Matric,
                    l.Reserve as Numero_ICF,
                    l.Naissance,
                    l.Origine as Saison,
                    a.arbitre
                FROM kp_licence l
                LEFT JOIN kp_arbitre a ON l.Matric = a.Matric
                WHERE l.Matric IN ($placeholders)
                ORDER BY
                    CASE WHEN l.Reserve IS NOT NULL AND l.Reserve != '' THEN 0 ELSE 1 END,
                    CASE WHEN l.Naissance IS NOT NULL AND l.Naissance != '0000-00-00' AND l.Naissance != '' THEN 0 ELSE 1 END,
                    CASE WHEN a.arbitre IS NOT NULL AND a.arbitre != '' THEN 0 ELSE 1 END,
                    l.Origine DESC,
                    l.Matric ASC
                LIMIT 1";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($matricules);
        $row = $result->fetchAssociative();

        return $row ? (int)$row['Matric'] : null;
    }

    /**
     * Enrich target player with missing information from source players
     */
    private function enrichTargetFromSources(int $targetMatric, array $sourceMatriculees): void
    {
        // Get target info
        $sql = "SELECT l.Naissance, l.Reserve as Numero_ICF, a.arbitre
                FROM kp_licence l
                LEFT JOIN kp_arbitre a ON l.Matric = a.Matric
                WHERE l.Matric = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$targetMatric]);
        $target = $result->fetchAssociative();

        if (!$target) {
            return;
        }

        $updateFields = [];
        $updateParams = [];

        // Check each source for missing info
        foreach ($sourceMatriculees as $sourceMatric) {
            $sql = "SELECT l.Naissance, l.Reserve as Numero_ICF, a.arbitre, a.Matric as ArbitreMatric
                    FROM kp_licence l
                    LEFT JOIN kp_arbitre a ON l.Matric = a.Matric
                    WHERE l.Matric = ?";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery([(int)$sourceMatric]);
            $source = $result->fetchAssociative();

            if (!$source) {
                continue;
            }

            // Copy birth date if target is missing
            if ((empty($target['Naissance']) || $target['Naissance'] === '0000-00-00') &&
                !empty($source['Naissance']) && $source['Naissance'] !== '0000-00-00') {
                $updateFields[] = 'Naissance = ?';
                $updateParams[] = $source['Naissance'];
                $target['Naissance'] = $source['Naissance'];
            }

            // Copy ICF number if target is missing
            if (empty($target['Numero_ICF']) && !empty($source['Numero_ICF'])) {
                $updateFields[] = 'Reserve = ?';
                $updateParams[] = $source['Numero_ICF'];
                $target['Numero_ICF'] = $source['Numero_ICF'];
            }

            // Copy referee info if target is missing
            if (empty($target['arbitre']) && !empty($source['arbitre'])) {
                $sql = "INSERT INTO kp_arbitre (Matric, regional, interregional, national, international, arbitre, livret, niveau, saison)
                        SELECT ?, regional, interregional, national, international, arbitre, livret, niveau, saison
                        FROM kp_arbitre
                        WHERE Matric = ?
                        ON DUPLICATE KEY UPDATE
                            regional = VALUES(regional),
                            interregional = VALUES(interregional),
                            national = VALUES(national),
                            international = VALUES(international),
                            arbitre = VALUES(arbitre),
                            livret = VALUES(livret),
                            niveau = VALUES(niveau),
                            saison = VALUES(saison)";
                $this->connection->executeStatement($sql, [$targetMatric, (int)$sourceMatric]);
                $target['arbitre'] = $source['arbitre'];
            }
        }

        // Update license if there are changes
        if (!empty($updateFields)) {
            $updateParams[] = $targetMatric;
            $sql = "UPDATE kp_licence SET " . implode(', ', $updateFields) . " WHERE Matric = ?";
            $this->connection->executeStatement($sql, $updateParams);
        }
    }
}
