@extends('layouts.app')

@section('title', 'LENEX Import')

@section('content')
    @php
        // Optional: Link zurück (Dashboard / Start)
        $dashboardUrl = url('/');
    @endphp

    <x-ui.page-title
        title="LENEX Import"
        subtitle="LENEX Datei (.lef/.lxf/.xml) hochladen und vor dem Import prüfen."
    >
        <div class="flex items-center gap-2">
            <a href="{{ $dashboardUrl }}">
                <x-ui.button variant="secondary">Zur Übersicht</x-ui.button>
            </a>
        </div>
    </x-ui.page-title>

    <x-ui.card>
        <x-ui.card-header
            title="Upload"
            subtitle="Wähle eine LENEX-Datei aus und starte die Vorschau."
        />

        <x-ui.card-body>
            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
                    <div class="font-semibold mb-2">Bitte korrigieren:</div>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-4" method="POST" action="{{ route('imports.lenex.store') }}"
                  enctype="multipart/form-data">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700">LENEX Datei</label>
                    <input
                        type="file"
                        name="lenex_file"
                        required
                        accept=".lef,.lxf,.xml,application/xml,text/xml"
                        class="mt-2 block w-full rounded-xl border border-slate-300 bg-white text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-white hover:file:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300"
                    />
                    <p class="mt-1 text-xs text-slate-500">Erlaubt: .lef, .lxf, .xml</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Typ (optional überschreiben)</label>
                    <select
                        name="forced_type"
                        class="mt-2 block w-full rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"
                    >
                        <option value="">Auto detect</option>
                        <option value="meet_structure">Meet Structure</option>
                        <option value="entries">Entries</option>
                        <option value="results">Results</option>
                        <option value="records">Records</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Leer lassen, wenn du Auto-Erkennung willst.</p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <x-ui.button type="submit">Preview</x-ui.button>
                </div>
            </form>
        </x-ui.card-body>
    </x-ui.card>
@endsection
