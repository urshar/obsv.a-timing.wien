@props([
    'variant' => 'primary', // primary|secondary|danger|ghost
    'type' => 'button',
    'href' => null,         // string|null
    'as' => null,           // 'a'|'button'|null
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

    // treat disabled as a boolean; Blade will pass it via attributes
    $disabled = filter_var($attributes->get('disabled'), FILTER_VALIDATE_BOOLEAN);

    $isLink = ($as === 'a') || ($href !== null);

    $disabledClasses = $disabled ? ' opacity-50 cursor-not-allowed pointer-events-none' : '';
    $finalClass = trim("$base $cls $disabledClasses");
@endphp

@if($isLink)
    <a
        href="{{ $disabled ? null : $href }}"
        {{ $attributes
            ->except(['type', 'disabled', 'href'])
            ->merge(['class' => $finalClass]) }}
        @if($disabled) aria-disabled="true" role="link" @endif
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes
            ->except(['href'])
            ->merge(['class' => $finalClass]) }}
        @if($disabled) disabled @endif
    >
        {{ $slot }}
    </button>
@endif
