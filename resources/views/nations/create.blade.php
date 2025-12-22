@extends('layouts.app')
@section('title','Create Nation')

@section('content')
    <x-ui.page-title title="Create Nation"/>

    <x-ui.card>
        <x-ui.card-body>
            @include('nations._form', [
                'nation' => null,
                'continents' => $continents,
                'action' => route('nations.store'),
                'method' => 'POST',
                'submitLabel' => 'Save',
                'cancelUrl' => route('nations.index'),
            ])
        </x-ui.card-body>
    </x-ui.card>
@endsection
