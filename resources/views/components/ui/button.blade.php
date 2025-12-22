@props([
    'variant' => 'primary', // primary|secondary|danger|ghost
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition';
    $variants = [
        'primary' => 'bg-slate-900 text-white hover:bg-slate-800',
        'secondary' => 'bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-500',
        'ghost' => 'bg-transparent text-slate-700 hover:bg-slate-100',
    ];
    $cls = $variants[$variant] ?? $variants['primary'];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$base $cls"]) }}>
    {{ $slot }}
</button>
