@props(['class' => ''])

<div {{ $attributes->merge(['class' => "bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden $class"]) }}>
    {{ $slot }}
</div>
