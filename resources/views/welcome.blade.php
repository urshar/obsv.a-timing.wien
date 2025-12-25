@extends('layouts.app')
@section('title','Admin Dashboard')

@section('content')
    @php
        $lenexUploadRoute = collect([
            'imports.lenex.upload',
            'imports.lenex.show',
            'imports.lenex.create',
            'imports.lenex.index',
        ])->first(fn ($r) => \Illuminate\Support\Facades\Route::has($r));
    @endphp

    <x-ui.page-title
        title="Admin Dashboard"
        subtitle="Manage master data and imports."
    />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        {{-- LENEX Import --}}
        @if ($lenexUploadRoute)
            <x-ui.card>
                <x-ui.card-header title="LENEX Import" subtitle="Import von .lef/.lxf/.xml"/>
                <x-ui.card-body>
                    <p class="text-sm text-slate-600">
                        LENEX-Datei hochladen, Vorschau prüfen und anschließend importieren.
                    </p>
                </x-ui.card-body>
                <x-ui.card-footer class="flex justify-end">
                    <a href="{{ route($lenexUploadRoute) }}">
                        <x-ui.button>Open</x-ui.button>
                    </a>
                </x-ui.card-footer>
            </x-ui.card>
        @endif

        {{-- Continents --}}
        <x-ui.card>
            <x-ui.card-header title="Continents" subtitle="Manage continents"/>
            <x-ui.card-body>
                <p class="text-sm text-slate-600">
                    Create, edit and delete continents.
                </p>
            </x-ui.card-body>
            <x-ui.card-footer class="flex justify-end">
                <a href="{{ route('continents.index') }}">
                    <x-ui.button>Open</x-ui.button>
                </a>
            </x-ui.card-footer>
        </x-ui.card>

        {{-- Nations --}}
        <x-ui.card>
            <x-ui.card-header title="Nations" subtitle="Manage countries and codes"/>
            <x-ui.card-body>
                <p class="text-sm text-slate-600">
                    Maintain country names and ISO/IOC codes.
                </p>
            </x-ui.card-body>
            <x-ui.card-footer class="flex justify-end gap-2">
                <a href="{{ route('nations.index') }}">
                    <x-ui.button>Open</x-ui.button>
                </a>
                <a href="{{ route('nations.import.show') }}">
                    <x-ui.button variant="secondary">Import</x-ui.button>
                </a>
            </x-ui.card-footer>
        </x-ui.card>

        {{-- Regions --}}
        <x-ui.card>
            <x-ui.card-header title="Regions" subtitle="Manage regions"/>
            <x-ui.card-body>
                <p class="text-sm text-slate-600">
                    Maintain regions and assignments.
                </p>
            </x-ui.card-body>
            <x-ui.card-footer class="flex justify-end gap-2">
                <a href="{{ route('regions.index') }}">
                    <x-ui.button>Open</x-ui.button>
                </a>
                <a href="{{ route('regions.import.show') }}">
                    <x-ui.button variant="secondary">Import</x-ui.button>
                </a>
            </x-ui.card-footer>
        </x-ui.card>

        {{-- Para Swim Styles --}}
        @if (Route::has('para-swim-styles.index'))
            <x-ui.card>
                <x-ui.card-header title="Para Swim Styles" subtitle="Distances, strokes and relay variants"/>
                <x-ui.card-body>
                    <p class="text-sm text-slate-600">
                        Create, edit and delete para swim styles for LENEX mapping.
                    </p>
                </x-ui.card-body>
                <x-ui.card-footer class="flex justify-end">
                    <a href="{{ route('para-swim-styles.index') }}">
                        <x-ui.button>Open</x-ui.button>
                    </a>
                </x-ui.card-footer>
            </x-ui.card>
        @endif

        {{-- Imports quick links --}}
        <x-ui.card>
            <x-ui.card-header title="Imports" subtitle="Quick access"/>
            <x-ui.card-body>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('nations.import.show') }}">
                        <x-ui.button variant="secondary" class="w-full justify-center">Import Nations</x-ui.button>
                    </a>
                    <a href="{{ route('regions.import.show') }}">
                        <x-ui.button variant="secondary" class="w-full justify-center">Import Regions</x-ui.button>
                    </a>
                    @if ($lenexUploadRoute)
                        <a href="{{ route($lenexUploadRoute) }}">
                            <x-ui.button variant="secondary" class="w-full justify-center">LENEX Import</x-ui.button>
                        </a>
                    @endif

                </div>
            </x-ui.card-body>
        </x-ui.card>

    </div>

    <div class="mt-6">
        <x-ui.card>
            <x-ui.card-header title="Info" subtitle="Status & next steps"/>
            <x-ui.card-body>
                <ul class="list-disc pl-5 text-sm text-slate-600 space-y-1">
                    <li>Use the menus above to manage master data.</li>
                    <li>Para swim styles are the basis for upcoming LENEX imports.</li>
                    <li>Middleware/auth can be added later without changing the UI.</li>
                </ul>
            </x-ui.card-body>
        </x-ui.card>
    </div>
@endsection
