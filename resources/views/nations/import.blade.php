@extends('layouts.app')
@section('title','Import Nations (CSV)')

@section('content')
    @include('imports._upload_form', [
        'title' => 'Import Nations (CSV)',
        'subtitle' => 'Upload CSV → Preview → Confirm Import.',
        'recommendedColumns' => 'nameEn,nameDe,continent_code,ioc,iso2,iso3,tld,Capital',
        'action' => route('nations.import.preview'),
        'backUrl' => route('nations.index'),
        'submitLabel' => 'Preview',
        'backLabel' => 'Back',
        'options' => [
            [
                'name' => 'truncate',
                'label' => 'Truncate nations before import (and regions if present)',
                'default' => false,
            ],
            [
                'name' => 'strict_unique',
                'label' => 'Strict UNIQUE: skip rows with ioc/iso2/iso3 conflicts (and report)',
                'default' => false,
            ],
        ],
    ])
@endsection
