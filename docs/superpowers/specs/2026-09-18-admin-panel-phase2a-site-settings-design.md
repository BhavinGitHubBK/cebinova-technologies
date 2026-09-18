# Admin Panel Phase 2A Design — Site Settings (Contact)

**Date:** 2026-09-18  
**Status:** Approved for implementation planning  
**Stack:** Filament 5 on existing Laravel 12 app (Phase 1 admin already live)

## Goal

Let admins edit public contact details from `/admin` without changing `.env` for day-to-day updates. Public site continues to read `config('cebinova.contact.*')`.

## Roadmap context

Phase 2 will eventually cover:

1. **2A (this spec):** Contact site settings  
2. **2B (later):** Marketing packages & pricing  
3. **2C (later):** CMS-style services / solutions / pages  

Do not implement 2B/2C in this phase.

## Decisions

| Decision | Choice |
|----------|--------|
| Approach | Filament Settings page + database singleton |
| Scope | Contact details only |
| WhatsApp message templates | Stay in config (not editable in 2A) |
| SEO / brand name / tagline | Out of scope for 2A |
| Public API | Keep `config('cebinova.contact.*')` + helpers unchanged |

## Editable fields

- `phone`
- `whatsapp` (number used for `wa.me` links)
- `email`
- `email_alt` (optional)
- `address`
- `maps_url`
- `linkedin`
- `instagram`
- `facebook`

Not editable in 2A: `whatsapp_message`, `whatsapp_package_message`, company name, SEO.

## Architecture

```text
.env / config/cebinova.php   -->  defaults (fallback)
site_settings (DB singleton) -->  overrides when non-empty
AppServiceProvider merge     -->  config('cebinova.contact.*')
helpers / Blade / JS         -->  unchanged consumers
```

- Table: `site_settings` with one row (singleton).
- Model: `App\Models\SiteSetting` with `SiteSetting::current()` (firstOrCreate).
- Filament custom page (not a multi-record Resource): **Site Settings**.
- On first deploy: seed/migrate fills the row from current `config('cebinova.contact')` / env so the site does not blank out.
- Empty string in DB means “use config/env fallback” for that field.

## Components

### Model & migration

Columns for each editable field (nullable strings). Timestamps included. No soft deletes.

### Config merge

After boot (e.g. in `AppServiceProvider`), load the singleton and merge non-empty values into:

- `cebinova.contact.phone`
- `cebinova.contact.whatsapp`
- `cebinova.contact.email`
- `cebinova.contact.email_alt`
- `cebinova.contact.address`
- `cebinova.contact.maps_url`
- `cebinova.contact.social.linkedin|instagram|facebook`

Skip merge in contexts where the DB is unavailable (e.g. during early migrate), or guard with `Schema::hasTable`.

### Filament page

- Navigation: Settings group or top-level “Site Settings”
- Form binds to the singleton record
- Save updates (or creates) the single row
- No list/create/delete UI for multiple settings

## Out of scope

- Packages & pricing editors (2B)
- CMS pages / services / solutions (2C)
- Editing WhatsApp message templates
- Multi-admin roles beyond existing Phase 1 panel access

## Testing

- Authenticated admin can open Site Settings and save fields
- After save, `config('cebinova.contact.phone')` (and peers) reflect DB values in a request
- Empty DB field falls back to config/env default
- Existing Admin auth/leads and Contact/Package feature tests still pass

## Build order

1. Migration + `SiteSetting` model + seed from current contact config  
2. Config merge on boot  
3. Filament Site Settings page  
4. Feature tests (save + reflection + fallback)  
5. Smoke-check footer / WhatsApp float on the public site  

## Success criteria

An admin can change phone, WhatsApp, emails, address, maps URL, and social links in `/admin` and see those values on the live site without editing `.env`.
