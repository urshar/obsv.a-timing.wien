<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'start_date' => ['nullable', 'date', 'required_with:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'age_date' => ['nullable', 'date'],

            'course' => ['nullable', 'string', 'max:20'],

            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],

            'facility_id' => ['nullable', 'integer', 'exists:facilities,id'],
        ];
    }
}
