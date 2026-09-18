# Phase 2A Site Settings Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a Filament Site Settings page so admins can edit contact details in the DB; public site keeps reading `config('cebinova.contact.*')` with DB overrides layered on env/config defaults.

**Architecture:** Singleton `site_settings` row + `SiteSetting::current()`. On boot, merge non-empty DB fields into `config('cebinova.contact')` **before** `View::share('company', ...)`. Filament custom page edits the singleton.

**Tech Stack:** Laravel 12, Filament 5, PHPUnit, existing `config/cebinova.php` contact keys.

**Spec:** `docs/superpowers/specs/2026-09-18-admin-panel-phase2a-site-settings-design.md`

## Global Constraints

- Filament major version: **5** (already installed)
- Editable fields only: phone, whatsapp, email, email_alt, address, maps_url, linkedin, instagram, facebook
- WhatsApp message templates stay in config (not editable)
- Empty DB string = fall back to env/config default for that field
- Public helpers/Blade keep using `config('cebinova.contact.*')` — no mass rewrite of call sites
- Phase 2B/2C (packages, CMS) are out of scope
- Existing Admin + Contact/Package tests must keep passing
- Config merge must run **before** `View::share('company', config('cebinova'))` in `AppServiceProvider`

## File structure (target)

| Path | Responsibility |
|------|----------------|
| `database/migrations/xxxx_create_site_settings_table.php` | Singleton table |
| `app/Models/SiteSetting.php` | Model + `current()` + `applyToConfig()` |
| `database/seeders/SiteSettingSeeder.php` | Seed from current config/env |
| `database/seeders/DatabaseSeeder.php` | Call SiteSettingSeeder |
| `app/Providers/AppServiceProvider.php` | Call merge before View::share |
| `app/Filament/Pages/ManageSiteSettings.php` | Filament settings page |
| `resources/views/filament/pages/manage-site-settings.blade.php` | Page view (form + save) |
| `tests/Feature/Admin/SiteSettingsTest.php` | Save, reflection, fallback |

---

### Task 1: Migration, model, seeder

**Files:**
- Create: `database/migrations/2026_09_18_120000_create_site_settings_table.php`
- Create: `app/Models/SiteSetting.php`
- Create: `database/seeders/SiteSettingSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/Admin/SiteSettingsTest.php` (model/seed portion first)

**Interfaces:**
- Consumes: `config('cebinova.contact')` shape
- Produces: `SiteSetting::current(): SiteSetting`; fillable contact columns listed below

- [ ] **Step 1: Write failing test for singleton + seed fields**

Create `tests/Feature/Admin/SiteSettingsTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_returns_singleton_seeded_from_config(): void
    {
        $this->seed(\Database\Seeders\SiteSettingSeeder::class);

        $settings = SiteSetting::current();

        $this->assertSame(1, SiteSetting::query()->count());
        $this->assertNotEmpty($settings->phone);
        $this->assertNotEmpty($settings->whatsapp);
        $this->assertNotEmpty($settings->email);
    }
}
```

- [ ] **Step 2: Run test — expect FAIL**

```powershell
php artisan test --filter=test_current_returns_singleton_seeded_from_config
```

Expected: FAIL (class/table missing).

- [ ] **Step 3: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('email_alt')->nullable();
            $table->text('address')->nullable();
            $table->string('maps_url')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
