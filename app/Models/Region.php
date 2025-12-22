<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Region extends Model
{
    protected $fillable = [
        'nation_id', 'nameEn', 'nameDe', 'lsvCode', 'bsvCode', 'isoSubRegionCode', 'abbreviation',
    ];

    public function nation(): BelongsTo
    {
        return $this->belongsTo(Nation::class);
    }
}
