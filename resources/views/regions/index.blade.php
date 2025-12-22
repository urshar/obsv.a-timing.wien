@extends('layouts.app')
@section('title','Regions')

@section('content')
    <x-ui.page-title title="Regions" subtitle="Manage subregions linked to nations."/>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('regions.index') }}" class="flex gap-2">
            <x-ui.input name="q" :value="$q ?? ''" placeholder="Search (name, code, nation)..." class="w-72"/>
            <x-ui.button variant="secondary" type="submit">Search</x-ui.button>
            @if(!empty($q))
                <a href="{{ route('regions.index') }}">
                    <x-ui.button variant="ghost" type="button">Reset</x-ui.button>
                </a>
            @endif
        </form>

        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('regions.create') }}">
                <x-ui.button>Add Region</x-ui.button>
            </a>
            <a href="{{ route('regions.import.show') }}">
                <x-ui.button variant="secondary">Import CSV</x-ui.button>
            </a>
        </div>
    </div>

    <x-ui.card>
        <x-ui.card-header title="List" subtitle="All regions"/>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Nation
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                        (EN)
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                        (DE)
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Abbr
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">LSV
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">BSV
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                @forelse($regions as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm">{{ $r->nation?->nameEn ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $r->nameEn }}</td>
                        <td class="px-4 py-3 text-sm">{{ $r->nameDe }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $r->abbreviation ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $r->lsvCode ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $r->bsvCode ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('regions.edit', $r) }}">
                                    <x-ui.button variant="secondary">Edit</x-ui.button>
                                </a>
                                <form method="POST" action="{{ route('regions.destroy', $r) }}"
                                      onsubmit="return confirm('Delete this region?')">
                                    @csrf @method('DELETE')
                                    <x-ui.button variant="danger" type="submit">Delete</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-sm text-slate-500">No regions found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <x-ui.card-footer class="flex justify-end">
            {{ $regions->links() }}
        </x-ui.card-footer>
    </x-ui.card>
@endsection
