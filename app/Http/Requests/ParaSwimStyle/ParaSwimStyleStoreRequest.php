<?php

namespace App\Http\Requests\ParaSwimStyle;

use Illuminate\Foundation\Http\FormRequest;

class ParaSwimStyleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // später ggf. Policy
    }

    public function rules(): array
    {
        return [
            'relay_count' => ['nullable', 'integer', 'min:2'], // null/kein Wert = Einzel
            'distance' => ['required', 'integer', 'min:1'],
            'stroke' => ['required', 'string', 'max:10'],

            'stroke_name_en' => ['required', 'string', 'max:255'],
            'stroke_name_de' => ['required', 'string', 'max:255'],
            'abbreviation' => ['required', 'string', 'max:10'],
        ];
    }
}
