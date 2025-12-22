@props([
    'title',
    'delimiter',
    'totalRows',

    'issues' => [],

    'headers' => [],
    'previewRows' => [],

    'cancelUrl',
    'confirmAction',

    'token',
    'storagePath',

    'hidden' => [],

    'confirmLabel' => 'Confirm Import',
    'cancelLabel' => 'Cancel',
])

@php
    $delimLabel = ($delimiter === "\t") ? 'TAB' : ($delimiter ?? '');
@endphp

<x-ui.page-title
    :title="$title"
    :meta-items="[
        ['label' => 'Delimiter', 'value' => $delimLabel],
        ['label' => 'Rows', 'value' => $totalRows],
    ]"
/>

@if(!empty($issues))
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
        <x-import.issue-list :issues="$issues" title="Issues" compact/>
    </div>
@endif

<x-ui.card>
    <x-ui.card-header title="Preview (first 20 rows)"/>

    <x-import.preview-table :headers="$headers" :rows="$previewRows"/>

    <x-ui.card-footer class="flex items-center justify-end gap-2">
        <a href="{{ $cancelUrl }}">
            <x-ui.button variant="secondary">{{ $cancelLabel }}</x-ui.button>
        </a>

        <form method="POST" action="{{ $confirmAction }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="storagePath" value="{{ $storagePath }}">

            @foreach($hidden as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach

            <x-ui.button type="submit">{{ $confirmLabel }}</x-ui.button>
        </form>
    </x-ui.card-footer>
</x-ui.card>
