@props([
    // array of ['name'=>'truncate','label'=>'...','default'=>false]
    'options' => [],
])

@if(!empty($options))
    <div class="space-y-3">
        @foreach($options as $opt)
            @php
                $name = $opt['name'];
                $label = $opt['label'] ?? $name;
                $default = (bool)($opt['default'] ?? false);
                $checked = old($name, $default);
            @endphp

            <label class="flex items-center gap-2">
                <input type="checkbox" name="{{ $name }}" value="1"
                       @checked($checked)
                       class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-0">
                <span class="text-sm text-slate-700">{{ $label }}</span>
            </label>
        @endforeach
    </div>
@endif
