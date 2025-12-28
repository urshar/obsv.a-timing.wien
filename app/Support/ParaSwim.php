<?php

namespace App\Support;

final class ParaSwim
{
    public static function formatSportClasses(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        // split CSV
        $parts = array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== ''));

        if (count($parts) === 0) {
            return null;
        }

        // Alle numerisch?
        $allNumeric = true;
        $nums = [];

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

            if (count($nums) >= 2 && self::isContiguous($nums)) {
                return $nums[0].'–'.$nums[count($nums) - 1];
            }

            return implode(', ', array_map('strval', $nums));
        }

        // Prefix+Num? (S11, SB3, SM10 etc.)
        $allPrefixed = true;
        $prefix = null;
        $pNums = [];

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

            $pNums[] = $num;
        }

        if ($allPrefixed && $prefix !== null) {
            sort($pNums);
            $pNums = array_values(array_unique($pNums));

            if (count($pNums) >= 2 && self::isContiguous($pNums)) {
                return $prefix.$pNums[0].'–'.$prefix.$pNums[count($pNums) - 1];
            }

            return implode(', ', array_map(fn ($n) => $prefix.$n, $pNums));
        }

        // Fallback: sauber mit "," joinen
        return implode(', ', $parts);
    }

    private static function isContiguous(array $nums): bool
    {
        $n = count($nums);
        if ($n < 2) {
            return false;
        }

        for ($i = 1; $i < $n; $i++) {
            if ($nums[$i] !== $nums[$i - 1] + 1) {
                return false;
            }
        }

        return true;
    }

    public static function strokePrefix(?string $stroke): string
    {
        $s = strtoupper(trim((string) $stroke));

        // wenn du Werte wie "BREAST", "MEDLEY" verwendest
        if ($s === 'BREAST') {
            return 'SB';
        }
        if ($s === 'MEDLEY') {
            return 'SM';
        }

        // FREE/BACK/FLY etc.
        return 'S';
    }
}
