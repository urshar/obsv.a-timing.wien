@props([
    'href',
    'active' => false,
])

<a href="{{ $href }}"
    {{ $attributes->merge([
         'class' => 'px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100 ' .
                    ($active ? 'bg-slate-900 text-white hover:bg-slate-900' : '')
    ]) }}>
    {{ $slot }}
</a>
