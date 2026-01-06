@php use App\Support\UiFormat; @endphp
@props([
    'start' => null,
    'end' => null,

    // formats
    'sameDayFormat' => 'd.m.Y',
    'leftFormat' => 'd.m.',
    'rightFormat' => 'd.m.Y',

    'fallback' => '—',
])

{{ UiFormat::dateRange($start, $end, $sameDayFormat, $leftFormat, $rightFormat, $fallback) }}