```

- [ ] **Step 4: Create model**

```php
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
```

- [ ] **Step 5: Create seeder and wire DatabaseSeeder**

```php
<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $contact = config('cebinova.contact', []);

        SiteSetting::query()->updateOrCreate(
            ['id' => SiteSetting::query()->value('id') ?? null],
            [
                'phone' => $contact['phone'] ?? null,
                'whatsapp' => $contact['whatsapp'] ?? null,
                'email' => $contact['email'] ?? null,
                'email_alt' => $contact['email_alt'] ?? null,
                'address' => $contact['address'] ?? null,
                'maps_url' => $contact['maps_url'] ?? null,
                'linkedin' => data_get($contact, 'social.linkedin'),
                'instagram' => data_get($contact, 'social.instagram'),
                'facebook' => data_get($contact, 'social.facebook'),
            ],
        );
    }
}
```

Prefer simpler seeder body if `updateOrCreate` on null id is awkward:

```php
SiteSetting::query()->firstOrCreate([], [
    'phone' => $contact['phone'] ?? null,
    // ... same fields
]);
```

Only fill attributes when creating; if a row already exists, leave it unless you intentionally refresh from config in local reset.

Add to `DatabaseSeeder::run()` after `AdminUserSeeder`:

```php
$this->call([
    AdminUserSeeder::class,
    SiteSettingSeeder::class,
]);
```

- [ ] **Step 6: Migrate, seed, re-run test**

```powershell
php artisan migrate
php artisan test --filter=test_current_returns_singleton_seeded_from_config
```

Expected: PASS.

- [ ] **Step 7: Commit**

```powershell
git add database/migrations database/seeders app/Models/SiteSetting.php tests/Feature/Admin/SiteSettingsTest.php
git commit -m "feat: add site_settings singleton model and seeder"
```

---

### Task 2: Config merge on boot

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `tests/Feature/Admin/SiteSettingsTest.php`

**Interfaces:**
- Consumes: `SiteSetting::applyToConfig(): void`
- Produces: `config('cebinova.contact.*')` overridden for non-empty DB fields before views share company

- [ ] **Step 1: Add failing tests for merge + fallback**

Append to `SiteSettingsTest`:

```php
public function test_non_empty_db_values_override_config(): void
{
    $this->seed(\Database\Seeders\SiteSettingSeeder::class);

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
```

- [ ] **Step 2: Run tests — expect FAIL until provider wired (second may pass if applyToConfig already correct)**

```powershell
php artisan test --filter=SiteSettingsTest
```

- [ ] **Step 3: Wire AppServiceProvider**

Replace `boot()` with:

```php
public function boot(): void
{
    \App\Models\SiteSetting::applyToConfig();

    View::share('company', config('cebinova'));

    RateLimiter::for('contact', function (Request $request) {
        return Limit::perMinute(8)->by($request->ip());
    });
}
```

Keep existing imports; add `use App\Models\SiteSetting;` if preferred over FQCN.

- [ ] **Step 4: Re-run SiteSettingsTest**

```powershell
php artisan test --filter=SiteSettingsTest
```

Expected: PASS.

- [ ] **Step 5: Commit**

```powershell
git add app/Providers/AppServiceProvider.php tests/Feature/Admin/SiteSettingsTest.php
git commit -m "feat: merge site settings into cebinova contact config"
```

---

### Task 3: Filament Manage Site Settings page

**Files:**
- Create: `app/Filament/Pages/ManageSiteSettings.php`
- Create: `resources/views/filament/pages/manage-site-settings.blade.php`
- Modify: `tests/Feature/Admin/SiteSettingsTest.php`

**Interfaces:**
- Consumes: `SiteSetting::current()`
- Produces: Filament page at `/admin/site-settings` (slug may vary; set `$slug = 'site-settings'`)

- [ ] **Step 1: Add failing Livewire save test**

```php
use App\Filament\Pages\ManageSiteSettings;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

// inside class, add:

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
```

Adjust `fillForm` / `save` / notification assertions to match the page implementation if Filament form API differs slightly — keep behavior identical.

- [ ] **Step 2: Run test — expect FAIL**

```powershell
php artisan test --filter=test_admin_can_save_site_settings_from_filament_page
```

- [ ] **Step 3: Generate page scaffold**

```powershell
php artisan make:filament-page ManageSiteSettings --no-interaction
```

- [ ] **Step 4: Implement page class**

Replace generated page with (adapt namespaces/imports to Filament 5 generated style):

```php
<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class ManageSiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    protected static ?string $slug = 'site-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected string $view = 'filament.pages.manage-site-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::current()->only([
            'phone',
            'whatsapp',
            'email',
            'email_alt',
            'address',
            'maps_url',
            'linkedin',
            'instagram',
            'facebook',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phone')->tel()->required(),
                TextInput::make('whatsapp')->required()->helperText('Digits used for WhatsApp links (e.g. 91962468831).'),
                TextInput::make('email')->email()->required(),
                TextInput::make('email_alt')->email(),
                Textarea::make('address')->rows(3)->columnSpanFull(),
                TextInput::make('maps_url')->url()->columnSpanFull(),
                TextInput::make('linkedin')->url(),
                TextInput::make('instagram')->url(),
                TextInput::make('facebook')->url(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::current()->update($data);

        SiteSetting::applyToConfig();

        Notification::make()
            ->title('Site settings saved')
            ->success()
            ->send();
    }
}
```

If `make:filament-page` already registered discovery under `App\Filament\Pages`, keep that. Fix any Filament 5 form trait/interface names if the scaffold differs — prefer the generated HasForms pattern from vendor examples.

- [ ] **Step 5: Blade view**

`resources/views/filament/pages/manage-site-settings.blade.php`:

```blade
<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Save settings
        </x-filament::button>
    </form>
</x-filament-panels::page>
```

- [ ] **Step 6: Run admin settings + regression tests**

```powershell
php artisan test --filter=SiteSettingsTest
php artisan test --filter=Admin
php artisan route:list --path=admin/site-settings
```

Expected: PASS; route exists.

- [ ] **Step 7: Commit**

```powershell
git add app/Filament/Pages resources/views/filament/pages tests/Feature/Admin/SiteSettingsTest.php
git commit -m "feat: add Filament site settings page for contact details"
```

---

### Task 4: Smoke verification

**Files:** none required unless a bug fix appears

- [ ] **Step 1: Seed and manual checklist**

```powershell
php artisan migrate --force
php artisan db:seed --class=SiteSettingSeeder
php artisan test --filter="SiteSettingsTest|AdminAuthTest|ContactFormTest"
```

Manual:
1. Login `/admin` → open **Site Settings**
2. Change phone / WhatsApp / email → Save
3. Confirm footer / WhatsApp float on public site show new values
4. Clear one field in admin, save, confirm fallback to `.env` default

- [ ] **Step 2: Commit any fixes only if needed**

```powershell
git status
# commit only phase-2a related fixes
```

---

## Spec coverage checklist

| Spec requirement | Task |
|------------------|------|
| Singleton `site_settings` + seed | Task 1 |
| Config merge + empty fallback | Task 2 |
| Merge before View::share | Task 2 |
| Filament Site Settings page | Task 3 |
| Contact fields only | Task 3 |
| Feature tests | Tasks 1–3 |
| No WhatsApp templates / 2B / 2C | All tasks |

## Plan self-review

- No TBD placeholders for 2A deliverables.
- View::share ordering called out explicitly (common footgun).
- Field list matches the approved spec exactly.
