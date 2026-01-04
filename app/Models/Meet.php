<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function sessions(): HasMany
    {
        return $this->hasMany(MeetSession::class, 'meet_id');
    }

    public function ageGroups(): HasMany
    {
        return $this->hasMany(MeetAgeGroup::class, 'meet_id');
    }

    public function events(): HasManyThrough
    {
        // meet -> meet_sessions (meet_id) -> meet_events (meet_session_id)
        return $this->hasManyThrough(
            MeetEvent::class,
            MeetSession::class,
            'meet_id',             // FK on meet_sessions
            'meet_session_id',  // FK on meet_events
            'id',                 // local key on meets
            'id'            // local key on meet_sessions
        );
    }

    public function importBatches(): HasMany
    {
        return $this->hasMany(ImportBatch::class, 'meet_id');
    }
}
