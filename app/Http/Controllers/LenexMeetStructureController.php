<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Models\MeetAgeGroup;
use App\Models\MeetEvent;
use App\Models\MeetSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

        $ageGroupsById = $ageGroups->keyBy('id');

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
            'ageGroupsById' => $ageGroupsById,
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

    public function tree(ImportBatch $batch)
    {
        abort_unless($batch->status === 'committed', 404);
        abort_unless($batch->type === 'meet_structure', 404);
        abort_unless($batch->meet_id, 404);

        $meetId = $batch->meet_id;

        $meet = \DB::table('meets')->where('id', $meetId)->first();

        $sessions = MeetSession::query()
            ->where('meet_id', $meetId)
            ->orderBy('session_no')
            ->with([
                'meetEvents' => function ($q) {
                    $q->orderBy('event_no');
                },
            ])
            ->get();

        $ageGroups = MeetAgeGroup::query()
            ->where('meet_id', $meetId)
            ->orderBy('name')
            ->get();

        return view('imports.lenex.meet_structure.tree', [
            'batch' => $batch,
            'meet' => $meet,
            'sessions' => $sessions,
            'ageGroups' => $ageGroups,
            'selectedEvent' => null,
        ]);
    }

    public function editEvent(ImportBatch $batch, MeetEvent $event)
    {
        abort_unless($batch->status === 'committed', 404);
        abort_unless($batch->type === 'meet_structure', 404);
        abort_unless($batch->meet_id, 404);

        $meetId = $batch->meet_id;

        $event->loadMissing('meetSession');
        abort_unless($event->meetSession && (int) $event->meetSession->meet_id === (int) $meetId, 404);

        $meet = DB::table('meets')->where('id', $meetId)->first();

        $sessions = MeetSession::query()
            ->where('meet_id', $meetId)
            ->orderBy('session_no')
            ->with([
                'meetEvents' => function ($q) {
                    $q->orderBy('event_no');
                },
            ])
            ->get();

        $ageGroups = MeetAgeGroup::query()
            ->where('meet_id', $meetId)
            ->orderBy('name')
            ->get();

        $event->load('meetAgeGroups', 'meetSession');

        return view('imports.lenex.meet_structure.tree', [
            'batch' => $batch,
            'meet' => $meet,
            'sessions' => $sessions,
            'ageGroups' => $ageGroups,
            'selectedEvent' => $event,
        ]);
    }

    public function updateEvent(Request $request, ImportBatch $batch, MeetEvent $event)
    {
        abort_unless($batch->status === 'committed', 404);
        abort_unless($batch->type === 'meet_structure', 404);
        abort_unless($batch->meet_id, 404);

        $meetId = $batch->meet_id;
        $event->loadMissing('meetSession');
        abort_unless($event->meetSession && (int) $event->meetSession->meet_id === (int) $meetId, 404);

        $data = $request->validate([
            'event_no' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:M,F,X'],
            'distance' => ['nullable', 'integer', 'min:0', 'max:50000'],
            'stroke' => ['nullable', 'string', 'max:20'],
            'round' => ['nullable', 'string', 'max:20'],
            'is_relay' => ['nullable', 'boolean'],

            'age_group_ids' => ['array'],
            'age_group_ids.*' => ['integer'],
        ]);

        $event->update([
            'event_no' => $data['event_no'] ?? $event->event_no,
            'name' => $data['name'] ?? $event->name,
            'gender' => $data['gender'] ?? $event->gender,
            'distance' => $data['distance'] ?? $event->distance,
            'stroke' => $data['stroke'] ?? $event->stroke,
            'round' => $data['round'] ?? $event->round,
            'is_relay' => ! empty($data['is_relay']),
        ]);

        $ageGroupIds = $data['age_group_ids'] ?? [];

        $allowedIds = MeetAgeGroup::query()
            ->where('meet_id', $meetId)
            ->whereIn('id', $ageGroupIds)
            ->pluck('id')
            ->all();

        $event->meetAgeGroups()->sync($allowedIds);

        return redirect()
            ->route('imports.lenex.meet_structure.events.edit', [$batch, $event])
            ->with('status', 'Event updated.');
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

            'age_groups' => ['sometimes', 'array'],
            'age_groups.*.id' => ['nullable', 'integer'],
            'age_groups.*.code' => ['nullable', 'string', 'max:50'],
            'age_groups.*.min_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_groups.*.max_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_groups.*.gender' => ['nullable', 'in:M,F,X'],
            'age_groups.*.name' => ['nullable', 'string', 'max:255'],
            'age_groups.*.handicap' => ['nullable', 'string', 'max:50'],

            'sessions' => ['sometimes', 'array'],
            'sessions.*.id' => ['nullable', 'integer'],
            'sessions.*.session_no' => ['nullable', 'integer', 'min:1', 'max:999'],
            'sessions.*.name' => ['nullable', 'string', 'max:255'],
            'sessions.*.date' => ['nullable', 'date'],
            'sessions.*.start_time' => ['nullable', 'date_format:H:i'],

            'sessions.*.events' => ['sometimes', 'array'],
            'sessions.*.events.*.id' => ['nullable', 'integer'],
            'sessions.*.events.*.event_no' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'sessions.*.events.*.name' => ['nullable', 'string', 'max:255'],
            'sessions.*.events.*.gender' => ['nullable', 'in:M,F,X'],
            'sessions.*.events.*.distance' => ['nullable', 'integer', 'min:0', 'max:50000'],
            'sessions.*.events.*.stroke' => ['nullable', 'string', 'max:20'],
            'sessions.*.events.*.round' => ['nullable', 'string', 'max:20'],
            'sessions.*.events.*.is_relay' => ['nullable', 'boolean'],
            'sessions.*.events.*.meet_age_group_id' => [
                'nullable',
                'integer',
                Rule::exists('meet_age_groups', 'id')->where(fn ($q) => $q->where('meet_id', $meetId)),
            ],
        ]);

        $hasAgeGroups = $request->has('age_groups');
        $hasSessions = $request->has('sessions');

        DB::transaction(function () use ($meetId, $data, $hasAgeGroups, $hasSessions) {
            // 1) Meet updaten
            DB::table('meets')->where('id', $meetId)->update([
                'name' => data_get($data, 'meet.name'),
                'start_date' => data_get($data, 'meet.start_date'),
                'end_date' => data_get($data, 'meet.end_date'),
                'updated_at' => now(),
            ]);

            // 2) AgeGroups sync
            if ($hasAgeGroups) {
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
                        DB::table('meet_age_groups')->where('id', $row['id'])->where('meet_id',
                            $meetId)->update($payload);
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
            }

            // 3) Sessions + Events sync

            if ($hasSessions) {
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

                    $existingEventIds = DB::table('meet_events')->where('meet_session_id',
                        $sessionId)->pluck('id')->all();
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
            }
        });

        return redirect()
            ->route('imports.lenex.meet_structure.tree', $batch)
            ->with('status', 'Meet structure updated.');
    }

    public function editEventAgeGroups(Request $request, ImportBatch $batch, MeetEvent $event)
    {
        abort_unless($batch->status === 'committed', 404);
        abort_unless($batch->type === 'meet_structure', 404);
        abort_unless($batch->meet_id, 404);

        $meetId = (int) $batch->meet_id;

        $event->loadMissing('meetSession', 'meetAgeGroups');
        abort_unless($event->meetSession && (int) $event->meetSession->meet_id === $meetId, 404);

        // Filter / Search (serverseitig, stabil)
        $q = trim((string) $request->query('q', ''));
        $gender = trim((string) $request->query('gender', '')); // F/M/X/''

        $ageGroupsQuery = MeetAgeGroup::query()->where('meet_id', $meetId);

        if ($q !== '') {
            $ageGroupsQuery->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('handicap', 'like', "%{$q}%")
                    ->orWhere('sport_class_raw', 'like', "%{$q}%");
            });
        }

        if (in_array($gender, ['F', 'M', 'X'], true)) {
            $ageGroupsQuery->where('gender', $gender);
        }

        $ageGroups = $ageGroupsQuery
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        $selectedIds = $event->meetAgeGroups->pluck('id')->all();

        return view('imports.lenex.meet_structure.event_age_groups', [
            'batch' => $batch,
            'event' => $event,
            'ageGroups' => $ageGroups,
            'selectedIds' => $selectedIds,
            'q' => $q,
            'gender' => $gender,
        ]);
    }

    public function updateEventAgeGroups(Request $request, ImportBatch $batch, MeetEvent $event)
    {
        abort_unless($batch->status === 'committed', 404);
        abort_unless($batch->type === 'meet_structure', 404);
        abort_unless($batch->meet_id, 404);

        $meetId = (int) $batch->meet_id;

        $event->loadMissing('meetSession');
        abort_unless($event->meetSession && (int) $event->meetSession->meet_id === $meetId, 404);

        $data = $request->validate([
            'age_group_ids' => ['array'],
            'age_group_ids.*' => ['integer'],
        ]);

        $ids = $data['age_group_ids'] ?? [];

        // Nur IDs zulassen, die zu diesem Meet gehören
        $allowedIds = MeetAgeGroup::query()
            ->where('meet_id', $meetId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        $event->meetAgeGroups()->sync($allowedIds);

        // Zurück ins Event-Detail im Tree (oder auf die Editor-Seite bleiben)
        return redirect()
            ->route('imports.lenex.meet_structure.events.edit', [$batch, $event])
            ->with('status', 'Age groups updated.');
    }
}
