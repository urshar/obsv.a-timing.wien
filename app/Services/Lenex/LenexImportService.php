<?php

namespace App\Services\Lenex;

use App\Models\Athlete;
use App\Models\Club;
use App\Models\Facility;
use App\Models\ImportBatch;
use App\Models\ImportIssue;
use App\Models\ImportMapping;
use App\Models\Meet;
use App\Support\Concerns\DeletesInChunks;
use App\Support\Concerns\LenexXmlValueHelpers;
use App\Support\ParaSwim;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

class LenexImportService
{
    use DeletesInChunks;
    use LenexXmlValueHelpers;

    /**
     * @throws Throwable
     */
    public function createPreviewFromUpload(
        string $filename,
        string $xmlString,
        ?string $forcedType = null
    ): ImportBatch {
        $xml = LenexXml::load($xmlString);
        $type = $forcedType ?: $this->detectType($xml);

        return DB::transaction(function () use ($filename, $xml, $type) {
            $batch = ImportBatch::create([
                'type' => $type,
                'status' => 'preview',
                'filename' => $filename,
                'summary_json' => [],
            ]);

            $this->buildPreview($batch, $xml);

            return $batch;
        });
    }

    private function detectType(SimpleXMLElement $xml): string
    {
        // Records
        $hasRecords =
            ! empty($xml->xpath('//RECORD')) ||
            ! empty($xml->xpath('//RECORDS'));

        if ($hasRecords) {
            return 'records';
        }

        // Results
        $hasResults =
            ! empty($xml->xpath('//RESULT')) ||
            ! empty($xml->xpath('//RESULTS'));

        if ($hasResults) {
            return 'results';
        }

        // Entries
        $hasEntries =
            ! empty($xml->xpath('//ENTRY')) ||
            ! empty($xml->xpath('//ENTRIES'));

        if ($hasEntries) {
            return 'entries';
        }

        // Fallback: nur Struktur (oder Meet-Infos ohne Entries/Results)
        return 'meet_structure';
    }

    private function buildPreview(ImportBatch $batch, SimpleXMLElement $xml): void
    {
        $batch->issues()->delete();
        $batch->mappings()->delete();

        $meetInfo = $this->extractMeetSummary($xml);

        $summary = [
            'meet' => $meetInfo,
            'counts' => [
                'clubs' => 0,
                'athletes' => 0,
                'relays' => 0,
            ],
        ];

        $summary['type'] = $batch->type;

        $summary['structure_only'] = $batch->type === 'meet_structure' && $this->isStructureOnly($xml);

        $this->createMeetIssueAndDefaultMapping($batch, $meetInfo);
        $this->createFacilityIssueAndDefaultMapping($batch, $meetInfo);

        $clubIssues = $this->buildClubIssues($batch, $xml);
        $summary['counts']['clubs'] = $clubIssues['count'] ?? 0;

        $athleteIssues = $this->buildAthleteIssues($batch, $xml);
        $summary['counts']['athletes'] = $athleteIssues['count'] ?? 0;

        $summary['counts']['relays'] = $this->countRelaysInXml($xml);

        if ($batch->type === 'meet_structure') {
            $summary['structure'] = $this->extractStructureForPreview($xml);
        }

        $batch->update(['summary_json' => $summary]);
    }

