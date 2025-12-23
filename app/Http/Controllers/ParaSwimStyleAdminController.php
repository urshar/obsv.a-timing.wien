<?php

namespace App\Http\Controllers;

use App\Models\ParaSwimStyle;
use Illuminate\Http\Request;

class ParaSwimStyleAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $stroke = strtoupper(trim((string) $request->input('stroke', '')));
        $distance = $request->input('distance');
        $relay = $request->input('relay'); // '', 'individual', 'relay'

        $styles = ParaSwimStyle::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('key', 'like', "%{$q}%")
                        ->orWhere('stroke', 'like', "%{$q}%")
                        ->orWhere('stroke_name_en', 'like', "%{$q}%")
                        ->orWhere('stroke_name_de', 'like', "%{$q}%")
                        ->orWhere('abbreviation', 'like', "%{$q}%");
                });
            })
            ->when($stroke !== '', fn ($query) => $query->where('stroke', $stroke))
            ->when(! empty($distance), fn ($query) => $query->where('distance', (int) $distance))
            ->when($relay === 'individual', function ($query) {
                $query->where(function ($qq) {
                    $qq->whereNull('relay_count')->orWhere('relay_count', '<=', 1);
                });
            })
            ->when($relay === 'relay', fn ($query) => $query->whereNotNull('relay_count')->where('relay_count', '>', 1))
            ->orderByRaw('COALESCE(relay_count, 1) ASC')
            ->orderBy('distance')
            ->orderBy('stroke')
            ->paginate(15)
            ->withQueryString();

        return view('para-swim-styles.index', [
            'styles' => $styles,
            'q' => $q,
            'stroke' => $stroke,
            'distance' => $distance,
            'relay' => $relay,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        // Key serverseitig stabil erzeugen
        $data['key'] = ParaSwimStyle::makeKey(
            $data['distance'],
            $data['stroke'],
            $data['relay_count'] ?? null
        );

        ParaSwimStyle::create($data);

        return redirect()
            ->route('para-swim-styles.index')
            ->with('status', 'Para swim style created.');
    }

    private function validatedData(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'relay_count' => ['nullable', 'integer', 'min:2'],
            'distance' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'min:1'],
            'stroke' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:10'],

            'stroke_name_en' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'stroke_name_de' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'abbreviation' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:10'],
        ];

        $data = $request->validate($rules);

        // Normalisieren (hilft dir auch beim Key)
        if (isset($data['stroke'])) {
            $data['stroke'] = strtoupper(trim((string) $data['stroke']));
        }

        // Leerstring -> null (z.B. wenn Feld leer gelassen wird)
        if (array_key_exists('relay_count', $data) && ($data['relay_count'] === '' || $data['relay_count'] === null)) {
            $data['relay_count'] = null;
        }

        return $data;
    }

    public function create()
    {
        return view('para-swim-styles.create');
    }

    public function edit(ParaSwimStyle $para_swim_style)
    {
        return view('para-swim-styles.edit', [
            'style' => $para_swim_style,
        ]);
    }

    public function update(Request $request, ParaSwimStyle $para_swim_style)
    {
        $data = $this->validatedData($request, isUpdate: true);

        $distance = $data['distance'] ?? $para_swim_style->distance;
        $stroke = $data['stroke'] ?? $para_swim_style->stroke;
        $relay = array_key_exists('relay_count', $data) ? $data['relay_count'] : $para_swim_style->relay_count;

        $data['key'] = ParaSwimStyle::makeKey($distance, $stroke, $relay);

        $para_swim_style->update($data);

        return redirect()
            ->route('para-swim-styles.index')
            ->with('status', 'Para swim style updated.');
    }

    public function destroy(ParaSwimStyle $para_swim_style)
    {
        $para_swim_style->delete();

        return redirect()
            ->route('para-swim-styles.index')
            ->with('status', 'Para swim style deleted.');
    }
}
