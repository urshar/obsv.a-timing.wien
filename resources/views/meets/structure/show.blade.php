@extends('layouts.app')

@php
    use App\Models\MeetAgeGroup;
    use App\Models\MeetSession;
    use App\Support\ParaSwim;
    use Illuminate\Database\Eloquent\Collection;

    /** @var Collection|MeetSession[] $sessions */
    /** @var Collection|MeetAgeGroup[] $ageGroups */
@endphp

@section('title', 'Meet Structure')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Meet Structure</h1>
                <div class="text-sm text-slate-600">Meet #{{ $meet->id }}</div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('meets.structure.tree', $meet) }}">
                    <x-ui.button variant="primary">Edit structure</x-ui.button>
                </a>

                <a href="{{ route('meets.show', $meet) }}">
                    <x-ui.button variant="secondary">Back</x-ui.button>
                </a>
            </div>
        </div>

        {{-- Meet summary --}}
        <x-ui.card>
            <x-ui.card-body>
                <div class="font-semibold text-slate-900">
                    {{ $meet->name ?? '—' }}
                </div>

                <div class="text-sm text-slate-600">
                    <x-ui.date-range :start="$meet->start_date" :end="$meet->end_date"/>
                </div>
            </x-ui.card-body>
        </x-ui.card>

        {{-- Main grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Sessions & Events (LEFT / wide) --}}
            <x-ui.card class="lg:col-span-2">
                <x-ui.card-header>
                    <div class="flex items-center justify-between gap-3">
                        <div class="font-semibold text-slate-900">Sessions & events</div>

                        <a href="{{ route('meets.structure.sessions.create', $meet) }}">
                            <x-ui.button variant="secondary">Add session</x-ui.button>
                        </a>
                    </div>
                </x-ui.card-header>

                <x-ui.card-body>
                    @if($sessions->isEmpty())
                        <div class="text-sm text-slate-600">No sessions.</div>
                    @else
                        <div class="space-y-6">
                            @foreach($sessions as $s)
                                @php($sessionEvents = $eventsBySession[$s->id] ?? collect())

                                <x-ui.card>
                                    <x-ui.card-body class="space-y-3">

                                        {{-- Session header --}}
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="font-semibold text-slate-900">
                                                    Session {{ $s->session_no ?? '—' }}
                                                    @if($s->name)
                                                        — {{ $s->name }}
                                                    @endif
                                                </div>

                                                <div class="text-sm text-slate-600">
                                                    <x-ui.date :value="$s->date"/>
                                                    @if($s->start_time)
                                                        · {{ $s->start_time }}
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="shrink-0 inline-flex gap-2">
                                                <a href="{{ route('meets.structure.events.create', [$meet, $s]) }}">
                                                    <x-ui.button variant="secondary">Add event</x-ui.button>
                                                </a>

                                                <a href="{{ route('meets.structure.sessions.edit', [$meet, $s]) }}">
                                                    <x-ui.button variant="ghost">Edit</x-ui.button>
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('meets.structure.sessions.destroy', [$meet, $s]) }}"
                                                      onsubmit="return confirm('Delete this session?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-ui.button variant="danger" type="submit">Delete</x-ui.button>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- Events --}}
                                        @if($sessionEvents->isEmpty())
                                            <div class="text-sm text-slate-600">No events.</div>
                                        @else
                                            <div class="space-y-3">
                                                @foreach($sessionEvents as $e)
                                                    @php(
                                                        $ag = $e->meet_age_group_id
                                                            ? ($ageGroupsById[$e->meet_age_group_id] ?? null)
                                                            : null
                                                    )

                                                    <div class="rounded-lg ring-1 ring-slate-200 p-3">
                                                        <div class="flex items-start justify-between gap-3">
                                                            <div class="min-w-0">
                                                                <div class="font-medium text-slate-900">
                                                                    Event {{ $e->event_no ?? '—' }}
                                                                    @if($e->name)
                                                                        — {{ $e->name }}
                                                                    @endif
                                                                </div>

                                                                <div class="text-sm text-slate-600">
                                                                    {{ $e->gender ?? '—' }}
                                                                    @if($e->distance)
                                                                        · {{ $e->distance }}m
                                                                    @endif
                                                                    @if($e->stroke)
                                                                        · {{ $e->stroke }}
                                                                    @endif
                                                                    @if($e->round)
                                                                        · {{ $e->round }}
                                                                    @endif
                                                                    @if($e->is_relay)
                                                                        · Relay
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="shrink-0 inline-flex items-center gap-2">
                                                                <a href="{{ route('meets.structure.events.edit', [$meet, $e]) }}">
                                                                    <x-ui.button variant="secondary">Edit</x-ui.button>
                                                                </a>

                                                                <a href="{{ route('meets.structure.events.age_groups.edit', [$meet, $e]) }}">
                                                                    <x-ui.button variant="ghost">Age groups
                                                                    </x-ui.button>
                                                                </a>

                                                                <form method="POST"
                                                                      action="{{ route('meets.structure.events.destroy', [$meet, $e]) }}"
                                                                      onsubmit="return confirm('Delete this event?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <x-ui.button variant="danger" type="submit">Delete
                                                                    </x-ui.button>
                                                                </form>
                                                            </div>
                                                        </div>

                                                        <div class="mt-2 text-sm">
                                                            <div class="text-slate-500">Age group</div>

                                                            @if($ag)
                                                                <div class="text-slate-900">
                                                                    {{ $ag->name ?? '—' }}
                                                                    @if($ag->gender)
                                                                        ({{ $ag->gender }})
                                                                    @endif
                                                                    @if($ag->code)
                                                                        · {{ $ag->code }}
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="text-slate-600">—</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </x-ui.card-body>
                                </x-ui.card>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card-body>
            </x-ui.card>

            {{-- Age Groups (RIGHT / narrow) --}}
            <x-ui.card>
                <x-ui.card-header>
                    <div class="font-semibold text-slate-900">Age groups</div>
                </x-ui.card-header>

                <x-ui.card-body>
                    @if($ageGroups->isEmpty())
                        <div class="text-sm text-slate-600">No age groups.</div>
                    @else
                        <ul class="text-sm space-y-2">
                            @foreach($ageGroups as $ag)
                                <li>
                                    <div class="font-medium text-slate-900">
                                        {{ $ag->name ?? '—' }}
                                    </div>

                                    <div class="text-xs text-slate-600">
                                        {{ $ag->gender ?? '' }}
                                        @if($ag->code)
                                            · {{ $ag->code }}
                                        @endif

                                        @php($label = ParaSwim::ageLabel($ag->min_age, $ag->max_age))

                                        @if($label !== '')
                                            <div class="text-xs text-slate-500">
                                                {{ $label }}
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card-body>
            </x-ui.card>

        </div>
    </div>
@endsection
