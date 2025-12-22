@props(['class' => ''])

<div {{ $attributes->merge(['class' => "p-6 $class"]) }}>
    {{ $slot }}
</div>
