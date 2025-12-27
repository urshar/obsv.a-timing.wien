<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class LenexMeetStructureController extends Controller
{
    public function show(ImportBatch $batch)
    {
        abort_unless($batch->status === 'committed', 404);
        abort_unless($batch->type === 'meet_structure', 404);

        $meetId = $batch->meet_id;

        $meet = $meetId ? DB::table('meets')->where('id', $meetId)->first() : null;

        $ageGroups = $meetId
            ? DB::table('meet_age_groups')->where('meet_id', $meetId)->orderBy('id')->get()
            : collect();

        $sessions = $meetId
            ? DB::table('meet_sessions')->where('meet_id', $meetId)->orderBy('session_no')->orderBy('id')->get()
            : collect();

        $sessionIds = $sessions->pluck('id')->all();

        $events = ! empty($sessionIds)
            ? DB::table('meet_events')->whereIn('meet_session_id',
                $sessionIds)->orderBy('event_no')->orderBy('id')->get()
            : collect();

        // Events nach Session gruppieren
        $eventsBySession = $events->groupBy('meet_session_id');

        return view('imports.lenex.meet_structure.show', [
            'batch' => $batch,
            'meet' => $meet,
            'ageGroups' => $ageGroups,
            'sessions' => $sessions,
            'eventsBySession' => $eventsBySession,
        ]);
    }

    public function edit(ImportBatch $batch)
    {
        abort_unless($batch->status === 'committed', 404);
        abort_unless($batch->type === 'meet_structure', 404);

        $meetId = $batch->meet_id;
        abort_unless($meetId, 404);

        $meet = DB::table('meets')->where('id', $meetId)->first();

        $ageGroups = DB::table('meet_age_groups')->where('meet_id', $meetId)->orderBy('id')->get();
        $sessions = DB::table('meet_sessions')->where('meet_id', $meetId)->orderBy('session_no')->orderBy('id')->get();

        $sessionIds = $sessions->pluck('id')->all();
        $events = ! empty($sessionIds)
            ? DB::table('meet_events')->whereIn('meet_session_id',
                $sessionIds)->orderBy('event_no')->orderBy('id')->get()
            : collect();

        $eventsBySession = $events->groupBy('meet_session_id');

        return view('imports.lenex.meet_structure.edit', [
            'batch' => $batch,
            'meet' => $meet,
            'ageGroups' => $ageGroups,
            'sessions' => $sessions,
            'eventsBySession' => $eventsBySession,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(Request $request, ImportBatch $batch)
    {
        abort_unless($batch->status === 'committed', 404);
        abort_unless($batch->type === 'meet_structure', 404);

        $meetId = $batch->meet_id;
        abort_unless($meetId, 404);

        $data = $request->validate([
            'meet.name' => ['required', 'string', 'max:255'],
            'meet.start_date' => ['nullable', 'date'],
            'meet.end_date' => ['nullable', 'date'],

            'age_groups' => ['array'],
            'age_groups.*.id' => ['nullable', 'integer'],
            'age_groups.*.code' => ['nullable', 'string', 'max:50'],
            'age_groups.*.min_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_groups.*.max_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_groups.*.gender' => ['nullable', 'in:M,F,X'],
            'age_groups.*.name' => ['nullable', 'string', 'max:255'],
            'age_groups.*.handicap' => ['nullable', 'string', 'max:50'],

            'sessions' => ['array'],
            'sessions.*.id' => ['nullable', 'integer'],
            'sessions.*.session_no' => ['nullable', 'integer', 'min:1', 'max:999'],
            'sessions.*.name' => ['nullable', 'string', 'max:255'],
            'sessions.*.date' => ['nullable', 'date'],
            'sessions.*.start_time' => ['nullable', 'date_format:H:i'],

            'sessions.*.events' => ['array'],
            'sessions.*.events.*.id' => ['nullable', 'integer'],
            'sessions.*.events.*.event_no' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'sessions.*.events.*.name' => ['nullable', 'string', 'max:255'],
            'sessions.*.events.*.gender' => ['nullable', 'in:M,F,X'],
            'sessions.*.events.*.distance' => ['nullable', 'integer', 'min:0', 'max:50000'],
            'sessions.*.events.*.stroke' => ['nullable', 'string', 'max:20'],
            'sessions.*.events.*.round' => ['nullable', 'string', 'max:20'],
            'sessions.*.events.*.is_relay' => ['nullable', 'boolean'],
            'sessions.*.events.*.meet_age_group_id' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($meetId, $data) {
            // 1) Meet updaten
            DB::table('meets')->where('id', $meetId)->update([
                'name' => data_get($data, 'meet.name'),
                'start_date' => data_get($data, 'meet.start_date'),
                'end_date' => data_get($data, 'meet.end_date'),
                'updated_at' => now(),
            ]);

            // 2) AgeGroups sync
            $incomingAg = collect($data['age_groups'] ?? [])
                ->filter(fn ($r) => is_array($r))
                ->values();

            $existingAgIds = DB::table('meet_age_groups')
                ->where('meet_id', $meetId)
                ->pluck('id')
                ->all();

            $keptAgIds = [];

            foreach ($incomingAg as $row) {
                $payload = [
                    'meet_id' => $meetId,
                    'code' => $row['code'] ?? null,
                    'min_age' => $row['min_age'] ?? null,
                    'max_age' => $row['max_age'] ?? null,
                    'gender' => $row['gender'] ?? null,
                    'name' => $row['name'] ?? null,
                    'handicap' => $row['handicap'] ?? null,
                    'updated_at' => now(),
                ];

                if (! empty($row['id'])) {
                    DB::table('meet_age_groups')->where('id', $row['id'])->where('meet_id', $meetId)->update($payload);
                    $keptAgIds[] = (int) $row['id'];
                } else {
                    $payload['created_at'] = now();
                    $newId = DB::table('meet_age_groups')->insertGetId($payload);
                    $keptAgIds[] = $newId;
                }
            }

            $deleteAgIds = array_values(array_diff($existingAgIds, $keptAgIds));
            if (! empty($deleteAgIds)) {
                DB::table('meet_age_groups')->whereIn('id', $deleteAgIds)->delete();
            }

            // 3) Sessions + Events sync
            $incomingSessions = collect($data['sessions'] ?? [])
                ->filter(fn ($r) => is_array($r))
                ->values();

            $existingSessionIds = DB::table('meet_sessions')->where('meet_id', $meetId)->pluck('id')->all();
            $keptSessionIds = [];

            foreach ($incomingSessions as $sRow) {
                $sessionPayload = [
                    'meet_id' => $meetId,
                    'session_no' => $sRow['session_no'] ?? null,
                    'name' => $sRow['name'] ?? null,
                    'date' => $sRow['date'] ?? null,
                    'start_time' => $sRow['start_time'] ?? null,
                    'updated_at' => now(),
                ];

                if (! empty($sRow['id'])) {
                    DB::table('meet_sessions')->where('id', $sRow['id'])->where('meet_id',
                        $meetId)->update($sessionPayload);
                    $sessionId = (int) $sRow['id'];
                } else {
                    $sessionPayload['created_at'] = now();
                    $sessionId = DB::table('meet_sessions')->insertGetId($sessionPayload);
                }

                $keptSessionIds[] = $sessionId;

                // Events für diese Session sync
                $incomingEvents = collect($sRow['events'] ?? [])
                    ->filter(fn ($r) => is_array($r))
                    ->values();

                $existingEventIds = DB::table('meet_events')->where('meet_session_id', $sessionId)->pluck('id')->all();
                $keptEventIds = [];

                foreach ($incomingEvents as $eRow) {
                    $eventPayload = [
                        'meet_session_id' => $sessionId,
                        'meet_age_group_id' => $eRow['meet_age_group_id'] ?? null,
                        'event_no' => $eRow['event_no'] ?? null,
                        'name' => $eRow['name'] ?? null,
                        'gender' => $eRow['gender'] ?? null,
                        'distance' => $eRow['distance'] ?? null,
                        'stroke' => $eRow['stroke'] ?? null,
                        'round' => $eRow['round'] ?? null,
                        'is_relay' => ! empty($eRow['is_relay']),
                        'updated_at' => now(),
                    ];

                    if (! empty($eRow['id'])) {
                        DB::table('meet_events')->where('id', $eRow['id'])->where('meet_session_id',
                            $sessionId)->update($eventPayload);
                        $keptEventIds[] = (int) $eRow['id'];
                    } else {
                        $eventPayload['created_at'] = now();
                        $newEventId = DB::table('meet_events')->insertGetId($eventPayload);
                        $keptEventIds[] = $newEventId;
                    }
                }

                $deleteEventIds = array_values(array_diff($existingEventIds, $keptEventIds));
                if (! empty($deleteEventIds)) {
                    DB::table('meet_events')->whereIn('id', $deleteEventIds)->delete();
                }
            }

            $deleteSessionIds = array_values(array_diff($existingSessionIds, $keptSessionIds));
            if (! empty($deleteSessionIds)) {
                DB::table('meet_sessions')->whereIn('id', $deleteSessionIds)->delete();
            }
        });

        return redirect()
            ->route('imports.lenex.meet_structure.edit', $batch)
            ->with('status', 'Meet structure updated.');
    }
}
