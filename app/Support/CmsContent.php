<?php

namespace App\Support;

use App\Models\PageSection;
use Illuminate\Support\Facades\Schema;

class CmsContent
{
    public static function section(string $page, string $key): ?PageSection
    {
        if (! self::has('page_sections')) {
            return null;
        }

        return PageSection::query()->visible()->where('page', $page)->where('key', $key)->first();
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @return array{eyebrow: string, heading: string, lead: string, body: string, cta_label: string, cta_url: ?string, secondary_label: ?string, secondary_url: ?string, proof: list<array{value: string, label: string}>}
     */
    public static function copy(string $page, string $key, array $defaults = []): array
    {
        $section = self::section($page, $key);
        $proof = collect($section?->settings['proof'] ?? [])
            ->filter(fn ($item) => is_array($item) && filled($item['value'] ?? null) && filled($item['label'] ?? null))
            ->map(fn ($item) => ['value' => (string) $item['value'], 'label' => (string) $item['label']])
            ->values()
            ->all();

        return [
            'eyebrow' => $section?->subheading ?: (string) ($defaults['eyebrow'] ?? ''),
            'heading' => $section?->heading ?: (string) ($defaults['heading'] ?? ''),
            'lead' => $section && filled($section->body) ? (string) $section->body : (string) ($defaults['lead'] ?? ''),
            'body' => $section && filled($section->body) ? (string) $section->body : (string) ($defaults['body'] ?? ''),
            'cta_label' => $section?->cta_label ?: (string) ($defaults['cta_label'] ?? 'Get Free Consultation'),
            'cta_url' => $section?->cta_url ?: ($defaults['cta_url'] ?? null),
            'secondary_label' => $section?->secondary_cta_label ?: ($defaults['secondary_label'] ?? null),
            'secondary_url' => $section?->secondary_cta_url ?: ($defaults['secondary_url'] ?? null),
            'proof' => $proof,
        ];
    }

    /**
     * Keep the designed number of slots. Later items are ignored.
     *
     * @param  list<array{value: string, label: string}>  $items
     * @param  list<array{value: string, label: string}>  $fallback
     * @return list<array{value: string, label: string}>
     */
    public static function slots(array $items, array $fallback, int $limit = 4): array
    {
        $base = array_values($fallback);
        foreach (array_values($items) as $index => $item) {
            if ($index >= $limit) {
                break;
            }
            $base[$index] = $item;
        }

        return array_slice($base, 0, $limit);
    }

    public static function href(?string $url, string $fallback): string
    {
        $url = trim((string) $url);
        if ($url === '' || in_array($url, ['/contact', '/whatsapp'], true)) {
            return $fallback;
        }
        if (str_starts_with($url, '#') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:') || preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return $fallback;
    }

    private static function has(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
