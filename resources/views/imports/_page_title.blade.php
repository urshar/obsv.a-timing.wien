@props([
    'title',
    'subtitle' => null,
    'recommendedColumns' => null,
])

<div class="mb-6">
    <h1 class="text-2xl font-semibold">{{ $title }}</h1>

    @if($subtitle)
        <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
    @endif

    @if($recommendedColumns)
        <p class="text-sm text-slate-500 mt-2">
            Recommended columns:
            <span class="font-mono text-xs bg-slate-100 px-2 py-1 rounded">{{ $recommendedColumns }}</span>
        </p>
    @endif
</div>
