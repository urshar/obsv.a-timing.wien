@extends('layouts.app')

@php
    use App\Models\MeetSession;
@endphp

@section('title', 'Assign age group')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Assign age group</h1>
                <div class="text-sm text-slate-600">
                    {{ $ageGroup->name ?? '—' }}
                    @if(!empty($ageGroup->code))
                        · Code: {{ $ageGroup->code }}
                    @endif
                </div>
            </div>

            <a href="{{ route('meets.structure.show', $meet) }}">
                <x-ui.button variant="secondary">Back</x-ui.button>
            </a>
        </div>

        <x-ui.card>
            <x-ui.card-header>
                <div class="font-semibold text-slate-900">Select events</div>
                <div class="text-xs text-slate-600">Tick events that should use this age group.</div>
            </x-ui.card-header>

            <x-ui.card-body>
                <form method="POST" action="{{ route('meets.structure.age_groups.assign.update', [$meet, $ageGroup]) }}"
                      class="space-y-6">
                    @csrf
                    @method('PUT')

                    @if($sessions->isEmpty())
                        <div class="text-sm text-slate-600">No sessions.</div>
                    @else
                        <div class="space-y-6">
                            @foreach($sessions as $s)
                                @php($sessionEvents = $eventsBySession[$s->id] ?? collect())

                                <x-ui.card>
                                    <x-ui.card-body class="space-y-3">
                                        <div class="font-semibold text-slate-900">
                                            Session {{ $s->session_no ?? '—' }}
                                            @if(!empty($s->name))
                                                — {{ $s->name }}
                                            @endif
                                        </div>

                                        @if($sessionEvents->isEmpty())
                                            <div class="text-sm text-slate-600">No events.</div>
                                        @else
                                            <div class="space-y-2">
                                                @foreach($sessionEvents as $e)
                                                    @php($checked = in_array((int)$e->id, $selectedEventIds ?? [], true))

                                                    <label
                                                        class="flex items-start justify-between gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50">
                                                        <div class="min-w-0">
                                                            <div class="font-medium text-slate-900">
                                                                Event {{ $e->event_no ?? '—' }}
                                                                @if(!empty($e->name))
                                                                    — {{ $e->name }}
                                                                @endif
                                                            </div>

                                                            <div class="mt-1 text-sm text-slate-600">
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

                                                        <div class="shrink-0 pt-1">
                                                            <input
                                                                type="checkbox"
                                                                name="event_ids[]"
                                                                value="{{ $e->id }}"
                                                                @checked($checked)
                                                                class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                                            />
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </x-ui.card-body>
                                </x-ui.card>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('meets.structure.show', $meet) }}">
                            <x-ui.button variant="secondary" type="button">Cancel</x-ui.button>
                        </a>
                        <x-ui.button variant="primary" type="submit">Save</x-ui.button>
                    </div>
                </form>
            </x-ui.card-body>
        </x-ui.card>
    </div>
@endsection
