@props([
    'issues' => [],
    'title' => 'Issues',
    'compact' => false, // if true, smaller spacing
])

@if(!empty($issues))
    <div class="{{ $compact ? '' : 'mt-4' }}">
        <div class="text-sm font-semibold mb-2">{{ $title }}</div>

        <ul class="{{ $compact ? 'space-y-1' : 'space-y-2' }} text-sm">
            @foreach($issues as $i)
                @php
                    // Supports both structured issues and fallback strings
                    $level = is_array($i) ? ($i['level'] ?? 'warning') : 'warning';
                    $code  = is_array($i) ? ($i['code'] ?? '') : '';
                    $msg   = is_array($i) ? ($i['message'] ?? '') : (string)$i;

                    $ibadge = match($level) {
                        'error' => 'bg-rose-100 text-rose-900',
                        'info' => 'bg-slate-100 text-slate-900',
                        default => 'bg-amber-100 text-amber-900',
                    };
                @endphp

                <li class="flex items-start gap-2">
                    <span
                        class="mt-0.5 inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $ibadge }}">
                        {{ strtoupper($level) }}
                    </span>

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
