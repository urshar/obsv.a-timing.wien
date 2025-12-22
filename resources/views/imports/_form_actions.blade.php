<@props([
    'backUrl',
    'submitLabel' => 'Preview',
    'backLabel' => 'Back',
])

<div class="flex items-center justify-end gap-2">
    <a href="{{ $backUrl }}"
       class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
        {{ $backLabel }}
    </a>

    <button
        class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
        {{ $submitLabel }}
    </button>
</div>
