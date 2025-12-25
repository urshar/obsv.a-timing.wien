@props([
    'href',
    'active' => false,
])

<a
    href="{{ $href }}"
    @click="openMenu = null"
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-slate-100
           {{ $active ? 'bg-slate-900 text-white hover:bg-slate-900' : 'text-slate-700' }}"
    role="menuitem"
>
    {{-- Active indicator dot --}}
    <span
        class="h-2 w-2 rounded-full flex-none
               {{ $active ? 'bg-white' : 'bg-transparent border border-slate-300' }}">
    </span>

    <span class="flex-1">
        {{ $slot }}
    </span>

    {{-- Subtle check icon when active --}}
    @if($active)
        <svg class="h-4 w-4 opacity-80 flex-none"
             viewBox="0 0 20 20"
             fill="currentColor"
             aria-hidden="true">
            <path fill-rule="evenodd"
                  d="M16.704 5.29a1 1 0 0 1 0 1.414l-7.2 7.2a1 1 0 0 1-1.414 0l-3.2-3.2a1 1 0 1 1 1.414-1.414l2.493 2.493 6.493-6.493a1 1 0 0 1 1.414 0z"
                  clip-rule="evenodd"/>
        </svg>
    @endif
</a>
