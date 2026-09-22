<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = [
        'company_name',
        'tagline',
        'logo_path',
        'favicon_path',
        'email',
        'email_alt',
        'phone',
        'whatsapp',
        'address',
        'maps_url',
        'facebook',
        'instagram',
        'linkedin',
        'youtube',
        'twitter',
        'seo_title',
        'seo_description',
        'og_image_path',
        'google_analytics_id',
        'google_search_console',
        'contact_map_embed',
        'maintenance_message',
        'footer_text',
        'copyright_text',
    ];

    public static function current(): self
    {
        return Cache::remember('site_settings', 3600, function () {
            return static::query()->first() ?? static::query()->create([]);
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('site_settings');
    }

    public static function applyToConfig(): void
    {
        try {
            if (! Schema::hasTable('site_settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $settings = static::current();

        $contact = array_filter([
            'phone' => $settings->phone,
            'email' => $settings->email,
            'email_alt' => $settings->email_alt,
            'whatsapp' => $settings->whatsapp,
            'address' => $settings->address,
            'maps_url' => $settings->maps_url,
        ], fn ($v) => filled($v));

        if ($contact !== []) {
            config([
                'cebinova.contact' => array_replace(config('cebinova.contact', []), $contact),
            ]);
        }

        $social = array_filter([
            'facebook' => $settings->facebook,
            'instagram' => $settings->instagram,
            'linkedin' => $settings->linkedin,
            'youtube' => $settings->youtube,
            'twitter' => $settings->twitter,
        ], fn ($v) => filled($v));

        if ($social !== []) {
            config([
                'cebinova.contact.social' => array_replace(config('cebinova.contact.social', []), $social),
            ]);
        }

        $seo = array_filter([
            'default_title' => $settings->seo_title,
            'default_description' => $settings->seo_description,
        ], fn ($v) => filled($v));

        if ($seo !== []) {
            config([
                'cebinova.seo' => array_replace(config('cebinova.seo', []), $seo),
            ]);
        }

        if (filled($settings->company_name)) {
            config(['cebinova.name' => $settings->company_name]);
        }
        if (filled($settings->tagline)) {
            config(['cebinova.tagline' => $settings->tagline]);
        }
    }
}
