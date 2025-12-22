@props([
    'title',
    'delimiter',
    'totalRows',
])

<div class="mb-6">
    <h1 class="text-2xl font-semibold">{{ $title }}</h1>
    <p class="text-sm text-slate-500 mt-1">
        Delimiter detected:
        <span
            class="font-mono text-xs bg-slate-100 px-2 py-1 rounded">{{ $delimiter === "\t" ? 'TAB' : $delimiter }}</span>
        · Rows: {{ $totalRows }}
    </p>
</div>
