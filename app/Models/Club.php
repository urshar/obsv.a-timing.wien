<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $fillable = [
        'nation_id',
        'name',
        'short_name',
        'officials_only',
    ];
}
