@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto p-6 space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold">Edit age groups</h1>
                <div class="text-sm text-slate-600">
                    Batch #{{ $batch->id }}
                    · Event {{ $event->event_no ?? '—' }} — {{ $event->name ?? '—' }}
                    · Session {{ $event->meetSession->session_no ?? '—' }}
                </div>
            </div>

            <a href="{{ route('imports.lenex.meet_structure.events.edit', [$batch, $event]) }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                Back to event
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 p-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        {{-- Filter --}}
        <form method="GET" action="{{ route('imports.lenex.meet_structure.events.age_groups.edit', [$batch, $event]) }}"
              class="rounded-lg bg-white ring-1 ring-slate-200 p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label class="block md:col-span-2">
                    <div class="text-sm font-medium text-slate-700">Search</div>
                    <input name="q" value="{{ $q }}"
                           placeholder="Name, code, handicap, raw list…"
                           class="mt-1 w-full rounded-lg border-slate-300"/>
                </label>

                <label class="block">
                    <div class="text-sm font-medium text-slate-700">Gender</div>
                    <select name="gender" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="" @selected($gender==='')>All</option>
                        <option value="F" @selected($gender==='F')>F</option>
                        <option value="M" @selected($gender==='M')>M</option>
                        <option value="X" @selected($gender==='X')>X</option>
                    </select>
                </label>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <a href="{{ route('imports.lenex.meet_structure.events.age_groups.edit', [$batch, $event]) }}"
                   class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                    Reset
                </a>
                <button type="submit"
                        class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-slate-900 text-white hover:bg-slate-800">
                    Apply
                </button>
            </div>
        </form>

        {{-- Selection --}}
        <form method="POST"
              action="{{ route('imports.lenex.meet_structure.events.age_groups.update', [$batch, $event]) }}"
              class="rounded-lg bg-white ring-1 ring-slate-200 p-4 space-y-4">
            @csrf
            @method('PUT')

            <div class="text-sm text-slate-600">
                Showing {{ $ageGroups->count() }} of {{ $ageGroups->total() }} age groups
                (page {{ $ageGroups->currentPage() }}).
                Selected overall: {{ count($selectedIds) }}.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @foreach($ageGroups as $ag)
                    @php($checked = in_array((int)$ag->id, array_map('intval', $selectedIds), true))

                    <label class="flex items-start gap-3 rounded-lg ring-1 ring-slate-200 px-3 py-2">
                        <input type="checkbox" name="age_group_ids[]" value="{{ $ag->id }}" class="mt-1"
                            @checked($checked) />

                        <div class="min-w-0">
                            <div class="text-sm font-medium">
                                {{ $ag->name ?? '—' }}
                                @if($ag->gender)
                                    <span class="text-slate-500">({{ $ag->gender }})</span>
                                @endif
                            </div>

                            <div class="text-xs text-slate-600">
                                @if($ag->code)
                                    Code: {{ $ag->code }} ·
                                @endif
                                @if($ag->handicap)
                                    Class: {{ $ag->handicap }} ·
                                @endif
                                @if($ag->sport_class_raw)
                                    List: {{ $ag->sport_class_raw }}
                                @endif
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="flex items-center justify-between">
                <div>
                    {{ $ageGroups->links() }}
                </div>

                <button type="submit"
                        class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-slate-900 text-white hover:bg-slate-800">
                    Save selection
                </button>
            </div>
        </form>
    </div>
@endsection
