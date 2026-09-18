# Admin Panel Phase 1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Install Filament 5 at `/admin` with login, seed an admin user, and ship a Leads inbox (list / view / status edit / filters / WhatsApp action).

**Architecture:** One Filament Admin panel uses the existing `User` model for auth and a `LeadResource` over the existing `leads` table. Website forms keep creating leads via `LeadService`; the admin only manages them. No CMS / packages / pricing in this phase.

**Tech Stack:** Laravel 12, Filament 5 (`filament/filament:"~5.0"` on Windows PowerShell), Livewire 4, PHPUnit feature tests, SQLite in tests.

**Spec:** `docs/superpowers/specs/2026-09-16-admin-panel-phase1-design.md`

## Global Constraints

- Filament major version: **5** (`composer require filament/filament:"~5.0"`)
- Panel path: **`/admin`**, panel id: **`admin`**
- Phase 1 scope: **login + leads only** (no CMS / packages / pricing resources)
- Lead statuses must match `Lead::STATUSES`: New, Contacted, Follow-up, Converted, Lost
- No public registration on the panel
- Admin credentials via `ADMIN_EMAIL` / `ADMIN_PASSWORD` env (never commit real secrets)
- Public contact/package behaviour and existing Feature tests must keep passing
- Prefer Windows-safe Composer constraints (`~5.0` not `^5.0` in PowerShell)

## File structure (target)

| Path | Responsibility |
|------|----------------|
| `app/Providers/Filament/AdminPanelProvider.php` | Panel config: path, brand, login, discover resources |
| `bootstrap/providers.php` | Register `AdminPanelProvider` |
| `app/Models/User.php` | Implement `FilamentUser::canAccessPanel()` |
| `database/seeders/AdminUserSeeder.php` | Create/update admin from env |
| `database/seeders/DatabaseSeeder.php` | Call `AdminUserSeeder` |
| `.env.example` | Document `ADMIN_EMAIL`, `ADMIN_PASSWORD` |
| `app/Filament/Resources/Leads/LeadResource.php` | Resource wiring (no create page) |
| `app/Filament/Resources/Leads/Pages/ListLeads.php` | List page |
| `app/Filament/Resources/Leads/Pages/ViewLead.php` | View page |
| `app/Filament/Resources/Leads/Pages/EditLead.php` | Edit status page |
| `app/Filament/Resources/Leads/Schemas/LeadForm.php` | Edit form (status + read-only context fields) |
| `app/Filament/Resources/Leads/Schemas/LeadInfolist.php` | View infolist |
| `app/Filament/Resources/Leads/Tables/LeadsTable.php` | Table, filters, search, actions |
| `database/factories/LeadFactory.php` | Test data for leads |
| `tests/Feature/Admin/AdminAuthTest.php` | Guest redirect + login gate |
| `tests/Feature/Admin/LeadResourceTest.php` | List / status update / filter |

---

### Task 1: Install Filament 5 Admin panel

**Files:**
- Create: `app/Providers/Filament/AdminPanelProvider.php` (via artisan)
- Modify: `bootstrap/providers.php`
- Modify: `composer.json` / `composer.lock` (via Composer)

**Interfaces:**
- Consumes: existing Laravel 12 app bootstrap
- Produces: Filament panel id `admin` at path `admin` with `->login()` enabled; provider registered in `bootstrap/providers.php`

- [ ] **Step 1: Install Filament (Windows PowerShell)**

```powershell
composer require filament/filament:"~5.0"
php artisan filament:install --panels
```

Expected: `app/Providers/Filament/AdminPanelProvider.php` created; Composer finishes without errors.

- [ ] **Step 2: Confirm provider registration**

Open `bootstrap/providers.php` and ensure it includes:

```php
<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
];
```

If `filament:install` already registered it, leave that registration; only add it if missing.

- [ ] **Step 3: Brand the panel lightly**

In `app/Providers/Filament/AdminPanelProvider.php`, inside `panel()`, ensure at least:

```php
return $panel
    ->default()
    ->id('admin')
    ->path('admin')
    ->login()
    ->brandName('Cebinova Admin')
    ->brandLogo(asset('images/branding/logo.png'))
    ->brandLogoHeight('2rem')
    ->favicon(asset('favicon.ico'))
    // keep discoverResources / discoverPages / middleware as generated
    ;
```

Do **not** call `->registration()`. Keep other generated middleware / discovery calls from `filament:install`.

