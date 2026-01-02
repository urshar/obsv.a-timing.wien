@props([
    'align' => 'left', // left|right|center
])

@php
    $alignClass = match($align) {
        'right' => 'text-right',
        'center' => 'text-center',
        default => 'text-left',
    };
@endphp

<th {{ $attributes->merge(['class' => "px-4 py-3 {$alignClass} text-xs font-semibold uppercase tracking-wide text-slate-600"]) }}>
    {{ $slot }}
</th>
