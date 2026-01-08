@extends('layouts.app')

@php
    use App\Support\ParaSwim;

    $q = $q ?? request('q', '');
    $gender = $gender ?? request('gender', '');

    $isPaginator = is_object($ageGroups) && method_exists($ageGroups, 'total') && method_exists($ageGroups, 'links');
    $total = $isPaginator ? (int) $ageGroups->total() : (is_countable($ageGroups) ? count($ageGroups) : 0);

    $items = $ageGroups;
@endphp

@section('title', 'Edit age groups')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Edit age groups</h1>
                <div class="text-sm text-slate-600">
                    Event #{{ $event->event_no }} · {{ $event->name ?? '—' }}
                    @if(!empty($prefix))
                        · {{ $prefix }}
                    @endif
                </div>
            </div>

            <a href="{{ route('meets.structure.events.edit', [$meet, $event]) }}">
                <x-ui.button variant="secondary">Back</x-ui.button>
            </a>
        </div>

        <x-ui.card>
            <x-ui.card-header>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="font-semibold text-slate-900">Available age groups</div>
                        <div class="text-xs text-slate-600">
                            {{ $total }} total
                        </div>
                    </div>

                    <form method="GET"
                          action="{{ route('meets.structure.events.age_groups.edit', [$meet, $event]) }}"
                          class="flex items-end gap-3"
                    >
                        <x-ui.field label="Search">
                            <x-ui.input name="q" value="{{ $q }}" placeholder="Name, code, classes…"/>
                        </x-ui.field>

                        <x-ui.field label="Gender">
                            <x-ui.select name="gender">
                                <option value="" {{ $gender === '' ? 'selected' : '' }}>All</option>
                                <option value="M" {{ $gender === 'M' ? 'selected' : '' }}>M</option>
                                <option value="F" {{ $gender === 'F' ? 'selected' : '' }}>F</option>
                                <option value="X" {{ $gender === 'X' ? 'selected' : '' }}>X</option>
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.button variant="secondary" type="submit">Filter</x-ui.button>
                    </form>
                </div>
            </x-ui.card-header>

            <x-ui.card-body>
                <form method="POST"
                      action="{{ route('meets.structure.events.age_groups.update', [$meet, $event]) }}"
                      class="space-y-4"
                >
                    @csrf
                    @method('PUT')

                    @if($items->isEmpty())
                        <div class="text-sm text-slate-600">No age groups found.</div>
                    @else
                        <div class="space-y-2">
                            @foreach($items as $ag)
                                @php
                                    $checked = in_array($ag->id, $selectedIds ?? [], true);
                                    $classes = ParaSwim::formatSportClasses($ag->handicap);
                                    $ageLabel = ParaSwim::ageLabel($ag->min_age, $ag->max_age);
                                @endphp

                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-900 truncate">
                                                {{ $ag->name ?? '—' }}
                                            </div>

                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                                                @if($ag->gender)
                                                    <x-ui.badge>{{ $ag->gender }}</x-ui.badge>
                                                @endif

                                                @if($classes !== '')
                                                    <x-ui.badge>{{ $classes }}</x-ui.badge>
                                                @endif

                                                @if($ageLabel !== '')
                                                    <x-ui.badge>{{ $ageLabel }}</x-ui.badge>
                                                @endif

                                                @if($ag->code)
                                                    <span class="text-slate-400">Code: {{ $ag->code }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="shrink-0 pt-1">
                                            <x-ui.checkbox name="age_group_ids[]" value="{{ $ag->id }}"
                                                           :checked="$checked">
                                                Assign
                                            </x-ui.checkbox>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <a href="{{ route('meets.structure.events.edit', [$meet, $event]) }}">
                            <x-ui.button variant="secondary" type="button">Cancel</x-ui.button>
                        </a>
                        <x-ui.button variant="primary" type="submit">Save age groups</x-ui.button>
                    </div>
                </form>
            </x-ui.card-body>

            @if($isPaginator)
                <x-ui.card-footer>
                    {{ $ageGroups->withQueryString()->links() }}
                </x-ui.card-footer>
            @endif
        </x-ui.card>
    </div>
@endsection
