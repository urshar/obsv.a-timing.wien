@props([
    'name',
    'checked' => false,
])

<input
    type="checkbox"
    name="{{ $name }}"
    value="1"
    @checked(old($name, $checked))
    {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-0']) }}
/>
