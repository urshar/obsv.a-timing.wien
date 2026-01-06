<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeetEventRequest;
use App\Http\Requests\MeetSessionRequest;
use App\Models\ImportBatch;
use App\Models\Meet;
use App\Models\MeetEvent;
use App\Models\MeetSession;
use Illuminate\Http\Request;

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
        $meetId = (int) $meet->id;

        $this->assertEventBelongsToMeet($event, $meetId);

        $data = $this->buildTreeData($meetId, $event);

        return view('imports.lenex.meet_structure.tree', [
            'meet' => $data['meet'],
            'sessions' => $data['sessions'],
            'ageGroups' => $data['ageGroups'],
            'selectedEvent' => $data['selectedEvent'],
        ]);
    }

    public function updateEvent(Request $request, Meet $meet, MeetEvent $event)
    {
        $meetId = (int) $meet->id;

        $this->assertEventBelongsToMeet($event, $meetId);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:1'],
            'distance' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'stroke' => ['nullable', 'string', 'max:20'],
            'round' => ['nullable', 'string', 'max:20'],
            'is_relay' => ['nullable', 'boolean'],
        ]);

        $data['is_relay'] = (bool) ($data['is_relay'] ?? false);

        $event->fill($data);
        $event->save();

        return redirect()
            ->route('meets.structure.events.edit', [$meet, $event])
            ->with('status', 'Event updated.');
    }

    public function editEventAgeGroups(Request $request, Meet $meet, MeetEvent $event)
    {
        $meetId = (int) $meet->id;

        $this->assertEventBelongsToMeet($event, $meetId);

        $data = $this->buildAgeGroupsEditorData($request, $meetId, $event);

        return view('imports.lenex.meet_structure.event_age_groups', [
            'meet' => $meet,
            'event' => $event,
            'q' => $data['q'],
            'gender' => $data['gender'],
            'prefix' => $data['prefix'],
            'selectedIds' => $data['selectedIds'],
            'ageGroups' => $data['ageGroups'],
        ]);
    }

    public function updateEventAgeGroups(Request $request, ImportBatch $batch, MeetEvent $event)
    {
        abort_unless($batch->status === 'committed', 404);
        abort_unless($batch->type === 'meet_structure', 404);
        abort_unless($batch->meet_id, 404);

        $meetId = (int) $batch->meet_id;

        $this->assertEventBelongsToMeet($event, $meetId);

        $data = $request->validate([
            'age_group_ids' => ['array'],
            'age_group_ids.*' => ['integer'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['age_group_ids'] ?? [])));

        $allowedIds = $this->allowedAgeGroupIds($meetId, $ids);

        $event->meetAgeGroups()->sync($allowedIds);

        return redirect()
            ->route('imports.lenex.meet_structure.events.edit', [$batch, $event])
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

        return view('meets.structure.events.create', [
            'meet' => $meet,
            'session' => $session,
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

        MeetEvent::query()->create([
            'meet_session_id' => $session->id,
            'event_no' => $data['event_no'],
            'name' => $data['name'],

            'gender' => $data['gender'] ?? null,
            'distance' => $data['distance'] ?? null,
            'stroke' => $data['stroke'] ?? null,
            'round' => $data['round'] ?? null,
            'is_relay' => (int) ($data['is_relay'] ?? false),

            // legacy field optional: leave null for manual
            'meet_age_group_id' => null,
        ]);

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Event created.');
    }

    public function destroyEvent(Meet $meet, MeetEvent $event)
    {
        // Ownership check via session->meet_id
        $session = $event->session; // relation needed, see note below
        abort_unless($session && $session->meet_id === $meet->id, 404);

        $event->delete();

        return redirect()
            ->route('meets.structure.show', $meet)
            ->with('status', 'Event deleted.');
    }
}
