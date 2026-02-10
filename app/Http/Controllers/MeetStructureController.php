<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeetAgeGroupRequest;
use App\Http\Requests\MeetEventRequest;
use App\Http\Requests\MeetSessionRequest;
use App\Models\Meet;
use App\Models\MeetAgeGroup;
use App\Models\MeetEvent;
use App\Models\MeetSession;
use App\Models\ParaSwimStyle;
use Illuminate\Http\Request;

/**
 * Meet Structure editor.
 *
 * Canonical assignment:
 *  - Event ↔ AgeGroups via pivot table age_group_event
 *
 * Legacy / read-only:
 *  - meet_events.meet_age_group_id (do not write, do not validate, do not rely on it)
 */
class MeetStructureController extends AbstractMeetStructureController
{
    public function show(Meet $meet)
    {
        $data = $this->buildShowData((int) $meet->id);

        return view('meets.structure.show', $data);
    }

    public function tree(Meet $meet)
    {
        $data = $this->buildTreeData((int) $meet->id, null);

        return view('imports.lenex.meet_structure.tree', [
            'meet' => $data['meet'],
            'sessions' => $data['sessions'],
            'ageGroups' => $data['ageGroups'],
            'selectedEvent' => $data['selectedEvent'],
        ]);
    }

    public function editEvent(Meet $meet, MeetEvent $event)
    {
        $session = $event->meetSession; // siehe Relation unten
        abort_unless($session && $session->meet_id === $meet->id, 404);

        $swimStyles = ParaSwimStyle::query()
            ->orderBy('relay_count')
            ->orderBy('distance')
            ->orderBy('stroke')
            ->get();

        return view('meets.structure.events.edit', [
            'meet' => $meet,
            'session' => $session,
            'event' => $event,
            'swimStyles' => $swimStyles,
        ]);
    }

    public function updateEvent(MeetEventRequest $request, Meet $meet, MeetEvent $event)
    {
        $session = $event->meetSession;
        abort_unless($session && $session->meet_id === $meet->id, 404);

        $data = $request->validated();

        $exists = MeetEvent::query()
            ->where('meet_session_id', $event->meet_session_id)
            ->where('event_no', $data['event_no'])
            ->where('id', '!=', $event->id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['event_no' => 'This event number is already used in this session.'])
                ->withInput();
        }

