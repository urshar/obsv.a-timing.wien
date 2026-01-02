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

<td {{ $attributes->merge(['class' => "px-4 py-3 {$alignClass} text-sm"]) }}>
    {{ $slot }}
</td>
