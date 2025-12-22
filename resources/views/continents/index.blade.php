@extends('layouts.app')
@section('title','Continents')

@section('content')
    <x-ui.page-title title="Continents" subtitle="Manage continents used for nations."/>

    <div class="mb-4 flex justify-end">
        <a href="{{ route('continents.create') }}">
            <x-ui.button>Add Continent</x-ui.button>
        </a>
    </div>

    <x-ui.card>
        <x-ui.card-header title="List" subtitle="All continents"/>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Code
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                        (DE)
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                        (EN)
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                @forelse($continents as $c)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-mono">{{ $c->code }}</td>
                        <td class="px-4 py-3 text-sm">{{ $c->nameDe }}</td>
                        <td class="px-4 py-3 text-sm">{{ $c->nameEn }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('continents.edit', $c) }}">
                                    <x-ui.button variant="secondary">Edit</x-ui.button>
                                </a>
                                <form method="POST" action="{{ route('continents.destroy', $c) }}"
                                      onsubmit="return confirm('Delete this continent?')">
                                    @csrf @method('DELETE')
                                    <x-ui.button variant="danger" type="submit">Delete</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-sm text-slate-500">No continents found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <x-ui.card-footer class="flex justify-end">
            {{ $continents->links() }}
        </x-ui.card-footer>
    </x-ui.card>
@endsection
