@props([
    'name',
    'required' => false,
    'accept' => '.csv,text/csv',
])

@php
    $hasError = $errors->has($name);
    $base = 'mt-2 block w-full text-sm text-slate-700';
    $cls = $hasError ? 'ring-1 ring-rose-300 rounded-lg p-1' : '';
@endphp

<input
    type="file"
    name="{{ $name }}"
    accept="{{ $accept }}"
    @if($required) required @endif
    {{ $attributes->merge(['class' => "$base $cls file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800"]) }}
/>
