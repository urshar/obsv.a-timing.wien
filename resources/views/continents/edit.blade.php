@extends('layouts.app')
@section('title','Edit Continent')

@section('content')
    <x-ui.page-title title="Edit Continent" subtitle="Update continent values."/>

    <x-ui.card>
        <x-ui.card-body>
            @include('continents._form', [
                'continent' => $continent,
                'action' => route('continents.update', $continent),
                'method' => 'PUT',
                'submitLabel' => 'Update',
                'cancelUrl' => route('continents.index'),
            ])
        </x-ui.card-body>
    </x-ui.card>
@endsection
