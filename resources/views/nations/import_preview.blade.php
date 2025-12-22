@extends('layouts.app')
@section('title','Preview Nations Import')

@section('content')
    <x-import.preview-page
        title="Preview Nations Import"
        :delimiter="$delimiter"
        :totalRows="$totalRows"
        :issues="$issues ?? []"
        :headers="$headers"
        :previewRows="$previewRows"
        :cancelUrl="route('nations.import.show')"
        :confirmAction="route('nations.import.commit')"
        :token="$token"
        :storagePath="$storagePath"
        :hidden="[
        'truncate' => $truncate ? 1 : 0,
        'strict_unique' => $strictUnique ? 1 : 0,
    ]"
    />
@endsection
