@extends('layouts.app')
@section('title','Para Swim Styles')

@section('content')
    <x-ui.page-title title="Para Swim Styles" subtitle="Manage distances, strokes and relay variants."/>

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">

        {{-- FILTER FORM --}}
        <form method="GET" action="{{ route('para-swim-styles.index') }}"
              class="flex flex-col gap-3 sm:flex-row sm:items-end">

            <x-ui.field label="Search" name="q" compact>
                <x-ui.input
                    name="q"
                    :value="$q"
                    placeholder="Key, stroke, names, abbreviation..."
                    class="w-72"
                />
            </x-ui.field>

            <x-ui.field label="Stroke" name="stroke" compact>
                <x-ui.select name="stroke" class="w-40">
                    <option value="">All</option>
                    @foreach(['FR','BK','BR','FL','IM'] as $code)
                        <option value="{{ $code }}" @selected($stroke === $code)>{{ $code }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field label="Distance" name="distance" compact>
                <x-ui.input
                    name="distance"
                    :value="$distance"
                    placeholder="e.g. 100"
                    class="w-32"
                />
            </x-ui.field>

            <x-ui.field label="Type" name="relay" compact>
                <x-ui.select name="relay" class="w-44">
                    <option value="">All</option>
                    <option value="individual" @selected($relay === 'individual')>Individual</option>
                    <option value="relay" @selected($relay === 'relay')>Relay</option>
                </x-ui.select>
            </x-ui.field>

            <div class="flex gap-2">
                <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>

                @if(!empty($q) || !empty($stroke) || !empty($distance) || !empty($relay))
                    <a href="{{ route('para-swim-styles.index') }}">
                        <x-ui.button type="button" variant="ghost">Reset</x-ui.button>
                    </a>
                @endif
            </div>
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('para-swim-styles.create') }}">
                <x-ui.button>Add Para Swim Style</x-ui.button>
            </a>
        </div>
    </div>

    <x-ui.card>
        <x-ui.card-header title="List" subtitle="Filtered para swim styles"/>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Key
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Type
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Distance
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Stroke
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                        (EN)
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Name
                        (DE)
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Abbr
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                @forelse($styles as $s)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-mono">{{ $s->key }}</td>
                        <td class="px-4 py-3 text-sm">
                            {{ ($s->relay_count ?? 1) > 1 ? ($s->relay_count . 'x Relay') : 'Individual' }}
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $s->distance }}m</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $s->stroke }}</td>
                        <td class="px-4 py-3 text-sm">{{ $s->stroke_name_en }}</td>
                        <td class="px-4 py-3 text-sm">{{ $s->stroke_name_de }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $s->abbreviation }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('para-swim-styles.edit', $s) }}">
                                    <x-ui.button variant="secondary">Edit</x-ui.button>
                                </a>
                                <form method="POST"
                                      action="{{ route('para-swim-styles.destroy', $s) }}"
                                      onsubmit="return confirm('Delete this para swim style?')">
                                    @csrf @method('DELETE')
                                    <x-ui.button variant="danger" type="submit">Delete</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-sm text-slate-500">
                            No para swim styles found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <x-ui.card-footer class="flex justify-end">
            {{ $styles->links() }}
        </x-ui.card-footer>
    </x-ui.card>
@endsection
