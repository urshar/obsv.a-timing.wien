@props([
    // required
    'title',
    'cancelUrl',
    'confirmAction',

    'token',
    'storagePath',

    'delimiter',
    'totalRows',

    'headers' => [],
    'previewRows' => [],

    // optional
    'issues' => [],
    'confirmLabel' => 'Confirm Import',
    'cancelLabel' => 'Cancel',

    // hidden fields for confirm (e.g. truncate/strict_unique)
    'hidden' => [],
])

@include('imports._preview_header', [
    'title' => $title,
    'delimiter' => $delimiter,
    'totalRows' => $totalRows,
])

@if(!empty($issues))
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
        @include('imports._issue_list', ['issues' => $issues, 'title' => 'Issues', 'compact' => true])
    </div>
@endif

@include('ui._card')
@slot('slot')
    @include('ui._card_header', ['title' => 'Preview (first 20 rows)'])
    @slot('slot')
        {{-- optional header actions could go here --}}
    @endslot
    @endinclude

    {{-- no padding here, table handles spacing --}}
    <div class="p-0">
        @include('imports._preview_table', ['headers' => $headers, 'rows' => $previewRows])
    </div>

    @include('ui._card_footer', ['class' => 'flex items-center justify-end gap-2'])
    @slot('slot')
        @include('imports._preview_footer', [
            'cancelUrl' => $cancelUrl,
            'confirmAction' => $confirmAction,
            'token' => $token,
            'storagePath' => $storagePath,
            'hidden' => $hidden,
            'confirmLabel' => $confirmLabel,
            'cancelLabel' => $cancelLabel,
        ])
    @endslot
    @endinclude
@endslot
@endinclude
