# Deploying to cPanel — no terminal, no SSH, no Git Version Control

Account: `pestonec` · Domain: `pestone.co.ke` is the account's **primary domain**, so its
document root is locked to `~/public_html`.

This host gives us: **File Manager**, **FTP Accounts**, **MySQL/phpMyAdmin**, **MultiPHP
Manager**, **Cron Jobs** maybe, but no Terminal and Git Version Control doesn't actually
work here. So the app runs from `~/pestone-shop`, its `public/` is published into the
locked `~/public_html` webroot, and artisan commands (migrate/seed/import) run through a
**token-protected URL** in the app instead of a shell (`App\Http\Controllers\DeployController`,
route `/deploy/{token}`).

Ongoing deploys: **`git push` → GitHub Actions FTP-uploads the app + web root → the Action
calls the deploy URL** to run migrations/seed/cache. One-time manual setup below.

---

## 1. One-time File Manager setup

1. **File Manager** → Settings → tick **Show Hidden Files**.
2. Create the app folder `/home/pestonec/pestone-shop/` if it doesn't exist yet (the first
   GitHub Actions run below will populate it).
3. You'll create `.env` **inside `/home/pestonec/pestone-shop/`** (not a `repositories/`
   folder — that cPanel Git path is no longer used). Do this *after* step 3 has run once
   (so the folder exists), or create the folder yourself now and add `.env` directly.

## 2. Database (if not already done)

**MySQL Databases**: DB `pestonec_prime`, user `pestonec_admin` — ✅ already created.

## 3. Create an FTP account for deploys

**cPanel → FTP Accounts → Create FTP Account**
- Username: `deploy` (becomes something like `deploy@pestone.co.ke`)
- Directory: **leave it at the home directory** (`/` or `/home/pestonec`) — it must be able
  to reach *both* `pestone-shop/` and `public_html/`, not just one.
- Quota: unlimited (or a few hundred MB)
- Generate a strong password

Note cPanel's **FTP server hostname** (shown on the same page, often `ftp.pestone.co.ke` or
a server hostname) and the port (21, using FTPS/explicit TLS — cPanel supports this by
default).

**Give me:** host, username, password, and confirm the two absolute paths I should deploy
to, e.g. `/pestone-shop` and `/public_html` (check by logging into File Manager and noting
the folder names relative to the FTP account's root). I'll store them as **encrypted
GitHub Actions secrets** (never in plain files) — or you can add them yourself under repo
**Settings → Secrets and variables → Actions**:

| Secret | Value |
|---|---|
| `FTP_HOST` | e.g. `ftp.pestone.co.ke` |
| `FTP_USERNAME` | the FTP account username |
| `FTP_PASSWORD` | the FTP account password |
| `FTP_APP_DIR` | `/pestone-shop` |
| `FTP_WEBROOT_DIR` | `/public_html` |

## 4. First deploy

Push to `main` (or re-run the "Deploy to cPanel (FTP)" workflow from the GitHub Actions
tab) once the secrets above exist. This uploads the whole app to `~/pestone-shop` and the
built `public/` assets + a front-controller shim to `~/public_html`.

## 5. Create `.env` (File Manager, one time)

In `/home/pestonec/pestone-shop/`, copy `.env.example` → `.env`, edit:

```
APP_NAME="Pestone Technologies"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pestone.co.ke
APP_KEY=

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=pestonec_prime
DB_USERNAME=pestonec_admin
DB_PASSWORD=AR6DXY4181

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

ADMIN_EMAIL=you@pestone.co.ke
ADMIN_PASSWORD=pick-a-strong-one

# Generate with: php -r "echo bin2hex(random_bytes(24));" — or ask me for one.
DEPLOY_TOKEN=
WEB_ROOT=/home/pestonec/public_html
```

Leave `APP_KEY=` blank — the deploy route generates it.

## 6. Run the setup

Visit **`https://pestone.co.ke/deploy/<DEPLOY_TOKEN>`** in your browser (paste the same
token you put in `.env`). It runs, in order: key generation, migrations (creates all
tables), seeding (admin user + shop settings), the 560-product catalogue import, the
`public_html/storage` symlink, and cache warm-up — and prints a plain-text log. **Paste me
that output.**

Add `DEPLOY_URL=https://pestone.co.ke/deploy/<DEPLOY_TOKEN>` as a GitHub secret afterwards
so every future push re-runs this automatically (safe to repeat — migrate/seed are
idempotent, and the catalogue only re-imports if you visit with `?reimport=1`).

## 7. AutoSSL

cPanel → **SSL/TLS Status** → run **AutoSSL** for `pestone.co.ke` (only needed once
`public_html/index.php` exists and the domain resolves).

## Everyday updates

```bash
git add -A && git commit -m "..." && git push
```
GitHub Actions does the rest. Watch it under the repo's **Actions** tab.

## Payments go-live

- **Daraja:** production Paybill/Till + passkey + consumer key/secret → `.env`,
  `MPESA_ENV=production`. Whitelist `https://pestone.co.ke/webhooks/mpesa` in the Daraja
  portal.
- **Pesapal:** production key/secret → `.env`, `PESAPAL_ENV=production`, then visit
  `/deploy/<token>` again isn't enough for IPN — run `pesapal:register-ipn` the same way:
  add a step to `DeployController` if needed, or ask me and I'll add a
  `/deploy/{token}/pesapal-ipn` route.
- Re-visit the deploy URL so `config:cache` picks up the new values.