- [ ] **Step 4: Smoke-check the login route exists**

```powershell
php artisan route:list --path=admin
```

Expected: routes including `filament.admin.auth.login` (or similar login route) and panel pages.

- [ ] **Step 5: Commit**

```powershell
git add composer.json composer.lock bootstrap/providers.php app/Providers/Filament/AdminPanelProvider.php
git commit -m "chore: install Filament 5 admin panel at /admin"
```

---

### Task 2: Gate users and seed admin credentials

**Files:**
- Modify: `app/Models/User.php`
- Create: `database/seeders/AdminUserSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `.env.example`
- Modify: `.env` (local only; do not commit)
- Test: `tests/Feature/Admin/AdminAuthTest.php`

**Interfaces:**
- Consumes: `User` authenticatable model; Filament `Panel` id `admin`
- Produces: `User::canAccessPanel(Panel $panel): bool`; `AdminUserSeeder` upserts user from `ADMIN_EMAIL` / `ADMIN_PASSWORD`

- [ ] **Step 1: Write failing auth tests**

Create `tests/Feature/Admin/AdminAuthTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_to_login(): void
    {
        $this->get('/admin')
            ->assertRedirect();

        $this->get('/admin/login')
            ->assertOk();
    }

    public function test_authenticated_admin_can_open_admin_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@cebinova.test',
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }
}
```

- [ ] **Step 2: Run tests to verify they fail (or redirect unexpectedly)**

```powershell
php artisan test --filter=AdminAuthTest
```

Expected: FAIL or incomplete until User implements `FilamentUser` and panel is reachable (may already pass partially after Task 1; `canAccessPanel` is still required for production-safe gating).

- [ ] **Step 3: Implement `FilamentUser` on `User`**

Replace `app/Models/User.php` contents with:

```php
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Phase 1: any User row may access admin (no public registration).
        return $panel->getId() === 'admin';
    }
}
```

- [ ] **Step 4: Add env keys and seeder**

Append to `.env.example`:

```env
ADMIN_NAME="Cebinova Admin"
ADMIN_EMAIL=admin@cebinova.test
ADMIN_PASSWORD=
```

Add the same keys to local `.env` with a strong local password (do not commit `.env`).

Create `database/seeders/AdminUserSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@cebinova.test');
        $password = (string) env('ADMIN_PASSWORD', '');
        $name = (string) env('ADMIN_NAME', 'Cebinova Admin');

        if ($password === '') {
            if (app()->environment('production')) {
                throw new RuntimeException('ADMIN_PASSWORD must be set to seed an admin in production.');
            }

            $password = 'password';
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ],
        );
    }
}
```

Update `database/seeders/DatabaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(AdminUserSeeder::class);
    }
}
```

Remove or stop relying on the old `test@example.com` factory create unless still needed elsewhere.

- [ ] **Step 5: Seed and re-run auth tests**

```powershell
php artisan db:seed --class=AdminUserSeeder
php artisan test --filter=AdminAuthTest
```

Expected: PASS. Manual check: open `http://localhost/.../admin/login` and sign in with seeded credentials.

- [ ] **Step 6: Commit**

```powershell
git add app/Models/User.php database/seeders/AdminUserSeeder.php database/seeders/DatabaseSeeder.php .env.example tests/Feature/Admin/AdminAuthTest.php
git commit -m "feat: gate Filament admin access and seed admin user"
```

---

### Task 3: Lead factory + LeadResource scaffold (list/view/edit, no create)

**Files:**
- Create: `database/factories/LeadFactory.php`
- Create: `app/Filament/Resources/Leads/*` (via artisan, then trim create page)
- Test: `tests/Feature/Admin/LeadResourceTest.php` (initial failing tests)

**Interfaces:**
- Consumes: `App\Models\Lead`, Filament resource conventions
- Produces: `LeadResource` with pages `index`, `view`, `edit` only; `LeadFactory` for tests

- [ ] **Step 1: Create Lead factory**

Create `database/factories/LeadFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'business_name' => fake()->company(),
            'phone' => '9876543210',
            'whatsapp' => '9876543210',
            'email' => fake()->safeEmail(),
            'business_type' => 'Retail',
            'service' => 'Website Development',
            'package_category' => null,
            'plan_duration' => null,
            'selected_price' => null,
            'city' => 'Ahmedabad',
            'budget' => 'To be discussed',
            'message' => fake()->sentence(),
            'free_consultation' => true,
            'source' => 'Website Contact',
            'status' => Lead::STATUS_NEW,
        ];
    }
}
```

