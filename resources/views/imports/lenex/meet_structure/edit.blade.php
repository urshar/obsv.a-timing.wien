@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto p-6 space-y-8">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold">Edit Meet Structure</h1>
                <div class="text-sm text-slate-600">Batch #{{ $batch->id }}</div>
            </div>

            <a href="{{ route('imports.lenex.meet_structure.show', $batch) }}"
               class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                Back
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 p-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-rose-50 text-rose-800 ring-1 ring-rose-200 p-3 text-sm space-y-1">
                <div class="font-semibold">Please fix:</div>
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('imports.lenex.meet_structure.update', $batch) }}" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- Meet --}}
            <section class="rounded-lg bg-white ring-1 ring-slate-200 p-4 space-y-4">
                <div class="font-semibold">Meet</div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="block">
                        <div class="text-sm font-medium text-slate-700">Name</div>
                        <input name="meet[name]" value="{{ old('meet.name', $meet->name ?? '') }}"
                               class="mt-1 w-full rounded-lg border-slate-300"/>
                    </label>

                    <label class="block">
                        <div class="text-sm font-medium text-slate-700">Start date</div>
                        <input type="date" name="meet[start_date]"
                               value="{{ old('meet.start_date', $meet->start_date ?? '') }}"
                               class="mt-1 w-full rounded-lg border-slate-300"/>
                    </label>

                    <label class="block">
                        <div class="text-sm font-medium text-slate-700">End date</div>
                        <input type="date" name="meet[end_date]"
                               value="{{ old('meet.end_date', $meet->end_date ?? '') }}"
                               class="mt-1 w-full rounded-lg border-slate-300"/>
                    </label>
                </div>
            </section>

            {{-- Age Groups --}}
            <section class="rounded-lg bg-white ring-1 ring-slate-200 p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="font-semibold">Age groups</div>
                    <button type="button"
                            class="rounded-lg px-3 py-2 text-sm font-semibold bg-slate-900 text-white hover:bg-slate-800"
                            onclick="addAgeGroupRow()">
                        Add age group
                    </button>
                </div>

                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-slate-600">
                        <tr>
                            <th class="p-2">Code</th>
                            <th class="p-2">Gender</th>
                            <th class="p-2">Min</th>
                            <th class="p-2">Max</th>
                            <th class="p-2">Name</th>
                            <th class="p-2">Handicap</th>
                            <th class="p-2"></th>
                        </tr>
                        </thead>
                        <tbody id="ageGroupsBody" class="divide-y divide-slate-100">
                        @foreach ($ageGroups as $i => $ag)
                            <tr>
                                <td class="p-2">
                                    <input type="hidden" name="age_groups[{{ $i }}][id]" value="{{ $ag->id }}">
                                    <input name="age_groups[{{ $i }}][code]"
                                           value="{{ old("age_groups.$i.code", $ag->code) }}"
                                           class="w-28 rounded-lg border-slate-300"/>
                                </td>
                                <td class="p-2">
                                    <select name="age_groups[{{ $i }}][gender]" class="rounded-lg border-slate-300">
                                        @php($g = old("age_groups.$i.gender", $ag->gender))
                                        <option value="" @selected($g==='')>—</option>
                                        <option value="F" @selected($g==='F')>F</option>
                                        <option value="M" @selected($g==='M')>M</option>
                                        <option value="X" @selected($g==='X')>X</option>
                                    </select>
                                </td>
                                <td class="p-2">
                                    <input name="age_groups[{{ $i }}][min_age]"
                                           value="{{ old("age_groups.$i.min_age", $ag->min_age) }}"
                                           class="w-20 rounded-lg border-slate-300"/>
                                </td>
                                <td class="p-2">
                                    <input name="age_groups[{{ $i }}][max_age]"
                                           value="{{ old("age_groups.$i.max_age", $ag->max_age) }}"
                                           class="w-20 rounded-lg border-slate-300"/>
                                </td>
                                <td class="p-2">
                                    <input name="age_groups[{{ $i }}][name]"
                                           value="{{ old("age_groups.$i.name", $ag->name) }}"
                                           class="w-80 rounded-lg border-slate-300"/>
                                </td>
                                <td class="p-2">
                                    <input name="age_groups[{{ $i }}][handicap]"
                                           value="{{ old("age_groups.$i.handicap", $ag->handicap) }}"
                                           class="w-28 rounded-lg border-slate-300"/>
                                </td>
                                <td class="p-2 text-right">
                                    <button type="button"
                                            class="rounded-lg px-3 py-2 text-sm font-semibold bg-rose-600 text-white hover:bg-rose-500"
                                            onclick="removeRow(this)">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Sessions + Events --}}
            <section class="rounded-lg bg-white ring-1 ring-slate-200 p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="font-semibold">Sessions & events</div>
                    <button type="button"
                            class="rounded-lg px-3 py-2 text-sm font-semibold bg-slate-900 text-white hover:bg-slate-800"
                            onclick="addSessionBlock()">
                        Add session
                    </button>
                </div>

                <div id="sessionsWrap" class="space-y-6">
                    @foreach ($sessions as $si => $s)
                        @php($sessionEvents = $eventsBySession[$s->id] ?? collect())
                        <div class="rounded-lg ring-1 ring-slate-200 p-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold">Session</div>
                                <button type="button"
                                        class="rounded-lg px-3 py-2 text-sm font-semibold bg-rose-600 text-white hover:bg-rose-500"
                                        onclick="removeRow(this, true)">
                                    Delete session
                                </button>
                            </div>

                            <input type="hidden" name="sessions[{{ $si }}][id]" value="{{ $s->id }}">

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <label class="block">
                                    <div class="text-sm font-medium text-slate-700">Session no</div>
                                    <input name="sessions[{{ $si }}][session_no]"
                                           value="{{ old("sessions.$si.session_no", $s->session_no) }}"
                                           class="mt-1 w-full rounded-lg border-slate-300"/>
                                </label>

                                <label class="block md:col-span-2">
                                    <div class="text-sm font-medium text-slate-700">Name</div>
                                    <input name="sessions[{{ $si }}][name]"
                                           value="{{ old("sessions.$si.name", $s->name) }}"
                                           class="mt-1 w-full rounded-lg border-slate-300"/>
                                </label>

                                <label class="block">
                                    <div class="text-sm font-medium text-slate-700">Date</div>
                                    <input type="date" name="sessions[{{ $si }}][date]"
                                           value="{{ old("sessions.$si.date", $s->date) }}"
                                           class="mt-1 w-full rounded-lg border-slate-300"/>
                                </label>

                                <label class="block">
                                    <div class="text-sm font-medium text-slate-700">Start time (HH:MM)</div>
                                    <input name="sessions[{{ $si }}][start_time]"
                                           value="{{ old("sessions.$si.start_time", $s->start_time ? substr($s->start_time,0,5) : '') }}"
                                           class="mt-1 w-full rounded-lg border-slate-300"/>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="font-medium">Events</div>
                                <button type="button"
                                        class="rounded-lg px-3 py-2 text-sm font-semibold bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50"
                                        onclick="addEventRow(this, {{ $si }})">
                                    Add event
                                </button>
                            </div>

                            <div class="overflow-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="text-left text-slate-600">
                                    <tr>
                                        <th class="p-2">No</th>
                                        <th class="p-2">Name</th>
                                        <th class="p-2">G</th>
                                        <th class="p-2">Dist</th>
                                        <th class="p-2">Stroke</th>
                                        <th class="p-2">Round</th>
                                        <th class="p-2">Relay</th>
                                        <th class="p-2"></th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100" data-session-events="{{ $si }}">
                                    @foreach ($sessionEvents as $ei => $e)
                                        <tr>
                                            <td class="p-2">
                                                <input type="hidden" name="sessions[{{ $si }}][events][{{ $ei }}][id]"
                                                       value="{{ $e->id }}">
                                                <input name="sessions[{{ $si }}][events][{{ $ei }}][event_no]"
                                                       value="{{ old("sessions.$si.events.$ei.event_no", $e->event_no) }}"
                                                       class="w-20 rounded-lg border-slate-300"/>
                                            </td>
                                            <td class="p-2">
                                                <input name="sessions[{{ $si }}][events][{{ $ei }}][name]"
                                                       value="{{ old("sessions.$si.events.$ei.name", $e->name) }}"
                                                       class="w-96 rounded-lg border-slate-300"/>
                                            </td>
                                            <td class="p-2">
                                                @php($eg = old("sessions.$si.events.$ei.gender", $e->gender))
                                                <select name="sessions[{{ $si }}][events][{{ $ei }}][gender]"
                                                        class="rounded-lg border-slate-300">
                                                    <option value="" @selected($eg==='')>—</option>
                                                    <option value="F" @selected($eg==='F')>F</option>
                                                    <option value="M" @selected($eg==='M')>M</option>
                                                    <option value="X" @selected($eg==='X')>X</option>
                                                </select>
                                            </td>
                                            <td class="p-2">
                                                <input name="sessions[{{ $si }}][events][{{ $ei }}][distance]"
                                                       value="{{ old("sessions.$si.events.$ei.distance", $e->distance) }}"
                                                       class="w-24 rounded-lg border-slate-300"/>
                                            </td>
                                            <td class="p-2">
                                                <input name="sessions[{{ $si }}][events][{{ $ei }}][stroke]"
                                                       value="{{ old("sessions.$si.events.$ei.stroke", $e->stroke) }}"
                                                       class="w-24 rounded-lg border-slate-300"/>
                                            </td>
                                            <td class="p-2">
                                                <input name="sessions[{{ $si }}][events][{{ $ei }}][round]"
                                                       value="{{ old("sessions.$si.events.$ei.round", $e->round) }}"
                                                       class="w-28 rounded-lg border-slate-300"/>
                                            </td>
                                            <td class="p-2">
                                                <input type="checkbox"
                                                       name="sessions[{{ $si }}][events][{{ $ei }}][is_relay]" value="1"
                                                    @checked(old("sessions.$si.events.$ei.is_relay", $e->is_relay)) />
                                            </td>
                                            <td class="p-2 text-right">
                                                <button type="button"
                                                        class="rounded-lg px-3 py-2 text-sm font-semibold bg-rose-600 text-white hover:bg-rose-500"
                                                        onclick="removeRow(this)">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    @endforeach
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-slate-900 text-white hover:bg-slate-800">
                    Save
                </button>
            </div>
        </form>
    </div>

    <script>
        let agIndex = {{ count($ageGroups) }};

        function removeRow(btn, removeBlock = false) {
            const el = removeBlock ? btn.closest('.rounded-lg') : btn.closest('tr');
            if (el) el.remove();
        }

        function addAgeGroupRow() {
            const body = document.getElementById('ageGroupsBody');
            const i = agIndex++;
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td class="p-2"><input name="age_groups[${i}][code]" class="w-28 rounded-lg border-slate-300" /></td>
        <td class="p-2">
            <select name="age_groups[${i}][gender]" class="rounded-lg border-slate-300">
                <option value="">—</option><option value="F">F</option><option value="M">M</option><option value="X">X</option>
            </select>
        </td>
        <td class="p-2"><input name="age_groups[${i}][min_age]" class="w-20 rounded-lg border-slate-300" /></td>
        <td class="p-2"><input name="age_groups[${i}][max_age]" class="w-20 rounded-lg border-slate-300" /></td>
        <td class="p-2"><input name="age_groups[${i}][name]" class="w-80 rounded-lg border-slate-300" /></td>
        <td class="p-2"><input name="age_groups[${i}][handicap]" class="w-28 rounded-lg border-slate-300" /></td>
        <td class="p-2 text-right">
            <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold bg-rose-600 text-white hover:bg-rose-500"
                    onclick="removeRow(this)">Delete</button>
        </td>
    `;
            body.appendChild(tr);
        }

        let sessionIndex = {{ count($sessions) }};

        function addSessionBlock() {
            const wrap = document.getElementById('sessionsWrap');
            const si = sessionIndex++;
            const block = document.createElement('div');
            block.className = 'rounded-lg ring-1 ring-slate-200 p-4 space-y-4';
            block.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="font-semibold">Session</div>
            <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold bg-rose-600 text-white hover:bg-rose-500"
                    onclick="removeRow(this,true)">Delete session</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <label class="block">
                <div class="text-sm font-medium text-slate-700">Session no</div>
                <input name="sessions[${si}][session_no]" class="mt-1 w-full rounded-lg border-slate-300" />
            </label>

            <label class="block md:col-span-2">
                <div class="text-sm font-medium text-slate-700">Name</div>
                <input name="sessions[${si}][name]" class="mt-1 w-full rounded-lg border-slate-300" />
            </label>

            <label class="block">
                <div class="text-sm font-medium text-slate-700">Date</div>
                <input type="date" name="sessions[${si}][date]" class="mt-1 w-full rounded-lg border-slate-300" />
            </label>

            <label class="block">
                <div class="text-sm font-medium text-slate-700">Start time (HH:MM)</div>
                <input name="sessions[${si}][start_time]" class="mt-1 w-full rounded-lg border-slate-300" />
            </label>
        </div>

        <div class="flex items-center justify-between">
            <div class="font-medium">Events</div>
            <button type="button"
                    class="rounded-lg px-3 py-2 text-sm font-semibold bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50"
                    onclick="addEventRow(this, ${si})">
                Add event
            </button>
        </div>

        <div class="overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-600">
                    <tr>
                        <th class="p-2">No</th><th class="p-2">Name</th><th class="p-2">G</th><th class="p-2">Dist</th>
                        <th class="p-2">Stroke</th><th class="p-2">Round</th><th class="p-2">Relay</th><th class="p-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" data-session-events="${si}"></tbody>
            </table>
        </div>
    `;
            wrap.appendChild(block);
        }

        function addEventRow(btn, si) {
            const tbody = btn.closest('.rounded-lg').querySelector(`tbody[data-session-events="${si}"]`);
            const idx = tbody.children.length;
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td class="p-2"><input name="sessions[${si}][events][${idx}][event_no]" class="w-20 rounded-lg border-slate-300" /></td>
        <td class="p-2"><input name="sessions[${si}][events][${idx}][name]" class="w-96 rounded-lg border-slate-300" /></td>
        <td class="p-2">
            <select name="sessions[${si}][events][${idx}][gender]" class="rounded-lg border-slate-300">
                <option value="">—</option><option value="F">F</option><option value="M">M</option><option value="X">X</option>
            </select>
        </td>
        <td class="p-2"><input name="sessions[${si}][events][${idx}][distance]" class="w-24 rounded-lg border-slate-300" /></td>
        <td class="p-2"><input name="sessions[${si}][events][${idx}][stroke]" class="w-24 rounded-lg border-slate-300" /></td>
        <td class="p-2"><input name="sessions[${si}][events][${idx}][round]" class="w-28 rounded-lg border-slate-300" /></td>
        <td class="p-2"><input type="checkbox" name="sessions[${si}][events][${idx}][is_relay]" value="1" /></td>
        <td class="p-2 text-right">
            <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold bg-rose-600 text-white hover:bg-rose-500"
                    onclick="removeRow(this)">Delete</button>
        </td>
    `;
            tbody.appendChild(tr);
        }
    </script>
@endsection
