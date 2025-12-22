@props([
    'label' => null,
    'name' => null,

    // Existing
    'hint' => null,

    // NEW
    'help' => null,        // additional help text (separate from hint)
    'compact' => false,    // less vertical spacing
    'noStack' => false,    // disable default "space-y-1"
])

@php
    $error = $name ? $errors->first($name) : null;

    $stackClass = $noStack ? '' : ($compact ? 'space-y-0.5' : 'space-y-1');
@endphp

<div {{ $attributes->merge(['class' => $stackClass]) }}>
    @if($label)
        <label class="text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    {{ $slot }}

    @if($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @if($help)
        <p class="text-xs text-slate-500">{{ $help }}</p>
    @endif

    @if($error)
        <p class="text-xs text-rose-600">{{ $error }}</p>
    @endif
</div>
