<?php

namespace App\Trait;

use Doctrine\DBAL\Connection;

/**
 * Trait for logging admin actions to kp_journal table.
 *
 * Controllers using this trait must have:
 * - A `$this->connection` property (Doctrine DBAL Connection)
 * - Access to `$this->getUser()` (AbstractController)
 */
trait AdminLoggableTrait
{
    /**
     * Log an admin action with a season context (Code_saison column).
     * Used by AdminCompetitionsController and AdminTeamsController.
     */
    private function logActionForSeason(string $action, ?string $season = null, ?string $details = null): void
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

    /**
     * Log an admin action with an event context (Code_evenement column).
     * Used by AdminEventController and AdminOperationsController.
     */
    private function logActionForEvent(string $action, ?int $eventId = null, ?string $details = null): void
    {
        try {
            $user = $this->getUser();
            $userId = $user?->getUserIdentifier() ?? 'system';

            $sql = "INSERT INTO kp_journal (Date, Heure, User, Action, Code_evenement, Details)
                    VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?)";

            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$userId, $action, $eventId, $details]);
        } catch (\Exception) {
            // Log silently fails - don't break the main operation
        }
    }
}
