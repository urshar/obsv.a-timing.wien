@props([
    'name' => 'csv',
    'label' => 'CSV file',
    'hint' => 'Delimiter is auto-detected (; , tab).',
    'accept' => '.csv,text/csv',
    'required' => true,
])

<div>
    <label class="text-sm font-medium text-slate-700">{{ $label }}</label>

    <input type="file"
           name="{{ $name }}"
           accept="{{ $accept }}"
           @if($required) required @endif
           class="mt-2 block w-full text-sm text-slate-700
                  file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900
                  file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white
                  hover:file:bg-slate-800">

    @if($hint)
        <p class="mt-2 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
