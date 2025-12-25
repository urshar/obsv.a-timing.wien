<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Athlete extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'birth_year',
        'birthdate',
        'external_splash_id',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];
}
