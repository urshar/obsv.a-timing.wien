@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@php
    $summary = $batch->summary_json ?? [];
    $meet = $summary['meet'] ?? [];
    $counts = $summary['counts'] ?? [];

    $sevIcon = function($sev) {
        return match($sev) {
            'error' => '✕',
            'warn'  => '!',
            'ok'    => '✓',
            default => '•',
        };
    };

    $sevLevelForBadge = function($sev) {
        // map DB severities to your badge levels
        return match($sev) {
            'error' => 'danger',
            'warn'  => 'warning',
            'ok'    => 'success',
            default => 'neutral',
        };
    };

    $currentMapLabel = function($map) {
        if (!$map) return 'create';
        if ($map->action === 'link') return 'link → #' . ($map->target_id ?? '—');
        return $map->action ?? 'create';
    };
@endphp

@section('content')
    <div class="max-w-6xl mx-auto p-6 space-y-6">

        {{-- Global alerts / validation --}}
        <x-layout.validation-errors/>
        <x-layout.import-alert/>

        @if (session('status'))
            <x-layout.import-alert :message="session('status')" level="success"/>
        @endif

        {{-- Title --}}
        <x-ui.page-title
            title="Lenex Import Preview (Batch #{{ $batch->id }})"
            subtitle="Type: {{ $batch->type }} · Status: {{ $batch->status }} · File: {{ $batch->filename }}"
        />

        {{-- Summary / Commit --}}
        <x-ui.card>
            <x-ui.card-header>
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="space-y-2">
                        <div class="text-sm text-slate-600">
                            <span class="font-semibold text-slate-900">Meet</span>
                            <span class="ml-2">{{ $meet['name'] ?? '—' }}</span>
                        </div>

                        @php
                            $from = data_get($meet, 'from');
                            $to   = data_get($meet, 'to');
                            $city = data_get($meet, 'city');

                            $fmt = function ($v) {
                                if (!$v) return null;
                                try {
                                    return Carbon::parse($v)->format('d.m.Y');
                                } catch (Throwable $e) {
                                    return (string) $v;
                                }
                            };

                            $fromFmt = $fmt($from);
                            $toFmt   = $fmt($to);

                            $dateLabel = null;
                            if ($fromFmt && $toFmt && $fromFmt !== $toFmt) {
                                $dateLabel = "{$fromFmt} – {$toFmt}";
                            } elseif ($fromFmt) {
                                $dateLabel = $fromFmt;
                            }
                        @endphp

                        <div class="text-sm text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-1">
                            @if($dateLabel)
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                    {{ $dateLabel }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-200">
                                    No meet date in LENEX
                                </span>
                            @endif

                            @if(!empty($city))
                                <span class="text-slate-400">•</span>
                                <span>{{ $city }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 text-xs">
                            <x-ui.badge level="neutral">Clubs: {{ $counts['clubs'] ?? 0 }}</x-ui.badge>
                            <x-ui.badge level="neutral">Athletes: {{ $counts['athletes'] ?? 0 }}</x-ui.badge>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('imports.lenex.commit', $batch) }}">
                            @csrf
                            <x-ui.button type="submit" :disabled="$batch->status !== 'preview'">
                                Commit Import
                            </x-ui.button>
                        </form>
                    </div>
                </div>
            </x-ui.card-header>
        </x-ui.card>

        {{-- MEET mapping --}}
        @php
            $meetIssue = ($issues['meet'] ?? collect())->first();
            $meetMapping = ($mappings['meet'] ?? collect())->firstWhere('source_key','meet');
        @endphp

        <x-ui.card>
            <x-ui.card-header>
                <div class="space-y-1">
                    <div class="text-lg font-semibold">Meet Selection</div>
                    <div class="text-sm text-slate-500">
                        Link to an existing meet (update) or keep “create” to insert a new meet.
                        <span class="font-medium text-slate-700">Variante A:</span>
                        Beim Link-&-Update werden Sessions/Events/Entries/Results ersetzt.
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-body>
                @if($meetIssue)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start gap-3">
                            <div class="h-7 w-7 rounded-lg border flex items-center justify-center font-semibold">
                                {{ $sevIcon($meetIssue->severity) }}
                            </div>

                            <div class="flex-1 space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-ui.badge :level="$sevLevelForBadge($meetIssue->severity)">
                                        {{ strtoupper($meetIssue->severity) }}
                                    </x-ui.badge>
                                    <div class="font-medium text-slate-900">{{ $meetIssue->message }}</div>
                                </div>

                                <x-import.mapping-controls
                                    :batch="$batch"
                                    entityType="meet"
                                    sourceKey="meet"
                                    :suggestions="$meetIssue->suggestions_json ?? []"
                                    :allowIgnore="false"
                                    :allowManualLink="true"
                                    manualLinkPlaceholder="Meet ID…"
                                    createLabel="Create new meet"
                                    linkLabel="Link & Update"
                                />

                                <div class="text-sm text-slate-600">
                                    Current action:
                                    <span class="font-medium">{{ $currentMapLabel($meetMapping) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-sm text-slate-600">No meet issues.</div>
                @endif
            </x-ui.card-body>
        </x-ui.card>

        {{-- CLUBS --}}
        <x-ui.card>
            <x-ui.card-header>
                <div class="space-y-1">
                    <div class="text-lg font-semibold">Clubs</div>
                    <div class="text-sm text-slate-500">
                        Review clubs. Use link to avoid duplicates. Officials-only clubs are never imported.
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-body>
                @forelse(($issues['club'] ?? collect()) as $issue)
                    @php
                        $map = ($mappings['club'] ?? collect())->firstWhere('source_key', $issue->entity_key);
                        $payload = $issue->payload_json ?? [];
                        $isOfficialsOnly = ($payload['officials_only_source'] ?? false) === true;
                    @endphp

                    <div
                        class="rounded-2xl border border-slate-200 p-4 mb-3 {{ $isOfficialsOnly ? 'opacity-60 bg-slate-50/60' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="h-7 w-7 rounded-lg border flex items-center justify-center font-semibold">
                                {{ $sevIcon($issue->severity) }}
                            </div>

                            <div class="flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-ui.badge :level="$sevLevelForBadge($issue->severity)">
                                        {{ strtoupper($issue->severity) }}
                                    </x-ui.badge>

                                    @if($isOfficialsOnly)
                                        <x-ui.badge level="neutral">IGNORED (Officials-only)</x-ui.badge>
                                    @endif

                                    <div class="font-medium text-slate-900">
                                        {{ $payload['name'] ?? 'Unnamed Club' }}
                                        <span class="text-slate-500 font-normal">
                                        ({{ $payload['nation'] ?? '—' }})
                                        @if(!empty($payload['short_name']))
                                                · {{ $payload['short_name'] }}
                                            @endif
                                    </span>
                                    </div>
                                </div>

                                <div class="text-sm text-slate-700">{{ $issue->message }}</div>

                                <x-import.mapping-controls
                                    :batch="$batch"
                                    entityType="club"
                                    :sourceKey="$issue->entity_key"
                                    :suggestions="$issue->suggestions_json ?? []"
                                    :isDisabled="$isOfficialsOnly"
                                    disabledText="Dieser Club wird automatisch ignoriert (Officials-only)."
                                    :allowManualLink="true"
                                    manualLinkPlaceholder="Club ID…"
                                    createLabel="Create"
                                    ignoreLabel="Ignore"
                                    linkLabel="Link"
                                />

                                <div class="text-xs text-slate-600">
                                    Current:
                                    <span class="font-medium">
                                    {{ $isOfficialsOnly ? 'ignore' : $currentMapLabel($map) }}
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-600">No club issues.</div>
                @endforelse
            </x-ui.card-body>
        </x-ui.card>

        {{-- ATHLETES --}}
        <x-ui.card>
            <x-ui.card-header>
                <div class="space-y-1">
                    <div class="text-lg font-semibold">Athletes</div>
                    <div class="text-sm text-slate-500">
                        Review athletes to prevent duplicates (matching by name + birth year).
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-body>
                @forelse(($issues['athlete'] ?? collect()) as $issue)
                    @php
                        $map = ($mappings['athlete'] ?? collect())->firstWhere('source_key', $issue->entity_key);
                        $payload = $issue->payload_json ?? [];
                    @endphp

                    <div class="rounded-2xl border border-slate-200 p-4 mb-3">
                        <div class="flex items-start gap-3">
                            <div class="h-7 w-7 rounded-lg border flex items-center justify-center font-semibold">
                                {{ $sevIcon($issue->severity) }}
                            </div>

                            <div class="flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-ui.badge :level="$sevLevelForBadge($issue->severity)">
                                        {{ strtoupper($issue->severity) }}
                                    </x-ui.badge>

                                    <div class="font-medium text-slate-900">
                                        {{ $payload['last_name'] ?? '—' }} {{ $payload['first_name'] ?? '' }}
                                        <span class="text-slate-500 font-normal">
                                        ({{ $payload['birth_year'] ?? '—' }})
                                    </span>
                                    </div>
                                </div>

                                <div class="text-sm text-slate-700">{{ $issue->message }}</div>

                                <x-import.mapping-controls
                                    :batch="$batch"
                                    entityType="athlete"
                                    :sourceKey="$issue->entity_key"
                                    :suggestions="$issue->suggestions_json ?? []"
                                    :allowManualLink="true"
                                    manualLinkPlaceholder="Athlete ID…"
                                    createLabel="Create"
                                    ignoreLabel="Ignore"
                                    linkLabel="Link"
                                />

                                <div class="text-xs text-slate-600">
                                    Current:
                                    <span class="font-medium">{{ $currentMapLabel($map) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-600">No athlete issues.</div>
                @endforelse
            </x-ui.card-body>
        </x-ui.card>

    </div>
@endsection
