<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700">Nation</label>
        <select name="nation_id" required
                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
            <option value="">—</option>
            @foreach($nations as $n)
                <option value="{{ $n->id }}" @selected(old('nation_id', $region->nation_id ?? null) == $n->id)>
                    {{ $n->nameEn }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700">Name (EN)</label>
        <input name="nameEn" value="{{ old('nameEn', $region->nameEn ?? '') }}" maxlength="250" required
               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">Name (DE)</label>
        <input name="nameDe" value="{{ old('nameDe', $region->nameDe ?? '') }}" maxlength="250"
               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700">LSV Code</label>
        <input name="lsvCode" value="{{ old('lsvCode', $region->lsvCode ?? '') }}" maxlength="50"
               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">BSV Code</label>
        <input name="bsvCode" value="{{ old('bsvCode', $region->bsvCode ?? '') }}" maxlength="50"
               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700">ISO SubRegion Code</label>
        <input name="isoSubRegionCode" value="{{ old('isoSubRegionCode', $region->isoSubRegionCode ?? '') }}"
               maxlength="50"
               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">Abbreviation</label>
        <input name="abbreviation" value="{{ old('abbreviation', $region->abbreviation ?? '') }}" maxlength="20"
               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
    </div>
</div>
