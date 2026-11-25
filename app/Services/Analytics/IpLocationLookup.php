<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IpLocationLookup
{
    public function lookup(string $ip, bool $allowServerLookup = false): array
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) return [];
        $public = (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        if (! $public && ! $allowServerLookup) return ['country' => 'Local network'];
        try {
            $response = Http::timeout(4)->acceptJson()->get($public ? 'https://ipwho.is/'.$ip : 'https://ipwho.is/');
            if (! $response->ok() || $response->json('success') === false) return [];
            $place = [];
            foreach (['city', 'region', 'country'] as $field) {
                $value = $response->json($field);
                $place[$field] = is_string($value) ? (Str::limit(trim($value), 80, '') ?: null) : null;
            }
            return array_filter($place, fn ($value) => $value !== null);
        } catch (\Throwable) {
            return [];
        }
    }
}
