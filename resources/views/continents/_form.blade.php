<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="text-sm font-medium text-slate-700">Code</label>
        <input name="code" value="{{ old('code', $continent->code ?? '') }}" maxlength="10" required
               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
        <p class="mt-1 text-xs text-slate-500">Example: EU, AF, AS</p>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700">Name (EN)</label>
        <input name="nameEn" value="{{ old('nameEn', $continent->nameEn ?? '') }}" maxlength="200" required
               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700">Name (DE)</label>
        <input name="nameDe" value="{{ old('nameDe', $continent->nameDe ?? '') }}" maxlength="200
               "
               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-900 focus:ring-0">
    </div>
</div>
