@extends('layouts.app')
@section('title','Preview Regions Import')

@section('content')
    <x-import.preview-page
        title="Preview Regions Import"
        :delimiter="$delimiter"
        :totalRows="$totalRows"
        :issues="$issues ?? []"
        :headers="$headers"
        :previewRows="$previewRows"
        :cancelUrl="route('regions.import.show')"
        :confirmAction="route('regions.import.commit')"
        :token="$token"
        :storagePath="$storagePath"
        :hidden="[
        'truncate' => $truncate ? 1 : 0,
        'strict_unique' => $strictUnique ? 1 : 0,
    ]"
    />
@endsection
