<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MeetEvent extends Model
{
    public $timestamps = false;

    protected $table = 'meet_events';

    protected $fillable = [
        'meet_session_id',
        'event_no',
        'name',
        'gender',
        'distance',
        'stroke',
        'round',
        'is_relay',
        'meet_age_group_id',
    ];

    public function meetSession(): BelongsTo
    {
        return $this->belongsTo(MeetSession::class, 'meet_session_id');
    }

    /**
     * Canonical age-group assignment for events.
     * IMPORTANT: Pivot table age_group_event is the source of truth.
     * meet_events.meet_age_group_id is legacy/read-only (kept for backward compatibility).
     */
    public function meetAgeGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            MeetAgeGroup::class,
            'age_group_event',
            'meet_event_id',
            'age_group_id');
    }
}
