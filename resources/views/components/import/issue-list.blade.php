@props([
    'issues' => [],
    'title' => 'Issues',
    'compact' => false,
])

@php
    // Normalize $issues to iterable
    $issuesList = $issues ?? [];
@endphp

@if(!empty($issuesList) && count($issuesList))
    <div class="{{ $compact ? '' : 'mt-4' }}">
        <div class="text-sm font-semibold mb-2">{{ $title }}</div>

        <ul class="{{ $compact ? 'space-y-1' : 'space-y-2' }} text-sm">
            @foreach($issuesList as $i)
                @php
                    // Support BOTH:
                    // A) array issues: ['level','code','message','payload','entity_type'...]
                    // B) Eloquent ImportIssue model: $i->severity, $i->entity_type, $i->entity_key, $i->message, $i->payload_json

                    $isArray = is_array($i);

                    // level/severity mapping
                    $level = $isArray ? ($i['level'] ?? 'warning') : ($i->severity ?? 'warning');

                    // normalize: Laravel side uses 'warn', UI uses 'warning'
                    if ($level === 'warn') $level = 'warning';

                    // code/key
                    $code = $isArray ? ($i['code'] ?? '') : ($i->entity_key ?? '');

                    // entity type
                    $entityType = $isArray ? ($i['entity_type'] ?? ($i['type'] ?? null)) : ($i->entity_type ?? null);

                    // message
                    $msg = $isArray ? ($i['message'] ?? '') : ($i->message ?? '');

                    // payload (array or json-cast array)
                    $payload = $isArray
                        ? ($i['payload'] ?? ($i['payload_json'] ?? []))
                        : ($i->payload_json ?? []);

                    // Officials-only marker (from Lenex parsing)
                    $isOfficialsOnlyClub =
                        (strtolower((string)$entityType) === 'club' || str_contains((string)$code, 'club:')) &&
                        (data_get($payload, 'officials_only_source') === true);

                    $rowClass = $isOfficialsOnlyClub
                        ? 'opacity-70 bg-slate-50/60 rounded-lg px-2 py-1'
                        : '';
                @endphp

                <li class="flex items-start gap-2 {{ $rowClass }}">
                    <x-ui.badge :level="$level">{{ strtoupper($level) }}</x-ui.badge>

                    @if($isOfficialsOnlyClub)
                        <x-ui.badge level="neutral">IGNORED</x-ui.badge>
                    @endif

                    <span class="text-slate-900">
                        {{ $msg }}

                        @if($code)
                            <span class="ml-2 text-xs text-slate-500 font-mono">({{ $code }})</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
