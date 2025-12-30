{{-- Session template --}}
<template id="tpl-session">
    <div class="rounded-xl ring-1 ring-slate-200 bg-white" data-session>
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <div class="font-semibold text-slate-900">Session</div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-600">No</span>
                    <input class="w-20 rounded-lg border-slate-300"
                           name="sessions[__SI__][session_no]"
                           value="">
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-600">Date</span>
                    <input class="w-40 rounded-lg border-slate-300"
                           name="sessions[__SI__][date]"
                           placeholder="YYYY-MM-DD"
                           value="">
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-600">Start</span>
                    <input class="w-32 rounded-lg border-slate-300"
                           name="sessions[__SI__][start_time]"
                           placeholder="HH:MM"
                           value="">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        class="btn-add-event inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-white text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                    Add event
                </button>

                <button type="button"
                        class="btn-remove-session inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-rose-600 text-white hover:bg-rose-500">
                    Remove
                </button>
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
                    {{-- rows via tpl-event --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

{{-- Event row template --}}
<template id="tpl-event">
    <tr data-event>
        <td class="py-2 pr-2">
            <input class="w-20 rounded-lg border-slate-300"
                   name="sessions[__SI__][events][__EI__][event_no]"
                   value="">
        </td>

        <td class="py-2 pr-2">
            <input class="w-96 rounded-lg border-slate-300"
                   name="sessions[__SI__][events][__EI__][name]"
                   value="">
        </td>

        <td class="py-2 pr-2">
            <select class="w-24 rounded-lg border-slate-300"
                    name="sessions[__SI__][events][__EI__][gender]">
                <option value="">—</option>
                <option value="F">F</option>
                <option value="M">M</option>
                <option value="X">X</option>
            </select>
        </td>

        <td class="py-2 pr-2">
            <input class="w-24 rounded-lg border-slate-300"
                   name="sessions[__SI__][events][__EI__][distance]"
                   value="">
        </td>

        <td class="py-2 pr-2">
            <input class="w-28 rounded-lg border-slate-300"
                   name="sessions[__SI__][events][__EI__][stroke]"
                   value="">
        </td>

        <td class="py-2 pr-2">
            <input class="w-28 rounded-lg border-slate-300"
                   name="sessions[__SI__][events][__EI__][round]"
                   value="">
        </td>

        <td class="py-2 pr-2">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox"
                       class="rounded border-slate-300"
                       name="sessions[__SI__][events][__EI__][is_relay]"
                       value="1">
                <span>Relay</span>
            </label>
        </td>

        <td class="py-2 pr-2">
            <select class="w-64 rounded-lg border-slate-300"
                    name="sessions[__SI__][events][__EI__][meet_age_group_id]">
                <option value="">—</option>
                {{-- Optionen werden serverseitig “hinein-injiziert” (siehe JS) --}}
            </select>
        </td>

        <td class="py-2 text-right">
            <button type="button"
                    class="btn-remove-event inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold bg-rose-600 text-white hover:bg-rose-500">
                Remove
            </button>
        </td>
    </tr>
</template>
