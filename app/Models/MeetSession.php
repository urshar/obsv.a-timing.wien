<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetSession extends Model
{
    public $timestamps = false;

    protected $table = 'meet_sessions';

    protected $fillable = [
        'meet_id',
        'session_no',
        'name',
        'date',
        'start_time',
    ];

    public function meetEvents(): HasMany
    {
        return $this->hasMany(MeetEvent::class, 'meet_session_id');
    }
}
