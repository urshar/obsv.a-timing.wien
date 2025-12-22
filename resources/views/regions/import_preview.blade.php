@extends('layouts.app')
@section('title','Preview Regions Import')

@section('content')
    @include('imports._preview_page', [
        'title' => 'Preview Regions Import',
        'cancelUrl' => route('regions.import.show'),
        'confirmAction' => route('regions.import.commit'),

        'token' => $token,
        'storagePath' => $storagePath,

        'delimiter' => $delimiter,
        'totalRows' => $totalRows,

        'headers' => $headers,
        'previewRows' => $previewRows,

        'issues' => $issues ?? [],

        // identical hidden fields to nations (both 0)
        'hidden' => [
            'truncate' => $truncate ? 1 : 0,
            'strict_unique' => $strictUnique ? 1 : 0,
        ],
    ])
@endsection
