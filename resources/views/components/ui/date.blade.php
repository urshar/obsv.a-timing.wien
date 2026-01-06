@props([
    'value' => null,
    'format' => 'd.m.Y',
    'fallback' => '—',
])

{{ \App\Support\UiFormat::date($value, $format, $fallback) }}
