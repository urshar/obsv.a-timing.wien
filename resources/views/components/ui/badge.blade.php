@props(['level' => 'info']) {{-- info|warning|error|success --}}

@php
    $map = [
        'success' => 'bg-emerald-100 text-emerald-900',
        'warning' => 'bg-amber-100 text-amber-900',
        'error' => 'bg-rose-100 text-rose-900',
        'info' => 'bg-slate-100 text-slate-900',
    ];
    $cls = $map[$level] ?? $map['info'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold $cls"]) }}>
    {{ $slot }}
</span>
