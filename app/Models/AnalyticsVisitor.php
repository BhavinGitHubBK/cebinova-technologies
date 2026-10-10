<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsVisitor extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'mobile',
        'ip_address',
        'city',
        'region',
        'country',
        'latitude',
        'longitude',
        'location_accuracy',
        'address',
        'first_seen_at',
        'last_seen_at',
        'first_referrer_domain',
        'first_landing_path',
        'referrer_url',
        'exit_path',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function pageviews(): HasMany
    {
        return $this->hasMany(AnalyticsPageview::class, 'visitor_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(AnalyticsVisit::class, 'visitor_id');
    }

    public function accountEvents(): HasMany
    {
        return $this->hasMany(AnalyticsAccountEvent::class, 'analytics_visitor_id');
    }

    public function code(): string
    {
        return strtoupper(substr(str_replace('-', '', $this->uuid), 0, 6));
    }

    public function label(): string
    {
        return $this->name ?: $this->numberedName();
    }

    public function numberedName(): string
    {
        return 'Visitor #'.str_pad((string) $this->getKey(), 4, '0', STR_PAD_LEFT);
    }

    public static function idFromNumberedName(string $text): ?int
    {
        if (! preg_match('/^\s*(?:visitor\s*#?|#)\s*(\d+)\s*$/i', $text, $match)) {
            return null;
        }

        $id = (int) $match[1];

        return $id > 0 ? $id : null;
    }

    public function place(): string
    {
        if (filled($this->address)) {
            return $this->address;
        }

        $parts = array_values(array_filter([$this->city, $this->region, $this->country]));

        return $parts !== [] ? implode(', ', $parts) : 'Not available yet';
    }

    public function mapUrl(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return 'https://www.google.com/maps?q='.$this->latitude.','.$this->longitude;
    }

    public function accuracyLabel(): ?string
    {
        if ($this->location_accuracy === null) {
            return null;
        }

        $meters = (int) $this->location_accuracy;

        return $meters < 1000
            ? 'Within '.$meters.' metres'
            : 'Within '.rtrim(rtrim(number_format($meters / 1000, 1), '0'), '.').' km';
    }

    public function cameFrom(): string
    {
        return $this->first_referrer_domain ?: 'Direct';
    }

    public function mobileDisplay(): string
    {
        if (blank($this->mobile)) {
            return '—';
        }

        return strlen($this->mobile) === 10
            ? substr($this->mobile, 0, 5).' '.substr($this->mobile, 5)
            : $this->mobile;
    }
}
