<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_non_empty_db_values_override_config(): void
    {
        $this->seed(SiteSettingSeeder::class);

        SiteSetting::current()->update([
            'phone' => '+91 11111 11111',
            'whatsapp' => '911111111111',
            'email' => 'override@example.com',
        ]);

        SiteSetting::applyToConfig();

        $this->assertSame('+91 11111 11111', config('cebinova.contact.phone'));
        $this->assertSame('911111111111', config('cebinova.contact.whatsapp'));
        $this->assertSame('override@example.com', config('cebinova.contact.email'));
    }

    public function test_empty_db_value_falls_back_to_config_default(): void
    {
        config(['cebinova.contact.phone' => '+91 96246 8831']);

        $settings = SiteSetting::current();
        $settings->update(['phone' => '']);

        SiteSetting::applyToConfig();

        $this->assertSame('+91 96246 8831', config('cebinova.contact.phone'));
    }

    public function test_admin_can_save_site_settings_from_filament_page(): void
    {
        Filament::setCurrentPanel('admin');

        $this->actingAs(User::factory()->create([
            'email' => 'admin@cebinova.test',
        ]));

        SiteSetting::current();

        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'phone' => '+91 99999 88888',
                'whatsapp' => '919999988888',
                'email' => 'hello@cebinova.test',
                'email_alt' => '',
                'address' => 'Test Address',
                'maps_url' => 'https://maps.example.com',
                'linkedin' => 'https://linkedin.com/company/test',
                'instagram' => 'https://instagram.com/test',
                'facebook' => 'https://facebook.com/test',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('site_settings', [
            'phone' => '+91 99999 88888',
            'whatsapp' => '919999988888',
            'email' => 'hello@cebinova.test',
        ]);
    }
}
