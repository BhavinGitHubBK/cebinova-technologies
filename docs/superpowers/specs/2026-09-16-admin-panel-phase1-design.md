# Admin Panel Phase 1 Design

**Date:** 2026-09-16  
**Status:** Approved for implementation planning  
**Stack:** Filament 5 on existing Laravel 12 app

## Goal

Ship an internal admin at `/admin` so Cebinova staff can log in and manage website leads (view, filter, update status). Public marketing site remains unchanged.

## Decisions

| Decision | Choice |
|----------|--------|
| Admin framework | Filament 5 |
| Phase 1 scope | Login + leads inbox only |
| Later phases | CMS pages, packages & pricing, and other modules as separate Filament resources |
| Auth model | Existing `App\Models\User` |
| Lead model | Existing `App\Models\Lead` (no schema rewrite for Phase 1) |
| Public registration | Not offered; admins created via seeder / artisan |

## Architecture

- Install Filament 5 and register an `AdminPanelProvider`.
- Panel id: `admin`; URL path: `/admin`.
- Filament provides login UI, session auth, and panel shell.
- First domain module: `LeadResource` backed by the existing `leads` table.
- Website contact / package forms continue to create leads via `LeadService`; admin only consumes and updates them.

```text
Public site                    Admin panel (/admin)
-----------                    --------------------
Contact / package forms  -->   leads table  -->  LeadResource
                               users table  -->  Filament login
```

## Components

### Panel

- Name: “Cebinova Admin”
- Light branding: existing logo asset when straightforward
- Auth: email + password (Filament login)
- No CMS / packages / pricing resources in Phase 1

### Admin user access

- Seed one admin using env vars (`ADMIN_EMAIL`, `ADMIN_PASSWORD`).
- Do not commit real production passwords; `.env.example` documents keys only.
- In production, refuse to seed a weak/default password if env is missing.
- `User` implements Filament panel access (`canAccessPanel` / `FilamentUser`) so only intended users enter `/admin`.

### Lead resource

**List**

- Columns: name, phone, email, service/package, city, status, source, created_at
- Filters: status, source, date range
- Search: name, phone, email, business_name

**View**

- Full lead detail (message, package fields, WhatsApp, budget, etc.)

**Edit**

- Primary editable field: `status` (values from `Lead::STATUSES`: New, Contacted, Follow-up, Converted, Lost)
- Do not expose free-form editing of all intake fields unless a clear need appears later

**Actions**

- Optional: open WhatsApp from stored number
- Admin create of leads is not required in Phase 1 (intake stays on the website)

## Security

- `/admin/*` requires authenticated Filament session
- CSRF / session handled by Laravel + Filament
- Admin credentials live in env / local seeder only, never in git
- Phase 1 uses a simple gate: logged-in users allowed into the panel (no multi-role matrix yet)

## Out of scope (Phase 1)

- CMS page management
- Packages & pricing editors (content still in config / Blade until a later phase moves it to DB)
- Roles & permissions beyond panel access
- Public self-registration
- Redesigning the public site UI

## Error handling & edge cases

- Empty leads table shows an empty state (no crash)
- Status edits limited to `Lead::STATUSES` via Filament select
- Missing production admin env must not create a known default password

## Testing

- Guest visiting `/admin` is sent to Filament login
- Invalid credentials do not grant access
- Valid admin can open the Leads list
- Admin can update lead status and persistence is verified
- Existing public contact / package feature tests continue to pass

## Build order

1. Install Filament 5 and create the Admin panel
2. Gate `User` for panel access and seed an admin
3. Verify login at `/admin`
4. Add `LeadResource` (list / view / status edit)
5. Add filters, search, WhatsApp action
6. Feature tests for admin auth + lead status update
7. Smoke-check public routes still work

## Success criteria

Phase 1 is done when an admin can log into `/admin`, see leads submitted from the website, filter/search them, and update status.

## Future phases (not implemented now)

- Phase 2+: Filament resources for site settings, CMS pages, packages & pricing — requires moving editable content from config/Blade into database models
