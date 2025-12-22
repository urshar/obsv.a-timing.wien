@props(['class' => ''])

<div {{ $attributes->merge(['class' => "px-4 py-4 border-t border-slate-200 $class"]) }}>
    {{ $slot }}
</div>
