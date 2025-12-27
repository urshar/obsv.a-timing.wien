@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto p-6 space-y-6">

        <x-layout.validation-errors/>
        <x-layout.import-alert/>

        <div class="flex items-start justify-between gap-3">
            <x-ui.page-title
                title="LENEX Batch #{{ $batch->id }}"
                subtitle="Readonly audit view (committed)."
            />
            <div class="flex items-center gap-2">
                <x-ui.button href="{{ route('imports.lenex.history') }}">
                    Back
                </x-ui.button>
            </div>
        </div>

        <x-ui.card>
            <x-ui.card-header>
                <div class="text-lg font-semibold">Batch Details</div>
            </x-ui.card-header>

            <x-ui.card-body>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</div>
                        <div class="mt-1">
                            <x-ui.badge level="success">{{ $batch->status }}</x-ui.badge>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Type</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">{{ $batch->type ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Created at</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">
                            {{ optional($batch->created_at)->format('Y-m-d H:i') }}
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Filename</div>
                        <div
                            class="mt-1 text-sm font-medium text-slate-900 wrap-break-word">{{ $batch->filename }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Meet ID</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">
                            @if($batch->meet_id)
                                #{{ $batch->meet_id }}
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Clubs</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">
                            {{ data_get($batch->summary_json, 'counts.clubs', 0) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Athletes</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">
                            {{ data_get($batch->summary_json, 'counts.athletes', 0) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Staffeln</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">
                            {{ $batch->relay_count }}
                        </div>
                    </div>
                </div>

                @if(!empty($batch->summary_json))
                    <div class="mt-6 pt-4 border-t border-slate-200">
                        <div class="text-sm font-semibold text-slate-900">Summary</div>
                        <pre
                            class="mt-2 text-xs bg-slate-50 border border-slate-200 rounded-xl p-3 overflow-x-auto">{{ json_encode($batch->summary_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </x-ui.card-body>
        </x-ui.card>

    </div>
@endsection