        /**
         * NOTE: age_group_event pivot is canonical for Event ↔ AgeGroups.
         * Do NOT touch meet_age_group_id here – it is legacy/read-only.
         */
        $event->update([
            'event_no' => $data['event_no'],
            'name' => $data['name'],
            'gender' => $data['gender'] ?? null,
            'distance' => $data['distance'] ?? null,
            'stroke' => $data['stroke'] ?? null,
            'round' => $data['round'] ?? null,
            'is_relay' => (int) ($data['is_relay'] ?? false),
        ]);

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Event updated.');
    }

    public function editEventAgeGroups(Request $request, Meet $meet, MeetEvent $event)
    {
        $meetId = (int) $meet->id;

        $this->assertEventBelongsToMeet($event, $meetId);

        $data = $this->buildAgeGroupsEditorData($request, $meetId, $event);

        return view('meets.structure.events.age_groups', [
            'meet' => $meet,
            'event' => $event,
            'q' => $data['q'],
            'gender' => $data['gender'],
            'prefix' => $data['prefix'],
            'selectedIds' => $data['selectedIds'],
            'ageGroups' => $data['ageGroups'],
        ]);
    }

    public function updateEventAgeGroups(Request $request, Meet $meet, MeetEvent $event)
    {
        $meetId = (int) $meet->id;

        $this->assertEventBelongsToMeet($event, $meetId);

        $data = $request->validate([
            'age_group_ids' => ['nullable', 'array'],
            'age_group_ids.*' => ['integer', 'exists:meet_age_groups,id'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['age_group_ids'] ?? [])));

        $allowedIds = $this->allowedAgeGroupIds($meetId, $ids);

        $event->meetAgeGroups()->sync($allowedIds);

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Age groups updated.');
    }

    public function createSession(Meet $meet)
    {
        return view('meets.structure.sessions.create', [
            'meet' => $meet,
        ]);
    }

    public function storeSession(MeetSessionRequest $request, Meet $meet)
    {
        $data = $request->validated();

        $exists = MeetSession::query()
            ->where('meet_id', $meet->id)
            ->where('session_no', $data['session_no'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['session_no' => 'This session number is already used in this meeting.'])
                ->withInput();
        }

        $meet->sessions()->create($data);

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Session created.');
    }

    public function editSession(Meet $meet, MeetSession $session)
    {
        abort_unless($session->meet_id === $meet->id, 404);

        return view('meets.structure.sessions.edit', [
            'meet' => $meet,
            'session' => $session,
        ]);
    }

    public function updateSession(MeetSessionRequest $request, Meet $meet, MeetSession $session)
    {
        abort_unless($session->meet_id === $meet->id, 404);

        $data = $request->validated();

        $exists = MeetSession::query()
            ->where('meet_id', $meet->id)
            ->where('session_no', $data['session_no'])
            ->where('id', '!=', $session->id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['session_no' => 'This session number is already used in this meeting.'])
                ->withInput();
        }

        $session->update($data);

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Session updated.');
    }

    public function destroySession(Meet $meet, MeetSession $session)
    {
        abort_unless($session->meet_id === $meet->id, 404);

        // optional: später guard wenn results/entries existieren
        $session->delete();

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Session deleted.');
    }

    public function createEvent(Meet $meet, MeetSession $session)
    {
        abort_unless($session->meet_id === $meet->id, 404);

        $swimStyles = ParaSwimStyle::query()
            ->orderBy('relay_count')
            ->orderBy('distance')
            ->orderBy('stroke')
            ->get();

        return view('meets.structure.events.create', [
            'meet' => $meet,
            'session' => $session,
            'swimStyles' => $swimStyles,
        ]);
    }

    public function storeEvent(MeetEventRequest $request, Meet $meet, MeetSession $session)
    {
        abort_unless($session->meet_id === $meet->id, 404);

        $data = $request->validated();

        $exists = MeetEvent::query()
            ->where('meet_session_id', $session->id)
            ->where('event_no', $data['event_no'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['event_no' => 'This event number is already used in this session.'])
                ->withInput();
        }

        /**
         * NOTE: Pivot table age_group_event is canonical for event ↔ age-group assignment.
         * meet_events.meet_age_group_id is legacy/read-only (kept for backward compatibility).
         * Do not write it here.
         */
        MeetEvent::query()->create([
            'meet_session_id' => $session->id,
            'event_no' => $data['event_no'],
            'name' => $data['name'],

            'gender' => $data['gender'] ?? null,
            'distance' => $data['distance'] ?? null,
            'stroke' => $data['stroke'] ?? null,
            'round' => $data['round'] ?? null,
            'is_relay' => (int) ($data['is_relay'] ?? false),
        ]);

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Event created.');
    }

    public function destroyEvent(Meet $meet, MeetEvent $event)
    {
        $session = $event->meetSession;
        abort_unless($session && $session->meet_id === $meet->id, 404);

        $event->delete();

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Event deleted.');
    }

    public function createAgeGroup(Meet $meet)
    {
        return view('meets.structure.age_groups.create', [
            'meet' => $meet,
        ]);
    }

    public function storeAgeGroup(MeetAgeGroupRequest $request, Meet $meet)
    {
        $data = $request->validated();

        MeetAgeGroup::query()->create([
            'meet_id' => $meet->id,
            'name' => $data['name'],
            'gender' => $data['gender'] ?? null,
            'code' => $data['code'] ?? null,
            'min_age' => $data['min_age'] ?? null,
            'max_age' => $data['max_age'] ?? null,
            'handicap' => $data['handicap'] ?? null,
        ]);

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Age group created.');
    }

    public function editAgeGroup(Meet $meet, MeetAgeGroup $ageGroup)
    {
        $this->assertAgeGroupBelongsToMeet($ageGroup, (int) $meet->id);

        return view('meets.structure.age_groups.edit', [
            'meet' => $meet,
            'ageGroup' => $ageGroup,
        ]);
    }

    protected function assertAgeGroupBelongsToMeet(MeetAgeGroup $ageGroup, int $meetId): void
    {
        abort_unless((int) $ageGroup->meet_id === $meetId, 404);
    }

    public function updateAgeGroup(MeetAgeGroupRequest $request, Meet $meet, MeetAgeGroup $ageGroup)
    {
        $this->assertAgeGroupBelongsToMeet($ageGroup, (int) $meet->id);

        $data = $request->validated();

        $ageGroup->update([
            'name' => $data['name'],
            'gender' => $data['gender'] ?? null,
            'code' => $data['code'] ?? null,
            'min_age' => $data['min_age'] ?? null,
            'max_age' => $data['max_age'] ?? null,
            'handicap' => $data['handicap'] ?? null,
        ]);

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Age group updated.');
    }

    public function destroyAgeGroup(Meet $meet, MeetAgeGroup $ageGroup)
    {
        $this->assertAgeGroupBelongsToMeet($ageGroup, (int) $meet->id);

        $ageGroup->delete();

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Age group deleted.');
    }

    public function editAgeGroupEvents(Meet $meet, MeetAgeGroup $ageGroup)
    {
        abort_unless((int) $ageGroup->meet_id === (int) $meet->id, 404);

        $meetId = (int) $meet->id;

        $sessions = MeetSession::query()
            ->where('meet_id', $meetId)
            ->orderBy('session_no')
            ->orderBy('id')
            ->get();

        $sessionIds = $sessions->pluck('id')->all();

        $events = ! empty($sessionIds)
            ? MeetEvent::query()
                ->whereIn('meet_session_id', $sessionIds)
                ->with([
                    'meetAgeGroups' => function ($q) use ($ageGroup) {
                        // reicht als "mark selected"; wir können auch ohne eager loading arbeiten,
                        // aber so ist es einfach im Blade.
                        $q->where('meet_age_groups.id', $ageGroup->id);
                    },
                ])
                ->orderBy('meet_session_id')
                ->orderBy('event_no')
                ->orderBy('id')
                ->get()
            : collect();

        $eventsBySession = $events->groupBy('meet_session_id');

        $selectedEventIds = $ageGroup->meetEvents()
            ->whereIn('meet_events.meet_session_id', $sessionIds)
            ->pluck('meet_events.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return view('meets.structure.age_groups.assign', [
            'meet' => $meet,
            'ageGroup' => $ageGroup,
            'sessions' => $sessions,
            'eventsBySession' => $eventsBySession,
            'selectedEventIds' => $selectedEventIds,
        ]);
    }

    public function updateAgeGroupEvents(Request $request, Meet $meet, MeetAgeGroup $ageGroup)
    {
        abort_unless((int) $ageGroup->meet_id === (int) $meet->id, 404);

        $data = $request->validate([
            'event_ids' => ['array'],
            'event_ids.*' => ['integer'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['event_ids'] ?? [])));

        // nur Events zulassen, die wirklich zu diesem Meet gehören
        $allowedEventIds = MeetEvent::query()
            ->whereIn('id', $ids)
            ->whereHas('session', function ($q) use ($meet) {
                $q->where('meet_id', $meet->id);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $ageGroup->meetEvents()->sync($allowedEventIds);

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Age group assignments updated.');
    }
}
