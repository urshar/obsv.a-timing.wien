@props([
    'name',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'id' => null,
])

@php
    $id = $id ?: $name . '_' . \Illuminate\Support\Str::random(6);

    $hasLabel = trim((string) $slot) !== '';
@endphp

@if($hasLabel)
    <label for="{{ $id }}" class="inline-flex items-center gap-2 select-none">
        <input
            id="{{ $id }}"
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            @if($checked) checked @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge(['class' => 'rounded border-slate-300 text-slate-900 focus:ring-slate-900']) }}
        />
        <span class="text-sm text-slate-700">{{ $slot }}</span>
    </label>
@else
    <input
        id="{{ $id }}"
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @if($checked) checked @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'rounded border-slate-300 text-slate-900 focus:ring-slate-900']) }}
    />
@endif
