@php
    $structure = $batch->summary_json['structure'] ?? null;
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

            <div class="p-6 space-y-8">
                {{-- Age Groups --}}
                <div>
                    <div class="font-semibold mb-2">Age Groups</div>
                    @if(empty($structure['age_groups']))
                        <div class="text-sm text-slate-500">No age groups found.</div>
                    @else
                        <div class="space-y-2">
                            @foreach($structure['age_groups'] as $ag)
                                <div class="text-sm">
                                    <span class="font-medium">{{ $ag['code'] }}</span>
                                    <span class="text-slate-500">
                                        ({{ $ag['min'] ?? '—' }} – {{ $ag['max'] ?? '—' }})
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Sessions + Events --}}
                <div>
                    <div class="font-semibold mb-2">Sessions</div>
                    @if(empty($structure['sessions']))
                        <div class="text-sm text-slate-500">No sessions found.</div>
                    @else
                        <div class="space-y-6">
                            @foreach($structure['sessions'] as $s)
                                <div class="border border-slate-200 rounded-xl p-4">
                                    <div class="font-medium">
                                        Session {{ $s['no'] ?? '—' }}
                                        <span class="text-slate-500 text-sm">
                                            · {{ $s['date'] ?? '—' }} {{ $s['start_time'] ?? '' }}
                                        </span>
                                    </div>

                                    <div class="mt-3 space-y-2">
                                        @forelse(($s['events'] ?? []) as $e)
                                            <div class="text-sm">
                                                <span class="font-medium">#{{ $e['no'] ?? '—' }}</span>
                                                {{ $e['name'] ?? '—' }}
                                                <span class="text-slate-500">
                                                    · {{ $e['gender'] ?? '—' }}
                                                    · {{ $e['distance'] ?? '—' }} {{ $e['stroke'] ?? '' }}
                                                    · {{ $e['round'] ?? '' }}
                                                    @if(!empty($e['age_group']))
                                                        · AG {{ $e['age_group'] }}
                                                    @endif
                                                    @if(!empty($e['is_relay']))
                                                        · Relay
                                                    @endif
                                                </span>
                                            </div>
                                        @empty
                                            <div class="text-sm text-slate-500">No events in this session.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
