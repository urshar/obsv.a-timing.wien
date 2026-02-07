<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MeetAgeGroup extends Model
{
    public $timestamps = false;

    protected $table = 'meet_age_groups';

    protected $fillable = [
        'meet_id',
        'name',
        'gender',
        'handicap',
        'code',
        'min_age',
        'max_age',
    ];

    /**
     * Eine MeetAgeGroup gehört zu vielen MeetEvents (Pivot-Tabelle age_group_event).
     */
    public function meetEvents(): BelongsToMany
    {
        return $this->belongsToMany(MeetEvent::class, 'age_group_event', 'age_group_id', 'meet_event_id');
    }
}
