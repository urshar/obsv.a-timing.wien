@props([
    'name',
    'required' => false,
])

@php
    $hasError = $errors->has($name);
    $base = 'mt-2 w-full rounded-lg border focus:ring-0';
    $cls = $hasError
        ? 'border-rose-300 focus:border-rose-400'
        : 'border-slate-300 focus:border-slate-400';
@endphp

<select
    name="{{ $name }}"
    @if($required) required @endif
    {{ $attributes->merge(['class' => "$base $cls"]) }}
>
    {{ $slot }}
</select>
