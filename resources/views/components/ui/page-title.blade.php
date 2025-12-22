@props([
    'title',
    'subtitle' => null,
    'meta' => null, // optional string (e.g. "Recommended columns: ...")
])

<div class="mb-6">
    <h1 class="text-2xl font-semibold">{{ $title }}</h1>

    @if($subtitle)
        <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
    @endif

    @if($meta)
        <p class="text-sm text-slate-500 mt-2">{!! $meta !!}</p>
    @endif
</div>
