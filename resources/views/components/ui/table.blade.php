@props([
    'class' => '',
])

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-slate-200 '.$class]) }}>
        <thead class="bg-white">
        {{ $head ?? '' }}
        </thead>

        <tbody class="divide-y divide-slate-200">
        {{ $slot }}
        </tbody>
    </table>
</div>
