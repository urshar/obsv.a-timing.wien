@php
    /** @var ParaSwimStyle|null $style */

    use App\Models\ParaSwimStyle;$strokeOptions = [
        'FR' => 'Freestyle',
        'BK' => 'Backstroke',
        'BR' => 'Breaststroke',
        'FL' => 'Butterfly',
        'IM' => 'Medley',
    ];

    $relayValue = old('relay_count', $style?->relay_count);
    $distanceValue = old('distance', $style?->distance);
    $strokeValue = old('stroke', $style?->stroke);
    $nameEnValue = old('stroke_name_en', $style?->stroke_name_en);
    $nameDeValue = old('stroke_name_de', $style?->stroke_name_de);
    $abbrValue   = old('abbreviation', $style?->abbreviation);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

        <x-ui.field label="Relay count" name="relay_count" hint="Leave empty for individual events."
                    help="For relays use 4 (e.g. 4x50m).">
            <x-ui.input
                name="relay_count"
                :value="$relayValue"
                placeholder="e.g. 4"
            />
        </x-ui.field>

        <x-ui.field label="Distance (m)" name="distance" hint="Meters (e.g. 25, 50, 100, 150, 200, 400...).">
            <x-ui.input
                name="distance"
                :value="$distanceValue"
                placeholder="e.g. 100"
            />
        </x-ui.field>

        <x-ui.field label="Stroke (LENEX code)" name="stroke" hint="FR, BK, BR, FL, IM">
            <x-ui.select name="stroke">
                <option value="">Select...</option>
                @foreach($strokeOptions as $code => $label)
                    <option value="{{ $code }}" @selected(strtoupper((string)$strokeValue) === $code)>
                        {{ $code }} — {{ $label }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.field>

        <x-ui.field label="Abbreviation" name="abbreviation" hint="Short display (e.g. Fr, Bk, Br, Fl, IM).">
            <x-ui.input
                name="abbreviation"
                :value="$abbrValue"
                placeholder="e.g. Fr"
            />
        </x-ui.field>

        <x-ui.field label="Stroke name (EN)" name="stroke_name_en">
            <x-ui.input
                name="stroke_name_en"
                :value="$nameEnValue"
                placeholder="e.g. Freestyle"
            />
        </x-ui.field>

        <x-ui.field label="Stroke name (DE)" name="stroke_name_de">
            <x-ui.input
                name="stroke_name_de"
                :value="$nameDeValue"
                placeholder="e.g. Freistil"
            />
        </x-ui.field>

    </div>

    @if($style)
        <div class="text-sm text-slate-600">
            <span class="font-medium">Current key:</span>
            <span class="font-mono">{{ $style->key }}</span>
            <span class="ml-2 text-slate-500">(Key is generated automatically from distance/stroke/relay.)</span>
        </div>
    @endif

    <div class="flex items-center justify-end gap-2">
        <a href="{{ $cancelUrl }}">
            <x-ui.button type="button" variant="ghost">Cancel</x-ui.button>
        </a>
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
    </div>
</form>
