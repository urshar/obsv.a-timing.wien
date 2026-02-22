<?php

namespace App\Services;

use App\Models\Athlete;
use App\Models\AthleteSportclassHistory;
use App\Models\SportClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class AthleteSportclassSyncService
{
    /**
     * @param  string|null  $code  z.B. "S10" (wenn null/leer: nichts tun)
     * @param  Carbon|string|null  $effectiveDate  Datum ab wann diese Klasse gilt (default: today)
     * @param  string  $source  'lenex'|'manual'|'api'
     * @param  string|null  $sourceRef  z.B. Lenex athleteId
     * @param  int|null  $meetId  optional
     *
     * @throws Throwable
     */
    public function syncDiscipline(
        Athlete $athlete,
        string $discipline,     // 'S'|'SB'|'SM'
        ?string $code,          // 'S7'|'SB6'|'SM7'
        Carbon|string|null $effectiveDate = null,
        string $source = 'lenex',
        ?string $sourceRef = null,
        ?int $meetId = null,
        ?string $notes = null
    ): void {
        $code = $code ? strtoupper(trim($code)) : null;
        if (! $code) {
            return;
        }

        $date = $effectiveDate
            ? ($effectiveDate instanceof Carbon ? $effectiveDate->copy() : Carbon::parse($effectiveDate))
            : Carbon::today();

        DB::transaction(function () use ($athlete, $discipline, $code, $date, $source, $sourceRef, $meetId, $notes) {
            DB::table('athletes')->where('id', $athlete->id)->lockForUpdate()->first();

            $sportClass = SportClass::query()->firstOrCreate(
                ['code' => $code],
                ['discipline' => $discipline, 'is_active' => true]
            );

            $current = AthleteSportclassHistory::query()
                ->where('athlete_id', $athlete->id)
                ->where('discipline', $discipline)
                ->whereNull('valid_to')
                ->lockForUpdate()
                ->first();

            if ($current && (int) $current->sport_class_id === (int) $sportClass->id) {
                return; // no-op
            }

            $current?->update(['valid_to' => $date->toDateString()]);

            AthleteSportclassHistory::query()->create([
                'athlete_id' => $athlete->id,
                'sport_class_id' => $sportClass->id,
                'discipline' => $discipline,
                'valid_from' => $date->toDateString(),
                'valid_to' => null,
                'source' => $source,
                'source_ref' => $sourceRef,
                'meet_id' => $meetId,
                'notes' => $notes,
            ]);
        });
    }

    private function findOrCreateSportClass(string $code): SportClass
    {
        // simple discipline detection: "SB9" -> "SB", "S10" -> "S"
        $discipline = null;
        if (preg_match('/^(SB|SM|S)\s*\d+/i', $code, $m)) {
            $discipline = strtoupper($m[1]);
        }

        return SportClass::query()->firstOrCreate(
            ['code' => strtoupper($code)],
            ['discipline' => $discipline, 'is_active' => true]
        );
    }
}
