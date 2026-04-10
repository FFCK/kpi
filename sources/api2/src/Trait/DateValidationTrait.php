<?php

namespace App\Trait;

/**
 * Trait for date validation utilities.
 */
trait DateValidationTrait
{
    /**
     * Validate date format (YYYY-MM-DD).
     * Returns true if the date is null/empty or if it's a valid date string.
     */
    private function isValidDate(?string $date): bool
    {
        if (empty($date)) {
            return true;
        }
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
