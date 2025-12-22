@props([
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'maxlength' => null,
    'placeholder' => null,
])

@php
    $hasError = $errors->has($name);
    $base = 'mt-2 w-full rounded-lg border focus:ring-0';
    $cls = $hasError
        ? 'border-rose-300 focus:border-rose-400'
        : 'border-slate-300 focus:border-slate-400';
@endphp

<input
    name="{{ $name }}"
    type="{{ $type }}"
    value="{{ old($name, $value) }}"
    @if($required) required @endif
    @if($maxlength) maxlength="{{ $maxlength }}" @endif
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    {{ $attributes->merge(['class' => "$base $cls"]) }}
/>
