@extends('layouts.app')
@section('title','Continents')

@section('content')
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Continents</h1>
            <p class="text-sm text-slate-500">Manage continent codes and multilingual names.</p>
        </div>
        <a href="{{ route('continents.create') }}"
           class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Create
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-4 mb-6">
        <form method="GET" action="{{ route('continents.index') }}"
              class="flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="flex-1">
                <label class="text-sm font-medium text-slate-700">Search</label>
                <input name="q" value="{{ $q ?? '' }}" placeholder="Search code / name"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
            <div class="flex gap-2">
                <button
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Filter
                </button>
                <a href="{{ route('continents.index') }}"
                   class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Code
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                        (EN)
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                        (DE)
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                @foreach($continents as $c)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $c->id }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $c->code }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $c->nameEn }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $c->nameDe }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('continents.edit', $c) }}"
                                   class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                                    Edit
                                </a>
                                <form action="{{ route('continents.destroy', $c) }}" method="POST"
                                      onsubmit="return confirm('Delete this continent?')">
                                    @csrf @method('DELETE')
                                    <button
                                        class="inline-flex items-center rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-4 border-t border-slate-200">
            {{ $continents->links() }}
        </div>
    </div>
@endsection