Add to `app/Models/Lead.php`:

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory;

    // ... existing constants, fillable, casts
}
```

- [ ] **Step 2: Write failing Lead resource tests**

Create `tests/Feature/Admin/LeadResourceTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Models\Lead;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs(User::factory()->create([
            'email' => 'admin@cebinova.test',
        ]));
    }

    public function test_admin_can_list_leads(): void
    {
        $leads = Lead::factory()->count(3)->create();

        Livewire::test(ListLeads::class)
            ->assertOk()
            ->assertCanSeeTableRecords($leads);
    }

    public function test_admin_can_view_a_lead(): void
    {
        $lead = Lead::factory()->create([
            'name' => 'Asha Patel',
        ]);

        Livewire::test(ViewLead::class, [
            'record' => $lead->getRouteKey(),
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => 'Asha Patel',
            ]);
    }

    public function test_admin_can_update_lead_status(): void
    {
        $lead = Lead::factory()->create([
            'status' => Lead::STATUS_NEW,
        ]);

        Livewire::test(EditLead::class, [
            'record' => $lead->getRouteKey(),
        ])
            ->fillForm([
                'status' => 'Contacted',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'Contacted',
        ]);
    }
}
```

- [ ] **Step 3: Run tests — expect FAIL (missing resource classes)**

```powershell
php artisan test --filter=LeadResourceTest
```

Expected: FAIL (classes not found).

- [ ] **Step 4: Generate resource with view page**

```powershell
php artisan make:filament-resource Lead --view --generate
```

If the generator places files under `app/Filament/Resources/Leads/`, keep that layout. If it uses a flat `LeadResource` layout, move/adapt to match the test namespaces above (`App\Filament\Resources\Leads\...`) **or** update the test namespaces to match what artisan generated — keep one consistent structure.

- [ ] **Step 5: Remove Create capability**

1. Delete `CreateLead.php` if generated.
2. In `LeadResource::getPages()`, keep only:

```php
public static function getPages(): array
{
    return [
        'index' => ListLeads::route('/'),
        'view' => ViewLead::route('/{record}'),
        'edit' => EditLead::route('/{record}/edit'),
    ];
}
```

3. On `ListLeads`, ensure header does not include create action (remove `CreateAction` if present).
4. Prefer disabling create via policy later only if needed; Phase 1 = no create page is enough.

- [ ] **Step 6: Commit scaffold**

```powershell
git add app/Models/Lead.php database/factories/LeadFactory.php app/Filament/Resources tests/Feature/Admin/LeadResourceTest.php
git commit -m "feat: scaffold Filament Lead resource without create"
```

---

### Task 4: Configure Leads table, filters, form, infolist, WhatsApp action

**Files:**
- Modify: `app/Filament/Resources/Leads/Tables/LeadsTable.php` (or inline table on resource)
- Modify: `app/Filament/Resources/Leads/Schemas/LeadForm.php`
- Modify: `app/Filament/Resources/Leads/Schemas/LeadInfolist.php`
- Modify: `tests/Feature/Admin/LeadResourceTest.php` (add filter test)

**Interfaces:**
- Consumes: `Lead` attributes listed in model `$fillable`
- Produces: searchable/filterable leads table; edit form focused on `status`; view infolist with full detail; WhatsApp action URL `https://wa.me/{digits}`

- [ ] **Step 1: Add filter test**

Append to `LeadResourceTest`:

```php
public function test_admin_can_filter_leads_by_status(): void
{
    $newLeads = Lead::factory()->count(2)->create(['status' => 'New']);
    $lostLeads = Lead::factory()->count(2)->create(['status' => 'Lost']);

    Livewire::test(ListLeads::class)
        ->assertCanSeeTableRecords($newLeads->merge($lostLeads))
        ->filterTable('status', 'Lost')
        ->assertCanSeeTableRecords($lostLeads)
        ->assertCanNotSeeTableRecords($newLeads);
}
```

Run:

```powershell
php artisan test --filter=test_admin_can_filter_leads_by_status
```

Expected: FAIL until filter exists.

- [ ] **Step 2: Implement table**

In the Leads table configuration:

