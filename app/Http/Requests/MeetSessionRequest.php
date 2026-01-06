<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization erfolgt über Routing/Controller (meet ownership)
        return true;
    }

    public function rules(): array
    {
        return [
            'session_no' => ['required', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
