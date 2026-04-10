<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Team Operations Service
 *
 * Handles team operations including rename, merge and move to another club.
 * Migrated from GestionOperations.php RenomEquipe, FusionEquipes, DeplaceEquipe.
 */
class TeamOperationsService
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Rename a team
     *
     * Updates team name in both kp_equipe and kp_competition_equipe tables.
     *
     * @param int $teamId Team number (Numero)
     * @param string $newName New team name
     * @throws \Exception if rename fails
     */
    public function renameTeam(int $teamId, string $newName): void
    {
        if ($teamId <= 0) {
            throw new \Exception('Invalid team ID');
        }

        if (empty(trim($newName))) {
            throw new \Exception('New name cannot be empty');
        }

        // Verify team exists
        $this->verifyTeamExists($teamId);

        $this->connection->beginTransaction();

        try {
            // Update main team table
            $sql = "UPDATE kp_equipe SET Libelle = ? WHERE Numero = ?";
            $this->connection->executeStatement($sql, [$newName, $teamId]);

            // Update competition team entries
            $sql = "UPDATE kp_competition_equipe SET Libelle = ? WHERE Numero = ?";
            $this->connection->executeStatement($sql, [$newName, $teamId]);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new \Exception('Team rename failed: ' . $e->getMessage());
        }
    }

    /**
     * Merge two teams (source into target)
     *
     * Updates all references to source team to point to target team,
     * then deletes the source team.
     *
     * @param int $sourceId Source team number to merge from
     * @param int $targetId Target team number to merge into
     * @throws \Exception if merge fails
     */
    public function mergeTeams(int $sourceId, int $targetId): void
    {
        if ($sourceId <= 0 || $targetId <= 0) {
            throw new \Exception('Invalid team IDs');
        }

        if ($sourceId === $targetId) {
            throw new \Exception('Source and target cannot be the same');
        }

        // Verify both teams exist
        $this->verifyTeamExists($sourceId);
        $targetTeam = $this->getTeam($targetId);

        if (!$targetTeam) {
            throw new \Exception("Target team $targetId does not exist");
        }

        $this->connection->beginTransaction();

        try {
            // Update competition team entries to point to target
            $sql = "UPDATE kp_competition_equipe
                    SET Numero = ?, Libelle = ?
                    WHERE Numero = ?";
            $this->connection->executeStatement($sql, [
                $targetId,
                $targetTeam['Libelle'],
                $sourceId
            ]);

            // Delete source team from main table
            $sql = "DELETE FROM kp_equipe WHERE Numero = ?";
            $this->connection->executeStatement($sql, [$sourceId]);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new \Exception('Team merge failed: ' . $e->getMessage());
        }
    }

    /**
     * Move a team to another club
     *
     * Updates the club code for a team in both kp_equipe and kp_competition_equipe tables.
     *
     * @param int $teamId Team number to move
     * @param string $clubCode New club code
     * @throws \Exception if move fails
     */
    public function moveTeamToClub(int $teamId, string $clubCode): void
    {
        if ($teamId <= 0) {
            throw new \Exception('Invalid team ID');
        }

        if (empty(trim($clubCode))) {
            throw new \Exception('Club code cannot be empty');
        }

        // Verify team exists
        $this->verifyTeamExists($teamId);

        // Verify club exists
        $this->verifyClubExists($clubCode);

        $this->connection->beginTransaction();

        try {
            // Update competition team entries
            $sql = "UPDATE kp_competition_equipe SET Code_club = ? WHERE Numero = ?";
            $this->connection->executeStatement($sql, [$clubCode, $teamId]);

            // Update main team table
            $sql = "UPDATE kp_equipe SET Code_club = ? WHERE Numero = ?";
            $this->connection->executeStatement($sql, [$clubCode, $teamId]);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new \Exception('Team move failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify a team exists in the database
     */
    private function verifyTeamExists(int $teamId): void
    {
        $sql = "SELECT COUNT(*) FROM kp_equipe WHERE Numero = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$teamId]);

        if ((int)$result->fetchOne() === 0) {
            throw new \Exception("Team with ID $teamId does not exist");
        }
    }

    /**
     * Verify a club exists in the database
     */
    private function verifyClubExists(string $clubCode): void
    {
        $sql = "SELECT COUNT(*) FROM kp_club WHERE Code = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$clubCode]);

        if ((int)$result->fetchOne() === 0) {
            throw new \Exception("Club with code $clubCode does not exist");
        }
    }

    /**
     * Get team details by ID
     */
    private function getTeam(int $teamId): ?array
    {
        $sql = "SELECT Numero, Libelle, Code_club FROM kp_equipe WHERE Numero = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$teamId]);
        $row = $result->fetchAssociative();

        return $row ?: null;
    }
}
