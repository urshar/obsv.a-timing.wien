@extends('layouts.app')
@section('title','Create Para Swim Style')

@section('content')
    <x-ui.page-title title="Create Para Swim Style"/>

    <x-ui.card>
        <x-ui.card-body>
            @include('para-swim-styles._form', [
                'style' => null,
                'action' => route('para-swim-styles.store'),
                'method' => 'POST',
                'submitLabel' => 'Save',
                'cancelUrl' => route('para-swim-styles.index'),
            ])
        </x-ui.card-body>
    </x-ui.card>
@endsection
