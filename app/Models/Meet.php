<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meet extends Model
{
    protected $fillable = [
        'name', 'start_date', 'end_date', 'facility_id', 'course', 'age_date',
        'contact_name', 'contact_email', 'contact_phone', 'fees_json', 'qualify_json',
        'source_filename', 'source_hash',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'age_date' => 'date',
        'fees_json' => 'array',
        'qualify_json' => 'array',
    ];
}
