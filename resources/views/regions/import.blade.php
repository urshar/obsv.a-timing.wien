@extends('layouts.app')
@section('title','Import Regions (CSV)')

@section('content')
    @include('imports._upload_form', [
        'title' => 'Import Regions (CSV)',
        'subtitle' => 'Upload CSV → Preview → Confirm Import.',
        'recommendedColumns' => 'nation_iso2,nameEn,nameDe,isoSubRegionCode,abbreviation,lsvCode,bsvCode',
        'action' => route('regions.import.preview'),
        'backUrl' => route('regions.index'),
        'submitLabel' => 'Preview',
        'backLabel' => 'Back',
        'options' => [],
    ])
@endsection
