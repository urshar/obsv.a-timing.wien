<?php

namespace App\Services\Lenex;

use App\Support\Concerns\LenexXmlValueHelpers;
use SimpleXMLElement;

class LenexStructureExtractor
{
    use LenexXmlValueHelpers;

    public function extract(SimpleXMLElement $xml): array
    {
        // AGE GROUPS
        $ageGroups = [];
        $agNodes = $xml->xpath('//AGEGROUP') ?: [];
        foreach ($agNodes as $ag) {
            if (! $ag instanceof SimpleXMLElement) {
                continue;
            }

            $code = $this->strAttrNullable($ag, 'agegroupid') ?? $this->strAttrNullable($ag, 'code');
            if (! $code) {
                continue;
            }

            $ageGroups[] = [
                'code' => $code,
                'min' => $this->intAttrNullable($ag, 'agemin') ?? $this->intAttrNullable($ag, 'min'),
                'max' => $this->intAttrNullable($ag, 'agemax') ?? $this->intAttrNullable($ag, 'max'),
                'gender' => $this->intAttrNullable($ag, 'gender') ?? $this->intAttrNullable($ag, 'gender'),
                'name' => $this->intAttrNullable($ag, 'name') ?? $this->intAttrNullable($ag, 'name'),
                'handicap' => $this->intAttrNullable($ag, 'handicap') ?? $this->intAttrNullable($ag, 'handicap'),
            ];
        }

        // SESSIONS + EVENTS
        $sessions = [];
        $sessionNodes = $xml->xpath('//SESSION') ?: [];
        foreach ($sessionNodes as $s) {
            if (! $s instanceof SimpleXMLElement) {
                continue;
            }

            $date = $this->strAttrNullable($s, 'date') ?? $this->strAttrNullable($s, 'day');
            $time = $this->strAttrNullable($s, 'starttime') ?? $this->strAttrNullable($s, 'time');
            $number = $this->intAttrNullable($s, 'number') ?? $this->intAttrNullable($s, 'sessionid');

            $events = [];
            $eventNodes = $s->xpath('.//EVENT') ?: [];
            foreach ($eventNodes as $e) {
                if (! $e instanceof SimpleXMLElement) {
                    continue;
                }

                $eventNo = $this->intAttrNullable($e, 'number') ?? $this->intAttrNullable($e, 'eventid');
                $eName = $this->strAttrNullable($e, 'name') ?? LenexXml::text($e->NAME ?? null);

                $gender = $this->genderChar($this->strAttrNullable($e, 'gender'));
                $distance = $this->intAttrNullable($e, 'distance');
                $stroke = $this->strAttrNullable($e, 'stroke');
                $round = $this->strAttrNullable($e, 'round');

                $isRelay = $this->boolAttr($e, ['relay', 'isrelay'])
                    ?? (! empty($e->xpath('.//RELAY')));

                $ageGroupCode = $this->strAttrNullable($e, 'agegroupid') ?? $this->strAttrNullable($e, 'agegroup');

                $events[] = [
                    'no' => $eventNo,
                    'name' => $eName,
                    'gender' => $gender,
                    'distance' => $distance,
                    'stroke' => $stroke,
                    'round' => $round,
                    'is_relay' => $isRelay,
                    'age_group' => $ageGroupCode,
                ];
            }

            $sessions[] = [
                'no' => $number,
                'date' => $date,
                'start_time' => $time,
                'events' => $events,
            ];
        }

        return [
            'age_groups' => $ageGroups,
            'sessions' => $sessions,
        ];
    }
}
