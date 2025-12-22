@extends('layouts.app')
@section('title','Nations')

@section('content')
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Nations</h1>
            <p class="text-sm text-slate-500">Filter by continent, search by codes and names.</p>
        </div>
        <a href="{{ route('nations.create') }}"
           class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Create
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-4 mb-6">
        <form method="GET" action="{{ route('nations.index') }}"
              class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-6">
                <label class="text-sm font-medium text-slate-700">Search</label>
                <input name="q" value="{{ $q ?? '' }}" placeholder="Search name/codes/tld"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>

            <div class="md:col-span-4">
                <label class="text-sm font-medium text-slate-700">Continent</label>
                <select name="continent_id"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
                    <option value="">All continents</option>
                    @foreach($continents as $c)
                        <option value="{{ $c->id }}" @selected(($continentId ?? '') == $c->id)>{{ $c->nameEn }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button
                    class="w-full inline-flex justify-center items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Filter
                </button>
                <a href="{{ route('nations.index') }}"
                   class="w-full inline-flex justify-center items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
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
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Continent
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">IOC
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">ISO2
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">ISO3
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                @foreach($nations as $n)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $n->id }}</td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-slate-900">{{ $n->nameEn }}</div>
                            <div class="text-xs text-slate-500">{{ $n->nameDe }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $n->continent?->nameEn }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $n->ioc }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $n->iso2 }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $n->iso3 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('nations.edit', $n) }}"
                                   class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                                    Edit
                                </a>
                                <form action="{{ route('nations.destroy', $n) }}" method="POST"
                                      onsubmit="return confirm('Delete this nation?')">
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
            {{ $nations->links() }}
        </div>
    </div>
@endsection
