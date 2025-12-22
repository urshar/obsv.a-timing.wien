@extends('layouts.app')
@section('title','Nations')

@section('content')
    <x-ui.page-title title="Nations" subtitle="Manage countries and codes."/>

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">

        {{-- FILTER FORM --}}
        <form method="GET" action="{{ route('nations.index') }}"
              class="flex flex-col gap-3 sm:flex-row sm:items-end">

            {{-- SEARCH --}}
            <x-ui.field label="Search" name="q" compact>
                <x-ui.input
                    name="q"
                    :value="$q"
                    placeholder="Name, ISO, IOC..."
                    class="w-64"
                />
            </x-ui.field>

            {{-- CONTINENT DROPDOWN --}}
            <x-ui.field label="Continent" name="continent_id" compact>
                <x-ui.select name="continent_id" class="w-56">
                    <option value="">All continents</option>
                    @foreach($continents as $c)
                        <option value="{{ $c->id }}"
                            @selected($continentId == $c->id)>
                            {{ $c->nameEn }}
                        </option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            {{-- ACTIONS --}}
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="secondary">
                    Filter
                </x-ui.button>

                @if(!empty($q) || !empty($continentId))
                    <a href="{{ route('nations.index') }}">
                        <x-ui.button type="button" variant="ghost">
                            Reset
                        </x-ui.button>
                    </a>
                @endif
            </div>
        </form>

        {{-- RIGHT ACTIONS --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('nations.create') }}">
                <x-ui.button>Add Nation</x-ui.button>
            </a>
            <a href="{{ route('nations.import.show') }}">
                <x-ui.button variant="secondary">Import CSV</x-ui.button>
            </a>
        </div>
    </div>

    <x-ui.card>
        <x-ui.card-header title="List" subtitle="Filtered nations"/>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                        (EN)
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                        (DE)
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Continent
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">ISO2
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">ISO3
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">IOC
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                @forelse($nations as $n)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm">{{ $n->nameEn }}</td>
                        <td class="px-4 py-3 text-sm">{{ $n->nameDe }}</td>
                        <td class="px-4 py-3 text-sm">{{ $n->continent?->nameEn ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $n->iso2 ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $n->iso3 ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $n->ioc ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('nations.edit', $n) }}">
                                    <x-ui.button variant="secondary">Edit</x-ui.button>
                                </a>
                                <form method="POST"
                                      action="{{ route('nations.destroy', $n) }}"
                                      onsubmit="return confirm('Delete this nation?')">
                                    @csrf @method('DELETE')
                                    <x-ui.button variant="danger" type="submit">Delete</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-sm text-slate-500">
                            No nations found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <x-ui.card-footer class="flex justify-end">
            {{ $nations->links() }}
        </x-ui.card-footer>
    </x-ui.card>
@endsection
