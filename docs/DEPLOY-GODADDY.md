# Deploy CEBINOVA to GoDaddy (cPanel / Shared Hosting)

Production checklist for Laravel 12 + MySQL on GoDaddy shared or cPanel hosting.

## 1. Hosting requirements

- PHP **8.2+** (enable in cPanel → Select PHP Version)
- Extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` or `imagick`
- MySQL database + user (create in cPanel → MySQL Databases)
- SSH access preferred (Terminal in cPanel works if SSH is locked)

## 2. What to upload

Upload the **project root** (not only `public/`). Typical layout on GoDaddy:

```text
/home/USERNAME/
  cebinova/                 ← full Laravel app (outside public_html if possible)
  public_html/              ← document root points here OR symlink to cebinova/public
```

**Preferred:** put the app in `~/cebinova` and point the domain document root to `~/cebinova/public`.

If you cannot change document root, upload into `public_html` and:

1. Move Laravel files one level up (or keep app in a sibling folder).
2. Copy/move contents of `public/` into `public_html`.
3. Edit `public_html/index.php` so paths point to the Laravel root:

```php
require __DIR__.'/../cebinova/vendor/autoload.php';
$app = require_once __DIR__.'/../cebinova/bootstrap/app.php';
```

Adjust `../cebinova` to match your folder name.

## 3. Production `.env`

Copy `.env.example` → `.env` on the server and set:

```env
APP_NAME="CEBINOVA Technologies"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_NAME="Cebinova Admin"
ADMIN_EMAIL=admin@your-domain.com
ADMIN_PASSWORD=use-a-long-unique-password
```

Also set contact vars (`CEBINOVA_PHONE`, `CEBINOVA_WHATSAPP`, etc.) to live values.

Generate the app key on the server:

```bash
php artisan key:generate
```

## 4. Install & build assets

On a machine with Node (local or CI), build assets **before** upload, or build on the server if Node is available:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Upload `vendor/` **or** run `composer install` on the server. Always upload `public/build/` (Vite manifest + assets).

## 5. Database & storage

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`db:seed` creates:

- Super admin from `ADMIN_EMAIL` / `ADMIN_PASSWORD`
- Packages, services, page sections, sample CMS content

**Change the admin password immediately** after first login at `/admin`.

## 6. Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

Owner should be the cPanel user (or `nobody`/`apache` depending on host). If uploads fail, fix ownership of `storage/app/public`.

## 7. SSL & HTTPS

1. Enable AutoSSL / Let’s Encrypt in cPanel.
2. Force HTTPS (cPanel redirect or `.htaccess` in `public/`).
3. Confirm `APP_URL` uses `https://`.

## 8. Post-deploy smoke test

- [ ] Homepage loads (no 500)
- [ ] `/pricing` calculator shows plan prices
- [ ] Contact / package enquiry creates a lead
- [ ] `/admin` login works
- [ ] Media upload + public image URL via `/storage/...`
- [ ] Blog index `/blog` loads
- [ ] WhatsApp float / consultation links use live numbers

## 9. Ongoing updates

```bash
git pull   # or re-upload changed files
composer install --no-dev --optimize-autoloader
npm ci && npm run build   # if frontend changed
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link  # only if missing
```

Clear caches after `.env` edits:

```bash
php artisan optimize:clear
php artisan config:cache
```

## 10. Common GoDaddy issues

| Symptom | Fix |
|--------|-----|
| 500 after deploy | Check `storage/logs/laravel.log`; usually missing `.env`, bad DB creds, or `APP_KEY` |
| CSS/JS missing | Ensure `public/build` exists and `npm run build` was run |
| `/storage/...` 404 | Run `php artisan storage:link` |
| Session / login loops | `SESSION_DRIVER=database` + migrations run; cookie secure if HTTPS |
| Wrong PHP version | cPanel → MultiPHP: select 8.2+ for the domain |
| Artisan not found | `cd` into Laravel root, use full path to `php` if needed |

## 11. Security notes

- Never set `APP_DEBUG=true` in production.
- Do not commit `.env`.
- Restrict `/admin` with a strong password; optionally IP-allowlist later.
- Keep `ADMIN_PASSWORD` out of chat logs and tickets.
