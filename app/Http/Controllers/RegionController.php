<?php

namespace App\Http\Controllers;

use App\Models\Nation;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $nationId = $request->query('nation_id');

        $nations = Nation::orderBy('nameEn')->get();

        $regions = Region::query()
            ->with('nation')
            ->when($nationId, fn ($query) => $query->where('nation_id', $nationId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('nameEn', 'like', "%$q%")
                        ->orWhere('nameDe', 'like', "%$q%")
                        ->orWhere('lsvCode', 'like', "%$q%")
                        ->orWhere('bsvCode', 'like', "%$q%")
                        ->orWhere('isoSubRegionCode', 'like', "%$q%")
                        ->orWhere('abbreviation', 'like', "%$q%");
                });
            })
            ->orderBy('nameEn')
            ->paginate(25)
            ->appends($request->query());

        return view('regions.index', compact('regions', 'nations', 'q', 'nationId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nation_id' => ['required', 'exists:nations,id'],
            'nameEn' => ['required', 'string', 'max:250'],
            'nameDe' => ['nullable', 'string', 'max:250'],
            'lsvCode' => ['nullable', 'string', 'max:50'],
            'bsvCode' => ['nullable', 'string', 'max:50'],
            'isoSubRegionCode' => ['nullable', 'string', 'max:50'],
            'abbreviation' => ['nullable', 'string', 'max:20'],
        ]);

        Region::create($data);

        return redirect()->route('regions.index')->with('success', 'Region created.');
    }

    public function create()
    {
        $nations = Nation::orderBy('nameEn')->get();

        return view('regions.create', compact('nations'));
    }

    public function edit(Region $region)
    {
        $nations = Nation::orderBy('nameEn')->get();

        return view('regions.edit', compact('region', 'nations'));
    }

    public function update(Request $request, Region $region)
    {
        $data = $request->validate([
            'nation_id' => ['required', 'exists:nations,id'],
            'nameEn' => ['required', 'string', 'max:250'],
            'nameDe' => ['nullable', 'string', 'max:250'],
            'lsvCode' => ['nullable', 'string', 'max:50'],
            'bsvCode' => ['nullable', 'string', 'max:50'],
            'isoSubRegionCode' => ['nullable', 'string', 'max:50'],
            'abbreviation' => ['nullable', 'string', 'max:20'],
        ]);

        $region->update($data);

        return redirect()->route('regions.index')->with('success', 'Region updated.');
    }

    public function destroy(Region $region)
    {
        $region->delete();

        return redirect()->route('regions.index')->with('success', 'Region deleted.');
    }
}
