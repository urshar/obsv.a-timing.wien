<?php

namespace App\Http\Resources;

use App\Models\ParaSwimStyle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ParaSwimStyle
 */
class ParaSwimStyleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $format = $request->string('format')->toString(); // en|de|abbr

        /** @var ParaSwimStyle $style */
        $style = $this->resource;

        return [
            'id' => $style->id,
            'key' => $style->key,

            'relay_count' => $style->relay_count,
            'distance' => $style->distance,
            'stroke' => $style->stroke,

            'stroke_name_en' => $style->stroke_name_en,
            'stroke_name_de' => $style->stroke_name_de,
            'abbreviation' => $style->abbreviation,

            // display() ist im Model, daher am Model aufrufen
            'display' => $format !== '' ? $style->display($format) : null,

            'created_at' => $style->created_at?->toISOString(),
            'updated_at' => $style->updated_at?->toISOString(),
        ];
    }
}
