@extends('layouts.app')
@section('title','Preview Nations Import')

@section('content')
    @include('imports._preview_page', [
        'title' => 'Preview Nations Import',
        'cancelUrl' => route('nations.import.show'),
        'confirmAction' => route('nations.import.commit'),

        'token' => $token,
        'storagePath' => $storagePath,

        'delimiter' => $delimiter,
        'totalRows' => $totalRows,

        'headers' => $headers,
        'previewRows' => $previewRows,

        'issues' => $issues ?? [],

        'hidden' => [
            'truncate' => $truncate ? 1 : 0,
            'strict_unique' => $strictUnique ? 1 : 0,
        ],
    ])
@endsection
