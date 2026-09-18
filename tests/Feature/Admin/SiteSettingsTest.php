<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_returns_singleton_seeded_from_config(): void
    {
        $this->seed(SiteSettingSeeder::class);

        $settings = SiteSetting::current();

        $this->assertSame(1, SiteSetting::query()->count());
        $this->assertNotEmpty($settings->phone);
        $this->assertNotEmpty($settings->whatsapp);
        $this->assertNotEmpty($settings->email);
    }
}
