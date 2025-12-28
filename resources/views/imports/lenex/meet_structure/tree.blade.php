@php
    use App\Support\ParaSwim;
@endphp

@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto p-6 space-y-4">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold">Meet Structure</h1>
                <div class="text-sm text-slate-600">Batch #{{ $batch->id }} · {{ $meet->name ?? '—' }}</div>
            </div>

            <a href="{{ route('imports.lenex.meet_structure.show', $batch) }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                Back to overview
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 p-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT: Tree --}}
            <div class="rounded-lg bg-white ring-1 ring-slate-200 p-4">
                <div class="font-semibold mb-3">Sessions</div>

                <div class="space-y-4">
                    @foreach($sessions as $s)
                        <div>
                            <div class="font-medium">
                                Session {{ $s->session_no ?? '—' }} — {{ $s->name ?? '—' }}
                            </div>

                            <div class="mt-2 space-y-1 pl-3 border-l border-slate-200">
                                @foreach($s->meetEvents as $e)
                                    @php($active = $selectedEvent && $selectedEvent->id === $e->id)
                                    <a href="{{ route('imports.lenex.meet_structure.events.edit', [$batch, $e]) }}"
                                       class="block rounded-md px-2 py-1 text-sm {{ $active ? 'bg-slate-900 text-white' : 'hover:bg-slate-50 text-slate-700' }}">
                                        Event {{ $e->event_no ?? '—' }} — {{ $e->name ?? '—' }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT: Details --}}
            <div class="lg:col-span-2 rounded-lg bg-white ring-1 ring-slate-200 p-4">
                @if(!$selectedEvent)
                    <div class="text-sm text-slate-600">
                        Select an event on the left to edit details (Splash-like).
                    </div>
                @else
                    <div class="flex items-center justify-between">


                        <div class="font-semibold">
                            Event {{ $selectedEvent->event_no ?? '—' }} — {{ $selectedEvent->name ?? '—' }}
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ring-inset ring-slate-200 bg-white">
                                {{ ParaSwim::strokePrefix($selectedEvent->stroke) }}
                            </span>
                        </div>
                        <div class="text-sm text-slate-600">
                            Session {{ $selectedEvent->meetSession->session_no ?? '—' }}
                        </div>
                    </div>

                    <form method="POST"
                          action="{{ route('imports.lenex.meet_structure.events.update', [$batch, $selectedEvent]) }}"
                          class="mt-4 space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="block">
                                <div class="text-sm font-medium text-slate-700">Event no</div>
                                <input name="event_no"
                                       value="{{ old('event_no', $selectedEvent->event_no) }}"
                                       class="mt-1 w-full rounded-lg border-slate-300"/>
                            </label>

                            <label class="block md:col-span-2">
                                <div class="text-sm font-medium text-slate-700">Name</div>
                                <input name="name" value="{{ old('name', $selectedEvent->name) }}"
                                       class="mt-1 w-full rounded-lg border-slate-300"/>
                            </label>

                            <label class="block">
                                <div class="text-sm font-medium text-slate-700">Gender</div>
                                @php($g = old('gender', $selectedEvent->gender))
                                <select name="gender" class="mt-1 w-full rounded-lg border-slate-300">
                                    <option value="">—</option>
                                    <option value="F" @selected($g==='F')>F</option>
                                    <option value="M" @selected($g==='M')>M</option>
                                    <option value="X" @selected($g==='X')>X</option>
                                </select>
                            </label>

                            <label class="block">
                                <div class="text-sm font-medium text-slate-700">Distance</div>
                                <input name="distance" value="{{ old('distance', $selectedEvent->distance) }}"
                                       class="mt-1 w-full rounded-lg border-slate-300"/>
                            </label>

                            <label class="block">
                                <div class="text-sm font-medium text-slate-700">Stroke</div>
                                <input name="stroke" value="{{ old('stroke', $selectedEvent->stroke) }}"
                                       class="mt-1 w-full rounded-lg border-slate-300"/>
                            </label>

                            <label class="block">
                                <div class="text-sm font-medium text-slate-700">Round</div>
                                <input name="round" value="{{ old('round', $selectedEvent->round) }}"
                                       class="mt-1 w-full rounded-lg border-slate-300"/>
                            </label>

                            <label class="block">
                                <div class="text-sm font-medium text-slate-700">Relay</div>
                                <div class="mt-2">
                                    <input type="checkbox" name="is_relay"
                                           value="1" @checked(old('is_relay', $selectedEvent->is_relay)) />
                                    <span class="text-sm text-slate-600 ml-2">Is relay</span>
                                </div>
                            </label>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-medium">Age groups</div>

                                <a href="{{ route('imports.lenex.meet_structure.events.age_groups.edit', [$batch, $selectedEvent]) }}"
                                   class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                                    Edit age groups
                                </a>
                            </div>

                            @php($ags = $selectedEvent->meetAgeGroups)

                            @if($ags->isEmpty())
                                <div class="text-sm text-slate-600">No age groups assigned.</div>
                            @else
                                <ul class="text-sm space-y-1">
                                    @foreach($ags as $ag)
                                        <li class="rounded-lg ring-1 ring-slate-200 px-3 py-2">
                                            {{ $ag->name ?? '—' }}
                                            @if($ag->gender)
                                                ({{ $ag->gender }})
                                            @endif
                                            @php
                                                $classes = ParaSwim::formatSportClasses($ag->handicap);
                                            @endphp
                                            @if($classes)
                                                <span class="text-xs text-slate-600">
                                                    Classes: {{ $classes }}
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-slate-900 text-white hover:bg-slate-800">
                                Save event
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
