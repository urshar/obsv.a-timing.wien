<?php

namespace App\Http\Controllers;

use App\Models\Continent;
use Illuminate\Http\Request;

class ContinentController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $continents = Continent::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('code', 'like', "%$q%")
                    ->orWhere('nameEn', 'like', "%$q%")
                    ->orWhere('nameDe', 'like', "%$q%");
            })
            ->orderBy('nameEn')
            ->paginate(20)
            ->appends($request->query());

        return view('continents.index', compact('continents', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:continents,code'],
            'nameEn' => ['required', 'string', 'max:200'],
            'nameDe' => ['nullable', 'string', 'max:200'],
        ]);

        Continent::create($data);

        return redirect()->route('continents.index')->with('success', 'Continent created.');
    }

    public function create()
    {
        return view('continents.create');
    }

    public function edit(Continent $continent)
    {
        return view('continents.edit', compact('continent'));
    }

    public function update(Request $request, Continent $continent)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:continents,code,'.$continent->id],
            'nameEn' => ['required', 'string', 'max:200'],
            'nameDe' => ['nullable', 'string', 'max:200'],
        ]);

        $continent->update($data);

        return redirect()->route('continents.index')->with('success', 'Continent updated.');
    }

    public function destroy(Continent $continent)
    {
        $continent->delete();

        return redirect()->route('continents.index')->with('success', 'Continent deleted.');
    }
}
