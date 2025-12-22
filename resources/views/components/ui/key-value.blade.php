@props([
    'label',
    'value',
])

<div class="flex items-center gap-2">
    <span class="text-sm text-slate-500">{{ $label }}:</span>
    <span class="font-mono text-xs bg-slate-100 px-2 py-1 rounded text-slate-700">{{ $value }}</span>
</div>
