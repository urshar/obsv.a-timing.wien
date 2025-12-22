@props([
    'region' => null,
    'nations' => [],
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save',
    'cancelUrl',
])

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.field label="Nation" name="nation_id">
        <x-ui.select name="nation_id" required>
            <option value="">—</option>
            @foreach($nations as $n)
                <option value="{{ $n->id }}"
                    @selected(old('nation_id', $region->nation_id ?? null) == $n->id)>
                    {{ $n->nameEn }}
                </option>
            @endforeach
        </x-ui.select>
    </x-ui.field>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-ui.field label="Name (EN)" name="nameEn">
            <x-ui.input name="nameEn" :value="$region->nameEn ?? ''" required/>
        </x-ui.field>

        <x-ui.field label="Name (DE)" name="nameDe">
            <x-ui.input name="nameDe" :value="$region->nameDe ?? ''"/>
        </x-ui.field>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-ui.field label="Abbreviation" name="abbreviation">
            <x-ui.input name="abbreviation" :value="$region->abbreviation ?? ''"/>
        </x-ui.field>

        <x-ui.field label="LSV Code" name="lsvCode">
            <x-ui.input name="lsvCode" :value="$region->lsvCode ?? ''"/>
        </x-ui.field>

        <x-ui.field label="BSV Code" name="bsvCode">
            <x-ui.input name="bsvCode" :value="$region->bsvCode ?? ''"/>
        </x-ui.field>

        <x-ui.field label="ISO SubRegion" name="isoSubRegionCode">
            <x-ui.input name="isoSubRegionCode" :value="$region->isoSubRegionCode ?? ''"/>
        </x-ui.field>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ $cancelUrl }}">
            <x-ui.button variant="secondary">Cancel</x-ui.button>
        </a>
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
    </div>
</form>
