<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Throwable;

final class UiFormat
{
    /**
     * Formats a date-like value for UI output.
     *
     * Accepts Carbon, DateTimeInterface-like (CarbonInterface), or a string (e.g. "2026-01-27").
     */
    public static function date(mixed $value, string $format = 'd.m.Y', string $fallback = '—'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format($format);
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value)->format($format);
            } catch (Throwable) {
                return $fallback;
            }
        }

        return $fallback;
    }

    public static function dateRange(
        mixed $start,
        mixed $end,
        string $sameDayFormat = 'd.m.Y',
        string $leftFormat = 'd.m.',
        string $rightFormat = 'd.m.Y',
        string $fallback = '—'
    ): string {
        $s = self::toCarbonOrNull($start);
        $e = self::toCarbonOrNull($end);

        if (! $s && ! $e) {
            return $fallback;
        }

        if ($s && ! $e) {
            return $s->format($sameDayFormat);
        }

        if (! $s && $e) {
            return $e->format($sameDayFormat);
        }

        // both present
        if ($s->greaterThan($e)) {
            [$s, $e] = [$e, $s];
        }

        if ($s->isSameDay($e)) {
            return $s->format($sameDayFormat);
        }

        return $s->format($leftFormat).' – '.$e->format($rightFormat);
    }

    /**
     * @internal
     */
    private static function toCarbonOrNull(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value);
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }
}
