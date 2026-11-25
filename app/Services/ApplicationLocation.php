<?php

namespace App\Services;

use App\Services\Analytics\IpLocationLookup;
use Illuminate\Http\Request;

class ApplicationLocation
{
    public function __construct(private IpLocationLookup $lookup) {}

    public function capture(Request $request): array
    {
        $place = $this->lookup->lookup((string) $request->ip());
        return [
            'location_city' => $place['city'] ?? null,
            'location_region' => $place['region'] ?? null,
            'location_country' => $place['country'] ?? null,
            'location_source' => ($place['country'] ?? null) === 'Local network' ? 'local_network' : ($place ? 'ip' : 'unavailable'),
            'location_permission' => 'not_requested',
            'latitude' => null,
            'longitude' => null,
            'location_accuracy' => null,
            'location_captured_at' => now(),
        ];
    }
}
