@extends('layouts.app')

@php
    use App\Models\MeetAgeGroup;
    use App\Models\MeetEvent;
    use App\Models\MeetSession;
    use App\Support\ParaSwim;
    use Illuminate\Database\Eloquent\Collection;

    /** @var Collection|MeetSession[] $sessions */
    /** @var Collection|MeetAgeGroup[] $ageGroups */
    /** @var MeetEvent|null $selectedEvent */

    $selectedEventId = $selectedEvent?->id;

    // Damit rechts die zugewiesenen AgeGroups sauber verfügbar sind:
    if ($selectedEvent) {
        $selectedEvent->loadMissing(['meetSession', 'meetAgeGroups']);
    }

    $strokeBadge = function (?string $stroke): string {
        return ParaSwim::strokePrefix($stroke);
    };
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Meet Structure</h1>
                <div class="text-sm text-slate-600">
                    Batch #{{ $batch->id }} · {{ $meet->name ?? '—' }}
                </div>
            </div>

            <div class="shrink-0">
                <a href="{{ route('imports.lenex.preview', $batch) }}">
                    <x-ui.button variant="secondary">Back to overview</x-ui.button>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: Sessions & Events --}}
            <x-ui.card>
                <x-ui.card-header>
                    <div class="font-semibold text-slate-900">Sessions & Events</div>
                    <div class="text-xs text-slate-600">
                        Click an event to edit its data (right) or its age groups
                    </div>
                </x-ui.card-header>

                <x-ui.card-body class="space-y-4">
                    @forelse ($sessions as $session)
                        @php
                            $events = $session->meetEvents ?? collect();
                            $eventCount = $events->count();
                        @endphp

                        <div class="rounded-lg ring-1 ring-slate-200">
                            <div
                                class="px-3 py-2 flex items-center justify-between bg-slate-50 border-b border-slate-200 rounded-t-lg">
                                <div class="text-sm font-semibold text-slate-900">
                                    Session {{ $session->session_no }}
                                    <span class="text-slate-500 font-normal">
                                        · {{ $session->date ?? '—' }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-600">{{ $eventCount }} events</div>
                            </div>

                            <div class="p-3 space-y-2">
                                @if ($eventCount === 0)
                                    <div class="text-sm text-slate-500">No events.</div>
                                @else
                                    @foreach ($events as $ev)
                                        @php
                                            $isActive = $selectedEventId === $ev->id;
                                            $prefix = $strokeBadge($ev->stroke);
                                        @endphp

                                        <a href="{{ route('imports.lenex.meet_structure.events.edit', [$batch, $ev]) }}"
                                           class="block rounded-lg border px-3 py-2 hover:bg-slate-50 {{ $isActive ? 'border-slate-900 bg-slate-50' : 'border-slate-200' }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-slate-900 truncate">
                                                        #{{ $ev->event_no }} · {{ $ev->name ?? '—' }}
                                                    </div>

                                                    <div
                                                        class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                                                        @if($ev->distance)
                                                            <x-ui.badge>{{ $ev->distance }}m</x-ui.badge>
                                                        @endif

                                                        <x-ui.badge>{{ $prefix }}</x-ui.badge>

                                                        @if($ev->round)
                                                            <x-ui.badge>{{ $ev->round }}</x-ui.badge>
                                                        @endif

                                                        @if($ev->gender)
                                                            <x-ui.badge>{{ $ev->gender }}</x-ui.badge>
                                                        @endif

                                                        @if($ev->is_relay)
                                                            <x-ui.badge>Relay</x-ui.badge>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="shrink-0 text-sm font-semibold text-slate-600">
                                                    Edit
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">No sessions.</div>
                    @endforelse
                </x-ui.card-body>
            </x-ui.card>

            {{-- RIGHT: Selected Event --}}
            <x-ui.card class="lg:col-span-2">
                <x-ui.card-header>
                    <div class="font-semibold text-slate-900">Selected event</div>
                    <div class="text-xs text-slate-600">
                        Edit event data here. Use “Edit age groups” to change assignments.
                    </div>
                </x-ui.card-header>

                <x-ui.card-body>
                    @if (! $selectedEvent)
                        <div class="text-sm text-slate-600">
                            Select an event on the left to edit it.
                        </div>
                    @else
                        @php
                            $prefix = $strokeBadge($selectedEvent->stroke);
                            $assigned = $selectedEvent->meetAgeGroups ?? collect();
                        @endphp

                        <div class="mb-4">
                            <div class="text-sm text-slate-600">Event</div>
                            <div class="text-lg font-semibold text-slate-900">
                                #{{ $selectedEvent->event_no }} · {{ $selectedEvent->name ?? '—' }}
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                                @if($selectedEvent->distance)
                                    <x-ui.badge>{{ $selectedEvent->distance }}m</x-ui.badge>
                                @endif
                                <x-ui.badge>{{ $prefix }}</x-ui.badge>
                                @if($selectedEvent->round)
                                    <x-ui.badge>{{ $selectedEvent->round }}</x-ui.badge>
                                @endif
                                @if($selectedEvent->gender)
                                    <x-ui.badge>{{ $selectedEvent->gender }}</x-ui.badge>
                                @endif
                                @if($selectedEvent->is_relay)
                                    <x-ui.badge>Relay</x-ui.badge>
                                @endif
                            </div>
                        </div>

                        {{-- Event edit form --}}
                        <form method="POST"
                              action="{{ route('imports.lenex.meet_structure.events.update', [$batch, $selectedEvent]) }}"
                              class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <x-ui.field label="Name">
                                    <x-ui.input name="name" value="{{ old('name', $selectedEvent->name) }}"/>
                                </x-ui.field>

                                @php $g = old('gender', $selectedEvent->gender); @endphp
                                <x-ui.field label="Gender">
                                    <x-ui.select name="gender">
                                        <option value="" {{ $g === null || $g === '' ? 'selected' : '' }}>—</option>
                                        <option value="M" {{ $g === 'M' ? 'selected' : '' }}>M</option>
                                        <option value="F" {{ $g === 'F' ? 'selected' : '' }}>F</option>
                                        <option value="X" {{ $g === 'X' ? 'selected' : '' }}>X</option>
                                    </x-ui.select>
                                </x-ui.field>

                                <x-ui.field label="Distance (m)">
                                    <x-ui.input name="distance"
                                                value="{{ old('distance', $selectedEvent->distance) }}"/>
                                </x-ui.field>

                                <x-ui.field label="Stroke">
                                    <x-ui.input name="stroke" value="{{ old('stroke', $selectedEvent->stroke) }}"/>
                                </x-ui.field>

                                <x-ui.field label="Round">
                                    <x-ui.input name="round" value="{{ old('round', $selectedEvent->round) }}"/>
                                </x-ui.field>

                                <div class="flex items-center md:mt-6">
                                    <x-ui.checkbox
                                        name="is_relay"
                                        value="1"
                                        :checked="(int) $selectedEvent->is_relay"
                                    >
                                        Relay
                                    </x-ui.checkbox>
                                </div>

                            </div>

                            <div class="flex items-center justify-end">
                                <x-ui.button type="submit" variant="primary">Save event</x-ui.button>
                            </div>
                        </form>

                        {{-- Assigned age groups + edit link --}}
                        <div class="mt-8 border-t border-slate-200 pt-4">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div>
                                    <div class="font-semibold text-slate-900">Assigned age groups</div>
                                    <div class="text-xs text-slate-600">
                                        Currently assigned to this event
                                    </div>
                                </div>

                                <a href="{{ route('imports.lenex.meet_structure.events.age_groups.edit', [$batch, $selectedEvent]) }}">
                                    <x-ui.button variant="secondary">Edit age groups</x-ui.button>
                                </a>
                            </div>

                            @if ($assigned->isEmpty())
                                <div class="text-sm text-slate-500">No age groups assigned.</div>
                            @else
                                <div class="space-y-2">
                                    @foreach ($assigned as $ag)
                                        <div class="rounded-lg border border-slate-200 p-3">
                                            <div class="min-w-0">
                                                <div class="font-semibold text-slate-900 truncate">
                                                    {{ $ag->name ?? '—' }}
                                                </div>

                                                <div
                                                    class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                                                    @if($ag->gender)
                                                        <x-ui.badge>{{ $ag->gender }}</x-ui.badge>
                                                    @endif

                                                    @php
                                                        $classes = ParaSwim::formatSportClasses($ag->handicap);
                                                        $ageLabel = ParaSwim::ageLabel($ag->min_age, $ag->max_age)
                                                    @endphp
                                                    @if($classes !== '')
                                                        <x-ui.badge>{{ $classes }}</x-ui.badge>
                                                    @endif

                                                    @if($ageLabel !== '')
                                                        <x-ui.badge>{{ $ageLabel }}</x-ui.badge>
                                                    @endif
                                                    <span class="text-slate-400">Code: {{ $ag->code }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </x-ui.card-body>
            </x-ui.card>

        </div>
    </div>
@endsection
