@extends('layouts.app')
@section('title','Import Regions (CSV)')

@section('content')
    <x-import.upload-form
        title="Import Regions (CSV)"
        subtitle="Upload CSV → Preview → Confirm Import."
        :meta-items="[
        ['label' => 'Format', 'value' => 'CSV'],
        ['label' => 'Delimiter', 'value' => 'auto'],
    ]"
        recommendedColumns="nation_iso2,nameEn,nameDe,isoSubRegionCode,abbreviation,lsvCode,bsvCode"
        :action="route('regions.import.preview')"
        :backUrl="route('regions.index')"
        submitLabel="Preview"
        backLabel="Back"
        :options="[]"
    />
@endsection
