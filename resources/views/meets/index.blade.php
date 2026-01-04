@extends('layouts.app')
@section('title','Meetings')

@section('content')
    <x-ui.page-title title="Meetings" subtitle="Manage imported and manually created meets."/>

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">

        {{-- FILTER FORM --}}
        <form method="GET" action="{{ route('meets.index') }}"
              class="flex flex-col gap-3 sm:flex-row sm:items-end">

            {{-- SEARCH --}}
            <x-ui.field label="Search" name="q" compact>
                <x-ui.input
                    name="q"
                    :value="$q"
                    placeholder="Meet name or source file..."
                    class="w-72"
                />
            </x-ui.field>

            {{-- ACTIONS --}}
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="secondary">
                    Filter
                </x-ui.button>

                @if(!empty($q))
                    <a href="{{ route('meets.index') }}">
                        <x-ui.button type="button" variant="ghost">
                            Reset
                        </x-ui.button>
                    </a>
                @endif
            </div>
        </form>

        {{-- RIGHT ACTIONS --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('meets.create') }}">
                <x-ui.button>New Meeting</x-ui.button>
            </a>

            <a href="{{ route('imports.lenex.create') }}">
                <x-ui.button variant="secondary">LENEX Import</x-ui.button>
            </a>
        </div>
    </div>

    <x-ui.card>
        <x-ui.card-header title="List" subtitle="Filtered meets"/>

        {{-- TABLE --}}
        <x-ui.table>
            <x-slot:head>
                <tr>
                    <x-ui.th>Name</x-ui.th>
                    <x-ui.th>Date</x-ui.th>
                    <x-ui.th>Course</x-ui.th>
                    <x-ui.th>Source</x-ui.th>
                    <x-ui.th>Structure</x-ui.th>
                    <x-ui.th align="right">Actions</x-ui.th>
                </tr>
            </x-slot:head>

            @forelse($meets as $meet)
                @php
                    $hasStructure = (($meet->sessions_count ?? 0) > 0)
                        || (($meet->events_count ?? 0) > 0)
                        || (($meet->age_groups_count ?? 0) > 0);
                @endphp

                <tr class="hover:bg-slate-50">
                    <x-ui.td>
                        <div class="font-medium text-slate-900">
                            <a href="{{ route('meets.show', $meet) }}"
                               class="font-medium text-slate-900 hover:underline">
                                {{ $meet->name ?? '—' }}
                            </a>
                        </div>
                        <div class="text-xs text-slate-500">
                            ID: {{ $meet->id }}
                        </div>
                    </x-ui.td>

                    <x-ui.td>
                        @php
                            $start = $meet->start_date;
                            $end = $meet->end_date;

                            $dateLabel = '—';

                            if ($start && $end) {
                                // Defensive: begin must not be after end
                                if ($start->lte($end)) {
                                    if ($start->isSameDay($end)) {
                                        $dateLabel = $start->format('d.m.Y');
                                    } else {
                                        $dateLabel = $start->format('d.m.') . ' - ' . $end->format('d.m.Y');
                                    }
                                } else {
                                    // Should not happen; show start only to surface the issue
                                    $dateLabel = $start->format('d.m.Y');
                                }
                            } elseif ($start) {
                                $dateLabel = $start->format('d.m.Y');
                            } elseif ($end) {
                                $dateLabel = $end->format('d.m.Y');
                            }
                        @endphp

                        <div class="text-sm text-slate-600">
                            {{ $dateLabel }}
                        </div>
                        
                        @if($meet->age_date)
                            <div class="text-xs text-slate-500">
                                Age Date: {{ $meet->age_date->format('d.m.Y') }}
                            </div>
                        @endif
                    </x-ui.td>

                    <x-ui.td>
                        @if($meet->course)
                            <x-ui.badge>{{ $meet->course }}</x-ui.badge>
                        @else
                            <span class="text-slate-500">—</span>
                        @endif
                    </x-ui.td>

                    <x-ui.td>
                        <div class="text-slate-900">
                            {{ $meet->source_filename ?? 'manual' }}
                        </div>
                        @if($meet->source_hash)
                            <div class="text-xs text-slate-500 font-mono truncate max-w-65">
                                {{ $meet->source_hash }}
                            </div>
                        @endif
                    </x-ui.td>

                    <x-ui.td>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge>Sessions: {{ $meet->sessions_count ?? 0 }}</x-ui.badge>
                            <x-ui.badge>Events: {{ $meet->events_count ?? 0 }}</x-ui.badge>
                            <x-ui.badge>AgeGroups: {{ $meet->age_groups_count ?? 0 }}</x-ui.badge>
                        </div>
                    </x-ui.td>

                    <x-ui.td align="right">
                        <div class="inline-flex gap-2">
                            <a href="{{ route('meets.edit', $meet) }}">
                                <x-ui.button variant="secondary">Edit</x-ui.button>
                            </a>

                            @if($hasStructure)
                                <a href="{{ route('meets.structure.show', $meet) }}">
                                    <x-ui.button variant="ghost">Structure</x-ui.button>
                                </a>
                            @else
                                <x-ui.button variant="ghost" disabled
                                             title="No structure available yet. Import LENEX meet structure or add sessions/events.">
                                    Structure
                                </x-ui.button>
                            @endif

                            @php
                                $rowCanDelete = (($meet->sessions_count ?? 0) === 0 && ($meet->events_count ?? 0) === 0 && ($meet->age_groups_count ?? 0) === 0);
                            @endphp

                            @if($rowCanDelete)
                                <form method="POST"
                                      action="{{ route('meets.destroy', $meet) }}"
                                      onsubmit="return confirm('Delete this meeting?')">
                                    @csrf @method('DELETE')
                                    <x-ui.button variant="danger" type="submit">Delete</x-ui.button>
                                </form>
                            @else
                                <x-ui.button variant="danger" disabled
                                             title="Delete is blocked because this meeting contains structure data.">
                                    Delete
                                </x-ui.button>
                            @endif
                        </div>
                    </x-ui.td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-sm text-slate-500">
                        No meets found.
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        <x-ui.card-footer class="flex justify-end">
            {{ $meets->links() }}
        </x-ui.card-footer>
    </x-ui.card>
@endsection
