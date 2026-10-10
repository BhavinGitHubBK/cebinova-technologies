# Homepage conversion flow

Date: 2026-10-07

This cycle changes only the public homepage composition and moves the future-ecosystem block to About. Encoding fixes, lead storage, pricing calculator, marketing-package management, service pages, case studies, testimonials, SEO, and blog stay out of scope.

## Goal

Make the homepage shorter and easier to act on. A visitor should see proof, services, a business problem, a package, and work samples before the close. Claims stay tied to real records. Empty proof is omitted.

## Page order

`resources/views/pages/home.blade.php` includes, in this order:

1. `sections.home.hero` — unchanged
2. `sections.home.proof` — new
3. `sections.home.services` — unchanged
4. `sections.home.problems` — new
5. `sections.home.packages` — new
6. `sections.home.portfolio` — unchanged (demos / work)
7. `sections.home.why` — unchanged
8. `sections.home.process` — unchanged
9. `sections.home.cta` — unchanged

Testimonials are not rendered. There is no testimonial store in this cycle. The include is added only when real quotes exist later.

These current includes leave the homepage:

- `sections.home.trust` (capability rail; services already cover this)
- `sections.home.about`
- `sections.home.action`
- `sections.home.transformation`
- `sections.home.industries`
- `sections.home.solutions`
- `sections.home.ai`
- `sections.home.marketing`
- `sections.home.future`

`resources/views/pages/about.blade.php` includes `sections.home.future` immediately after `sections.about.vision`. The future partial stays as it is.

Industry and solution index pages stay the full catalogs. The homepage shows counts and the problem picker only.

## Data

No new table. `HomepageBuilder` gains three methods used by the new partials. Missing tables follow the existing `hasTable` guard and return empty lists. The public homepage does not throw.

### Proof

`HomepageBuilder::proofStats()` returns up to five items. An item is included only when it has a non-empty display value.

| Order | Label | Source | Include when |
| --- | --- | --- | --- |
| 1 | Projects delivered | Admin | `value` is non-blank |
| 2 | Industries served | `Industry::active()->count()` | count is greater than 0 |
| 3 | Years of experience | Admin | `value` is non-blank |
| 4 | Demo projects | `Demo::active()->count()` | count is greater than 0 |
| 5 | Support commitment | Admin | `value` is non-blank |

Admin values live on the `page_sections` row `page=home`, `key=proof`, inside `settings.proof`. Each entry is `{ "key": "projects"|"years"|"support", "value": "", "label": "" }`. A non-blank `label` replaces the default label. Blank `value` entries are ignored. This renderer does not call `CmsContent::slots()` and does not fill placeholder numbers.

`PageSectionSeeder` creates that row when missing, with empty values and the default labels, `is_visible` true. Empty values produce no public stats.

If `proofStats()` is empty, the proof section is not rendered.

Live counts use the active scopes already on `Industry` and `Demo`. They are not stored in settings.

### Problem picker

`HomepageBuilder::problemOptions()` reads active solutions, featured first, then `sort_order`, then `id`, and keeps the first four usable rows.

A solution is usable when all of these are filled:

- `name`
- `outcome`
- at least one of `audience`, `hero_subtitle`, `short_description`

Each option contains:

- `id`
- `name` — button label
- `problem` — `audience`, otherwise `hero_subtitle`, otherwise `short_description`
- `outcome`
- `href` — `route('solutions.show', $slug)`

If fewer than two usable solutions exist, `problemOptions()` returns an empty list and the section is not rendered.

All usable options are in the HTML. The first has `is-active` and `aria-pressed="true"`. Inactive panels are hidden with CSS. A small public script toggles the active button and panel. It does not fetch and does not reload the page. Without JavaScript, the first problem remains visible.

### Featured packages

`HomepageBuilder::featuredPackages()` uses active website packages (`Package::website()->active()`), highlighted first (`is_highlighted`), then `sort_order`, limit 3. Each card shows name, short description or teaser, and links to `route('pricing')#plans`. The pricing page has no per-package anchor. If the package query is empty or the table is missing, use the first three items from `WebsitePackages::plans()`.

The section also shows one text link to `route('marketing-packages')` with the label "Explore marketing packages". It does not render Regular, Festival, or Complete Growth cards.

If there are zero website packages after the fallback, the section is not rendered. The marketing link is not shown alone.

## Behaviour details

- Proof, problems, and packages sections each have a heading and one short support line. Copy can be overridden later through the existing page-section heading fields. Defaults ship in the partials so the page works before those rows exist.
- Proof stats are text, not animated counters.
- Problem buttons are real `<button type="button">` elements inside a `role="tablist"` pattern: `aria-selected` on the button and `aria-controls` pointing at the matching panel.
- Package cards link to `/pricing#plans`. They do not add an enquiry form on the homepage.

## Files

| File | Change |
| --- | --- |
| `app/Services/HomepageBuilder.php` | Add `proofStats()`, `problemOptions()`, `featuredPackages()` |
| `resources/views/pages/home.blade.php` | New include list |
| `resources/views/pages/about.blade.php` | Include future ecosystem after vision |
| `resources/views/sections/home/proof.blade.php` | New |
| `resources/views/sections/home/problems.blade.php` | New |
| `resources/views/sections/home/packages.blade.php` | New |
| `resources/js` public entry already loaded by the marketing layout | Problem-picker toggle only |
| `resources/css` public entry already loaded by the marketing layout | Proof, picker, and package layout |
| `database/seeders/PageSectionSeeder.php` | Seed empty `home` / `proof` settings |
| `tests/Feature/PageRoutesTest.php` | Update homepage assertions that currently require removed sections |

Removed partials stay in the repo. They are unused by the homepage. About reuses `sections/home/future.blade.php`.

## Tests

Extend `tests/Feature/PageRoutesTest.php`:

- Homepage responds OK and shows the hero, services, why, process, and final CTA headings that remain.
- Homepage does not show "Future ecosystem" or "More Than a Service Company."
- About shows "Future ecosystem" and "More Than a Service Company."
- With industries and demos seeded active, the homepage shows those counts and the labels "Industries served" and "Demo projects".
- A `home` / `proof` section with a blank projects value does not show "Projects delivered". A non-blank value does.
- Two usable solutions render the picker and both names. One usable solution omits the problems section.
- Three active website packages render as cards. "Regular Marketing", "Festival Marketing", and "Complete Growth" are absent from the homepage. "Explore marketing packages" is present and points at the marketing packages route.

`test_homepage_uses_brand_title` and `test_homepage_teases_three_marketing_categories` currently assert future-ecosystem and three marketing-category copy on `/`. Those assertions move or are replaced by the cases above. Header, footer, and hero assertions stay.

## Out of scope

- Fake or estimated proof numbers
- A testimonial model or homepage testimonial section
- Technology-stack logo row
- Admin UI beyond the existing page-section settings JSON
- Reordering homepage blocks from `page_sections.sort_order`
- Pricing calculator and dynamic marketing-package pages
- Deleting unused home partials
