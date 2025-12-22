@props([
    'title',
    'subtitle' => null,

    // meta items: [['label'=>'Format','value'=>'CSV'], ...]
    'metaItems' => [],

    // NEW: show as separate line under title/subtitle
    'recommendedColumns' => null,

    'action',
    'backUrl',
    'fileName' => 'csv',

    // [['name'=>'truncate','label'=>'...','default'=>false], ...]
    'options' => [],

    'submitLabel' => 'Preview',
    'backLabel' => 'Back',
])

<x-ui.page-title :title="$title" :subtitle="$subtitle" :meta-items="$metaItems"/>

@if($recommendedColumns)
    <p class="text-sm text-slate-500 -mt-4 mb-6">
        Recommended columns:
        <span class="font-mono text-xs bg-slate-100 px-2 py-1 rounded text-slate-700">{{ $recommendedColumns }}</span>
    </p>
@endif

<x-ui.card>
    <x-ui.card-body>
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <x-ui.field label="CSV file" name="{{ $fileName }}" hint="Delimiter is auto-detected (; , tab).">
                <x-ui.file :name="$fileName" required/>
            </x-ui.field>

            @if(!empty($options))
                <div class="space-y-3">
                    @foreach($options as $opt)
                        @php
                            $name = $opt['name'];
                            $label = $opt['label'] ?? $name;
                            $default = (bool)($opt['default'] ?? false);
                        @endphp

                        <label class="flex items-center gap-2">
                            <x-ui.checkbox :name="$name" :checked="$default"/>
                            <span class="text-sm text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            @endif

            <div class="flex items-center justify-end gap-2">
                <a href="{{ $backUrl }}">
                    <x-ui.button variant="secondary">{{ $backLabel }}</x-ui.button>
                </a>
                <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
            </div>
        </form>
    </x-ui.card-body>
</x-ui.card>
