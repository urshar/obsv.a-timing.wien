@props([
    'label' => 'Actions',
    'variant' => 'secondary', // primary|secondary|danger|ghost
    'align' => 'right',       // right|left
    'width' => 'w-72',        // tailwind width
])

@php
    $base = 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition';
    $variants = [
        'primary' => 'bg-slate-900 text-white hover:bg-slate-800',
        'secondary' => 'bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-500',
        'ghost' => 'bg-transparent text-slate-700 hover:bg-slate-100',
    ];
    $cls = $variants[$variant] ?? $variants['secondary'];

    $alignClass = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<details class="relative inline-block">
    <summary
        role="button"
        tabindex="0"
        class="list-none cursor-pointer select-none {{ $base }} {{ $cls }}"
    >
        {{ $label }}
    </summary>

    <div
        class="absolute z-50 mt-2 {{ $alignClass }} {{ $width }} overflow-hidden rounded-lg bg-white ring-1 ring-slate-200 shadow-sm">
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</details>

<style>
    details > summary::-webkit-details-marker {
        display: none;
    }
</style>
