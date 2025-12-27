<?php

namespace App\Services\Lenex;

use App\Support\Concerns\LenexXmlValueHelpers;
use SimpleXMLElement;

class LenexStructureExtractor
{
    use LenexXmlValueHelpers;

    public function extract(SimpleXMLElement $xml): array
    {
        $ageGroupsByCode = [];
        $sessions = [];

        /**
         * 1) Sessions + Events (namespace-safe)
         */
        $sessionNodes = $xml->xpath('//*[local-name()="SESSION"]') ?: [];

        foreach ($sessionNodes as $session) {
            $sessionNo = $this->intAttrAnyNullable($session, ['number', 'no', 'sessionid', 'session_no']);
            $sessionDate = $this->strAttrAnyNullable($session, ['date']);
            $sessionTime = $this->strAttrAnyNullable($session, ['starttime', 'start_time', 'time']);

            $eventsOut = [];

            $eventNodes = $session->xpath('./*[local-name()="EVENTS"]/*[local-name()="EVENT"]') ?: [];
            foreach ($eventNodes as $event) {
                $eventNo = $this->intAttrAnyNullable($event, ['number', 'no', 'eventid', 'event_no']);
                $round = $this->strAttrAnyNullable($event, ['round', 'roundid']);
                $gender = $this->normGender($this->strAttrAnyNullable($event, ['gender']));

                // SWIMSTYLE (namespace-safe)
                $style = $event->xpath('./*[local-name()="SWIMSTYLE"]')[0] ?? null;

                $distance = $style ? $this->intAttrAnyNullable($style, ['distance']) : null;
                $stroke = $style ? $this->strAttrAnyNullable($style, ['stroke']) : null;
                $relayCount = $style ? $this->intAttrAnyNullable($style, ['relaycount', 'relay_count']) : null;
                $isRelay = ($relayCount !== null && $relayCount > 1);

                // Event display name
                $eventName = $this->buildEventName($distance, $stroke, $relayCount);

                /**
                 * Event AgeGroups (namespace-safe) – this is the key fix
                 * EVENT/AGEGROUPS/AGEGROUP @agegroupid
                 */
                $eventAgeGroups = [];
                $eventAgNodes = $event->xpath('./*[local-name()="AGEGROUPS"]/*[local-name()="AGEGROUP"]') ?: [];

                foreach ($eventAgNodes as $agNode) {
                    $code = $this->strAttrAnyNullable($agNode, ['agegroupid', 'code', 'id']);
                    if ($code === null) {
                        continue;
                    }

                    $code = trim($code);
                    if ($code === '') {
                        continue;
                    }

                    $eventAgeGroups[] = $code;

                    // Collect/merge global age group details from the node (robust)
                    $normalized = $this->normalizeAgeGroup($agNode);
                    if ($normalized !== null) {
                        $existing = $ageGroupsByCode[$normalized['code']] ?? null;
                        $ageGroupsByCode[$normalized['code']] = $this->mergeAgeGroups($existing, $normalized);
                    }
                }

                $eventAgeGroups = array_values(array_unique($eventAgeGroups));

                $eventsOut[] = [
                    'no' => $eventNo,
                    'name' => $eventName,
                    'gender' => $gender,
                    'round' => $round,
                    'distance' => $distance,
                    'stroke' => $stroke,
                    'is_relay' => $isRelay,
                    'age_groups' => $eventAgeGroups,
                ];
            }

            $sessions[] = [
                'no' => $sessionNo,
                'date' => $sessionDate,
                'start_time' => $sessionTime,
                'events' => $eventsOut,
            ];
        }

        /**
         * 2) Global AgeGroups (avoid duplicates):
         * Do NOT use //AGEGROUPS/AGEGROUP, because it also matches EVENT/AGEGROUPS.
         * Instead, read only MEET-level AGEGROUPS.
         */
        $globalAgNodes =
            $xml->xpath('//*[local-name()="MEET"]/*[local-name()="AGEGROUPS"]/*[local-name()="AGEGROUP"]') ?: [];

        foreach ($globalAgNodes as $agNode) {
            $normalized = $this->normalizeAgeGroup($agNode);
            if ($normalized === null) {
                continue;
            }

            $existing = $ageGroupsByCode[$normalized['code']] ?? null;
            $ageGroupsByCode[$normalized['code']] = $this->mergeAgeGroups($existing, $normalized);
        }

        // Optional: stable order by code (numeric first)
        $ageGroups = array_values($ageGroupsByCode);
        usort($ageGroups, function (array $a, array $b): int {
            $ac = (string) ($a['code'] ?? '');
            $bc = (string) ($b['code'] ?? '');

            $an = ctype_digit($ac) ? (int) $ac : null;
            $bn = ctype_digit($bc) ? (int) $bc : null;

            if ($an !== null && $bn !== null) {
                return $an <=> $bn;
            }

            return strcmp($ac, $bc);
        });

        return [
            'age_groups' => $ageGroups,
            'sessions' => $sessions,
        ];
    }

