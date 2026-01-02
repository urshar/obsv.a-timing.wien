<?php

namespace App\Support;

use App\Models\Meet;

class MeetDeletionGuard
{
    public static function canDelete(Meet $meet): bool
    {
        return self::reasons($meet) === [];
    }

    /**
     * @return array<string> Liste von Gründen, warum Delete blockiert ist.
     */
    public static function reasons(Meet $meet): array
    {
        // Sehr schnell: COUNT's aus DB
        $meet->loadCount(['sessions', 'events', 'ageGroups']);

        $reasons = [];

        if (($meet->sessions_count ?? 0) > 0) {
            $reasons[] = 'This meeting has sessions.';
        }

        if (($meet->events_count ?? 0) > 0) {
            $reasons[] = 'This meeting has events.';
        }

        if (($meet->age_groups_count ?? 0) > 0) {
            $reasons[] = 'This meeting has age groups.';
        }

        /**
         * TODO (später):
         * - entries/results/records checks ergänzen, sobald Tabellen/Relationen existieren
         * z.B.:
         * if ($meet->entries()->exists()) $reasons[] = 'This meeting has entries.';
         */

        return $reasons;
    }
}