    public function extractMeetSummary(SimpleXMLElement $xml): array
    {
        $meetNode = ($xml->xpath('//MEET')[0] ?? null);
        if (! $meetNode instanceof SimpleXMLElement) {
            return [];
        }

        $name = $this->strAttrNullable($meetNode, 'name') ?? LenexXml::text($meetNode->NAME ?? null);

        // LENEX: Datum kann je nach Export in date / from / datefrom stehen
        $date = $this->strAttrNullable($meetNode, 'date');
        $from = $this->strAttrNullable($meetNode, 'from') ?? $this->strAttrNullable($meetNode, 'datefrom');
        $to = $this->strAttrNullable($meetNode, 'to') ?? $this->strAttrNullable($meetNode, 'dateto');

        // Wenn "from" fehlt, nimm "date"
        if (! $from && $date) {
            $from = $date;
        }

        // Fallback: wenn MEET kein Datum hat, nimm erstes SESSION@date
        if (! $from) {
            $sessionNode = ($meetNode->xpath('.//SESSION[@date][1]')[0] ?? null);
            if ($sessionNode instanceof SimpleXMLElement) {
                $from = $this->strAttrNullable($sessionNode, 'date');
            }
        }

        $city = null;
        $facilityNode = ($meetNode->xpath('.//FACILITY[1]')[0] ?? null);
        if ($facilityNode instanceof SimpleXMLElement) {
            $city = $this->strAttrNullable($facilityNode, 'city') ?? LenexXml::text($facilityNode->CITY ?? null);
        }

        return [
            'name' => $name,
            'from' => $from,
            'to' => $to,
            'city' => $city,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    private function isStructureOnly(SimpleXMLElement $xml): bool
    {
        $hasResults =
            ! empty($xml->xpath('//RESULT')) ||
            ! empty($xml->xpath('//RESULTS'));

        $hasEntries =
            ! empty($xml->xpath('//ENTRY')) ||
            ! empty($xml->xpath('//ENTRIES'));

        return ! $hasResults && ! $hasEntries;
    }

    private function createMeetIssueAndDefaultMapping(ImportBatch $batch, array $meetInfo): void
    {
        $suggestions = $this->suggestMeets($meetInfo);

        $this->createIssue(
            batch: $batch,
            entityType: 'meet',
            entityKey: 'meet',
            severity: empty($suggestions) ? 'ok' : 'warn',
            message: empty($suggestions)
                ? 'No similar meet found. Will create a new meet.'
                : 'Similar meets found. Link to update instead of creating a duplicate.',
            payload: $meetInfo,
            suggestions: $suggestions
        );

        ImportMapping::create([
            'import_batch_id' => $batch->id,
            'entity_type' => 'meet',
            'source_key' => 'meet',
            'action' => 'create',
            'target_id' => null,
        ]);
    }

    private function suggestMeets(array $meetInfo): array
    {
        $name = trim((string) ($meetInfo['name'] ?? ''));
        $from = $meetInfo['from'] ?? null;
        $to = $meetInfo['to'] ?? null;

        if ($name === '' || ! $from || ! $to) {
            return [];
        }

        $res = Meet::query()
            ->where('name', 'like', '%'.$name.'%')
            ->where(function ($w) use ($from, $to) {
                $w->whereBetween('start_date', [$from, $to])
                    ->orWhereBetween('end_date', [$from, $to]);
            })
            ->limit(10)
            ->get();

        return $this->mapSuggestions($res, fn ($m) => "#{$m->id} {$m->name} ({$m->start_date} → {$m->end_date})");
    }

    /**
     * @param  Collection<int, mixed>  $models
     * @param  callable  $labelFn  fn($model): string
     * @return array<int, array{id:int, label:string}>
     */
    private function mapSuggestions(Collection $models, callable $labelFn): array
    {
        return $models->map(fn ($m) => [
            'id' => $m->id,
            'label' => $labelFn($m),
        ])->all();
    }

    private function createIssue(
        ImportBatch $batch,
        string $entityType,
        string $entityKey,
        string $severity,
        string $message,
        array $payload = [],
        array $suggestions = []
    ): ImportIssue {
        return ImportIssue::create([
            'import_batch_id' => $batch->id,
            'entity_type' => $entityType,
            'entity_key' => $entityKey,
            'severity' => $severity,
            'message' => $message,
            'payload_json' => $payload,
            'suggestions_json' => $suggestions,
        ]);
    }

    private function createFacilityIssueAndDefaultMapping(ImportBatch $batch, array $meetInfo): void
    {
        if (empty($meetInfo['city']) && empty($meetInfo['name'])) {
            return;
        }

        $suggestions = $this->suggestFacilities($meetInfo);

        $this->createIssue(
            batch: $batch,
            entityType: 'facility',
            entityKey: 'facility',
            severity: empty($suggestions) ? 'ok' : 'warn',
            message: empty($suggestions)
                ? 'No similar facility found. Will create or keep empty depending on data.'
                : 'Similar facilities found. Link to avoid duplicates.',
            payload: [
                'name' => $meetInfo['name'] ?? null,
                'city' => $meetInfo['city'] ?? null,
            ],
            suggestions: $suggestions
        );

        ImportMapping::create([
            'import_batch_id' => $batch->id,
            'entity_type' => 'facility',
            'source_key' => 'facility',
            'action' => 'create',
            'target_id' => null,
        ]);
    }

    private function suggestFacilities(array $meetInfo): array
    {
        $city = trim((string) ($meetInfo['city'] ?? ''));
        $name = trim((string) ($meetInfo['name'] ?? ''));

        if ($city === '' && $name === '') {
            return [];
        }

        $q = Facility::query();

        if ($city !== '') {
            $q->where('city', 'like', '%'.$city.'%');
        }
        if ($name !== '') {
            $q->orWhere('name', 'like', '%'.$name.'%');
        }

        $res = $q->limit(10)->get();

        return $this->mapSuggestions($res, fn ($f) => "#{$f->id} {$f->name} ({$f->city})");
    }

    private function buildClubIssues(ImportBatch $batch, SimpleXMLElement $xml): array
    {
        $clubs = $this->extractClubsFromXml($xml);

        $count = 0;
        foreach ($clubs as $club) {
            $isOfficialsOnly = ($club['officials_only_source'] ?? false) === true;

            $suggestions = $isOfficialsOnly ? [] : $this->suggestClubs($club);

            $severity = $isOfficialsOnly ? 'ok' : (empty($suggestions) ? 'ok' : 'warn');
            $msg = $isOfficialsOnly
                ? 'Officials-only club detected. This club will be ignored.'
                : (empty($suggestions)
                    ? 'No similar club found. Will create.'
                    : 'Similar clubs found. Link to avoid duplicates.');

            $this->createIssue(
                batch: $batch,
                entityType: 'club',
                entityKey: $club['source_key'],
                severity: $severity,
                message: $msg,
                payload: $club,
                suggestions: $suggestions
            );

            ImportMapping::create([
                'import_batch_id' => $batch->id,
                'entity_type' => 'club',
                'source_key' => $club['source_key'],
                'action' => $isOfficialsOnly ? 'ignore' : 'create',
                'target_id' => null,
            ]);

            $count++;
        }

        return ['count' => $count];
    }

    private function extractClubsFromXml(SimpleXMLElement $xml): array
    {
        $clubNodes = $xml->xpath('//CLUB') ?: [];
        $clubs = [];

        foreach ($clubNodes as $c) {
            $clubs[] = $this->parseClubNode($c);
        }

        return $this->dedupeBySourceKey($clubs);
    }

    /**
     * Parse a CLUB node into a normalized payload (for preview + source_key).
     */
    private function parseClubNode(SimpleXMLElement $clubNode): array
    {
        $name = $this->strAttrNullable($clubNode, 'name') ?? LenexXml::text($clubNode->NAME ?? null);

        $short = $this->strAttrNullable($clubNode, 'shortname')
            ?? $this->strAttrNullable($clubNode, 'short')
            ?? LenexXml::text($clubNode->SHORTNAME ?? null);

        // LENEX IOC code as string (later resolved to nation_id)
        $nation = $this->strAttrNullable($clubNode, 'nation') ?? LenexXml::text($clubNode->NATION ?? null);

        $hasOfficials = ! empty($clubNode->xpath('.//OFFICIAL'));
        $hasAthletes = ! empty($clubNode->xpath('.//ATHLETE'));
        $officialsOnly = $hasOfficials && ! $hasAthletes;

        $sourceKey = $this->clubSourceKey($nation, $name, $short);

        return [
            'source_key' => $sourceKey,
            'name' => $name,
            'short_name' => $short,
            'nation' => $nation,
            'officials_only_source' => $officialsOnly,
        ];
    }

    private function clubSourceKey(?string $nation, ?string $name, ?string $short): string
    {
        return 'club:'.Str::upper(trim((string) $nation)).'|'.trim((string) $name).'|'.trim((string) $short);
    }

    /**
     * @param  array<int, array{source_key:string}>  $rows
     * @return array<int, array>
     */
    private function dedupeBySourceKey(array $rows): array
    {
        $unique = [];
        foreach ($rows as $row) {
            $unique[$row['source_key']] = $row;
        }

        return array_values($unique);
    }

    private function suggestClubs(array $club): array
    {
        $name = trim((string) ($club['name'] ?? ''));
        $short = trim((string) ($club['short_name'] ?? ''));
        $nationCodeOrName = trim((string) ($club['nation'] ?? ''));

        if ($name === '' && $short === '') {
            return [];
        }

        $q = Club::query();

        // IMPORTANT: Club uses nation_id (FK), not nation (string)
        $nationId = $nationCodeOrName !== '' ? $this->resolveNationId($nationCodeOrName) : null;
        if ($nationId) {
            $q->where('nation_id', $nationId);
        }

        $q->where(function ($w) use ($name, $short) {
            if ($name !== '') {
                $w->where('name', 'like', '%'.$name.'%');
            }
            if ($short !== '') {
                $w->orWhere('short_name', 'like', '%'.$short.'%');
            }
        });

        $res = $q->limit(10)->get();

        return $this->mapSuggestions(
            $res,
            fn ($c) => "#{$c->id} {$c->name}".($c->short_name ? " ({$c->short_name})" : '')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Meet + Facility mapping
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve nation_id from LENEX nation (IOC code only, e.g. AUT, GER, ...).
     */
    private function resolveNationId(string $ioc): ?int
    {
        $ioc = strtoupper(trim($ioc));
        if ($ioc === '') {
            return null;
        }

        $row = DB::table('nations')
            ->select('id')
            ->where('ioc', $ioc)
            ->first();

        return $row?->id ? (int) $row->id : null;
    }

    private function buildAthleteIssues(ImportBatch $batch, SimpleXMLElement $xml): array
    {
        $athletes = $this->extractAthletesFromXml($xml);

        $count = 0;
        foreach ($athletes as $ath) {
            $suggestions = $this->suggestAthletes($ath);

            $this->createIssue(
                batch: $batch,
                entityType: 'athlete',
                entityKey: $ath['source_key'],
                severity: empty($suggestions) ? 'ok' : 'warn',
                message: empty($suggestions)
                    ? 'No similar athlete found. Will create.'
                    : 'Similar athletes found. Link to avoid duplicates.',
                payload: $ath,
                suggestions: $suggestions
            );

            ImportMapping::create([
                'import_batch_id' => $batch->id,
                'entity_type' => 'athlete',
                'source_key' => $ath['source_key'],
                'action' => 'create',
                'target_id' => null,
            ]);

            $count++;
        }

        return ['count' => $count];
    }

    /*
    |--------------------------------------------------------------------------
    | Structure import
    |--------------------------------------------------------------------------
    */

    private function extractAthletesFromXml(SimpleXMLElement $xml): array
    {
        $nodes = $xml->xpath('//ATHLETE') ?: [];
        $athletes = [];

        foreach ($nodes as $a) {
            $athletes[] = $this->parseAthleteNode($a);
        }

        return $this->dedupeBySourceKey($athletes);
    }

    /*
    |--------------------------------------------------------------------------
    | Entries / Results import with event fallback
    |--------------------------------------------------------------------------
    */

    /**
     * Parse an ATHLETE node into a normalized payload (for preview + source_key).
     */
    private function parseAthleteNode(SimpleXMLElement $athNode): array
    {
        $lastName = $this->strAttrNullable($athNode, 'lastname')
            ?? $this->strAttrNullable($athNode, 'familyname')
            ?? LenexXml::text($athNode->LASTNAME ?? null);

        $firstName = $this->strAttrNullable($athNode, 'firstname')
            ?? LenexXml::text($athNode->FIRSTNAME ?? null);

        $birthdate = $this->strAttrNullable($athNode, 'birthdate')
            ?? $this->strAttrNullable($athNode, 'birth');

        if ($birthdate !== null) {
            $birthYear = (int) substr($birthdate, 0, 4);
        } else {
            $by = $this->strAttrNullable($athNode, 'birthyear');
            $birthYear = $by !== null ? (int) $by : null;
        }

        $gender = $this->strAttrNullable($athNode, 'gender')
            ?? LenexXml::text($athNode->GENDER ?? null);

        $sourceKey = $this->athleteSourceKey($lastName, $firstName, $birthYear);

        return [
            'source_key' => $sourceKey,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'birth_year' => $birthYear,
            'gender' => $gender,
        ];
    }

    private function athleteSourceKey(?string $ln, ?string $fn, ?int $by): string
    {
        $ln = $ln !== null ? trim($ln) : '';
        $fn = $fn !== null ? trim($fn) : '';

        return 'athlete:'
            .Str::upper($ln)
            .'|'
            .Str::upper($fn)
            .'|'
            .($by ?? 0);
    }

    private function suggestAthletes(array $ath): array
    {
        $ln = trim((string) ($ath['last_name'] ?? ''));
        $fn = trim((string) ($ath['first_name'] ?? ''));
        $by = $ath['birth_year'] ?? null;

        if ($ln === '' || ! $by) {
            return [];
        }

        $q = Athlete::query()
            ->where('last_name', 'like', '%'.$ln.'%')
            ->where('birth_year', (int) $by);

        if ($fn !== '') {
            $q->where('first_name', 'like', '%'.$fn.'%');
        }

        $res = $q->limit(10)->get();

        return $this->mapSuggestions(
            $res,
            fn ($a) => "#{$a->id} {$a->last_name} {$a->first_name} ({$a->birth_year})"
        );
    }

    private function countRelaysInXml(SimpleXMLElement $xml): int
    {
        $nodes = $xml->xpath('//RELAY') ?: [];

        return count($nodes);
    }

    private function extractStructureForPreview(SimpleXMLElement $xml): array
    {
        return app(LenexStructureExtractor::class)->extract($xml);
    }

    /*
    |--------------------------------------------------------------------------
    | Entity resolution (mappings)
    |--------------------------------------------------------------------------
    */

    /**
     * @throws Throwable
     */
    public function commit(ImportBatch $batch, string $xmlString): void
    {
        if ($batch->status !== 'preview') {
            throw new RuntimeException('Batch is not in preview state.');
        }

        if ($batch->issues()->where('severity', 'error')->exists()) {
            throw new RuntimeException('Batch has blocking errors and cannot be committed.');
        }

        $xml = LenexXml::load($xmlString);

        DB::transaction(function () use ($batch, $xml) {
            $meet = $this->resolveOrCreateMeet($batch, $xml);

            // Variante A: Reset bei Link
            $meetMapping = $batch->mappings()
                ->where('entity_type', 'meet')
                ->where('source_key', 'meet')
                ->first();

            if ($meetMapping && $meetMapping->action === 'link') {
                $this->resetMeetData($meet);
            }

            $this->applyFacilityToMeetIfMapped($batch, $meet);

            if ($batch->type === 'meet_structure' || $this->xmlHasStructure($xml)) {
                $this->importStructure($xml, $meet);
            }
            $this->applyMeetMetaFromXml($xml, $meet);

            if (in_array($batch->type, ['entries', 'results'], true)) {
                $this->importEntriesAndOrResults($batch, $xml, $meet);
            }

            $summary = $batch->summary_json;
            if (! is_array($summary)) {
                $summary = [];
            }

            /** counts sicherstellen */
            if (! isset($summary['counts']) || ! is_array($summary['counts'])) {
                $summary['counts'] = [];
            }
            $summary['counts']['relays'] = $this->countRelaysInXml($xml);

            /** meet sicherstellen + meet.id setzen */
            if (! isset($summary['meet']) || ! is_array($summary['meet'])) {
                $summary['meet'] = [];
            }
            $summary['meet']['id'] = $meet->id;

            /**
             * Optional aber empfehlenswert: nach Commit die DB-Werte zurückschreiben,
             * damit summary konsistent ist (falls z.B. link/update gemacht wurde)
             */
            $summary['meet']['name'] = $meet->name;
            $summary['meet']['from'] = $meet->start_date;
            $summary['meet']['to'] = $meet->end_date;

            $batch->update([
                'meet_id' => $meet->id,
                'status' => 'committed',
                'summary_json' => $summary,
            ]);

        });
    }

    private function resolveOrCreateMeet(ImportBatch $batch, SimpleXMLElement $xml): Meet
    {
        $mapping = $batch->mappings()
            ->where('entity_type', 'meet')
            ->where('source_key', 'meet')
            ->first();

        $meetNode = $xml->xpath('//MEET')[0] ?? null;
        if (! $meetNode instanceof SimpleXMLElement) {
            throw new RuntimeException('No MEET node found in LENEX.');
        }

        $name = $this->strAttrNullable($meetNode, 'name') ?? LenexXml::text($meetNode->NAME ?? null);
        $from = $this->strAttrNullable($meetNode, 'from') ?? $this->strAttrNullable($meetNode, 'datefrom');
        $to = $this->strAttrNullable($meetNode, 'to') ?? $this->strAttrNullable($meetNode, 'dateto');

        if ($mapping && $mapping->action === 'link' && $mapping->target_id) {
            $meet = Meet::findOrFail($mapping->target_id);

            $meet->update([
                'name' => $name ?: $meet->name,
                'start_date' => $from ?: $meet->start_date,
                'end_date' => $to ?: $meet->end_date,
            ]);

            return $meet;
        }

        return Meet::create([
            'name' => $name,
            'start_date' => $from,
            'end_date' => $to,
        ]);
    }

    private function resetMeetData(Meet $meet): void
    {
        $sessionIds = DB::table('meet_sessions')->where('meet_id', $meet->id)->pluck('id');

        $eventIds = $sessionIds->isEmpty()
            ? collect()
            : DB::table('meet_events')->whereIn('meet_session_id', $sessionIds)->pluck('id');

        $resultIds = $eventIds->isEmpty()
            ? collect()
            : DB::table('meet_results')->whereIn('meet_event_id', $eventIds)->pluck('id');

        $this->deleteWhereInChunks('result_points', 'meet_result_id', $resultIds);
        $this->deleteWhereInChunks('meet_result_splits', 'meet_result_id', $resultIds);

        $this->deleteWhereInChunks('meet_results', 'meet_event_id', $eventIds);
        $this->deleteWhereInChunks('meet_entries', 'meet_event_id', $eventIds);

        $this->deleteWhereInChunks('meet_events', 'meet_session_id', $sessionIds);
        $this->deleteWhereInChunks('meet_sessions', 'id', $sessionIds);

        DB::table('meet_age_groups')->where('meet_id', $meet->id)->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Writes
    |--------------------------------------------------------------------------
    */

    private function applyFacilityToMeetIfMapped(ImportBatch $batch, Meet $meet): void
    {
        $mapping = $batch->mappings()->where('entity_type', 'facility')->where('source_key', 'facility')->first();
        $facilityIssue = $batch->issues()->where('entity_type', 'facility')->where('entity_key', 'facility')->first();

        if (! $mapping) {
            return;
        }

        if ($mapping->action === 'ignore') {
            $meet->update(['facility_id' => null]);

            return;
        }

        if ($mapping->action === 'link' && $mapping->target_id) {
            $meet->update(['facility_id' => $mapping->target_id]);

            return;
        }

        if ($mapping->action === 'create') {
            $payload = $facilityIssue?->payload_json ?? [];
            if (empty($payload)) {
                return;
            }

            $facility = Facility::create([
                'name' => $payload['name'] ?? 'Unknown Facility',
                'city' => $payload['city'] ?? null,
            ]);

            $meet->update(['facility_id' => $facility->id]);
        }
    }

    private function xmlHasStructure(SimpleXMLElement $xml): bool
    {
        return
            ! empty($xml->xpath('//SESSION')) ||
            ! empty($xml->xpath('//EVENT')) ||
            ! empty($xml->xpath('//AGEGROUP'));
    }

    private function importStructure(SimpleXMLElement $xml, Meet $meet): void
    {
        $structure = app(LenexStructureExtractor::class)->extract($xml);
        $meta = $structure['meet'] ?? [];

        $now = now();

        /*
         * AGE GROUPS (canonical)
         * - Dedupe auf meet_id + canonical_key
         * - LENEX code bleibt als mapping in lenex_age_group_maps
         */
        $ageRows = [];

        foreach (($structure['age_groups'] ?? []) as $ag) {
            $lenexCode = $this->normLenexId($ag['code'] ?? null);
            if (! $lenexCode) {
                continue;
            }

            $minAge = isset($ag['min']) ? (int) $ag['min'] : null;
            $maxAge = isset($ag['max']) ? (int) $ag['max'] : null;
            $gender = $this->normGender($ag['gender'] ?? null);

            // handicap kann "14" oder "1,2,3" sein → als string behalten (fachlich relevant)
            $handicap = $this->normString((string) ($ag['handicap'] ?? null));

            // canonical_key: absichtlich OHNE name (Name kann frei vergeben werden)
            $canonicalKey = implode('|', [
                $gender ?: '',
                (string) ($minAge ?? ''),
                (string) ($maxAge ?? ''),
                $handicap ?: '',
            ]);

            $ageRows[] = [
                'meet_id' => $meet->id,
                'canonical_key' => $canonicalKey,

                // optional: ein "repräsentativer" Code im record (nicht für Join verwenden!)
                'code' => $lenexCode,

                'min_age' => $minAge,
                'max_age' => $maxAge,
                'handicap' => $handicap,
                'gender' => $gender,
                'name' => $this->normString($ag['name'] ?? null),

                'updated_at' => $now,
                'created_at' => $now,
            ];
        }

        if (! empty($ageRows)) {
            // Upsert auf canonical_key statt code
            $this->upsertWithoutTouchingCreatedAt(
                'meet_age_groups',
                $ageRows,
                ['meet_id', 'canonical_key'],
                ['code', 'min_age', 'max_age', 'handicap', 'gender', 'name', 'updated_at']
            );
        }

        // canonical_key -> id
        $ageGroupIdByCanonical = DB::table('meet_age_groups')
            ->where('meet_id', $meet->id)
            ->pluck('id', 'canonical_key')
            ->all();

        // LENEX code -> meet_age_group_id mapping schreiben
        $mapRows = [];
        foreach ($ageRows as $r) {
            $lenexCode = $r['code'] ?? null; // der LENEX code
            $canonical = $r['canonical_key'] ?? null;
            if (! $lenexCode || ! $canonical) {
                continue;
            }

            $agId = $ageGroupIdByCanonical[$canonical] ?? null;
            if (! $agId) {
                continue;
            }

            $mapRows[] = [
                'meet_id' => $meet->id,
                'lenex_code' => (int) $lenexCode,
                'meet_age_group_id' => (int) $agId,
            ];
        }

        if (! empty($mapRows)) {
            DB::table('lenex_age_group_maps')->upsert(
                $mapRows,
                ['meet_id', 'lenex_code'],
                ['meet_age_group_id']
            );
        }

        // Für Pivot/Events: LENEX code -> canonical meet_age_group_id
        $ageGroupIdByLenexCode = DB::table('lenex_age_group_maps')
            ->where('meet_id', $meet->id)
            ->pluck('meet_age_group_id', 'lenex_code')
            ->all();

        /*
         * SESSIONS
         */
        $sessionRows = [];

        foreach (($structure['sessions'] ?? []) as $s) {
            $no = isset($s['no']) ? (int) $s['no'] : null;
            if ($no === null) {
                continue;
            }

            $sessionRows[] = [
                'meet_id' => $meet->id,
                'session_no' => $no,
                'date' => $s['date'] ?? null,
                'start_time' => $s['start_time'] ?? null,
                'updated_at' => $now,
                'created_at' => $now,
            ];
        }

        if (! empty($sessionRows)) {
            $this->upsertWithoutTouchingCreatedAt(
                'meet_sessions',
                $sessionRows,
                ['meet_id', 'session_no'],
                ['date', 'start_time', 'updated_at']
            );
        }

        $sessionIdByNo = DB::table('meet_sessions')
            ->where('meet_id', $meet->id)
            ->pluck('id', 'session_no')
            ->all();

        /*
         * EVENTS
         * - speichert nur Event-Stammdaten
         * - Zuordnung zu AgeGroups über Pivot age_group_event
         */
        $eventRows = [];
        $eventAgeGroupCodesByKey = []; // "{meet_session_id}:{event_no}" => [lenex_code, lenex_code, ...]

        foreach (($structure['sessions'] ?? []) as $s) {
            $no = isset($s['no']) ? (int) $s['no'] : null;
            if ($no === null) {
                continue;
            }

            $sessionId = $sessionIdByNo[$no] ?? null;
            if (! $sessionId) {
                continue;
            }

            foreach (($s['events'] ?? []) as $e) {
                $eventNo = isset($e['no']) ? (int) $e['no'] : null;
                if ($eventNo === null) {
                    continue;
                }

                // Extractor: age_groups ist array von LENEX codes
                $ageGroupCodes = $e['age_groups'] ?? [];
                if (! is_array($ageGroupCodes)) {
                    $ageGroupCodes = [];
                }

                $ageGroupCodes = array_values(array_unique(array_filter(array_map(
                    fn ($c) => $this->normLenexId($c),
                    $ageGroupCodes
                ))));

                $key = ((int) $sessionId).':'.($eventNo);
                $eventAgeGroupCodesByKey[$key] = $ageGroupCodes;

                // optional/legacy: erste canonical AG ins meet_events schreiben
                $legacyAgId = null;
                foreach ($ageGroupCodes as $c) {
                    $legacyAgId = $ageGroupIdByLenexCode[$c] ?? null;
                    if ($legacyAgId) {
                        break;
                    }
                }

                $eventRows[] = [
                    'meet_session_id' => $sessionId,
                    'event_no' => $eventNo,
                    'lenex_event_id' => isset($e['lenex_event_id']) ? (int) $e['lenex_event_id'] : null,

                    // optional/legacy (kannst du später entfernen)
                    'meet_age_group_id' => $legacyAgId,

                    'name' => $this->normString($e['name'] ?? null),
                    'gender' => $this->normGender($e['gender'] ?? null),
                    'distance' => isset($e['distance']) ? (int) $e['distance'] : null,
                    'stroke' => $this->normString($e['stroke'] ?? null, 50),
                    'round' => $this->normString($e['round'] ?? null, 50),
                    'is_relay' => (int) ($e['is_relay'] ?? false),

                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }
        }

        if (! empty($eventRows)) {
            $this->upsertWithoutTouchingCreatedAt(
                'meet_events',
                $eventRows,
                ['meet_session_id', 'event_no'],
                [
                    'meet_age_group_id', 'lenex_event_id', 'name', 'gender', 'distance', 'stroke', 'round', 'is_relay',
                    'updated_at',
                ]
            );
        }

        /*
         * PIVOT: age_group_event
         * - Rebuild pro import (idempotent)
         * - verwendet Mapping lenex_code -> canonical meet_age_group_id
         */
        $eventIdRows = DB::table('meet_events')
            ->whereIn('meet_session_id', array_values($sessionIdByNo))
            ->select('id', 'meet_session_id', 'event_no')
            ->get();

        $eventIdByKey = [];
        $eventIds = [];

        foreach ($eventIdRows as $r) {
            $k = ((int) $r->meet_session_id).':'.((int) $r->event_no);
            $eventIdByKey[$k] = (int) $r->id;
            $eventIds[] = (int) $r->id;
        }

        // alte Zuordnungen löschen (für diese Events)
        if (! empty($eventIds)) {
            DB::table('age_group_event')->whereIn('meet_event_id', $eventIds)->delete();
        }

        $pivotRows = [];
        foreach ($eventAgeGroupCodesByKey as $k => $codes) {
            $eventId = $eventIdByKey[$k] ?? null;
            if (! $eventId) {
                continue;
            }

            foreach ($codes as $lenexCode) {
                $agId = $ageGroupIdByLenexCode[$lenexCode] ?? null;
                if (! $agId) {
                    continue;
                }

                $pivotRows[] = [
                    'meet_event_id' => $eventId,
                    'age_group_id' => (int) $agId,
                ];
            }
        }

        // dedupe, weil mehrere LENEX codes auf dieselbe canonical AG mappen können
        if (! empty($pivotRows)) {
            $pivotRows = collect($pivotRows)
                ->unique(fn ($r) => $r['meet_event_id'].':'.$r['age_group_id'])
                ->values()
                ->all();

            // pivot: duplicates einfach ignorieren (stabil in SQLite + MySQL)
            DB::table('age_group_event')->insertOrIgnore($pivotRows);
        }

        /*
         * MEET META (write to meets)
         * - course from MEET tag
         * - age_date from AGEDATE
         * - contact_* from CONTACT
         * - start/end derived from sessions if not set
         */
        $meetUpdates = [];

        // course
        if (empty($meet->course) && ! empty($meta['course'])) {
            $meetUpdates['course'] = $this->normString($meta['course'], 20);
        }

        // age_date
        if (empty($meet->age_date) && ! empty($meta['age_date'])) {
            $meetUpdates['age_date'] = $meta['age_date'];
        }

        // contact fields
        if (empty($meet->contact_name) && ! empty($meta['contact_name'])) {
            $meetUpdates['contact_name'] = $this->normString($meta['contact_name']);
        }
        if (empty($meet->contact_email) && ! empty($meta['contact_email'])) {
            $meetUpdates['contact_email'] = $this->normString($meta['contact_email']);
        }
        if (empty($meet->contact_phone) && ! empty($meta['contact_phone'])) {
            $meetUpdates['contact_phone'] = $this->normString($meta['contact_phone'], 50);
        }

        // start_date / end_date (prefer meta, fallback to sessions table)
        if (empty($meet->start_date) || empty($meet->end_date)) {
            $start = $meta['start_date'] ?? null;
            $end = $meta['end_date'] ?? null;

            if (! $start || ! $end) {
                $dates = DB::table('meet_sessions')
                    ->where('meet_id', $meet->id)
                    ->whereNotNull('date')
                    ->pluck('date')
                    ->filter()
                    ->values();

                if ($dates->isNotEmpty()) {
                    $start = $start ?: $dates->min();
                    $end = $end ?: $dates->max();
                }
            }

            if (empty($meet->start_date) && $start) {
                $meetUpdates['start_date'] = $start;
            }
            if (empty($meet->end_date) && $end) {
                $meetUpdates['end_date'] = $end;
            }

            // If age_date still empty, use start_date as fallback
            if (empty($meet->age_date) && ($meetUpdates['age_date'] ?? null) === null && $start) {
                $meetUpdates['age_date'] = $start;
            }
        }

        if (! empty($meetUpdates)) {
            $meet->fill($meetUpdates);
            $meet->save();
        }
    }

    private function normLenexId(mixed $raw): ?int
    {
        if ($raw === null) {
            return null;
        }

        $s = trim((string) $raw);
        if ($s === '') {
            return null;
        }

        if (! ctype_digit($s)) {
            return null;
        }

        $v = (int) $s;

        return $v > 0 ? $v : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Small helpers (DRY)
    |--------------------------------------------------------------------------
    */

    private function normGender(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = strtoupper(trim($value));
        if ($v === '') {
            return null;
        }

        // häufige Varianten abfangen
        if (in_array($v, ['M', 'MALE', 'MAN'], true)) {
            return 'M';
        }
        if (in_array($v, ['F', 'FEMALE', 'WOMAN'], true)) {
            return 'F';
        }

        // LENEX/Meet-Setups haben manchmal X/MIXED/ALL
        if (in_array($v, ['X', 'MIX', 'MIXED', 'A', 'ALL'], true)) {
            return 'X';
        }

        // wenn dein System nur M/F erlaubt, hier stattdessen null zurückgeben
        if (strlen($v) === 1) {
            return $v;
        }

        return null;
    }

    private function normString(?string $value, int $maxLen = 255): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = trim($value);
        if ($v === '') {
            return null;
        }

        return mb_substr($v, 0, $maxLen);
    }

    /**
     * Upsert ohne created_at-Überschreibung: nutzt Query Builder upsert().
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $uniqueBy
     * @param  array<int, string>  $updateColumns
     */
    private function upsertWithoutTouchingCreatedAt(
        string $table,
        array $rows,
        array $uniqueBy,
        array $updateColumns
    ): void {
        if ($rows === []) {
            return;
        }

        DB::table($table)->upsert(
            $rows,
            $uniqueBy,
            $updateColumns
        );
    }

    private function applyMeetMetaFromXml(SimpleXMLElement $xml, Meet $meet): void
    {
        $meetNode = $xml->MEETS->MEET[0] ?? null;
        if (! $meetNode) {
            return;
        }

        $updates = [];

        // 1) course (MEET attribute)
        if (empty($meet->course)) {
            $course = $this->firstAttr($meetNode, ['course', 'COURSE']);
            if ($course !== null && trim($course) !== '') {
                $updates['course'] = trim($course);
            }
        }

        // 2) age_date (MEET/AGEDATE)
        if (empty($meet->age_date)) {
            $ageDateNode = $meetNode->AGEDATE[0] ?? null;

            $rawAgeDate =
                $this->firstAttr($ageDateNode, ['value', 'date', 'AGEDATE']) // falls als attr gespeichert
                ?? LenexXml::text($ageDateNode);                              // falls als Text-Knoten

            $ageDate = $this->parseLenexDate($rawAgeDate);
            if ($ageDate) {
                $updates['age_date'] = $ageDate;
            }
        }

        // 3) contact fields (MEET/CONTACT)
        $contactNode = $meetNode->CONTACT[0] ?? null;
        if ($contactNode) {
            if (empty($meet->contact_name)) {
                $name =
                    $this->firstAttr($contactNode, ['name', 'NAME'])
                    ?? LenexXml::text($contactNode->NAME[0] ?? null);

                if ($name) {
                    $updates['contact_name'] = $name;
                }
            }

            if (empty($meet->contact_email)) {
                $email =
                    $this->firstAttr($contactNode, ['email', 'EMAIL'])
                    ?? LenexXml::text($contactNode->EMAIL[0] ?? null);

                if ($email) {
                    $updates['contact_email'] = $email;
                }
            }

            if (empty($meet->contact_phone)) {
                $phone =
                    $this->firstAttr($contactNode, ['phone', 'PHONE', 'tel', 'TEL'])
                    ?? LenexXml::text($contactNode->PHONE[0] ?? null)
                    ?? LenexXml::text($contactNode->TEL[0] ?? null);

                if ($phone) {
                    $updates['contact_phone'] = $phone;
                }
            }
        }

        // 4) start_date / end_date: falls nicht gesetzt, aus meet_sessions.date min/max ableiten
        if (empty($meet->start_date) || empty($meet->end_date)) {
            $dates = DB::table('meet_sessions')
                ->where('meet_id', $meet->id)
                ->whereNotNull('date')
                ->pluck('date')
                ->filter()
                ->values();

            if ($dates->isNotEmpty()) {
                if (empty($meet->start_date)) {
                    $updates['start_date'] = $dates->min();
                }
                if (empty($meet->end_date)) {
                    $updates['end_date'] = $dates->max();
                }
            }
        }

        if ($updates !== []) {
            $meet->fill($updates);
            $meet->save();
        }
    }

    private function firstAttr(?SimpleXMLElement $node, array $names): ?string
    {
        if (! $node) {
            return null;
        }

        foreach ($names as $name) {
            $val = LenexXml::attr($node, $name);
            if ($val !== null && trim($val) !== '') {
                return $val;
            }
        }

        return null;
    }

    private function parseLenexDate(?string $raw): ?string
    {
        $raw = $raw !== null ? trim($raw) : '';
        if ($raw === '') {
            return null;
        }

        try {
            // LENEX liefert üblicherweise YYYY-MM-DD; Carbon kann auch ISO robust lesen
            return Carbon::parse($raw)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function importEntriesAndOrResults(ImportBatch $batch, SimpleXMLElement $xml, Meet $meet): void
    {
        $eventIndexes = $this->buildEventIndexesForMeet($meet);

        $swimStyleIdByKey = DB::table('swim_styles')
            ->select('id', 'distance', 'stroke', 'relay_count')
            ->get()
            ->mapWithKeys(function ($r) {
                $relay = $r->relay_count === null ? 'null' : (string) $r->relay_count;
                $key = $relay.':'.(int) $r->distance.':'.$r->stroke;

                return [$key => (int) $r->id];
            })
            ->all();

        // meet_event_id => [distance, stroke, is_relay]
        $eventStyleByMeetEventId = DB::table('meet_events')
            ->join('meet_sessions', 'meet_sessions.id', '=', 'meet_events.meet_session_id')
            ->where('meet_sessions.meet_id', $meet->id)
            ->select('meet_events.id', 'meet_events.distance', 'meet_events.stroke', 'meet_events.is_relay')
            ->get()
            ->mapWithKeys(fn ($r) => [
                (int) $r->id => [
                    'distance' => $r->distance !== null ? (int) $r->distance : null,
                    'stroke' => $r->stroke !== null ? (string) $r->stroke : null,
                    'is_relay' => (bool) $r->is_relay,
                ],
            ])
            ->all();

        $clubIdBySourceKey = $this->resolveEntitiesForBatch($batch, 'club');
        $athleteIdBySourceKey = $this->resolveEntitiesForBatch($batch, 'athlete');

        if ($batch->type === 'entries') {
            $entries = $xml->xpath('//ENTRY') ?: [];
            foreach ($entries as $en) {
                $meetEventId = $this->resolveMeetEventIdFromNode($en, $eventIndexes);
                if (! $meetEventId) {
                    $this->createUnresolvedEventIssue($batch, 'entry', $en);

                    continue;
                }

                $style = $eventStyleByMeetEventId[$meetEventId] ?? null;

                $swimStyleId = null;
                if ($style && $style['distance'] && $style['stroke']) {
                    $stroke = $this->mapLenexStrokeToSwimStroke($style['stroke']);
                    $relayCount = $this->relayCountForSwimStyle((bool) $style['is_relay']);

                    if ($stroke) {
                        $key = ($relayCount === null ? 'null' : (string) $relayCount)
                            .':'.(int) $style['distance']
                            .':'.$stroke;

                        $swimStyleId = $swimStyleIdByKey[$key] ?? null;
                    }
                }

                $clubId = $this->resolveClubIdFromNode($en, $clubIdBySourceKey);
                $athleteId = $this->resolveAthleteIdFromNode($en, $athleteIdBySourceKey);
                $seedRaw = $this->strAttrNullable($en, 'entrytime') ?? $this->strAttrNullable($en, 'seed');
                $seedCs = ParaSwim::parseLenexTimeToCentiseconds($seedRaw);

                $this->insertEntry($meetEventId, $athleteId, $clubId, $seedRaw, $seedCs, $swimStyleId);

            }
        }

        if ($batch->type === 'results') {
            $results = $xml->xpath('//RESULT') ?: [];
            foreach ($results as $r) {
                $meetEventId = $this->resolveMeetEventIdFromNode($r, $eventIndexes);
                if (! $meetEventId) {
                    $this->createUnresolvedEventIssue($batch, 'result', $r);

                    continue;
                }

                $clubId = $this->resolveClubIdFromNode($r, $clubIdBySourceKey);
                $athleteId = $this->resolveAthleteIdFromNode($r, $athleteIdBySourceKey);

                $time = $this->strAttrNullable($r, 'swimtime') ?? $this->strAttrNullable($r, 'time');
                $status = $this->strAttrNullable($r, 'status');

                $rank = $this->intAttrNullable($r, 'rank') ?? $this->intAttrNullable($r, 'place');
                $points = $this->intAttrNullable($r, 'points');

                $resultId = $this->insertResult(
                    $meetEventId,
                    $athleteId,
                    $clubId,
                    $time,
                    $status,
                    $rank,
                    $points
                );

                $this->insertResultSplits($resultId, $r);
            }
        }
    }

    private function buildEventIndexesForMeet(Meet $meet): array
    {
        $meetEvents = DB::table('meet_events')
            ->join('meet_sessions', 'meet_sessions.id', '=', 'meet_events.meet_session_id')
            ->where('meet_sessions.meet_id', $meet->id)
            ->select('meet_events.*')
            ->get();

        $byNo = $meetEvents
            ->filter(fn ($e) => $e->event_no !== null)
            ->keyBy(fn ($e) => (string) $e->event_no);

        $byComposite = $meetEvents->mapWithKeys(function ($e) {
            $key = $this->eventCompositeKey(
                $e->distance !== null ? (int) $e->distance : null,
                $e->stroke !== null ? (string) $e->stroke : null,
                $e->gender !== null ? (string) $e->gender : null,
                $e->round !== null ? (string) $e->round : null,
                (bool) $e->is_relay
            );

            return $key ? [$key => $e] : [];
        });

        $byLenexEventId = [];
        foreach ($meetEvents as $e) {
            if ($e->lenex_event_id !== null) {
                $byLenexEventId[(string) $e->lenex_event_id] = $e;
            }
        }

        return [
            'byNo' => $byNo,
            'byComposite' => $byComposite,
            'byLenexEventId' => $byLenexEventId,
        ];
    }

    private function eventCompositeKey(
        ?int $distance,
        ?string $stroke,
        ?string $gender,
        ?string $round,
        bool $isRelay
    ): ?string {
        if ($distance === null || ! $stroke) {
            return null;
        }

        return implode('|', [
            (string) $distance,
            strtoupper(trim($stroke)),
            strtoupper(trim((string) $gender)),
            strtolower(trim((string) $round)),
            $isRelay ? 'relay' : 'individual',
        ]);
    }

    private function resolveEntitiesForBatch(ImportBatch $batch, string $entityType): array
    {
        /** @var Collection<string, ImportMapping> $maps */
        $maps = $batch->mappings()->where('entity_type', $entityType)->get()->keyBy('source_key');

        /** @var Collection<string, ImportIssue> $issues */
        $issues = $batch->issues()->where('entity_type', $entityType)->get()->keyBy('entity_key');

        $idByKey = [];

        foreach ($maps as $sourceKey => $map) {
            if ($map->action === 'ignore') {
                continue;
            }

            if ($map->action === 'link' && $map->target_id) {
                $idByKey[$sourceKey] = $map->target_id;

                continue;
            }

            $issue = $issues->get($sourceKey);
            $payload = $issue?->payload_json ?? [];

            if ($entityType === 'club') {

                // Officials-only Clubs niemals anlegen
                if (! empty($payload['officials_only_source'])) {
                    continue;
                }

                $nationCode = trim((string) ($payload['nation'] ?? ''));
                $nationId = $nationCode !== '' ? $this->resolveNationId($nationCode) : null;

                // OPTIONALER TEIL: Warnung bei unbekanntem IOC
                if ($nationCode !== '' && ! $nationId) {
                    $this->createIssue(
                        batch: $batch,
                        entityType: 'club',
                        entityKey: $sourceKey,
                        severity: 'warn',
                        message: "Unknown IOC nation code '{$nationCode}'. Club will be created without nation.",
                        payload: $payload
                    );
                }

                $club = Club::create([
                    'nation_id' => $nationId, // kann null sein
                    'name' => $payload['name'] ?? 'Club',
                    'short_name' => $payload['short_name'] ?? null,
                    'officials_only' => false,
                ]);

                $idByKey[$sourceKey] = $club->id;
                $map->update([
                    'action' => 'link',
                    'target_id' => $club->id,
                ]);
            }

            if ($entityType === 'athlete') {
                $ath = Athlete::create([
                    'last_name' => $payload['last_name'] ?? '—',
                    'first_name' => $payload['first_name'] ?? null,
                    'birth_year' => $payload['birth_year'] ?? null,
                    'gender' => $payload['gender'] ?? null,
                ]);

                $idByKey[$sourceKey] = $ath->id;
                $map->update(['action' => 'link', 'target_id' => $ath->id]);
            }
        }

        return $idByKey;
    }

    private function resolveMeetEventIdFromNode(SimpleXMLElement $node, array $eventIndexes): ?int
    {
        $byLenex = $eventIndexes['byLenexEventId'] ?? [];
        $byNo = $eventIndexes['byNo'] ?? [];
        $byComposite = $eventIndexes['byComposite'] ?? [];

        // 0) Best: eventid attribute (ENTRY hat das)
        $lenexEventId = $this->strAttrNullable($node, 'eventid');
        if ($lenexEventId !== null && isset($byLenex[$lenexEventId])) {
            return $byLenex[$lenexEventId]->id;
        }

        // 1) Primary: event / eventnumber (already string|null)
        $eventNo = $this->strAttrNullable($node, 'event') ?? $this->strAttrNullable($node, 'eventnumber');
        if ($eventNo !== null && isset($byNo[$eventNo])) {
            return $byNo[$eventNo]->id;
        }

        // 2) Fallback: nearest EVENT ancestor and composite match
        $eventAncestor = $node->xpath('ancestor::EVENT[1]') ?: [];
        $eventNode = $eventAncestor[0] ?? null;

        if (! $eventNode instanceof SimpleXMLElement) {
            return null;
        }

        $distance = $this->intAttrNullable($eventNode, 'distance');
        $stroke = $this->strAttrNullable($eventNode, 'stroke');
        $gender = $this->genderChar($this->strAttrNullable($eventNode, 'gender'));
        $round = $this->strAttrNullable($eventNode, 'round');

        $isRelay = $this->boolAttr($eventNode, ['relay', 'isrelay']);
        $isRelay = $isRelay ?? (! empty($eventNode->xpath('.//RELAY')));

        $key = $this->eventCompositeKey($distance, $stroke, $gender, $round, $isRelay);
        if ($key !== null && isset($byComposite[$key])) {
            return $byComposite[$key]->id;
        }

        return null;
    }

    private function createUnresolvedEventIssue(ImportBatch $batch, string $context, SimpleXMLElement $node): void
    {
        // LENEX references
        $eventId = $this->strAttrNullable($node, 'eventid');
        $eventNo = $this->strAttrNullable($node, 'event')
            ?? $this->strAttrNullable($node, 'eventnumber');

        $seed = $this->strAttrNullable($node, 'entrytime')
            ?? $this->strAttrNullable($node, 'seed');

        // Course aus ENTRY/MEETINFO
        $course = null;
        $meetInfo = $node->xpath('./*[local-name()="MEETINFO"]') ?: [];
        $meetInfoNode = $meetInfo[0] ?? null;

        if ($meetInfoNode instanceof SimpleXMLElement) {
            $course = $this->strAttrNullable($meetInfoNode, 'course');
        }

        // ATHLETE ancestor (für ENTRY unter ATHLETE)
        $athleteAncestor = $node->xpath('ancestor::*[local-name()="ATHLETE"][1]') ?: [];
        $athleteNode = $athleteAncestor[0] ?? null;

        $athleteId = $athleteNode instanceof SimpleXMLElement
            ? $this->strAttrNullable($athleteNode, 'athleteid')
            : null;

        $payload = [
            'context' => $context,
            'event_id' => $eventId ?: null,
            'event_no' => $eventNo ?: null,
            'athlete_id' => $athleteId ?: null,
            'seed' => $seed ?: null,
            'course' => $course ?: null,
        ];

        // Composite-Fallback Infos (nur wenn EVENT ancestor existiert)
        $eventAncestor = $node->xpath('ancestor::EVENT[1]') ?: [];
        $eventNode = $eventAncestor[0] ?? null;

        if ($eventNode instanceof SimpleXMLElement) {
            $payload['distance'] = $this->intAttrNullable($eventNode, 'distance');
            $payload['stroke'] = $this->strAttrNullable($eventNode, 'stroke');
            $payload['gender'] = $this->genderChar(
                $this->strAttrNullable($eventNode, 'gender')
            );
            $payload['round'] = $this->strAttrNullable($eventNode, 'round');
            $payload['is_relay'] = $this->boolAttr($eventNode, ['relay', 'isrelay'])
                ?? ! empty($eventNode->xpath('.//RELAY'));
        }

        $entityKey = $eventId
            ? ('eventid:'.$eventId)
            : ($eventNo ? ('event:'.$eventNo) : 'event:unresolved');

        $this->createIssue(
            batch: $batch,
            entityType: 'event',
            entityKey: $entityKey,
            severity: 'warn',
            message: 'Event could not be resolved (no match by eventid/eventnumber and no composite match).',
            payload: $payload
        );
    }

    private function resolveClubIdFromNode(SimpleXMLElement $node, array $clubIdBySourceKey): ?int
    {
        $clubNode = ($node->xpath('ancestor::CLUB[1]')[0] ?? null);

        if ($clubNode instanceof SimpleXMLElement) {
            $payload = $this->parseClubNode($clubNode);
            $key = $payload['source_key'];

            return $clubIdBySourceKey[$key] ?? null;
        }

        // Fallback (wenn kein CLUB ancestor vorhanden ist)
        $name = $this->strAttrNullable($node, 'clubname');
        $short = $this->strAttrNullable($node, 'clubshort');
        $nation = $this->strAttrNullable($node, 'clubnation');

        $key = $this->clubSourceKey($nation, $name, $short);

        return $clubIdBySourceKey[$key] ?? null;
    }

    private function resolveAthleteIdFromNode(SimpleXMLElement $node, array $athleteIdBySourceKey): ?int
    {
        $athNode = ($node->xpath('ancestor::ATHLETE[1]')[0] ?? null);

        if ($athNode instanceof SimpleXMLElement) {
            $payload = $this->parseAthleteNode($athNode);
            $key = $payload['source_key'];

            return $athleteIdBySourceKey[$key] ?? null;
        }

        // Fallback (wenn kein ATHLETE ancestor vorhanden ist)
        $ln = $this->strAttrNullable($node, 'lastname');
        $fn = $this->strAttrNullable($node, 'firstname');
        $by = $this->intAttrNullable($node, 'birthyear');

        $key = $this->athleteSourceKey($ln, $fn, $by);

        return $athleteIdBySourceKey[$key] ?? null;
    }

    private function insertEntry(
        ?int $meetEventId,
        ?int $athleteId,
        ?int $clubId,
        ?string $seedRaw,
        ?int $seedCs,
        ?int $swimStyleId
    ): void {
        if (! $meetEventId || ! $athleteId) {
            return;
        }

        $now = now();

        DB::table('meet_entries')->upsert(
            [
                [
                    'meet_event_id' => $meetEventId,
                    'athlete_id' => $athleteId,

                    'club_id' => $clubId,
                    'swim_style_id' => $swimStyleId,

                    'seed_time' => $seedRaw,
                    'seed_time_cs' => $seedCs,

                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['meet_event_id', 'athlete_id'],
            ['club_id', 'swim_style_id', 'seed_time', 'seed_time_cs', 'updated_at']
        );
    }

    private function insertResult(
        ?int $meetEventId,
        ?int $athleteId,
        ?int $clubId,
        ?string $time,
        ?string $status,
        ?int $rank,
        ?int $points
    ): ?int {
        if (! $meetEventId || ! $athleteId) {
            return null;
        }

        return DB::table('meet_results')->insertGetId([
            'meet_event_id' => $meetEventId,
            'athlete_id' => $athleteId,
            'club_id' => $clubId,
            'time' => $time,
            'status' => $status,
            'rank' => $rank,
            'points' => $points,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertResultSplits(?int $resultId, SimpleXMLElement $resultNode): void
    {
        if (! $resultId) {
            return;
        }

        $splits = $resultNode->xpath('.//SPLIT') ?: [];
        foreach ($splits as $s) {
            $distance = $this->intAttrNullable($s, 'distance');
            $time = $this->strAttrNullable($s, 'time');

            if ($distance === null || ! $time) {
                continue;
            }

            DB::table('meet_result_splits')->insert([
                'meet_result_id' => $resultId,
                'distance' => $distance,
                'time' => $time,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function abortBatch(ImportBatch $batch): void
    {
        // 1) Status
        $batch->status = 'aborted';
        $batch->save();

        // 2) Child-Tabellen aufräumen
        $batch->issues()->delete();
        $batch->mappings()->delete();

        // 3) XML löschen
        $path = "imports/lenex/batch_{$batch->id}.xml";
        Storage::disk('local')->delete($path);

        // Optional: Summary/Metadaten zurücksetzen
        // $batch->summary_json = null; $batch->save();
    }

    private function normUInt(?int $value, int $min = 0, int $max = 65535): ?int
    {
        if ($value === null) {
            return null;
        }
        if ($value < $min || $value > $max) {
            return null;
        }

        return $value;
    }

    private function mapLenexStrokeToSwimStroke(?string $lenexStroke): ?string
    {
        $s = strtoupper(trim((string) $lenexStroke));
        if ($s === '') {
            return null;
        }

        return match ($s) {
            'FREE' => 'FR',
            'BACK' => 'BK',
            'BREAST' => 'BR',
            'FLY' => 'FL',
            'MEDLEY' => 'IM',
            default => null,
        };
    }

    private function relayCountForSwimStyle(bool $isRelay): ?int
    {
        // in swim_styles: relay_count ist NULL bei Einzelbewerben, 4 bei Staffel
        return $isRelay ? 4 : null;
    }
}
