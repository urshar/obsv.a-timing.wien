<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Continent extends Model
{
    protected $fillable = [
        'code', 'nameEn', 'nameDe',
    ];

    public function nations(): HasMany
    {
        return $this->hasMany(Nation::class);
    }
}
