<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AthleteSportclassHistory extends Model
{
    protected $fillable = [
        'athlete_id', 'sport_class_id', 'discipline', 'valid_from', 'valid_to',
        'source', 'source_ref', 'meet_id', 'notes',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    public function sportClass(): BelongsTo
    {
        return $this->belongsTo(SportClass::class);
    }

    public function meet(): BelongsTo
    {
        return $this->belongsTo(Meet::class);
    }

    public function scopeCurrent(Builder $q): Builder
    {
        return $q->whereNull('valid_to');
    }

    public function scopeAtDate(Builder $q, CarbonInterface $date): Builder
    {
        return $q->where(function (Builder $q) use ($date) {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', $date->toDateString());
        })->where(function (Builder $q) use ($date) {
            $q->whereNull('valid_to')->orWhere('valid_to', '>=', $date->toDateString());
        });
    }

    public function scopeCurrentFor(Builder $q, string $discipline): Builder
    {
        return $q->where('discipline', $discipline)->whereNull('valid_to');
    }
}