```php
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

public static function configure(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('phone')->searchable(),
            TextColumn::make('email')->searchable()->toggleable(),
            TextColumn::make('service')->toggleable(),
            TextColumn::make('package_category')->label('Package')->toggleable(),
            TextColumn::make('city')->toggleable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('source')->toggleable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            SelectFilter::make('status')
                ->options(array_combine(Lead::STATUSES, Lead::STATUSES)),
            SelectFilter::make('source')
                ->options(fn (): array => Lead::query()
                    ->whereNotNull('source')
                    ->distinct()
                    ->orderBy('source')
                    ->pluck('source', 'source')
                    ->all()),
            Filter::make('created_at')
                ->schema([
                    DatePicker::make('from'),
                    DatePicker::make('until'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                }),
        ])
        ->recordActions([
            ViewAction::make(),
            EditAction::make(),
            Action::make('whatsapp')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->url(function (Lead $record): ?string {
                    $raw = $record->whatsapp ?: $record->phone;
                    $digits = preg_replace('/\D+/', '', (string) $raw);

                    return $digits ? 'https://wa.me/'.$digits : null;
                })
                ->openUrlInNewTab()
                ->visible(fn (Lead $record): bool => filled($record->whatsapp ?: $record->phone)),
        ]);
}
```

Adjust method names (`recordActions` vs `actions`) to match Filament 5 generated table API if the scaffold differs — follow the generated file’s style.

- [ ] **Step 3: Implement edit form (status-focused)**

```php
use App\Models\Lead;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

public static function configure(Schema $schema): Schema
{
    return $schema->components([
        Select::make('status')
            ->options(array_combine(Lead::STATUSES, Lead::STATUSES))
            ->required(),
        TextInput::make('name')->disabled(),
        TextInput::make('phone')->disabled(),
        TextInput::make('email')->disabled(),
        TextInput::make('service')->disabled(),
        Textarea::make('message')->disabled()->columnSpanFull(),
    ]);
}
```

- [ ] **Step 4: Implement view infolist**

Show at least: name, business_name, phone, whatsapp, email, business_type, service, package_category, plan_duration, selected_price, city, budget, message, free_consultation, source, status, created_at.

Use `TextEntry` / `IconEntry` from Filament infolists. Match generated `LeadInfolist` structure.

- [ ] **Step 5: Run Lead + auth + public smoke tests**

```powershell
php artisan test --filter=Admin
php artisan test --filter=ContactFormTest
php artisan test --filter=PackageEnquiryTest
```

Expected: all PASS.

- [ ] **Step 6: Commit**

```powershell
git add app/Filament/Resources/Leads tests/Feature/Admin/LeadResourceTest.php
git commit -m "feat: complete leads inbox with filters and WhatsApp action"
```

---

### Task 5: Final verification & docs touch-up

**Files:**
- Possibly update: `README.md` only if the repo already documents setup commands (skip if no README admin section exists — do not invent a large README)
- Verify: `.env.example` has admin keys

- [ ] **Step 1: Full test suite**

```powershell
php artisan test
```

Expected: PASS (or only pre-existing failures unrelated to admin — fix any failures introduced by this phase).

- [ ] **Step 2: Manual checklist**

1. `php artisan migrate --seed`
2. Visit `/admin/login`
3. Log in with `ADMIN_EMAIL` / `ADMIN_PASSWORD`
4. Submit a lead from `/contact`
5. Confirm it appears in `/admin` Leads
6. Change status to Contacted and save
7. Click WhatsApp action

- [ ] **Step 3: Commit any leftover fixes**

```powershell
git add -A
git status
git commit -m "test: verify admin panel phase 1 end-to-end"
```

Only stage files related to this phase; leave unrelated working-tree changes unstaged.

---

## Spec coverage checklist

| Spec requirement | Task |
|------------------|------|
| Filament 5 panel at `/admin` | Task 1 |
| Login, no registration | Task 1 |
| Brand name / logo | Task 1 |
| `FilamentUser` gate | Task 2 |
| Admin seeder + env keys | Task 2 |
| Lead list / view / status edit | Tasks 3–4 |
| Filters, search, WhatsApp | Task 4 |
| No CMS / packages in Phase 1 | All tasks (not added) |
| Feature tests + public forms still work | Tasks 2, 4, 5 |

## Plan self-review

- No TBD placeholders left for Phase 1 deliverables.
- Filament 5 namespaces (`Schemas\Schema`, resource folder layout) documented; implementer must follow artisan output if paths differ slightly.
- Status values aligned with `Lead::STATUSES`.
- Create page explicitly removed to match “website intake only.”
