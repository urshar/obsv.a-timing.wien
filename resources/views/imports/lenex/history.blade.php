@extends('layouts.app')

@php
    // erwartet vom Controller:
    // $batches, $types, $q, $type, $perPage

    $statusLevel = function (?string $status): string {
        return match ($status) {
            'committed' => 'success',
            'preview'   => 'warning',
            'failed'    => 'danger',
            default     => 'neutral',
        };
    };
@endphp

@section('content')
    <div class="max-w-6xl mx-auto p-6 space-y-6">

        <x-layout.validation-errors/>
        <x-layout.import-alert/>

        @if (session('status'))
            <x-layout.import-alert :message="session('status')" level="success"/>
        @endif

        <x-ui.page-title
            title="LENEX History"
            subtitle="Committed LENEX imports (audit/history)."
        />

        {{-- Actions --}}
        <x-ui.button href="{{ route('imports.lenex.create') }}">
            New Import
        </x-ui.button>

        {{-- Filters --}}
        <x-ui.card>
            <x-ui.card-header>
                <div class="text-lg font-semibold">Filters</div>
            </x-ui.card-header>

            <x-ui.card-body>
                <form method="GET" action="{{ route('imports.lenex.history') }}"
                      class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-6">
                        <label class="block text-sm font-medium text-slate-700">Search</label>
                        <input
                            name="q"
                            value="{{ $q }}"
                            placeholder="Filename, Batch ID, Meet ID …"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        />
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-slate-700">Type</label>
                        <select
                            name="type"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        >
                            <option value="">All</option>
                            @foreach(($types ?? []) as $t)
                                <option value="{{ $t }}" @selected(($type ?? '') === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-slate-700">Per page</label>
                        <select
                            name="per_page"
                            class="mt-1 w-full rounded-xl border-slate-200 focus:border-slate-400 focus:ring-slate-400"
                        >
                            @foreach([10,20,50,100] as $n)
                                <option value="{{ $n }}" @selected((int) $perPage === $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-12 flex items-center gap-2 pt-1">
                        <x-ui.button
                            href="{{ route('imports.lenex.history.show', $batch) }}"
                            size="sm"
                        >
                            Details
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card-body>
        </x-ui.card>

        {{-- List --}}
        <x-ui.card>
            <x-ui.card-header>
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm text-slate-600">
                        Showing
                        <span class="font-medium text-slate-900">{{ $batches->firstItem() ?? 0 }}</span>
                        –
                        <span class="font-medium text-slate-900">{{ $batches->lastItem() ?? 0 }}</span>
                        of
                        <span class="font-medium text-slate-900">{{ $batches->total() }}</span>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-body>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left font-medium px-4 py-3">Batch</th>
                            <th class="text-left font-medium px-4 py-3">Status</th>
                            <th class="text-left font-medium px-4 py-3">Type</th>
                            <th class="text-left font-medium px-4 py-3">Filename</th>
                            <th class="text-left font-medium px-4 py-3">Meet</th>
                            <th class="text-left font-medium px-4 py-3">Created</th>
                            <th class="text-right font-medium px-4 py-3">Actions</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                        @forelse($batches as $batch)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">#{{ $batch->id }}</td>

                                <td class="px-4 py-3">
                                    <x-ui.badge :level="$statusLevel($batch->status)">
                                        {{ $batch->status }}
                                    </x-ui.badge>
                                </td>

                                <td class="px-4 py-3 text-slate-700">
                                    <x-ui.badge level="neutral">{{ $batch->type ?? '—' }}</x-ui.badge>
                                </td>

                                <td class="px-4 py-3 text-slate-700">{{ $batch->filename }}</td>

                                <td class="px-4 py-3 text-slate-700">
                                    @if($batch->meet_id)
                                        <span class="font-medium">#{{ $batch->meet_id }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    {{ optional($batch->created_at)->format('Y-m-d H:i') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('imports.lenex.history.show', $batch) }}">
                                        <x-ui.button type="button">Details</x-ui.button>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                    No committed imports found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4">
                    {{ $batches->links() }}
                </div>
            </x-ui.card-body>
        </x-ui.card>

    </div>
@endsection
