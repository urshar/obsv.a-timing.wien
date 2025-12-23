<?php

namespace App\Http\Requests\ParaSwimStyle;

use Illuminate\Foundation\Http\FormRequest;

class ParaSwimStyleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relay_count' => ['nullable', 'integer', 'min:2'],
            'distance' => ['sometimes', 'integer', 'min:1'],
            'stroke' => ['sometimes', 'string', 'max:10'],

            'stroke_name_en' => ['sometimes', 'string', 'max:255'],
            'stroke_name_de' => ['sometimes', 'string', 'max:255'],
            'abbreviation' => ['sometimes', 'string', 'max:10'],
        ];
    }
}
