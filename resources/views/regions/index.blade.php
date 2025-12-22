@extends('layouts.app')
@section('title','Regions')

@section('content')
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Regions</h1>
            <p class="text-sm text-slate-500">Filter by nation, search by codes and names.</p>
        </div>
        <a href="{{ route('regions.create') }}"
           class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Create
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-4 mb-6">
        <form method="GET" action="{{ route('regions.index') }}"
              class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-6">
                <label class="text-sm font-medium text-slate-700">Search</label>
                <input name="q" value="{{ $q ?? '' }}" placeholder="Search name/codes"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>

            <div class="md:col-span-4">
                <label class="text-sm font-medium text-slate-700">Nation</label>
                <select name="nation_id"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
                    <option value="">All nations</option>
                    @foreach($nations as $n)
                        <option value="{{ $n->id }}" @selected(($nationId ?? '') == $n->id)>{{ $n->nameEn }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button
                    class="w-full inline-flex justify-center items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Filter
                </button>
                <a href="{{ route('regions.index') }}"
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
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Nation
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">LSV
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">BSV
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">ISO
                        SubRegion
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Abbr
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                @foreach($regions as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $r->nation?->nameEn }}</td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-slate-900">{{ $r->nameEn }}</div>
                            <div class="text-xs text-slate-500">{{ $r->nameDe }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $r->lsvCode }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $r->bsvCode }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $r->isoSubRegionCode }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $r->abbreviation }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('regions.edit', $r) }}"
                                   class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                                    Edit
                                </a>
                                <form action="{{ route('regions.destroy', $r) }}" method="POST"
                                      onsubmit="return confirm('Delete this region?')">
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
            {{ $regions->links() }}
        </div>
    </div>
@endsection
