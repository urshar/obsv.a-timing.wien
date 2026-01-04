@props([
    'href' => null,
    'disabled' => false,
])

@php
    $base = 'block w-full px-3 py-2 text-sm text-left';
    $enabled = 'text-slate-700 hover:bg-slate-50';
    $disabledCls = 'text-slate-400 cursor-not-allowed';
@endphp

@if($disabled || empty($href))
    <span class="{{ $base }} {{ $disabledCls }}">
        {{ $slot }}
    </span>
@else
    <a href="{{ $href }}" class="{{ $base }} {{ $enabled }}">
        {{ $slot }}
    </a>
@endif
