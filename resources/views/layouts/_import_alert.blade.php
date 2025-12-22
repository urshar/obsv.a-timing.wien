@if(session('import_summary'))
    @php
        $status = session('import_status', 'success'); // success|warning|error
        $summary = session('import_summary', []);
        $issues = session('import_issues', []);

        $map = [
            'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
            'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
            'error'   => 'border-rose-200 bg-rose-50 text-rose-900',
        ];
        $cls = $map[$status] ?? $map['success'];

        $title = $summary['title'] ?? 'Import result';

        $badge = match($status) {
            'error' => 'bg-rose-100 text-rose-900',
            'warning' => 'bg-amber-100 text-amber-900',
            default => 'bg-emerald-100 text-emerald-900',
        };

        $inserted = (int)($summary['inserted'] ?? 0);
        $updated  = (int)($summary['updated'] ?? 0);
        $skipped  = (int)($summary['skipped'] ?? 0);
    @endphp

    <div class="mb-6 rounded-xl border px-4 py-4 {{ $cls }}">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="font-semibold">{{ $title }}</div>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-lg bg-white/60 border border-slate-200 px-3 py-2">
                        <div class="text-xs text-slate-600">Inserted</div>
                        <div class="font-semibold text-slate-900">{{ $inserted }}</div>
                    </div>
                    <div class="rounded-lg bg-white/60 border border-slate-200 px-3 py-2">
                        <div class="text-xs text-slate-600">Updated</div>
                        <div class="font-semibold text-slate-900">{{ $updated }}</div>
                    </div>
                    <div class="rounded-lg bg-white/60 border border-slate-200 px-3 py-2">
                        <div class="text-xs text-slate-600">Skipped</div>
                        <div class="font-semibold text-slate-900">{{ $skipped }}</div>
                    </div>
                </div>
            </div>

            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold {{ $badge }}">
                {{ strtoupper($status) }}
            </span>
        </div>

        @include('imports._issue_list', ['issues' => $issues, 'title' => 'Issues'])
    </div>
@endif
