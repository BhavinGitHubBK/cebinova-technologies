<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = [
        'phone',
        'whatsapp',
        'email',
        'email_alt',
        'address',
        'maps_url',
        'linkedin',
        'instagram',
        'facebook',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public static function applyToConfig(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $settings = static::query()->first();

        if (! $settings) {
            return;
        }

        $contact = config('cebinova.contact', []);

        foreach (['phone', 'whatsapp', 'email', 'email_alt', 'address', 'maps_url'] as $key) {
            $value = $settings->{$key};
            if (is_string($value) && trim($value) !== '') {
                $contact[$key] = $value;
            }
        }

        foreach (['linkedin', 'instagram', 'facebook'] as $key) {
            $value = $settings->{$key};
            if (is_string($value) && trim($value) !== '') {
                data_set($contact, 'social.'.$key, $value);
            }
        }

        config(['cebinova.contact' => $contact]);
    }
}