    /**
     * Wrapper: try multiple attribute names; uses LenexXmlValueHelpers::intAttrNullable($node, string $attr)
     */
    private function intAttrAnyNullable(SimpleXMLElement $node, array $attrs): ?int
    {
        foreach ($attrs as $attr) {
            $v = $this->intAttrNullable($node, (string) $attr);
            if ($v === null) {
                continue;
            }

            return $v;
        }

        return null;
    }

    /**
     * Wrapper: try multiple attribute names; uses LenexXmlValueHelpers::strAttrNullable($node, string $attr)
     */
    private function strAttrAnyNullable(SimpleXMLElement $node, array $attrs): ?string
    {
        foreach ($attrs as $attr) {
            $v = $this->strAttrNullable($node, (string) $attr);
            if ($v === null) {
                continue;
            }
            $v = trim($v);
            if ($v === '') {
                continue;
            }

            return $v;
        }

        return null;
    }

    private function normGender(?string $g): ?string
    {
        if ($g === null) {
            return null;
        }

        $g = strtoupper(trim($g));
        if ($g === '') {
            return null;
        }

        return match ($g) {
            'MALE' => 'M',
            'FEMALE' => 'F',
            'MIXED' => 'X',
            default => $g, // already M/F/X or other
        };
    }

    /**
     * UI name from SWIMSTYLE; relay: "4x50m Freistil", else "100m Brust"
     */
    private function buildEventName(?int $distance, ?string $stroke, ?int $relayCount): string
    {
        $strokeLabel = $this->strokeLabel($stroke);

        if ($relayCount !== null && $relayCount > 1) {
            $rc = $relayCount;
            if ($distance !== null) {
                return trim($rc.'x'.$distance.'m '.($strokeLabel ?? ''));
            }

            return trim($rc.'x?m '.($strokeLabel ?? ''));
        }

        if ($distance !== null && $strokeLabel !== null) {
            return $distance.'m '.$strokeLabel;
        }

        if ($distance !== null) {
            return $distance.'m';
        }

        return 'Event';
    }

    private function strokeLabel(?string $stroke): ?string
    {
        if ($stroke === null) {
            return null;
        }

        $s = strtoupper(trim($stroke));
        if ($s === '') {
            return null;
        }

        return match ($s) {
            'FREE' => 'Freistil',
            'BACK' => 'Rücken',
            'BREAST' => 'Brust',
            'FLY' => 'Delfin',
            'MEDLEY' => 'Lagen',
            default => $s,
        };
    }

    /**
     * Normalize an AGEGROUP node (handicap is a single valuation string; do not split).
     */
    private function normalizeAgeGroup(SimpleXMLElement $ag): ?array
    {
        $code = $this->strAttrAnyNullable($ag, ['agegroupid', 'code', 'id']);
        if ($code === null) {
            return null;
        }
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        $min = $this->intAttrAnyNullable($ag, ['agemin', 'min', 'min_age']);
        $max = $this->intAttrAnyNullable($ag, ['agemax', 'max', 'max_age']);

        if ($min !== null && $min < 0) {
            $min = null;
        }
        if ($max !== null && $max < 0) {
            $max = null;
        }

        $gender = $this->normGender($this->strAttrAnyNullable($ag, ['gender']));
        $name = $this->strAttrAnyNullable($ag, ['name', 'label', 'description']);

        $handicap = $this->strAttrAnyNullable($ag, ['handicap']);
        $handicap = is_string($handicap) ? trim($handicap) : null;
        if ($handicap === '') {
            $handicap = null;
        }

        return [
            'code' => $code,
            'min' => $min,
            'max' => $max,
            'gender' => $gender,
            'name' => $name,
            'handicap' => $handicap,
        ];
    }

    /**
     * Merge AGEGROUP info (event nodes may be partial; global nodes may be richer).
     * Handicap is merged as a unique comma-list string, still treated as ONE valuation string.
     */
    private function mergeAgeGroups(?array $existing, array $incoming): array
    {
        if ($existing === null) {
            return $incoming;
        }

        foreach (['min', 'max', 'gender', 'name', 'handicap'] as $key) {
            if (! array_key_exists($key, $incoming)) {
                continue;
            }

            $val = $incoming[$key];

            if ($key === 'handicap') {
                $existing[$key] = $this->mergeHandicapStrings($existing[$key] ?? null, $val);

                continue;
            }

            if ($val !== null && $val !== '') {
                $existing[$key] = $val;
            }
        }

        $existing['code'] = $incoming['code'];

        return $existing;
    }

    private function mergeHandicapStrings($a, $b): ?string
    {
        $a = is_string($a) ? trim($a) : '';
        $b = is_string($b) ? trim($b) : '';

        if ($a === '' && $b === '') {
            return null;
        }
        if ($a === '') {
            return $b;
        }
        if ($b === '') {
            return $a;
        }

        // Merge as unique list but keep it as ONE string field
        $parts = array_merge(
            preg_split('/\s*,\s*/', $a) ?: [],
            preg_split('/\s*,\s*/', $b) ?: []
        );

        $seen = [];
        $out = [];
        foreach ($parts as $p) {
            $p = trim((string) $p);
            if ($p === '') {
                continue;
            }
            if (isset($seen[$p])) {
                continue;
            }
            $seen[$p] = true;
            $out[] = $p;
        }

        return implode(',', $out);
    }
}
