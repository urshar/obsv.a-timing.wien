@extends('layouts.app')
@section('title','Edit Region')

@section('content')
    <x-ui.page-title title="Edit Region" subtitle="Update region values."/>

    <x-ui.card>
        <x-ui.card-body>
            @include('regions._form', [
                'region' => $region,
                'nations' => $nations,
                'action' => route('regions.update', $region),
                'method' => 'PUT',
                'submitLabel' => 'Update',
                'cancelUrl' => route('regions.index'),
            ])
        </x-ui.card-body>
    </x-ui.card>
@endsection
