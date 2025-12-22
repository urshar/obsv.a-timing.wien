@props([
    // required
    'title',
    'action',
    'backUrl',

    // optional
    'subtitle' => null,
    'recommendedColumns' => null,
    'fileName' => 'csv',
    'options' => [],
    'submitLabel' => 'Preview',
    'backLabel' => 'Back',
])

@include('imports._page_title', [
    'title' => $title,
    'subtitle' => $subtitle,
    'recommendedColumns' => $recommendedColumns,
])

@include('ui._card', [
    'class' => '',
])
@slot('slot')
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        @include('ui._card_body')
        @slot('slot')
            @include('imports._file_input', [
                'name' => $fileName,
                'label' => 'CSV file',
                'hint' => 'Delimiter is auto-detected (; , tab).',
                'required' => true,
            ])

            <div class="mt-6">
                @include('imports._option_checkboxes', ['options' => $options])
            </div>
        @endslot
        @endinclude

        @include('ui._card_footer', ['class' => 'flex items-center justify-end gap-2'])
        @slot('slot')
            @include('imports._form_actions', [
                'backUrl' => $backUrl,
                'submitLabel' => $submitLabel,
                'backLabel' => $backLabel,
            ])
        @endslot
        @endinclude
    </form>
@endslot
@endinclude
