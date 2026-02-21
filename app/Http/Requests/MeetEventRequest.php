<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_no' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],

            'gender' => ['nullable', 'in:M,F,X'],

            // distance bleibt optional (du kannst sie aus style ableiten – später)
            'distance' => ['nullable', 'integer', 'min:1'],

            // stroke wird jetzt ParaSwimStyle.key
            'stroke' => ['nullable', 'string', 'max:50', 'exists:swim_styles,key'],

            'round' => ['nullable', 'string', 'max:50'],
            'is_relay' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $gender = $this->input('gender');

        $this->merge([
            'gender' => $gender === '' ? null : $gender,
            'is_relay' => $this->boolean('is_relay'),
        ]);
    }
}
