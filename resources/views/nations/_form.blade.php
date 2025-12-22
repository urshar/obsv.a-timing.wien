@props([
    'nation' => null,
    'continents' => [],
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-ui.field label="Name (EN)" name="nameEn">
            <x-ui.input name="nameEn" :value="$nation->nameEn ?? ''" required/>
        </x-ui.field>

        <x-ui.field label="Name (DE)" name="nameDe">
            <x-ui.input name="nameDe" :value="$nation->nameDe ?? ''"/>
        </x-ui.field>
    </div>

    <x-ui.field label="Continent" name="continent_id">
        <x-ui.select name="continent_id">
            <option value="">—</option>
            @foreach($continents as $c)
                <option value="{{ $c->id }}"
                    @selected(old('continent_id', $nation->continent_id ?? null) == $c->id)>
                    {{ $c->nameEn }} ({{ $c->code }})
                </option>
            @endforeach
        </x-ui.select>
    </x-ui.field>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-ui.field label="ISO2" name="iso2">
            <x-ui.input name="iso2" :value="$nation->iso2 ?? ''" maxlength="2"/>
        </x-ui.field>

        <x-ui.field label="ISO3" name="iso3">
            <x-ui.input name="iso3" :value="$nation->iso3 ?? ''" maxlength="3"/>
        </x-ui.field>

        <x-ui.field label="IOC" name="ioc">
            <x-ui.input name="ioc" :value="$nation->ioc ?? ''" maxlength="3"/>
        </x-ui.field>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ $cancelUrl }}">
            <x-ui.button variant="secondary">Cancel</x-ui.button>
        </a>
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
    </div>
</form>
