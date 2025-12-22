<div class="space-y-8">

    <div>
        <h3 class="text-sm font-semibold text-slate-900">Basic</h3>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-slate-700">Name (EN)</label>
                <input name="nameEn" value="{{ old('nameEn', $nation->nameEn ?? '') }}" maxlength="200" required
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Name (DE)</label>
                <input name="nameDe" value="{{ old('nameDe', $nation->nameDe ?? '') }}" maxlength="200
                       "
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-700">Continent</label>
                <select name="continent_id"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
                    <option value="">—</option>
                    @foreach($continents as $c)
                        <option
                            value="{{ $c->id }}" @selected(old('continent_id', $nation->continent_id ?? null) == $c->id)>
                            {{ $c->nameEn }} ({{ $c->code }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-sm font-semibold text-slate-900">Codes</h3>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium text-slate-700">IOC</label>
                <input name="ioc" value="{{ old('ioc', $nation->ioc ?? '') }}" maxlength="3"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">ISO2</label>
                <input name="iso2" value="{{ old('iso2', $nation->iso2 ?? '') }}" maxlength="2"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">ISO3</label>
                <input name="iso3" value="{{ old('iso3', $nation->iso3 ?? '') }}" maxlength="3"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-sm font-semibold text-slate-900">Federations</h3>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-slate-700">worldAquaNF</label>
                <input name="worldAquaNF" value="{{ old('worldAquaNF', $nation->worldAquaNF ?? '') }}" maxlength="250"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">worldAquaNFurl</label>
                <input name="worldAquaNFurl" value="{{ old('worldAquaNFurl', $nation->worldAquaNFurl ?? '') }}"
                       maxlength="250"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">worldParaNF</label>
                <input name="worldParaNF" value="{{ old('worldParaNF', $nation->worldParaNF ?? '') }}" maxlength="250"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">worldParaNFurl</label>
                <input name="worldParaNFurl" value="{{ old('worldParaNFurl', $nation->worldParaNFurl ?? '') }}"
                       maxlength="250"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
        </div>
    </div>

    <div>
        <h3 class="text-sm font-semibold text-slate-900">Other</h3>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-slate-700">tld</label>
                <input name="tld" value="{{ old('tld', $nation->tld ?? '') }}" maxlength="20"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Capital</label>
                <input name="Capital" value="{{ old('Capital', $nation->Capital ?? '') }}" maxlength="250"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            </div>
        </div>
    </div>

</div>
