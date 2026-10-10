<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsPageview;
use App\Models\AnalyticsVisit;
use App\Models\AnalyticsVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VisitorCapture
{
    public const COOKIE = 'cn_visitor';

    public const SEGMENT_MINUTES = 30;

    public function record(Request $request, string $path, ?string $title, ?string $referrer, ?string $visitorKey): AnalyticsVisitor
    {
        $path = $this->path($path);
        $visitor = $this->visitor($visitorKey, $path, $referrer, $request->ip());

        $previous = AnalyticsPageview::query()
            ->where('visitor_id', $visitor->id)
            ->latest('viewed_at')
            ->latest('id')
            ->first();

        if ($previous && $previous->path === $path) {
            $visitor->forceFill([
                'last_seen_at' => now(),
                'ip_address' => $request->ip(),
            ])->save();

            if ($previous->visit_id) {
                AnalyticsVisit::query()->whereKey($previous->visit_id)->update([
                    'last_seen_at' => now(),
                ]);
            }

            return $visitor->fresh();
        }

        $visit = $this->segment($visitor, $path, $referrer);

        AnalyticsPageview::query()->create([
            'visitor_id' => $visitor->id,
            'visit_id' => $visit->id,
            'path' => $path,
            'page_title' => Str::limit(trim((string) $title), 180, ''),
            'referrer_path' => $this->sameSitePath($referrer),
            'viewed_at' => now(),
        ]);

        $visit->forceFill([
            'last_seen_at' => now(),
            'exit_path' => $path,
        ])->save();

        $visitor->forceFill([
            'last_seen_at' => now(),
            'exit_path' => $path,
            'ip_address' => $request->ip(),
        ])->save();

        if ($this->needsPlace($visitor)) {
            $this->locate($visitor, (string) $request->ip());
        }

        return $visitor->fresh();
    }

    public function identify(Request $request, string $name, string $mobile, ?string $visitorKey, ?string $path): AnalyticsVisitor
    {
        $visitor = $this->visitor($visitorKey, $this->path($path ?: '/'), null, $request->ip());
        $existing = AnalyticsVisitor::query()
            ->where('mobile', $mobile)
            ->whereKeyNot($visitor->id)
            ->orderBy('first_seen_at')
            ->first();

        if ($existing) {
            $this->mergeInto($existing, $visitor, $name);

            if ($this->needsPlace($existing)) {
                $this->locate($existing, (string) $request->ip());
            }

            return $existing->fresh();
        }

        $visitor->forceFill([
            'name' => $name,
            'mobile' => $mobile,
            'last_seen_at' => now(),
            'ip_address' => $request->ip(),
        ])->save();

        if ($this->needsPlace($visitor)) {
            $this->locate($visitor, (string) $request->ip());
        }

        return $visitor->fresh();
    }

    public function visitor(?string $key, string $path, ?string $referrer, ?string $ip): AnalyticsVisitor
    {
        $uuid = Str::isUuid((string) $key) ? $key : (string) Str::uuid();
        $visitor = AnalyticsVisitor::query()->firstOrCreate(
            ['uuid' => $uuid],
            [
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'first_landing_path' => $path,
                'first_referrer_domain' => $this->externalDomain($referrer),
                'referrer_url' => $this->externalUrl($referrer),
                'exit_path' => $path,
                'ip_address' => $ip,
            ],
        );

        if ($visitor->wasRecentlyCreated && $this->needsPlace($visitor) && $ip) {
            $this->locate($visitor, $ip);
        }

        return $visitor;
    }

    public function segment(AnalyticsVisitor $visitor, string $path, ?string $referrer): AnalyticsVisit
    {
        $visit = $visitor->visits()->latest('last_seen_at')->first();
        $open = $visit
            && $visit->last_seen_at
            && $visit->last_seen_at->gt(now()->subMinutes(self::SEGMENT_MINUTES));

        if ($open) {
            return $visit;
        }

        return $visitor->visits()->create([
            'started_at' => now(),
            'last_seen_at' => now(),
            'landing_path' => $path,
            'exit_path' => $path,
            'referrer_domain' => $this->externalDomain($referrer),
            'referrer_url' => $this->externalUrl($referrer),
        ]);
    }

    public function pin(AnalyticsVisitor $visitor, float $latitude, float $longitude, ?int $accuracy): AnalyticsVisitor
    {
        $visitor->forceFill([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location_accuracy' => $accuracy,
            'address' => $this->addressFor($latitude, $longitude) ?: $visitor->address,
            'last_seen_at' => now(),
        ])->save();

        return $visitor->fresh();
    }

    public function locate(AnalyticsVisitor $visitor, string $ip): void
    {
        $lookupIp = $ip;
        $private = $this->isPrivate($ip);

        if ($private) {
            if (! app()->environment('local')) {
                $visitor->forceFill([
                    'country' => 'Local network',
                ])->save();

                return;
            }

            $lookupIp = '';
        }

        try {
            $response = Http::timeout(4)
                ->acceptJson()
                ->get($lookupIp === '' ? 'https://ipwho.is/' : 'https://ipwho.is/'.$lookupIp);

            if (! $response->ok() || $response->json('success') === false) {
                $this->markLocal($visitor, $private);

                return;
            }

            $visitor->forceFill([
                'city' => Str::limit((string) $response->json('city'), 80, '') ?: null,
                'region' => Str::limit((string) $response->json('region'), 80, '') ?: null,
                'country' => Str::limit((string) $response->json('country'), 80, '') ?: null,
            ])->save();
        } catch (\Throwable) {
            $this->markLocal($visitor, $private);
        }
    }

    public function fillMissingLocalPlaces(iterable $visitors): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $pending = collect($visitors)->filter(fn (AnalyticsVisitor $visitor) => $this->needsPlace($visitor))->values();
        if ($pending->isEmpty()) {
            return;
        }

        $first = $pending->first();
        $this->locate($first, (string) ($first->ip_address ?: '127.0.0.1'));
        $first->refresh();

        if ($this->needsPlace($first)) {
            return;
        }

        foreach ($pending->skip(1) as $visitor) {
            $visitor->forceFill([
                'city' => $first->city,
                'region' => $first->region,
                'country' => $first->country,
            ])->save();
        }
    }

    public function fillMissingAddresses(iterable $visitors): void
    {
        foreach ($visitors as $visitor) {
            if (! $visitor instanceof AnalyticsVisitor || filled($visitor->address) || $visitor->latitude === null || $visitor->longitude === null) {
                continue;
            }

            $address = $this->addressFor((float) $visitor->latitude, (float) $visitor->longitude);
            if (filled($address)) {
                $visitor->forceFill(['address' => $address])->save();
            }
        }
    }

    private function needsPlace(AnalyticsVisitor $visitor): bool
    {
        if (blank($visitor->country)) {
            return true;
        }

        return $visitor->country === 'Local network' && app()->environment('local');
    }

    private function mergeInto(AnalyticsVisitor $canonical, AnalyticsVisitor $extra, string $name): void
    {
        $extra->visits()->update(['visitor_id' => $canonical->id]);
        $extra->pageviews()->update(['visitor_id' => $canonical->id]);

        $updates = [
            'name' => $canonical->name ?: $name,
            'mobile' => $canonical->mobile ?: $extra->mobile,
            'last_seen_at' => now(),
        ];

        if ($extra->last_seen_at && (! $canonical->last_seen_at || $extra->last_seen_at->gte($canonical->last_seen_at))) {
            $updates['exit_path'] = $extra->exit_path ?: $canonical->exit_path;
            $updates['ip_address'] = $extra->ip_address ?: $canonical->ip_address;
        }

        if ($canonical->latitude === null && $extra->latitude !== null) {
            $updates['latitude'] = $extra->latitude;
            $updates['longitude'] = $extra->longitude;
            $updates['location_accuracy'] = $extra->location_accuracy;
            $updates['address'] = $extra->address;
        }

        if ($this->weakPlace($canonical) && ! $this->weakPlace($extra)) {
            $updates['city'] = $extra->city;
            $updates['region'] = $extra->region;
            $updates['country'] = $extra->country;
        }

        $canonical->forceFill($updates)->save();
        $extra->delete();
    }

    private function weakPlace(AnalyticsVisitor $visitor): bool
    {
        return blank($visitor->country) || $visitor->country === 'Local network';
    }

    private function addressFor(float $latitude, float $longitude): ?string
    {
        try {
            $response = Http::timeout(4)
                ->withHeaders([
                    'User-Agent' => 'CEBINOVA Technologies ('.config('app.url').')',
                ])
                ->acceptJson()
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                ]);

            if (! $response->ok()) {
                return null;
            }

            $address = $response->json('address');
            if (! is_array($address)) {
                return Str::limit((string) $response->json('display_name'), 255, '') ?: null;
            }

            $parts = array_values(array_filter([
                isset($address['house_number'], $address['road']) ? $address['house_number'].' '.$address['road'] : ($address['road'] ?? null),
                $address['suburb'] ?? $address['neighbourhood'] ?? $address['quarter'] ?? null,
                $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['state_district'] ?? null,
                $address['state'] ?? null,
                $address['postcode'] ?? null,
                $address['country'] ?? null,
            ]));

            return $parts !== [] ? Str::limit(implode(', ', $parts), 255, '') : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function markLocal(AnalyticsVisitor $visitor, bool $private): void
    {
        if ($private && blank($visitor->country)) {
            $visitor->forceFill(['country' => 'Local network'])->save();
        }
    }

    private function path(string $path): string
    {
        $path = '/'.ltrim(parse_url($path, PHP_URL_PATH) ?: '/', '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function externalUrl(?string $referrer): ?string
    {
        $referrer = trim((string) $referrer);
        if ($referrer === '' || $this->isOwnHost($referrer)) {
            return null;
        }

        return Str::limit($referrer, 500, '');
    }

    private function externalDomain(?string $referrer): ?string
    {
        $url = $this->externalUrl($referrer);
        if (! $url) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return $host ? Str::limit(preg_replace('/^www\./', '', strtolower($host)) ?: $host, 190, '') : null;
    }

    private function sameSitePath(?string $referrer): ?string
    {
        $referrer = trim((string) $referrer);
        if ($referrer === '' || ! $this->isOwnHost($referrer)) {
            return null;
        }

        return $this->path((string) (parse_url($referrer, PHP_URL_PATH) ?: '/'));
    }

    private function isOwnHost(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return false;
        }

        return strcasecmp($host, (string) request()->getHost()) === 0;
    }

    private function isPrivate(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
