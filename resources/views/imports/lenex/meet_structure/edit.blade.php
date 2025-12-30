@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    /**
     * Erwartete Variablen:
     * - $batch
     * - $meet (DB::table('meets')->first() oder Model)
     * - $sessions (Collection<MeetSession> mit ->meetEvents eager loaded)
     * - $ageGroups (Collection<MeetAgeGroup>)
     */

    $oldMeetName = old('meet.name', data_get($meet, 'name'));
    $oldMeetCity = old('meet.city', data_get($meet, 'city'));
    $oldMeetFrom = old('meet.from', data_get($meet, 'from'));
    $oldMeetTo   = old('meet.to', data_get($meet, 'to'));
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Edit meet structure</h1>
                <div class="text-sm text-slate-600">
                    Batch #{{ $batch->id }} · {{ data_get($meet, 'name', '—') }}
                </div>
            </div>

            <div class="shrink-0">
                <a href="{{ route('imports.lenex.preview', $batch) }}"
                   class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                    Back to overview
                </a>
            </div>
        </div>

        <form id="meet-structure-form"
              method="POST"
              action="{{ route('imports.lenex.meet_structure.show', $batch) }}"
              class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Meet info --}}
            <x-ui.card>
                <x-ui.card-header>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">Meet information</div>
                            <div class="text-xs text-slate-600">Edit the basic meet data.</div>
                        </div>
                    </div>
                </x-ui.card-header>

                <x-ui.card-body>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.field label="Name">
                            <x-ui.input name="meet[name]" :value="$oldMeetName"/>
                        </x-ui.field>

                        <x-ui.field label="City">
                            <x-ui.input name="meet[city]" :value="$oldMeetCity"/>
                        </x-ui.field>

                        <x-ui.field label="From">
                            <x-ui.input name="meet[from]" :value="$oldMeetFrom" placeholder="YYYY-MM-DD"/>
                        </x-ui.field>

                        <x-ui.field label="To">
                            <x-ui.input name="meet[to]" :value="$oldMeetTo" placeholder="YYYY-MM-DD"/>
                        </x-ui.field>
                    </div>
                </x-ui.card-body>
            </x-ui.card>

            {{-- Sessions & Events editor --}}
            <x-ui.card>
                <x-ui.card-header>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-slate-900">Sessions & Events</div>
                            <div class="text-xs text-slate-600">Add/remove sessions and events, edit event fields.</div>
                        </div>

                        <x-ui.button type="button" variant="secondary" id="btn-add-session">
                            Add session
                        </x-ui.button>
                    </div>
                </x-ui.card-header>

                <x-ui.card-body>
                    <div id="sessions-container" class="space-y-4">
                        @php
                            // Wir serialisieren Sessions/Events als "sessions[si][...]" Inputs.
                            // Indexe sind 0 bis n-1 und werden von JS weitergeführt.
                            $sessionIndex = 0;
                        @endphp

                        @foreach($sessions as $s)
                            @php
                                $si = $sessionIndex++;
                                $events = $s->meetEvents ?? collect();
                                $sessionNo = old("sessions.$si.session_no", $s->session_no);
                                $sessionDate = old("sessions.$si.date", $s->date);
                                $sessionStart = old("sessions.$si.start_time", $s->start_time);
                            @endphp

                            <div class="rounded-xl ring-1 ring-slate-200 bg-white" data-session>
                                <div
                                    class="px-4 py-3 border-b border-slate-200 flex items-center justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <div class="font-semibold text-slate-900">Session</div>

                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-slate-600">No</span>
                                            <x-ui.input class="w-20"
                                                        name="sessions[{{ $si }}][session_no]"
                                                        :value="$sessionNo"/>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-slate-600">Date</span>
                                            <x-ui.input class="w-40"
                                                        name="sessions[{{ $si }}][date]"
                                                        :value="$sessionDate"
                                                        placeholder="YYYY-MM-DD"/>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-slate-600">Start</span>
                                            <x-ui.input class="w-32"
                                                        name="sessions[{{ $si }}][start_time]"
                                                        :value="$sessionStart"
                                                        placeholder="HH:MM"/>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <x-ui.button type="button" variant="secondary" class="btn-add-event">
                                            Add event
                                        </x-ui.button>

                                        <x-ui.button type="button" variant="danger" class="btn-remove-session">
                                            Remove
                                        </x-ui.button>
                                    </div>
                                </div>

                                <div class="p-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead class="text-xs text-slate-600">
                                            <tr class="border-b border-slate-200">
                                                <th class="text-left py-2 pr-2">No</th>
                                                <th class="text-left py-2 pr-2">Name</th>
                                                <th class="text-left py-2 pr-2">Gender</th>
                                                <th class="text-left py-2 pr-2">Distance</th>
                                                <th class="text-left py-2 pr-2">Stroke</th>
                                                <th class="text-left py-2 pr-2">Round</th>
                                                <th class="text-left py-2 pr-2">Relay</th>
                                                <th class="text-left py-2 pr-2">Primary AG</th>
                                                <th class="py-2"></th>
                                            </tr>
                                            </thead>

                                            <tbody class="divide-y divide-slate-100" data-events-container>
                                            @php $eventIndex = 0; @endphp
                                            @foreach($events as $e)
                                                @php
                                                    $ei = $eventIndex++;
                                                    $evNo = old("sessions.$si.events.$ei.event_no", $e->event_no);
                                                    $evName = old("sessions.$si.events.$ei.name", $e->name);
                                                    $evGender = old("sessions.$si.events.$ei.gender", (string)($e->gender ?? ''));
                                                    $evDistance = old("sessions.$si.events.$ei.distance", $e->distance);
                                                    $evStroke = old("sessions.$si.events.$ei.stroke", $e->stroke);
                                                    $evRound = old("sessions.$si.events.$ei.round", $e->round);
                                                    $evRelay = (bool) old("sessions.$si.events.$ei.is_relay", (int) $e->is_relay);
                                                    $evPrimaryAgId = old("sessions.$si.events.$ei.meet_age_group_id", $e->meet_age_group_id);
                                                @endphp

                                                <tr data-event>
                                                    <td class="py-2 pr-2">
                                                        <x-ui.input class="w-20"
                                                                    name="sessions[{{ $si }}][events][{{ $ei }}][event_no]"
                                                                    :value="$evNo"/>
                                                    </td>

                                                    <td class="py-2 pr-2">
                                                        <x-ui.input class="w-96"
                                                                    name="sessions[{{ $si }}][events][{{ $ei }}][name]"
                                                                    :value="$evName"/>
                                                    </td>

                                                    <td class="py-2 pr-2">
                                                        <x-ui.select
                                                            name="sessions[{{ $si }}][events][{{ $ei }}][gender]"
                                                            class="w-24">
                                                            <option value="" @selected($evGender==='')>—</option>
                                                            <option value="F" @selected($evGender==='F')>F</option>
                                                            <option value="M" @selected($evGender==='M')>M</option>
                                                            <option value="X" @selected($evGender==='X')>X</option>
                                                        </x-ui.select>
                                                    </td>

                                                    <td class="py-2 pr-2">
                                                        <x-ui.input class="w-24"
                                                                    name="sessions[{{ $si }}][events][{{ $ei }}][distance]"
                                                                    :value="$evDistance"/>
                                                    </td>

                                                    <td class="py-2 pr-2">
                                                        <x-ui.input class="w-28"
                                                                    name="sessions[{{ $si }}][events][{{ $ei }}][stroke]"
                                                                    :value="$evStroke"/>
                                                    </td>

                                                    <td class="py-2 pr-2">
                                                        <x-ui.input class="w-28"
                                                                    name="sessions[{{ $si }}][events][{{ $ei }}][round]"
                                                                    :value="$evRound"/>
                                                    </td>

                                                    <td class="py-2 pr-2">
                                                        <x-ui.checkbox
                                                            name="sessions[{{ $si }}][events][{{ $ei }}][is_relay]"
                                                            value="1"
                                                            :checked="$evRelay">
                                                            Relay
                                                        </x-ui.checkbox>
                                                    </td>

                                                    <td class="py-2 pr-2">
                                                        <x-ui.select
                                                            name="sessions[{{ $si }}][events][{{ $ei }}][meet_age_group_id]"
                                                            class="w-64">
                                                            <option value="">—</option>
                                                            @foreach($ageGroups as $ag)
                                                                <option value="{{ $ag->id }}"
                                                                    @selected((string)$evPrimaryAgId === (string)$ag->id)>
                                                                    {{ $ag->name ?? '—' }} ({{ $ag->code }})
                                                                </option>
                                                            @endforeach
                                                        </x-ui.select>
                                                    </td>

                                                    <td class="py-2 text-right">
                                                        <x-ui.button type="button" variant="danger"
                                                                     class="btn-remove-event">
                                                            Remove
                                                        </x-ui.button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card-body>

                <x-ui.card-footer>
                    <div class="flex items-center justify-end">
                        <x-ui.button type="submit" variant="primary">
                            Save changes
                        </x-ui.button>
                    </div>
                </x-ui.card-footer>
            </x-ui.card>
        </form>

        {{-- Templates (ausgelagert) --}}
        @include('imports.lenex.meet_structure._edit_templates')
    </div>

    {{-- Page-specific JS (Vite) --}}
    @vite('resources/js/meet_structure_edit.js')

    <script>
        /** @type {{sessionCount:number}} */
        window.__MEET_STRUCTURE_EDIT__ = {sessionCount: {{ $sessions->count() }}};
    </script>
@endsection
