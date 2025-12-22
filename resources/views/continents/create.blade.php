@php use App\Models\Continent; @endphp
@extends('layouts.app')
@section('title','Create Continent')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Create Continent</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-6">
        <form method="POST" action="{{ route('continents.store') }}" class="space-y-6">
            @csrf
            @include('continents._form', ['continent' => new Continent()])
            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('continents.index') }}"
                   class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                    Cancel
                </a>
                <button
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Save
                </button>
            </div>
        </form>
    </div>
@endsection
