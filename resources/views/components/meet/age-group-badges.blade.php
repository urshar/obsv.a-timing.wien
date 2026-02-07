@props([
    'meet',
    'event',
    'max' => 4,
])

@php
    $assigned = $event->meetAgeGroups ?? collect();

    // per-event fold/expand without JS
    $showAllForThisEvent = (string) request('ag_event') === (string) data_get($event, 'id');

    $maxBadges = (int) $max;
    if ($maxBadges < 1) {
        $maxBadges = 1;
    }

    $visible = $showAllForThisEvent ? $assigned : $assigned->take($maxBadges);
    $hiddenCount = max(0, $assigned->count() - $visible->count());

    // jump directly to the editor for this event
    $editUrl = route('meets.structure.events.age_groups.edit', [$meet, $event]);

    // fold/expand urls
    $showAllUrl = request()->fullUrlWithQuery(['ag_event' => data_get($event, 'id')]);
    $showLessUrl = request()->fullUrlWithQuery(['ag_event' => null]);
@endphp

<div class="mt-2 text-sm">
    <div class="flex items-center justify-between gap-3">
        <div class="text-slate-500">Age groups</div>

        <a href="{{ $editUrl }}" class="text-xs text-slate-600 hover:text-slate-900 underline">
            Edit
        </a>
    </div>

    @if($assigned->isEmpty())
        <div class="text-slate-600">—</div>
    @else
        <div class="mt-1 flex flex-wrap items-center gap-2">
            @foreach($visible as $ag)
                <a href="{{ $editUrl }}" class="inline-flex hover:opacity-80">
                    <x-ui.badge>
                        {{ $ag->name ?? '—' }}
                        @if($ag->gender)
                            ({{ $ag->gender }})
                        @endif
                        @if($ag->code)
                            · {{ $ag->code }}
                        @endif
                    </x-ui.badge>
                </a>
            @endforeach

            @if(! $showAllForThisEvent && $hiddenCount > 0)
                <a href="{{ $editUrl }}" class="inline-flex hover:opacity-80">
                    <x-ui.badge>+{{ $hiddenCount }} more</x-ui.badge>
                </a>
            @endif

            @if($showAllForThisEvent && $assigned->count() > $maxBadges)
                <a href="{{ $showLessUrl }}" class="text-xs text-slate-600 hover:text-slate-900 underline">
                    Show less
                </a>
            @endif

            @if(! $showAllForThisEvent && $assigned->count() > $maxBadges)
                <a href="{{ $showAllUrl }}" class="text-xs text-slate-600 hover:text-slate-900 underline">
                    Show all
                </a>
            @endif
        </div>
    @endif
</div>
