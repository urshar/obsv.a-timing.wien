<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function sportclassHistories(): HasMany
    {
        return $this->hasMany(AthleteSportclassHistory::class);
    }

    public function currentSportclassHistory(): HasOne
    {
        return $this->hasOne(AthleteSportclassHistory::class)->whereNull('valid_to');
    }

    // optional Convenience
    public function currentSportClass(): ?SportClass
    {
        return $this->currentSportclassHistory?->sportClass;
    }
}
