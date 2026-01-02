@extends('layouts.app')
@section('title', 'Meeting')

@php
    $from = $meet->start_date?->format('d.m.Y');
    $to = $meet->end_date?->format('d.m.Y');
    $ageDate = $meet->age_date?->format('d.m.Y');
@endphp

@section('content')
    <x-ui.page-title
        title="{{ $meet->name ?? 'Meeting' }}"
        subtitle="Overview and quick access to editing, structure, and imports."
    />

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('meets.index') }}">
                <x-ui.button variant="secondary">Back</x-ui.button>
            </a>

            <a href="{{ route('meets.edit', $meet) }}">
                <x-ui.button variant="secondary">Edit</x-ui.button>
            </a>

            <a href="{{ route('meets.structure.show', $meet) }}">
                <x-ui.button variant="ghost">Structure</x-ui.button>
            </a>
        </div>

        @php
            $canDelete = !(($meet->sessions_count ?? null) !== null) || ($meet->sessions_count ?? 0) === 0 && ($meet->events_count ?? 0) === 0 && ($meet->age_groups_count ?? 0) === 0;
        @endphp

        <div class="flex items-center gap-2">
            @if($canDelete)
                <form method="POST"
                      action="{{ route('meets.destroy', $meet) }}"
                      onsubmit="return confirm('Delete this meeting?')">
                    @csrf
                    @method('DELETE')
                    <x-ui.button variant="danger" type="submit">Delete</x-ui.button>
                </form>
            @else
                <x-ui.button variant="danger" disabled
                             title="Delete is blocked because this meeting contains structure data.">
                    Delete
                </x-ui.button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Base data --}}
        <x-ui.card class="lg:col-span-2">
            <x-ui.card-header title="Base data" subtitle="Meeting metadata"/>
            <x-ui.card-body class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-slate-500">Date</div>
                        <div class="text-slate-900">
                            {{ $from ?? '—' }}@if($to)
                                – {{ $to }}
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-slate-500">Age date</div>
                        <div class="text-slate-900">{{ $ageDate ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-slate-500">Course</div>
                        <div class="text-slate-900">{{ $meet->course ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-slate-500">Facility</div>
                        <div class="text-slate-900">{{ $meet->facility_id ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-slate-500">Contact</div>
                        <div class="text-slate-900">
                            {{ $meet->contact_name ?? '—' }}
                            @if($meet->contact_email)
                                <div class="text-xs text-slate-500">{{ $meet->contact_email }}</div>
                            @endif
                            @if($meet->contact_phone)
                                <div class="text-xs text-slate-500">{{ $meet->contact_phone }}</div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-slate-500">Source</div>
                        <div class="text-slate-900">
                            {{ $meet->source_filename ?? 'manual' }}
                            @if($meet->source_hash)
                                <div class="text-xs text-slate-500 font-mono truncate">
                                    {{ $meet->source_hash }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui.card-body>
        </x-ui.card>

        {{-- Structure counts --}}
        <x-ui.card>
            <x-ui.card-header title="Structure" subtitle="Current counts"/>
            <x-ui.card-body class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Sessions</span>
                    <x-ui.badge>{{ $meet->sessions_count ?? 0 }}</x-ui.badge>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Events</span>
                    <x-ui.badge>{{ $meet->events_count ?? 0 }}</x-ui.badge>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Age groups</span>
                    <x-ui.badge>{{ $meet->age_groups_count ?? 0 }}</x-ui.badge>
                </div>

                <div class="pt-3">
                    <a href="{{ route('meets.structure.tree', $meet) }}">
                        <x-ui.button class="w-full" variant="primary">Open structure editor</x-ui.button>
                    </a>
                </div>
            </x-ui.card-body>
        </x-ui.card>
    </div>

    {{-- Import history (optional) --}}
    <x-ui.card class="mt-6">
        <x-ui.card-header title="Imports" subtitle="LENEX batches linked to this meeting"/>
        <x-ui.card-body>
            @if($batches->isEmpty())
                <div class="text-sm text-slate-600">No import batches linked to this meeting.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                ID
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Type
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Filename
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                        @foreach($batches as $b)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm">{{ $b->id }}</td>
                                <td class="px-4 py-3 text-sm">{{ $b->type ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.badge>{{ $b->status ?? '—' }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $b->filename ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <a href="{{ route('imports.lenex.history.show', $b) }}">
                                        <x-ui.button variant="secondary">Open</x-ui.button>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card-body>
    </x-ui.card>
@endsection
