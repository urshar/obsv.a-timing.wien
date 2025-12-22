@extends('layouts.app')
@section('title','Edit Region')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Region</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-6">
        <form method="POST" action="{{ route('regions.update', $region) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('regions._form', ['region' => $region])

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('regions.index') }}"
                   class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                    Cancel
                </a>
                <button
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Update
                </button>
            </div>
        </form>
    </div>
@endsection
