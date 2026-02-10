<?php

namespace App\Http\Controllers;

use App\Models\Meet;
use App\Models\MeetAgeGroup;
use App\Models\MeetEvent;
use App\Models\MeetSession;
use App\Support\ParaSwim;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class AbstractMeetStructureController extends Controller
{
    protected function assertEventBelongsToMeet(MeetEvent $event, int $meetId): void
    {
        $event->loadMissing('meetSession');

        abort_unless(
            $event->meetSession && (int) $event->meetSession->meet_id === $meetId,
            404
        );
    }

    /**
     * Daten für Tree (links Sessions/Events, rechts Event-Editor).
     */
    protected function buildTreeData(int $meetId, ?MeetEvent $selectedEvent): array
    {
        $meet = $this->loadMeet($meetId);

        $sessions = MeetSession::query()
            ->where('meet_id', $meetId)
            ->orderBy('session_no')
            ->with([
                'meetEvents' => function ($q) {
                    $q->orderBy('event_no')->orderBy('id');
                },
            ])
            ->get();

        $ageGroups = MeetAgeGroup::query()
            ->where('meet_id', $meetId)
            ->orderBy('name')
            ->get();

        $selectedEvent?->loadMissing(['meetAgeGroups', 'meetSession']);

        return [
            'meet' => $meet,
            'sessions' => $sessions,
            'ageGroups' => $ageGroups,
            'selectedEvent' => $selectedEvent,
        ];
    }

    protected function loadMeet(int $meetId): Meet
    {
        return Meet::query()->findOrFail($meetId);
    }

    /**
     * Daten für Show (Splash/Overview).
     */
    protected function buildShowData(int $meetId): array
    {
        $meet = $this->loadMeet($meetId);

        $ageGroups = MeetAgeGroup::query()
            ->where('meet_id', $meetId)
            ->orderBy('id')
            ->get();

        $ageGroupsById = $ageGroups->keyBy('id');

        $sessions = MeetSession::query()
            ->where('meet_id', $meetId)
            ->orderBy('session_no')
            ->orderBy('id')
            ->get();

        $sessionIds = $sessions->pluck('id')->all();

        $usageCounts = ! empty($eventIds)
            ? DB::table('age_group_event')
                ->whereIn('meet_event_id', $eventIds)
                ->selectRaw('age_group_id, COUNT(*) as cnt')
                ->groupBy('age_group_id')
                ->pluck('cnt', 'age_group_id')
                ->all()
            : [];

        $events = ! empty($sessionIds)
            ? MeetEvent::query()
                ->with([
                    'meetAgeGroups' => function ($q) {
                        $q->orderBy('id');
                    },
                ])
                ->whereIn('meet_session_id', $sessionIds)
                ->orderBy('event_no')
                ->orderBy('id')
                ->get()
            : collect();

        $eventsBySession = $events->groupBy('meet_session_id');

        $usedAgeGroupIds = $events
            ->flatMap(fn ($ev) => $ev->meetAgeGroups?->pluck('id') ?? collect())
            ->unique()
            ->values()
            ->all();

        return [
            'meet' => $meet,
            'ageGroups' => $ageGroups,
            'ageGroupsById' => $ageGroupsById,
            'sessions' => $sessions,
            'eventsBySession' => $eventsBySession,
            'usedAgeGroupIds' => $usedAgeGroupIds,
            'ageGroupUsageCounts' => $usageCounts,
        ];
    }

    /**
     * Gemeinsame Datenaufbereitung für AgeGroup-Editor (Filter/Pagination).
     * Liefert die View-Variablen (ohne Redirect/Route-Namen).
     */
    protected function buildAgeGroupsEditorData(Request $request, int $meetId, MeetEvent $event): array
    {
        $q = trim((string) $request->query('q', ''));
        $gender = trim((string) $request->query('gender', ''));

        // normalize gender
        if (! in_array($gender, ['M', 'F', 'X', ''], true)) {
            $gender = '';
        }

        $event->loadMissing('meetAgeGroups');
        $selectedIds = $event->meetAgeGroups->pluck('id')->all();

        $prefix = ParaSwim::strokePrefix($event->stroke);

        $baseQuery = MeetAgeGroup::query()
            ->where('meet_id', $meetId);

        if ($q !== '') {
            $baseQuery->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('sport_class_group', 'like', "%{$q}%")
                    ->orWhere('sport_class_raw', 'like', "%{$q}%");
            });
        }

        if ($gender !== '') {
            // Gender in AG kann null sein; je nach Wunsch:
            // - streng: nur exakt passend
            // - relaxed: null zulassen
            $baseQuery->where(function ($sub) use ($gender) {
                $sub->where('gender', $gender)->orWhereNull('gender');
            });
        }

        $baseQuery->orderBy('name')->orderBy('id');

        $perPage = 30;
        $page = max(1, (int) $request->query('page', 1));

        $total = (clone $baseQuery)->count();
        $items = (clone $baseQuery)
            ->forPage($page, $perPage)
            ->get();

        $ageGroups = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return [
            'q' => $q,
            'gender' => $gender,
            'prefix' => $prefix,
            'selectedIds' => $selectedIds,
            'ageGroups' => $ageGroups,
        ];
    }

    protected function allowedAgeGroupIds(int $meetId, array $ids): array
    {
        return MeetAgeGroup::query()
            ->where('meet_id', $meetId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();
    }
}
