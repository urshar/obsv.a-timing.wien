@extends('layouts.app')
@section('title','Create Region')

@section('content')
    <x-ui.page-title title="Create Region"/>

    <x-ui.card>
        <x-ui.card-body>
            @include('regions._form', [
                'region' => null,
                'nations' => $nations,
                'action' => route('regions.store'),
                'method' => 'POST',
                'submitLabel' => 'Save',
                'cancelUrl' => route('regions.index'),
            ])
        </x-ui.card-body>
    </x-ui.card>
@endsection
