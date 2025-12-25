@props([
    'label',
    'key',
    'active' => false,
])

<div
    class="relative"
    @keydown.escape.window="openMenu = null"
    @click.outside="if (openMenu === '{{ $key }}') openMenu = null"
>
    <button
        type="button"
        @click="openMenu = (openMenu === '{{ $key }}' ? null : '{{ $key }}')"
        :aria-expanded="(openMenu === '{{ $key }}').toString()"
        class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100 inline-flex items-center gap-2
               {{ $active ? 'bg-slate-900 text-white hover:bg-slate-900' : 'text-slate-700' }}"
    >
        <span>{{ $label }}</span>

        <svg class="h-4 w-4 opacity-80 transition-transform"
             :class="openMenu === '{{ $key }}' ? 'rotate-180' : ''"
             viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z" clip-rule="evenodd" />
        </svg>
    </button>

    {{-- Mobile: panel flows in layout (w-full). Desktop: absolute dropdown (w-56). --}}
    <div
        x-show="openMenu === '{{ $key }}'"
        x-transition
        class="mt-2 w-full rounded-xl border border-slate-200 bg-white shadow-lg p-1 z-50
               sm:absolute sm:left-0 sm:mt-2 sm:w-56"
        style="display: none;"
        role="menu"
        aria-label="{{ $label }}"
    >
        {{ $slot }}
    </div>
</div>
