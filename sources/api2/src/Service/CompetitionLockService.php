<?php

namespace App\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

class CompetitionLockService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Lock competitions starting within 6 days and unlock those ended < 3 days ago.
     *
     * @return array{locked: string[], unlocked: string[]}
     */
    public function updateCompetitionLocks(): array
    {
        $season = $this->getActiveSeason();

        $locked = $this->lockUpcoming($season);
        $unlocked = $this->unlockRecent($season);

        $this->logger->info('Competition locks updated', [
            'season' => $season,
            'locked' => $locked,
            'unlocked' => $unlocked,
        ]);

        return ['locked' => $locked, 'unlocked' => $unlocked];
    }

    private function getActiveSeason(): string
    {
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $result = $this->connection->fetchOne($sql);

        if ($result === false) {
            return (string) date('Y');
        }

        return (string) $result;
    }

    /**
     * Lock national/CF competitions starting within 6 days.
     *
     * @return string[] Competition codes that were locked
     */
    private function lockUpcoming(string $season): array
    {
        $sql = "SELECT DISTINCT Code_competition
            FROM kp_journee
            WHERE Code_saison = ?
            AND Date_debut > CURDATE()
            AND DATEDIFF(Date_debut, CURDATE()) < 6
            AND (Code_competition LIKE 'N%' OR Code_competition LIKE 'CF%')";

        $codes = $this->connection->fetchFirstColumn($sql, [$season]);

        if (empty($codes)) {
            return [];
        }

        $this->connection->executeStatement(
            "UPDATE kp_competition SET Verrou = 'O' WHERE Code_saison = ? AND Code IN (?)",
            [$season, $codes],
            [\Doctrine\DBAL\ParameterType::STRING, ArrayParameterType::STRING]
        );

        return $codes;
    }

    /**
     * Unlock national/CF competitions ended less than 3 days ago.
     *
     * @return string[] Competition codes that were unlocked
     */
    private function unlockRecent(string $season): array
    {
        $sql = "SELECT DISTINCT Code_competition
            FROM kp_journee
            WHERE Code_saison = ?
            AND Date_fin < CURDATE()
            AND DATEDIFF(CURDATE(), Date_fin) < 3
            AND (Code_competition LIKE 'N%' OR Code_competition LIKE 'CF%')";

        $codes = $this->connection->fetchFirstColumn($sql, [$season]);

        if (empty($codes)) {
            return [];
        }

        $this->connection->executeStatement(
            "UPDATE kp_competition SET Verrou = 'N' WHERE Code_saison = ? AND Code IN (?)",
            [$season, $codes],
            [\Doctrine\DBAL\ParameterType::STRING, ArrayParameterType::STRING]
        );

        return $codes;
    }
}
