<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParaSwimStyle\ParaSwimStyleStoreRequest;
use App\Http\Requests\ParaSwimStyle\ParaSwimStyleUpdateRequest;
use App\Http\Resources\ParaSwimStyleResource;
use App\Models\ParaSwimStyle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ParaSwimStyleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $format = $request->string('format')->toString(); // en|de|abbr optional

        $query = ParaSwimStyle::query()
            ->when(
                $request->filled('distance'),
                fn ($q) => $q->where('distance', (int) $request->input('distance'))
            )
            ->when(
                $request->filled('stroke'),
                fn ($q) => $q->where('stroke', strtoupper((string) $request->input('stroke')))
            )
            ->when($request->has('relay_count'), function ($q) use ($request) {
                $relayCount = $request->input('relay_count');

                // Einzel: relay_count fehlt/leer/<=1 -> (relay_count is null OR relay_count <= 1)
                if ($relayCount === null || $relayCount === '' || (int) $relayCount <= 1) {
                    $q->where(function ($qq) {
                        $qq->whereNull('relay_count')
                            ->orWhere('relay_count', '<=', 1);
                    });
                } else {
                    $q->where('relay_count', (int) $relayCount);
                }

                // Staffel: relay_count >= 2
                $q->where('relay_count', (int) $relayCount);
            })
            ->orderByRaw('COALESCE(relay_count, 1) ASC')
            ->orderBy('distance')
            ->orderBy('stroke');

        return ParaSwimStyleResource::collection($query->paginate(50))
            ->additional(['meta' => ['format' => $format !== '' ? $format : null]]);
    }

    public function show(ParaSwimStyle $paraSwimStyle): ParaSwimStyleResource
    {
        return new ParaSwimStyleResource($paraSwimStyle);
    }

    public function store(ParaSwimStyleStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        // key serverseitig setzen/überschreiben (stabil)
        $data['key'] = ParaSwimStyle::makeKey(
            (int) $data['distance'],
            (string) $data['stroke'],
            $data['relay_count'] ?? null
        );

        $style = ParaSwimStyle::create($data);

        return (new ParaSwimStyleResource($style))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(ParaSwimStyleUpdateRequest $request, ParaSwimStyle $paraSwimStyle): ParaSwimStyleResource
    {
        $data = $request->validated();

        $distance = isset($data['distance']) ? (int) $data['distance'] : $paraSwimStyle->distance;
        $stroke = isset($data['stroke']) ? (string) $data['stroke'] : $paraSwimStyle->stroke;
        $relay = array_key_exists('relay_count', $data) ? $data['relay_count'] : $paraSwimStyle->relay_count;

        $data['key'] = ParaSwimStyle::makeKey($distance, $stroke, $relay);

        $paraSwimStyle->update($data);

        return new ParaSwimStyleResource($paraSwimStyle->refresh());
    }

    public function destroy(ParaSwimStyle $paraSwimStyle): JsonResponse
    {
        $paraSwimStyle->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function resolve(Request $request): ParaSwimStyleResource
    {
        $validated = $request->validate([
            'distance' => ['required', 'integer', 'min:1'],
            'stroke' => ['required', 'string', 'max:10'],
            'relay_count' => ['nullable', 'integer', 'min:2'],
        ]);

        $key = ParaSwimStyle::makeKey(
            (int) $validated['distance'],
            (string) $validated['stroke'],
            $validated['relay_count'] ?? null
        );

        $style = ParaSwimStyle::where('key', $key)->firstOrFail();

        return new ParaSwimStyleResource($style);
    }

    public function byKey(string $key)
    {
        $style = ParaSwimStyle::where('key', $key)->firstOrFail();

        return new ParaSwimStyleResource($style);
    }
}
