@extends('layouts.app')
@section('title','Edit Para Swim Style')

@section('content')
    <x-ui.page-title title="Edit Para Swim Style" subtitle="{{ $style->key }}"/>

    <x-ui.card>
        <x-ui.card-body>
            @include('para-swim-styles._form', [
                'style' => $style,
                'action' => route('para-swim-styles.update', $style),
                'method' => 'PUT',
                'submitLabel' => 'Update',
                'cancelUrl' => route('para-swim-styles.index'),
            ])
        </x-ui.card-body>
    </x-ui.card>
@endsection
