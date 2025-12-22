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
            <tr class="hover:bg-slate-50">
                @foreach($headers as $h)
                    <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                        {{ $r[$h] ?? '' }}
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
