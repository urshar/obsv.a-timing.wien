@props([
    'title' => null,
    'subtitle' => null,
    'class' => '',
])

<div {{ $attributes->merge(['class' => "px-4 py-3 border-b border-slate-200 bg-slate-50 $class"]) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            @if($title)
                <div class="text-sm font-semibold text-slate-700">{{ $title }}</div>
            @endif
            @if($subtitle)
                <div class="mt-0.5 text-xs text-slate-500">{{ $subtitle }}</div>
            @endif
        </div>

        @if(trim($slot) !== '')
            <div class="shrink-0">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
