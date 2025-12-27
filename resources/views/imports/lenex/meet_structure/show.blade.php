@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto p-6 space-y-6">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold">Meet Structure</h1>
                <div class="text-sm text-slate-600">Batch #{{ $batch->id }}</div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('imports.lenex.meet_structure.edit', $batch) }}"
                   class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-slate-900 text-white hover:bg-slate-800">
                    Edit structure
                </a>

                <a href="{{ route('imports.lenex.history.show', $batch) }}"
                   class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                    History
                </a>
            </div>
        </div>

        <div class="rounded-lg bg-white ring-1 ring-slate-200 p-4">
            <div class="font-semibold">
                {{ $meet->name ?? '—' }}
            </div>
            <div class="text-sm text-slate-600">
                @php
                    $from = $meet->start_date ?? null;
                    $to = $meet->end_date ?? null;
                @endphp
                {{ $from ?? '—' }}@if($to)
                    – {{ $to }}
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-lg bg-white ring-1 ring-slate-200 p-4">
                <div class="font-semibold mb-2">Age groups</div>
                @if($ageGroups->isEmpty())
                    <div class="text-sm text-slate-600">No age groups.</div>
                @else
                    <ul class="text-sm space-y-1">
                        @foreach($ageGroups as $ag)
                            <li class="flex justify-between gap-2">
                                <span>{{ $ag->name ?? '—' }}</span>
                                <span class="text-slate-500">{{ $ag->gender ?? '' }} {{ $ag->code ?? '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="lg:col-span-2 rounded-lg bg-white ring-1 ring-slate-200 p-4">
                <div class="font-semibold mb-2">Sessions & events</div>

                @if($sessions->isEmpty())
                    <div class="text-sm text-slate-600">No sessions.</div>
                @else
                    <div class="space-y-4">
                        @foreach($sessions as $s)
                            @php($sessionEvents = $eventsBySession[$s->id] ?? collect())
                            <div class="rounded-lg ring-1 ring-slate-200 p-3">
                                <div class="flex items-center justify-between">
                                    <div class="font-medium">
                                        Session {{ $s->session_no ?? '—' }} — {{ $s->name ?? '—' }}
                                    </div>
                                    <div class="text-sm text-slate-600">
                                        {{ $s->date ?? '—' }} {{ $s->start_time ? substr($s->start_time,0,5) : '' }}
                                    </div>
                                </div>

                                <div class="mt-2">
                                    @if($sessionEvents->isEmpty())
                                        <div class="text-sm text-slate-600">No events.</div>
                                    @else
                                        <ul class="text-sm space-y-1">
                                            @foreach($sessionEvents as $e)
                                                <li class="flex justify-between gap-3">
                                                <span>
                                                    {{ $e->event_no ?? '—' }} — {{ $e->name ?? '—' }}
                                                </span>
                                                    <span class="text-slate-500">
                                                    {{ $e->gender ?? '' }}
                                                        @if($e->distance)
                                                            {{ $e->distance }}m
                                                        @endif
                                                        {{ $e->stroke ?? '' }}
                                                        @if($e->is_relay)
                                                            · Relay
                                                        @endif
                                                </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
