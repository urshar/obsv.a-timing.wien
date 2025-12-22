@extends('layouts.app')
@section('title','Create Continent')

@section('content')
    <x-ui.page-title title="Create Continent"/>

    <x-ui.card>
        <x-ui.card-body>
            @include('continents._form', [
                'continent' => null,
                'action' => route('continents.store'),
                'method' => 'POST',
                'submitLabel' => 'Save',
                'cancelUrl' => route('continents.index'),
            ])
        </x-ui.card-body>
    </x-ui.card>
@endsection
