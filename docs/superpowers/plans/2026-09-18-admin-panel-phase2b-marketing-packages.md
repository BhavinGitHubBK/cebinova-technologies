# Phase 2B Marketing Packages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move marketing packages/plans into the database with full Filament CRUD; public pages and enquiry pricing read active packages from DB via `MarketingPackages`.

**Architecture:** `MarketingPackage` has many `MarketingPlan`. Seeder imports current `config/cebinova.php` marketing package trees. `MarketingPackages` keeps the same public method names but queries active DB records. Blade package cards call a new `MarketingPackages::catalog()` / `packageArray($key)` shaped like the old config arrays. Hero/notes/FAQ stay in config.

**Tech Stack:** Laravel 12, Filament 5, PHPUnit, existing contact/package enquiry flow.

**Spec:** `docs/superpowers/specs/2026-09-18-admin-panel-phase2b-marketing-packages-design.md`

## Global Constraints

- Full CRUD for packages and plans (add/remove allowed)
- Active-only on public site and in enquiry validation
- Enquiry price always from server (`MarketingPackages::priceAmount` / `formattedPrice`)
- Do **not** move marketing hero/notes/how_it_works/trust/samples/faq/addons/matrix into DB in this phase
- Do **not** implement `/pricing` (Kirana) page — that is 2B.2
- Preserve `MarketingPackages::SERVICE` = `Digital Marketing`
- Existing Admin / Site Settings / Contact / PackageEnquiry tests must pass (update package tests to seed DB)
- Prefer Windows-safe artisan/composer commands

## File structure (target)

| Path | Responsibility |
|------|----------------|
| `database/migrations/2026_09_18_140000_create_marketing_packages_table.php` | packages table |
| `database/migrations/2026_09_18_140100_create_marketing_plans_table.php` | plans table |
| `app/Models/MarketingPackage.php` | package model |
| `app/Models/MarketingPlan.php` | plan model |
| `database/seeders/MarketingPackageSeeder.php` | import from config |
| `database/seeders/DatabaseSeeder.php` | call seeder |
| `app/Support/MarketingPackages.php` | DB-backed helper + catalog arrays |
| `resources/views/sections/marketing/explorer.blade.php` | use catalog |
| `resources/views/sections/marketing/growth.blade.php` | use catalog |
| `resources/views/sections/home/marketing.blade.php` | use catalog |
| `app/Filament/Resources/MarketingPackages/*` | Filament CRUD |
| `tests/Feature/Admin/MarketingPackageResourceTest.php` | admin CRUD |
| `tests/Feature/MarketingPackagesHelperTest.php` | helper/DB prices |
| Update: `tests/Feature/ContactFormTest.php`, `PackageEnquiryTest.php` | seed packages |

---

### Task 1: Migrations, models, seeder

**Files:**
- Create migrations + models + `MarketingPackageSeeder`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/MarketingPackagesHelperTest.php` (seed assertions)

**Interfaces:**
- Consumes: `config('cebinova.marketing.regular|festival|growth')`
- Produces: `MarketingPackage::query()->active()`; `MarketingPlan` with casts `includes`, `monthly_pace` → array; `MarketingPackageSeeder`

- [ ] **Step 1: Write failing seed test**

```php
<?php

namespace Tests\Feature;

use App\Models\MarketingPackage;
use App\Models\MarketingPlan;
use App\Support\MarketingPackages;
use Database\Seeders\MarketingPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingPackagesHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_imports_three_packages_and_regular_monthly_price(): void
    {
        $this->seed(MarketingPackageSeeder::class);

        $this->assertSame(3, MarketingPackage::query()->count());
        $this->assertGreaterThanOrEqual(12, MarketingPlan::query()->count());
        $this->assertSame(4999, MarketingPackages::priceAmount('Regular Marketing', 'Monthly'));
    }
}
```

- [ ] **Step 2: Run — expect FAIL**

```powershell
php artisan test --filter=test_seeder_imports_three_packages_and_regular_monthly_price
```

- [ ] **Step 3: Create migrations**

`marketing_packages`: id, string key unique, title, heading nullable, subheading nullable text, teaser nullable text, best_if nullable, service string, unsignedInteger sort_order default 0, boolean is_active default true, timestamps.

`marketing_plans`: id, foreignId marketing_package_id constrained cascadeOnDelete, string key, string label, string duration nullable, unsignedInteger price, string period nullable, string badge nullable, string cta nullable, json includes nullable, json monthly_pace nullable, unsignedInteger sort_order default 0, boolean is_active default true, timestamps, unique(marketing_package_id, key).

- [ ] **Step 4: Models**

```php
// MarketingPackage
public function plans(): HasMany { return $this->hasMany(MarketingPlan::class)->orderBy('sort_order'); }
public function scopeActive($q) { return $q->where('is_active', true); }

