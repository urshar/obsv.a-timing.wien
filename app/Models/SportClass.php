<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SportClass extends Model
{
    protected $fillable = [
        'code', 'discipline', 'label', 'is_active',
    ];

    public function athleteHistories(): HasMany
    {
        return $this->hasMany(AthleteSportclassHistory::class);
    }
}
