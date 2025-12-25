<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportIssue extends Model
{
    protected $fillable = [
        'import_batch_id', 'entity_type', 'entity_key', 'severity', 'message', 'payload_json', 'suggestions_json',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'suggestions_json' => 'array',
    ];
}
