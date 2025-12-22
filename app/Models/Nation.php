<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nation extends Model
{
    protected $fillable = [
        'nameEn', 'nameDe',
        'worldAquaNF', 'worldAquaNFurl', 'worldParaNF', 'worldParaNFurl',
        'continent_id',
        'ioc', 'iso2', 'iso3',
        'officialNameEn', 'officialShortEn', 'officialNameDe', 'officialShortDe',
        'officialNameCn', 'officialShortCn', 'officialNameFr', 'officialShortFr',
        'officialNameAr', 'officialShortAr', 'officialNameRu', 'officialShortRu',
        'officialNameEs', 'officialShortEs',
        'subRegionName', 'tld', 'currencyAlphabeticCode', 'currencyName',
        'isIndependent', 'Capital', 'IntermediateRegionName',
    ];

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }
}
