@extends('layouts.app')
@section('title','Edit Nation')

@section('content')
    <x-ui.page-title title="Edit Nation" subtitle="Update nation values."/>

    <x-ui.card>
        <x-ui.card-body>
            @include('nations._form', [
                'nation' => $nation,
                'continents' => $continents,
                'action' => route('nations.update', $nation),
                'method' => 'PUT',
                'submitLabel' => 'Update',
                'cancelUrl' => route('nations.index'),
            ])
        </x-ui.card-body>
    </x-ui.card>
@endsection
