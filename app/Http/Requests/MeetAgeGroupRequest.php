<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetAgeGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:M,F,X'],
            'code' => ['nullable', 'string', 'max:50'],

            'min_age' => ['nullable', 'integer', 'min:0', 'max:200'],
            'max_age' => ['nullable', 'integer', 'min:0', 'max:200'],

            // Handicap/Sport Classes: bewusst string (z.B. "1,2,3" oder "14")
            'handicap' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $gender = $this->input('gender');

        $this->merge([
            'gender' => ($gender === '' ? null : $gender),
        ]);
    }
}
