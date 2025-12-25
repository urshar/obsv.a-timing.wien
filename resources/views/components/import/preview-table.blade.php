@props([
    'headers' => [],
    'rows' => [],
])

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-white">
        <tr>
            @foreach($headers as $h)
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    {{ $h }}
                </th>
            @endforeach
        </tr>
        </thead>

        <tbody class="divide-y divide-slate-200">
        @forelse($rows as $r)
            @php
                // Support rows as arrays (your current structure)
                $entityType = $r['entity_type'] ?? null;
                $payload = $r['payload'] ?? ($r['payload_json'] ?? []);

                $isOfficialsOnlyClub =
                    $entityType === 'club'
                    && data_get($payload, 'officials_only_source') === true;

                // first header key for badge placement
                $firstHeader = $headers[0] ?? null;
            @endphp

            <tr class="hover:bg-slate-50 {{ $isOfficialsOnlyClub ? 'opacity-60 bg-slate-50/60' : '' }}">
                @foreach($headers as $h)
                    <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                        {{-- Put IGNORED badge into the first column --}}
                        @if($isOfficialsOnlyClub && $h === $firstHeader)
                            <span class="inline-flex items-center gap-2">
                                <x-ui.badge level="neutral">IGNORED</x-ui.badge>
                                <span>{{ $r[$h] ?? '' }}</span>
                            </span>
                        @else
                            {{ $r[$h] ?? '' }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ max(1, count($headers)) }}" class="px-4 py-6 text-sm text-slate-500">
                    No preview rows available.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
