<?php

namespace App\Services\Lenex;

use App\Support\Concerns\LenexXmlValueHelpers;
use SimpleXMLElement;

class LenexEntryExtractor
{
    use LenexXmlValueHelpers;

    /**
     * @return array<int, array[]> eventId => entries[]
     */
    public function extract(SimpleXMLElement $xml): array
    {
        $entriesByEventId = [];

        $athletes = $xml->xpath('//*[local-name()="ATHLETE"]') ?: [];

        foreach ($athletes as $athlete) {
            $athleteId = $this->intAttrAnyNullable($athlete, ['athleteid']);
            if (! $athleteId) {
                continue;
            }

            $entryNodes = $athlete->xpath('./*[local-name()="ENTRIES"]/*[local-name()="ENTRY"]') ?: [];

            foreach ($entryNodes as $entry) {
                $eventId = $this->intAttrAnyNullable($entry, ['eventid']);
                if (! $eventId) {
                    continue;
                }

                $entryTime = $this->strAttrAnyNullable($entry, ['entrytime']);
                $course = null;

                $meetInfo = $entry->xpath('./*[local-name()="MEETINFO"]')[0] ?? null;
                if ($meetInfo) {
                    $course = $this->strAttrAnyNullable($meetInfo, ['course']);
                }

                $entriesByEventId[$eventId][] = [
                    'athlete_id' => $athleteId,
                    'entry_time' => $entryTime,
                    'course' => $course,
                ];
            }
        }

        return $entriesByEventId;
    }
}