// MarketingPlan
protected function casts(): array {
    return [
        'includes' => 'array',
        'monthly_pace' => 'array',
        'is_active' => 'boolean',
        'price' => 'integer',
    ];
}
public function package(): BelongsTo { return $this->belongsTo(MarketingPackage::class, 'marketing_package_id'); }
public function scopeActive($q) { return $q->where('is_active', true); }
```

- [ ] **Step 5: Seeder**

Loop keys `regular`, `festival`, `growth` from config. For each package config block, `updateOrCreate` by `key`, then for each plan entry `updateOrCreate` by package_id + plan key. Map `sort_order` from loop index. Set `is_active` true.

Wire into `DatabaseSeeder` after `SiteSettingSeeder`.

- [ ] **Step 6: Temporarily keep helper reading config until Task 2 — OR implement minimal `priceAmount` reading DB so seed test passes**

For the seed test’s `MarketingPackages::priceAmount` call, implement Task 2 helper methods in the same commit if needed so the assertion can pass. Prefer implementing helper DB reads in Task 2; for Task 1 assert only model counts/prices via Eloquent:

```php
$plan = MarketingPlan::query()
    ->whereHas('package', fn ($q) => $q->where('title', 'Regular Marketing'))
    ->where('label', 'Monthly')
    ->first();
$this->assertSame(4999, $plan?->price);
```

Update Step 1 test accordingly if helper not ready.

- [ ] **Step 7: Migrate, test, commit**

```powershell
php artisan migrate
php artisan test --filter=MarketingPackagesHelperTest
git add database app/Models/MarketingPackage.php app/Models/MarketingPlan.php tests/Feature/MarketingPackagesHelperTest.php
git commit -m "feat: add marketing packages and plans tables with seeder"
```

---

### Task 2: Refactor `MarketingPackages` + Blade catalog reads

**Files:**
- Modify: `app/Support/MarketingPackages.php`
- Modify: `resources/views/sections/marketing/explorer.blade.php`
- Modify: `resources/views/sections/marketing/growth.blade.php`
- Modify: `resources/views/sections/home/marketing.blade.php`
- Modify: any other blade that uses `config('cebinova.marketing.regular|festival|growth')` for package cards
- Expand: `tests/Feature/MarketingPackagesHelperTest.php`

**Interfaces:**
- Produces:
  - `MarketingPackages::categories(): list<string>` — active package titles
  - `MarketingPackages::categoryKeys(): array<title, key>`
  - `MarketingPackages::durations(): list<string>` — distinct active plan labels ordered sensibly
  - `MarketingPackages::priceAmount(?string $category, ?string $duration): ?int`
  - `MarketingPackages::packageArray(string $key): ?array` — config-shaped array with nested `plans` keyed by plan key
  - `MarketingPackages::catalog(): array<string, array>` — key → packageArray for all active packages

Package array shape (compatible with existing blades):

```php
[
  'key' => 'regular',
  'title' => '...',
  'heading' => '...',
  'subheading' => '...',
  'teaser' => '...',
  'best_if' => '...',
  'service' => '...', // should match title used as category in forms
  'plans' => [
    'monthly' => [
      'label' => 'Monthly',
      'duration' => '1 Month',
      'price' => 4999,
      'period' => '/ month',
      'badge' => null,
      'cta' => 'Get This Plan',
      'includes' => [...],
      'monthly_pace' => [...], // optional
    ],
    // ...
  ],
]
```

Note: blades iterate `$category['plans']` — preserve associative keys.

- [ ] **Step 1: Add helper tests**

```php
public function test_helper_reads_prices_and_rejects_inactive_plan(): void
{
    $this->seed(MarketingPackageSeeder::class);

    $this->assertSame(4999, MarketingPackages::priceAmount('Regular Marketing', 'Monthly'));
    $this->assertContains('Regular Marketing', MarketingPackages::categories());

    $plan = MarketingPlan::query()->where('label', 'Monthly')->whereHas('package', fn ($q) => $q->where('key', 'regular'))->first();
    $plan->update(['is_active' => false]);

    $this->assertFalse(MarketingPackages::isValid('Regular Marketing', 'Monthly'));
    $this->assertNull(MarketingPackages::priceAmount('Regular Marketing', 'Monthly'));
}

