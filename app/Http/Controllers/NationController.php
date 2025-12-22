<?php

namespace App\Http\Controllers;

use App\Models\Continent;
use App\Models\Nation;
use Illuminate\Http\Request;

class NationController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $continentId = $request->query('continent_id');

        $nations = Nation::query()
            ->with('continent')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('nameEn', 'like', "%{$q}%")
                        ->orWhere('nameDe', 'like', "%{$q}%")
                        ->orWhere('iso2', 'like', "%{$q}%")
                        ->orWhere('iso3', 'like', "%{$q}%")
                        ->orWhere('ioc', 'like', "%{$q}%");
                });
            })
            ->when($continentId, function ($query) use ($continentId) {
                $query->where('continent_id', $continentId);
            })
            ->orderBy('nameEn')
            ->paginate(20)
            ->withQueryString();

        // Für Dropdown
        $continents = Continent::orderBy('nameEn')->get();

        return view('nations.index', [
            'nations' => $nations,
            'continents' => $continents,
            'q' => $q,
            'continentId' => $continentId,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nameEn' => ['required', 'string', 'max:200', 'unique:nations,nameEn'],
            'nameDe' => ['nullable', 'string', 'max:200'],

            'worldAquaNF' => ['nullable', 'string', 'max:250'],
            'worldAquaNFurl' => ['nullable', 'string', 'max:250'],
            'worldParaNF' => ['nullable', 'string', 'max:250'],
            'worldParaNFurl' => ['nullable', 'string', 'max:250'],

            'continent_id' => ['nullable', 'exists:continents,id'],

            'ioc' => ['nullable', 'string', 'size:3', 'unique:nations,ioc'],
            'iso2' => ['nullable', 'string', 'size:2', 'unique:nations,iso2'],
            'iso3' => ['nullable', 'string', 'size:3', 'unique:nations,iso3'],

            'officialNameEn' => ['nullable', 'string', 'max:250'],
            'officialShortEn' => ['nullable', 'string', 'max:250'],
            'officialNameDe' => ['nullable', 'string', 'max:250'],
            'officialShortDe' => ['nullable', 'string', 'max:250'],
            'officialNameCn' => ['nullable', 'string', 'max:250'],
            'officialShortCn' => ['nullable', 'string', 'max:250'],
            'officialNameFr' => ['nullable', 'string', 'max:250'],
            'officialShortFr' => ['nullable', 'string', 'max:250'],
            'officialNameAr' => ['nullable', 'string', 'max:250'],
            'officialShortAr' => ['nullable', 'string', 'max:250'],
            'officialNameRu' => ['nullable', 'string', 'max:250'],
            'officialShortRu' => ['nullable', 'string', 'max:250'],
            'officialNameEs' => ['nullable', 'string', 'max:250'],
            'officialShortEs' => ['nullable', 'string', 'max:250'],

            'subRegionName' => ['nullable', 'string', 'max:250'],
            'tld' => ['nullable', 'string', 'max:20'],
            'currencyAlphabeticCode' => ['nullable', 'string', 'max:20'],
            'currencyName' => ['nullable', 'string', 'max:50'],
            'isIndependent' => ['nullable', 'string', 'max:30'],
            'Capital' => ['nullable', 'string', 'max:250'],
            'IntermediateRegionName' => ['nullable', 'string', 'max:50'],
        ]);

        Nation::create($data);

        return redirect()->route('nations.index')->with('success', 'Nation created.');
    }

    public function create()
    {
        $continents = Continent::orderBy('nameEn')->get();

        return view('nations.create', compact('continents'));
    }

    public function edit(Nation $nation)
    {
        $continents = Continent::orderBy('nameEn')->get();

        return view('nations.edit', compact('nation', 'continents'));
    }

    public function update(Request $request, Nation $nation)
    {
        $data = $request->validate([
            'nameEn' => ['required', 'string', 'max:200', 'unique:nations,nameEn,'.$nation->id],
            'nameDe' => ['nullable', 'string', 'max:200'],

            'worldAquaNF' => ['nullable', 'string', 'max:250'],
            'worldAquaNFurl' => ['nullable', 'string', 'max:250'],
            'worldParaNF' => ['nullable', 'string', 'max:250'],
            'worldParaNFurl' => ['nullable', 'string', 'max:250'],

            'continent_id' => ['nullable', 'exists:continents,id'],

            'ioc' => ['nullable', 'string', 'size:3', 'unique:nations,ioc,'.$nation->id],
            'iso2' => ['nullable', 'string', 'size:2', 'unique:nations,iso2,'.$nation->id],
            'iso3' => ['nullable', 'string', 'size:3', 'unique:nations,iso3,'.$nation->id],

            'officialNameEn' => ['nullable', 'string', 'max:250'],
            'officialShortEn' => ['nullable', 'string', 'max:250'],
            'officialNameDe' => ['nullable', 'string', 'max:250'],
            'officialShortDe' => ['nullable', 'string', 'max:250'],
            'officialNameCn' => ['nullable', 'string', 'max:250'],
            'officialShortCn' => ['nullable', 'string', 'max:250'],
            'officialNameFr' => ['nullable', 'string', 'max:250'],
            'officialShortFr' => ['nullable', 'string', 'max:250'],
            'officialNameAr' => ['nullable', 'string', 'max:250'],
            'officialShortAr' => ['nullable', 'string', 'max:250'],
            'officialNameRu' => ['nullable', 'string', 'max:250'],
            'officialShortRu' => ['nullable', 'string', 'max:250'],
            'officialNameEs' => ['nullable', 'string', 'max:250'],
            'officialShortEs' => ['nullable', 'string', 'max:250'],

            'subRegionName' => ['nullable', 'string', 'max:250'],
            'tld' => ['nullable', 'string', 'max:20'],
            'currencyAlphabeticCode' => ['nullable', 'string', 'max:20'],
            'currencyName' => ['nullable', 'string', 'max:50'],
            'isIndependent' => ['nullable', 'string', 'max:30'],
            'Capital' => ['nullable', 'string', 'max:250'],
            'IntermediateRegionName' => ['nullable', 'string', 'max:50'],
        ]);

        $nation->update($data);

        return redirect()->route('nations.index')->with('success', 'Nation updated.');
    }

    public function destroy(Nation $nation)
    {
        $nation->delete();

        return redirect()->route('nations.index')->with('success', 'Nation deleted.');
    }
}
