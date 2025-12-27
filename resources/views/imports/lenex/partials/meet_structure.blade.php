@php
    $structure = $batch->summary_json['structure'] ?? [];

    if (!is_array($structure)) {
        $structure = [];
    }

    $sessions = [];
    if (isset($structure['sessions']) && is_array($structure['sessions'])) {
        $sessions = $structure['sessions'];
    }

    $ageGroups = [];
    if (isset($structure['age_groups']) && is_array($structure['age_groups'])) {
        $ageGroups = $structure['age_groups'];
    }

    // Map: code => age group (unique)
    $ageGroupsByCode = [];
    foreach ($ageGroups as $ag) {
        if (!isset($ag['code']) || $ag['code'] === '' || !is_scalar($ag['code'])) {
            continue;
        }
        $ageGroupsByCode[(string) $ag['code']] = $ag;
    }
@endphp

@if(is_array($structure))
    <section class="mt-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="text-lg font-semibold">Meet Structure</div>
                <div class="text-sm text-slate-500">
                    Displayed only for LENEX type <span class="font-medium">meet_structure</span>.
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div>
                    <div class="font-semibold mb-3">Sessions</div>

                    @if(!empty($sessions))
                        <div class="space-y-6">
                            @foreach($sessions as $s)
                                @php
                                    $sessionNo = $s['no'] ?? '—';
                                    $sessionDate = $s['date'] ?? null;
                                    $sessionTime = $s['start_time'] ?? null;

                                    $dt = trim(($sessionDate ? (string)$sessionDate : '') . ($sessionTime ? (' ' . $sessionTime) : ''));
                                    if ($dt === '') $dt = '—';

                                    $sessionHeader = "Session {$sessionNo} - {$dt}";

                                    $events = (isset($s['events']) && is_array($s['events'])) ? $s['events'] : [];
                                @endphp

                                <div class="border border-slate-200 rounded-xl p-4">
                                    <div class="font-medium text-slate-900">
                                        {{ $sessionHeader }}
                                    </div>

                                    <div class="mt-3">
                                        @if(!empty($events))
                                            <div class="space-y-4">
                                                @foreach($events as $e)
                                                    @php
                                                        $eventNo = $e['no'] ?? '—';

                                                        $eventName = isset($e['name']) ? trim((string)$e['name']) : '';
                                                        if ($eventName === '') {
                                                            $parts = [];
                                                            if (!empty($e['distance'])) $parts[] = ((int)$e['distance']) . 'm';
                                                            if (!empty($e['stroke'])) $parts[] = (string)$e['stroke'];
                                                            $eventName = $parts ? implode(' ', $parts) : 'Event';
                                                        }

                                                        $eventRound = $e['round'] ?? '—';

                                                        // event may have multiple age groups
                                                        $eventAgCodes = (isset($e['age_groups']) && is_array($e['age_groups'])) ? $e['age_groups'] : [];
                                                        $eventAgCodes = array_values(array_filter(
                                                            $eventAgCodes,
                                                            fn ($v) => is_scalar($v) && trim((string)$v) !== ''
                                                        ));

                                                        // Gender: prefer event gender; fallback to AG gender if missing
                                                        $eventGender = $e['gender'] ?? null;
                                                        $eventGender = is_string($eventGender) ? trim($eventGender) : '';

                                                        if ($eventGender === '' || $eventGender === '—') {
                                                            $gSeen = [];

                                                            foreach ($eventAgCodes as $agCodeRaw) {
                                                                $agCode = trim((string)$agCodeRaw);
                                                                if ($agCode === '' || !isset($ageGroupsByCode[$agCode])) {
                                                                    continue;
                                                                }

                                                                $agGender = $ageGroupsByCode[$agCode]['gender'] ?? '';
                                                                $agGender = is_string($agGender) ? strtoupper(trim($agGender)) : '';

                                                                if ($agGender === '' || $agGender === '—') {
                                                                    continue;
                                                                }

                                                                $gSeen[$agGender] = true;
                                                            }

                                                            $gKeys = array_keys($gSeen);

                                                            if (count($gKeys) === 1) {
                                                                $eventGender = $gKeys[0];
                                                            } elseif (count($gKeys) > 1) {
                                                                $eventGender = 'X';
                                                            } else {
                                                                $eventGender = '—';
                                                            }
                                                        } else {
                                                            $eventGender = strtoupper($eventGender);
                                                        }

                                                        // show first AG code in event line (as requested)
                                                        $eventAgFirst = !empty($eventAgCodes) ? (string)$eventAgCodes[0] : '—';

                                                        $eventLine = "#{$eventNo} {$eventName}, {$eventGender}, AG {$eventAgFirst}, {$eventRound}";

                                                        // Detail lines: 1 line per age group (handicap is ONE valuation string, not split)
                                                        $detailLines = [];
                                                        $idx = 1;

                                                        foreach ($eventAgCodes as $agCodeRaw) {
                                                            $agCode = trim((string)$agCodeRaw);

                                                            if (!isset($ageGroupsByCode[$agCode])) {
                                                                $detailLines[] = "AG #{$idx}: {$agCode} (not found)";
                                                                $idx++;
                                                                continue;
                                                            }

                                                            $ag = $ageGroupsByCode[$agCode];

                                                            $agName = isset($ag['name']) ? trim((string)$ag['name']) : '';
                                                            if ($agName === '') $agName = '—';

                                                            $agGender = $ag['gender'] ?? '—';
                                                            $agGender = is_string($agGender) ? strtoupper(trim($agGender)) : '—';
                                                            if ($agGender === '') $agGender = '—';

                                                            $agMax = $ag['max'] ?? null;
                                                            $agMaxText = ($agMax === null || $agMax === '' || (string)$agMax === '-1') ? '—' : (string)$agMax;

                                                            $hcRaw = isset($ag['handicap']) ? trim((string)$ag['handicap']) : '';

                                                            if ($hcRaw !== '') {
                                                                $detailLines[] = "AG #{$idx}: {$agName} ({$agGender}), handicap {$hcRaw}, max age {$agMaxText}";
                                                            } else {
                                                                $detailLines[] = "AG #{$idx}: {$agName} ({$agGender}), max age {$agMaxText}";
                                                            }

                                                            $idx++;
                                                        }
                                                    @endphp

                                                    <div>
                                                        <div class="text-sm text-slate-900">
                                                            {{ $eventLine }}
                                                        </div>

                                                        @if(!empty($detailLines))
                                                            <div class="mt-1 pl-6 space-y-1 text-sm text-slate-600">
                                                                @foreach($detailLines as $line)
                                                                    <div>{{ $line }}</div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="mt-1 pl-6 text-sm text-slate-500">
                                                                No age groups assigned.
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-sm text-slate-500">No events in this session.</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-slate-500">No sessions found.</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
