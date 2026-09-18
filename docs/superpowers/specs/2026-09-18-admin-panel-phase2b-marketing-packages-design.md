# Admin Panel Phase 2B Design — Marketing Packages CRUD

**Date:** 2026-09-18  
**Status:** Approved for implementation planning  
**Stack:** Filament 5 on existing Laravel 12 app (Phase 1 admin + Phase 2A site settings live)

## Goal

Manage marketing packages and plans from `/admin` with full CRUD. The public marketing packages page and package enquiry pricing read from the database instead of hard-coded `config/cebinova.php` package arrays.

## Roadmap context

| Phase | Scope |
|-------|--------|
| 2A (done) | Contact site settings |
| **2B (this spec)** | Marketing packages & plans CRUD |
| **2B.2 (later)** | Solution `/pricing` page (Kirana, etc.) |
| 2C (later) | CMS-style services / solutions / pages |

## Decisions

| Decision | Choice |
|----------|--------|
| Approach | DB models + Filament Resources |
| Editable content | Full package + plan content (not prices-only) |
| Structure | Full CRUD — add/remove packages and plans |
| Public API | Keep `MarketingPackages` method names; change internals to DB |
| Enquiry pricing | Still server-side from DB (never trust client price) |
| `/pricing` page | Out of scope → 2B.2 |

## Architecture

```text
config/cebinova.php (marketing.*)  -->  one-time seed
marketing_packages + marketing_plans (DB)
        |
        v
MarketingPackages helper (active records only)
        |
        +--> marketing-packages page / explorer UI
        +--> StoreLeadRequest / LeadService price validation
        +--> Filament admin CRUD
```

Inactive packages/plans remain editable in admin but are hidden from the public site and rejected by enquiry validation.

## Data model

### `marketing_packages`

| Column | Notes |
|--------|--------|
| `key` | Unique slug (e.g. `regular`) |
| `title` | Display name (e.g. Regular Marketing) |
| `heading`, `subheading`, `teaser`, `best_if`, `service` | Marketing copy |
| `sort_order` | Integer |
| `is_active` | Boolean, default true |
| timestamps | |

### `marketing_plans`

| Column | Notes |
|--------|--------|
| `marketing_package_id` | FK |
| `key` | e.g. `monthly` |
| `label` | e.g. `Monthly` (used in enquiry duration matching) |
| `duration` | Human text e.g. `1 Month` |
| `price` | Integer INR amount |
| `period` | e.g. `/ month` |
| `badge` | Nullable string |
| `cta` | e.g. Get This Plan |
| `includes` | JSON array of strings |
| `monthly_pace` | JSON array, nullable (yearly plans) |
| `sort_order` | Integer |
| `is_active` | Boolean, default true |
| timestamps | |

Unique constraint recommended: (`marketing_package_id`, `key`) and/or (`marketing_package_id`, `label`).

## Components

### Models

- `App\Models\MarketingPackage` — `hasMany` plans; scope `active()`
- `App\Models\MarketingPlan` — `belongsTo` package; scope `active()`; cast `includes` / `monthly_pace` to array

### Seeder

- `MarketingPackageSeeder` imports Regular / Festival / Complete Growth and all plans from current `config('cebinova.marketing.{regular,festival,growth}')`
- Wired into `DatabaseSeeder` after site settings / admin seeders

### `MarketingPackages` helper

- `categories()`, `categoryKeys()`, `durations()`, `priceAmount()`, `formattedPrice()`, `priceMap()`, etc. read **active** DB records
- `categoryKeys()` maps title → key from packages table
- `isValid()` / price lookups require active package + active plan

### Filament

- Resource: **Marketing Packages** (list / create / edit / delete)
- Relation manager **Plans** on package edit (full CRUD)
- Navigation group: e.g. **Marketing** (alongside existing Settings)

### Public pages

- Marketing packages page / sections that currently pull package arrays from config switch to helper/service backed by DB
- Shared marketing notes/how-it-works/trust/samples in config may remain in config for 2B (YAGNI) unless already required for package cards — package cards and plans must come from DB

## Enquiry behaviour

- `StoreLeadRequest` / `LeadService` continue to resolve price via `MarketingPackages::formattedPrice()` / `priceAmount()`
- Invalid or inactive category/duration → validation failure
- Client cannot set `selected_price` / status arbitrarily (existing rules stay)

## Out of scope

- Solution `/pricing` Blade page (2B.2)
- Editing global marketing notes, hero, samples, FAQ (unless needed for package cards — prefer leave in config for 2B)
- CMS services/solutions (2C)
- Changing WhatsApp message templates

## Testing

- Seeder creates expected packages/plans and known prices (e.g. Regular Monthly = 4999)
- Admin can create/edit/delete package and plans
- Helper price methods match DB
- Package enquiry feature tests pass with DB seed
- Inactive plan cannot be purchased via enquiry
- Admin auth / leads / site settings tests still pass

## Build order

1. Migrations + models + relationships  
2. Seeder from config marketing data  
3. Refactor `MarketingPackages` (+ marketing page package reads) to DB  
4. Filament Package resource + Plans relation manager  
5. Feature tests + smoke `/marketing-packages` and enquiry  

## Success criteria

An admin can change a plan price or add a package/plan in `/admin` and see it on `/marketing-packages` and in new enquiries without editing `config/cebinova.php`.
