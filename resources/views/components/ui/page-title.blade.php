@props([
    'title',
    'subtitle' => null,

    // array of ['label' => string, 'value' => string|int]
    'metaItems' => [],
])

<div class="mb-6">
    <h1 class="text-2xl font-semibold">{{ $title }}</h1>

    @if($subtitle)
        <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
    @endif

    @if(!empty($metaItems))
        <div class="mt-2 flex flex-wrap items-center gap-2">
            @foreach($metaItems as $item)
                <x-ui.key-value :label="$item['label']" :value="$item['value']"/>

                @if(!$loop->last)
                    <span class="text-slate-300">·</span>
                @endif
            @endforeach
        </div>
    @endif
</div>
