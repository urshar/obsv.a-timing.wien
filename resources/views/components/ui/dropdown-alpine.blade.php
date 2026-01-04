@props([
    'label' => 'Actions',
    'variant' => 'secondary',   // primary|secondary|danger|ghost
    'align' => 'right',         // right|left
    'width' => 'w-72',          // tailwind width
])

@php
    $alignClass = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<div
    x-data="{ open: false }"
    class="relative inline-block"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
>
    <button type="button" class="hidden" aria-hidden="true"></button>

    <div>
        <x-ui.button
            :variant="$variant"
            type="button"
            x-on:click="open = !open"
            x-bind:aria-expanded="open.toString()"
            aria-haspopup="menu"
        >
            {{ $label }}
        </x-ui.button>
    </div>

    <div
        x-show="open"
        x-transition
        class="absolute z-50 mt-2 {{ $alignClass }} {{ $width }} overflow-hidden rounded-lg bg-white ring-1 ring-slate-200 shadow-sm"
        style="display: none;"
        role="menu"
    >
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>