public function test_package_array_matches_catalog_keys(): void
{
    $this->seed(MarketingPackageSeeder::class);
    $regular = MarketingPackages::packageArray('regular');
    $this->assertSame('Regular Marketing', $regular['title']);
    $this->assertArrayHasKey('monthly', $regular['plans']);
}
```

- [ ] **Step 2: Run — expect FAIL**

```powershell
php artisan test --filter=MarketingPackagesHelperTest
```

- [ ] **Step 3: Rewrite `MarketingPackages` to query Eloquent**

Implementation notes:
- Cache nothing unless needed (YAGNI); queries are small
- `durations()`: unique labels from active plans, ordered Monthly → Quarterly → Half-Yearly → Yearly then others
- `months()` can stay label-based match as today
- `categoryKeys()`: title → key from active packages (`service` field often equals title — use `title` as category string for forms to match existing lead payloads)

- [ ] **Step 4: Update blades**

Replace:

```php
$regular = config('cebinova.marketing.regular');
```

with:

```php
$regular = \App\Support\MarketingPackages::packageArray('regular');
```

Guard empty packages (show empty state or skip section if null). Same for festival/growth and home marketing cards.

Leave `config('cebinova.marketing.notes.*')` etc. unchanged.

- [ ] **Step 5: Run helper + package enquiry tests**

```powershell
php artisan test --filter=MarketingPackagesHelperTest
php artisan test --filter=ContactFormTest
php artisan test --filter=PackageEnquiryTest
```

Ensure those tests call `MarketingPackageSeeder` in `setUp` or rely on DatabaseSeeder — with `RefreshDatabase`, **add** `$this->seed(MarketingPackageSeeder::class)` to ContactFormTest / PackageEnquiryTest setUp or individual tests that need packages.

- [ ] **Step 6: Commit**

```powershell
git add app/Support/MarketingPackages.php resources/views tests/Feature
git commit -m "feat: serve marketing packages from database via helper"
```

---

### Task 3: Filament Marketing Package resource + Plans relation manager

**Files:**
- Create under `app/Filament/Resources/MarketingPackages/`
- Test: `tests/Feature/Admin/MarketingPackageResourceTest.php`

**Interfaces:**
- Filament resource pages List/Create/Edit
- Relation manager `PlansRelationManager` on Edit page

- [ ] **Step 1: Write failing admin test**

```php
<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\MarketingPackages\Pages\CreateMarketingPackage;
use App\Filament\Resources\MarketingPackages\Pages\EditMarketingPackage;
use App\Filament\Resources\MarketingPackages\Pages\ListMarketingPackages;
use App\Models\MarketingPackage;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MarketingPackageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('admin');
        $this->actingAs(User::factory()->create(['email' => 'admin@cebinova.test']));
    }

    public function test_admin_can_list_and_create_package(): void
    {
        Livewire::test(ListMarketingPackages::class)->assertOk();

        Livewire::test(CreateMarketingPackage::class)
            ->fillForm([
                'key' => 'custom',
                'title' => 'Custom Package',
                'service' => 'Custom Package',
                'heading' => 'Custom heading',
                'is_active' => true,
                'sort_order' => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('marketing_packages', [
            'key' => 'custom',
            'title' => 'Custom Package',
        ]);
    }

    public function test_admin_can_add_plan_via_relation_manager(): void
    {
        $package = MarketingPackage::query()->create([
            'key' => 'custom',
            'title' => 'Custom Package',
            'service' => 'Custom Package',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Use Filament relation manager Livewire test pattern from Filament 5 docs:
        // Livewire::test(PlansRelationManager::class, ['ownerRecord' => $package, 'pageClass' => EditMarketingPackage::class])
        //   ->callTableAction('create', data: [...])
        // Assert plan exists with price 1234
    }
}
```

Fill in relation-manager assertion using Filament 5 testing API (`callAction` / table create) matching generated code.

- [ ] **Step 2: Generate resource**

```powershell
php artisan make:filament-resource MarketingPackage --generate --no-interaction
```

Then add relation manager:

```powershell
php artisan make:filament-relation-manager MarketingPackageResource plans label --no-interaction
```

(Adjust command to Filament 5 exact syntax if different.)

- [ ] **Step 3: Configure forms/tables**

Package form: key, title, service, heading, subheading, teaser, best_if, sort_order, is_active.  
Plan form: key, label, duration, price, period, badge, cta, includes (TagsInput or Repeater), monthly_pace (Repeater optional), sort_order, is_active.  
Navigation group: `Marketing`.

- [ ] **Step 4: Run admin + regression tests**

```powershell
php artisan test --filter=MarketingPackageResourceTest
php artisan test --filter=Admin
php artisan test --filter=MarketingPackagesHelperTest
```

- [ ] **Step 5: Commit**

```powershell
git add app/Filament/Resources tests/Feature/Admin/MarketingPackageResourceTest.php
git commit -m "feat: add Filament CRUD for marketing packages and plans"
```

---

### Task 4: Smoke verification

- [ ] **Step 1: Full relevant suite**

```powershell
php artisan migrate:fresh --seed
php artisan test --filter="MarketingPackages|PackageEnquiry|ContactForm|Admin"
```

Manual:
1. `/admin` → Marketing Packages → edit Regular Monthly price  
2. Confirm `/marketing-packages` shows new price  
3. Submit package enquiry → lead `selected_price` matches server formatting  
4. Deactivate a plan → enquiry with that duration fails validation  

- [ ] **Step 2: Commit fixes only if needed**

---

## Spec coverage checklist

| Spec requirement | Task |
|------------------|------|
| Tables + models | Task 1 |
| Seed from config | Task 1 |
| Helper DB-backed + active-only | Task 2 |
| Blade package cards from DB | Task 2 |
| Filament full CRUD | Task 3 |
| Enquiry server price | Task 2 (existing LeadService) |
| No `/pricing` / CMS | All |

## Plan self-review

- Config marketing chrome (notes/FAQ) explicitly left in config  
- View compatibility via `packageArray()` avoids rewriting every include line  
- Inactive plan behaviour covered by tests  
- 2B.2 called out as out of scope
