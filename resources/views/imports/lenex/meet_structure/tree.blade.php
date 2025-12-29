{{-- resources/views/imports/lenex/meet_structure/tree.blade.php --}}
@extends('layouts.app')

@section('content')
    @php
        use App\Models\ImportBatch;use App\Models\MeetAgeGroup;use App\Models\MeetEvent;use App\Models\MeetSession;use App\Support\ParaSwim;use Illuminate\Support\Collection;

        /** @var ImportBatch $batch */
        /** @var object $meet */
        /** @var Collection|MeetSession[] $sessions */
        /** @var Collection|MeetAgeGroup[] $ageGroups */
        /** @var MeetEvent|null $selectedEvent */

        $selectedEventId = $selectedEvent?->id;

        // Für rechte Seite: welche AgeGroups sind aktuell dem Event zugewiesen?
        // (setzt voraus: $selectedEvent ist mit Relation "ageGroups" geladen – sonst fallback leeres Array)
        $selectedAgeGroupIds = [];
        if ($selectedEvent && method_exists($selectedEvent, 'ageGroups')) {
            try {
                $selectedAgeGroupIds = $selectedEvent->ageGroups->pluck('id')->map(fn ($v) => (int) $v)->all();
            } catch (Throwable $e) {
                $selectedAgeGroupIds = [];
            }
        }
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Meet Structure</h1>
                <div class="text-sm text-slate-600">
                    Batch #{{ $batch->id }} · {{ $meet->name ?? '—' }}
                </div>
            </div>

            <x-ui.button
                variant="secondary"
                type="button"
                onclick="window.location='{{ route('imports.lenex.meet_structure.show', ['batch' => $batch]) }}'">
                Back to overview
            </x-ui.button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT: Sessions & Events --}}
            <div class="lg:col-span-2">
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="font-semibold text-slate-900">Sessions &amp; Events</div>
                                <div class="text-sm text-slate-600">Click an event to edit age groups</div>
                            </div>
                        </div>
                    </x-ui.card-header>

                    <x-ui.card-body>
                        @forelse ($sessions as $session)
                            <div class="border border-slate-200 rounded-xl mb-4">
                                <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                                    <div class="text-sm font-semibold text-slate-900">
                                        Session {{ $session->session_no }}
                                        <span class="font-normal text-slate-600">
                                        · {{ $session->date ?? '—' }}
                                    </span>
                                    </div>

                                    @php
                                        $eventCount = (int) ($session->meetEvents?->count() ?? 0);
                                    @endphp
                                    <div class="text-sm text-slate-600">
                                        {{ $eventCount }} events
                                    </div>
                                </div>

                                <div class="p-4">
                                    @php
                                        $events = $session->meetEvents ?? collect();
                                    @endphp

                                    @forelse ($events as $event)
                                        @php
                                            $isSelected = ($selectedEventId !== null) && ((int) $event->id === (int) $selectedEventId);
                                            $strokeBadge = ParaSwim::strokePrefix($event->stroke);
                                        @endphp

                                        <a
                                            href="{{ route('imports.lenex.meet_structure.events.age_groups.edit', ['batch' => $batch, 'event' => $event]) }}"
                                            class="block rounded-lg border px-4 py-3 mb-3 hover:bg-slate-50 {{ $isSelected ? 'border-slate-900 bg-slate-50' : 'border-slate-200 bg-white' }}"
                                        >
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <div class="font-semibold text-slate-900">
                                                        #{{ $event->event_no }} · {{ $event->name ?? '—' }}
                                                    </div>

                                                    <div
                                                        class="text-sm text-slate-600 mt-1 flex flex-wrap items-center gap-2">
                                                        <span>{{ $event->distance ? $event->distance.'m' : '—' }}</span>

                                                        <x-ui.badge>
                                                            {{ $strokeBadge }}
                                                        </x-ui.badge>

                                                        @if(!empty($event->round))
                                                            <x-ui.badge>{{ $event->round }}</x-ui.badge>
                                                        @endif

                                                        @if(!empty($event->gender))
                                                            <x-ui.badge>{{ $event->gender }}</x-ui.badge>
                                                        @endif

                                                        @if((int) ($event->is_relay ?? 0) === 1)
                                                            <x-ui.badge>Relay</x-ui.badge>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="text-sm text-slate-600 whitespace-nowrap">
                                                    Edit
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="text-sm text-slate-600">No events.</div>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-600">No sessions.</div>
                        @endforelse
                    </x-ui.card-body>
                </x-ui.card>
            </div>

            {{-- RIGHT: Edit age groups --}}
            <div class="lg:col-span-1">
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="font-semibold text-slate-900">Edit event age groups</div>
                        <div class="text-sm text-slate-600">Assign age groups to the selected event</div>
                    </x-ui.card-header>

                    <x-ui.card-body>
                        @if (!$selectedEvent)
                            <div class="text-sm text-slate-600">
                                Select an event on the left to edit its age groups.
                            </div>
                        @else
                            <div class="mb-4">
                                <div class="text-sm text-slate-600">Selected event</div>
                                <div class="font-semibold text-slate-900">
                                    #{{ $selectedEvent->event_no }} · {{ $selectedEvent->name ?? '—' }}
                                </div>
                            </div>

                            <form method="POST"
                                  action="{{ route('imports.lenex.meet_structure.events.age_groups.update', ['batch' => $batch, 'event' => $selectedEvent]) }}">
                                @csrf
                                @method('PUT')

                                <div class="space-y-2 max-h-105 overflow-auto pr-1">
                                    @foreach ($ageGroups as $ag)
                                        @php
                                            $agId = (int) $ag->id;
                                            $checked = in_array($agId, $selectedAgeGroupIds, true);
                                        @endphp

                                        <label
                                            class="flex items-start gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 hover:bg-slate-50">
                                            <input
                                                type="checkbox"
                                                name="age_group_ids[]"
                                                value="{{ $agId }}"
                                                class="mt-1"
                                                @checked($checked)
                                            >

                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-slate-900 truncate">
                                                    {{ $ag->name ?? '—' }}
                                                </div>

                                                <div class="text-xs text-slate-600 flex flex-wrap gap-2 mt-1">
                                                    @if(!empty($ag->gender))
                                                        <span>{{ $ag->gender }}</span>
                                                    @endif
                                                    @if(!empty($ag->handicap))
                                                        <span>{{ $ag->handicap }}</span>
                                                    @endif
                                                    <span class="text-slate-400">Code: {{ $ag->code }}</span>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <x-ui.button variant="primary" type="submit">
                                        Save
                                    </x-ui.button>
                                </div>
                            </form>
                        @endif
                    </x-ui.card-body>
                </x-ui.card>
            </div>
        </div>
    </div>
@endsection
