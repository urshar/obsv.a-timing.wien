<?php

namespace App\Support;

final class ParaSwim
{
    public static function strokePrefix(?string $stroke): string
    {
        $s = strtoupper(trim((string) $stroke));

        if ($s === 'BREAST') {
            return 'SB';
        }
        if ($s === 'MEDLEY') {
            return 'SM';
        }

        return 'S';
    }

    public static function formatSportClasses(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== ''));
        if (count($parts) === 0) {
            return null;
        }

        // 1) rein numerisch? (z.B. "1,2,3,10,21")
        $nums = [];
        $allNumeric = true;

        foreach ($parts as $p) {
            if (! preg_match('/^\d+$/', $p)) {
                $allNumeric = false;
                break;
            }
            $nums[] = (int) $p;
        }

        if ($allNumeric) {
            sort($nums);
            $nums = array_values(array_unique($nums));

            $ranges = self::rangesFromSorted($nums);

            // "1–4, 5, 8–10, 21"
            return implode(', ', array_map(function (array $r) {
                [$a, $b] = $r;

                return ($a === $b) ? (string) $a : ($a.'–'.$b);
            }, $ranges));
        }

        // 2) Prefix+Num? (z.B. "S11,S12,S13,S15")
        $prefix = null;
        $pnums = [];
        $allPrefixed = true;

        foreach ($parts as $p) {
            if (! preg_match('/^([A-Z]+)\s*(\d+)$/i', $p, $m)) {
                $allPrefixed = false;
                break;
            }

            $pfx = strtoupper($m[1]);
            $num = (int) $m[2];

            if ($prefix === null) {
                $prefix = $pfx;
            } elseif ($prefix !== $pfx) {
                $allPrefixed = false;
                break;
            }

            $pnums[] = $num;
        }

        if ($allPrefixed && $prefix !== null) {
            sort($pnums);
            $pnums = array_values(array_unique($pnums));

            $ranges = self::rangesFromSorted($pnums);

            // "S11–S13, S15"
            return implode(', ', array_map(function (array $r) use ($prefix) {
                [$a, $b] = $r;

                return ($a === $b)
                    ? ($prefix.$a)
                    : ($prefix.$a.'–'.$prefix.$b);
            }, $ranges));
        }

        // 3) Fallback: einfach sauber joinen
        return implode(', ', $parts);
    }

    private static function rangesFromSorted(array $nums): array
    {
        // Erwartet: sortiert + unique
        $ranges = [];
        $n = count($nums);

        if ($n === 0) {
            return $ranges;
        }

        $start = $nums[0];
        $prev = $nums[0];

        for ($i = 1; $i < $n; $i++) {
            $cur = $nums[$i];

            if ($cur === $prev + 1) {
                $prev = $cur;

                continue;
            }

            // Block endet
            $ranges[] = [$start, $prev];
            $start = $cur;
            $prev = $cur;
        }

        // letzten Block hinzufügen
        $ranges[] = [$start, $prev];

        return $ranges;
    }

    public static function ageLabel(?int $maxAge): string
    {
        // ParaSwim: nur 2 Altersklassen: ≤18 und offen,
        // wenn max_age gesetzt und ≤ 18 → Jugend, sonst Offen
        if ($maxAge !== null && $maxAge <= 18) {
            return 'Jugend';
        }

        return 'Offen';
    }

    /**
     * Parsed handicap CSV in integer set (z.B. "1,2,3,10" => [1,2,3,10])
     */
    public static function parseClasses(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== ''));

        $out = [];
        foreach ($parts as $p) {
            if (preg_match('/^\d+$/', $p)) {
                $out[] = (int) $p;
            }
        }

        $out = array_values(array_unique($out));
        sort($out);

        return $out;
    }
}
