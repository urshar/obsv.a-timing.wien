@props([
    'issues' => [],
    'title' => 'Issues',
    'compact' => false,
])

@if(!empty($issues))
    <div class="{{ $compact ? '' : 'mt-4' }}">
        <div class="text-sm font-semibold mb-2">{{ $title }}</div>

        <ul class="{{ $compact ? 'space-y-1' : 'space-y-2' }} text-sm">
            @foreach($issues as $i)
                @php
                    $level = is_array($i) ? ($i['level'] ?? 'warning') : 'warning';
                    $code  = is_array($i) ? ($i['code'] ?? '') : '';
                    $msg   = is_array($i) ? ($i['message'] ?? '') : (string)$i;
                @endphp

                <li class="flex items-start gap-2">
                    <x-ui.badge :level="$level">{{ strtoupper($level) }}</x-ui.badge>

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
