<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ParaSwimStyle extends Model
{
    protected $table = 'para_swim_styles';

    protected $fillable = [
        'key',
        'relay_count',
        'distance',
        'stroke',
        'stroke_name_en',
        'stroke_name_de',
        'abbreviation',
    ];

    protected $casts = [
        'relay_count' => 'integer',
        'distance' => 'integer',
    ];

    /**
     * Resolver für LENEX-Importe
     */
    public static function resolve(
        int $distance,
        string $stroke,
        ?int $relayCount = null
    ): self {
        $key = self::makeKey($distance, $stroke, $relayCount);

        return self::where('key', $key)->firstOrFail();
    }

    /**
     * Generiert einen stabilen Key
     * Beispiele:
     *  - 25:FR
     *  - 4x25:FR
     */
    public static function makeKey(
        int $distance,
        string $stroke,
        ?int $relayCount = null
    ): string {
        $stroke = strtoupper($stroke);

        if (! is_null($relayCount) && $relayCount > 1) {
            return "{$relayCount}x{$distance}:{$stroke}";
        }

        return "{$distance}:{$stroke}";
    }

    /**
     * -------------------------
     * Display helpers
     * -------------------------
     */

    /**
     * Anzeigeformat:
     *  - en   → 4x25m Freestyle
     *  - de   → 4x25m Freistil
     *  - abbr → 4x25m Fr
     */
    public function display(string $mode = 'en'): string
    {
        $distance = $this->distance.'m';

        return match ($mode) {
            'de' => $this->relayPrefix().$distance.' '.$this->stroke_name_de,
            'abbr' => $this->relayPrefix().$distance.' '.$this->abbreviation,
            default => $this->relayPrefix().$distance.' '.$this->stroke_name_en,
        };
    }

    /**
     * -------------------------
     * LENEX key handling
     * -------------------------
     */
    public function relayPrefix(): string
    {
        return $this->isRelay()
            ? $this->relay_count.'x'
            : '';
    }

    /**
     * -------------------------
     * Derived attributes
     * -------------------------
     */
    public function isRelay(): bool
    {
        return ! is_null($this->relay_count) && $this->relay_count > 1;
    }

    /**
     * -------------------------
     * Query Scopes (optional, aber sehr praktisch)
     * -------------------------
     */
    public function scopeRelay(Builder $query): Builder
    {
        return $query->whereNotNull('relay_count')
            ->where('relay_count', '>', 1);
    }

    public function scopeIndividual(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('relay_count')
                ->orWhere('relay_count', '<=', 1);
        });
    }
}
