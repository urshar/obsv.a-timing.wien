<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Models\Meet;
use App\Models\MeetEvent;
use Illuminate\Http\Request;

class MeetStructureController extends AbstractMeetStructureController
{
    public function show(Meet $meet)
    {
        $data = $this->buildShowData((int) $meet->id);

        return view('imports.lenex.meet_structure.show', [
            'meet' => $data['meet'],
            'ageGroups' => $data['ageGroups'],
            'ageGroupsById' => $data['ageGroupsById'],
            'sessions' => $data['sessions'],
            'eventsBySession' => $data['eventsBySession'],
        ]);
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
}
