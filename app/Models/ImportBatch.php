<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $fillable = [
        'type', 'filename', 'file_hash', 'status', 'meet_id', 'summary_json',
    ];

    protected $casts = [
        'summary_json' => 'array',
    ];

    public function issues(): HasMany
    {
        return $this->hasMany(ImportIssue::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(ImportMapping::class);
    }
}
