@extends('layouts.app')

@php
    use App\Models\MeetAgeGroup;use App\Models\MeetSession;use Illuminate\Support\Collection;

    /** @var \Illuminate\Database\Eloquent\Collection|MeetSession[] $sessions */
    /** @var \Illuminate\Database\Eloquent\Collection|MeetAgeGroup[] $ageGroups */
@endphp

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Meet Structure</h1>
                <div class="text-sm text-slate-600">Batch #{{ $batch->id }}</div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('imports.lenex.meet_structure.tree', $batch) }}">
                    <x-ui.button variant="primary">Edit structure</x-ui.button>
                </a>

                <a href="{{ route('imports.lenex.history.show', $batch) }}">
                    <x-ui.button variant="secondary">History</x-ui.button>
                </a>
            </div>
        </div>

        <x-ui.card>
            <x-ui.card-body>
                <div class="font-semibold text-slate-900">
                    {{ $meet->name ?? '—' }}
                </div>

                @php
                    $from = $meet->start_date ?? null;
                    $to = $meet->end_date ?? null;
                @endphp

                <div class="text-sm text-slate-600">
                    {{ $from ?? '—' }}@if($to)
                        – {{ $to }}
                    @endif
                </div>
            </x-ui.card-body>
        </x-ui.card>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
                                <li class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-900 truncate">{{ $ag->name ?? '—' }}</div>
                                        <div class="text-xs text-slate-600">
                                            {{ $ag->gender ?? '' }}
                                            @if($ag->code)
                                                · {{ $ag->code }}
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card-body>
            </x-ui.card>

            <x-ui.card class="lg:col-span-2">
                <x-ui.card-header>
                    <div class="font-semibold text-slate-900">Sessions & events</div>
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
                                        <div class="font-semibold text-slate-900">
                                            Session {{ $s->session_no ?? '—' }} — {{ $s->name ?? '—' }}
                                        </div>

                                        @if($sessionEvents->isEmpty())
                                            <div class="text-sm text-slate-600">No events.</div>
                                        @else
                                            <div class="space-y-3">
                                                @foreach($sessionEvents as $e)
                                                    @php($ag = $e->meet_age_group_id ? ($ageGroupsById[$e->meet_age_group_id] ?? null) : null)

                                                    <div class="rounded-lg ring-1 ring-slate-200 p-3">
                                                        <div class="font-medium text-slate-900">
                                                            Event {{ $e->event_no ?? '—' }} — {{ $e->name ?? '—' }}
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

                                                        <div class="mt-2 text-sm">
                                                            <div class="text-slate-500">Agegroup</div>
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
        </div>
    </div>
@endsection
