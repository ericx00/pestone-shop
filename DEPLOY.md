# Deploying to cPanel — no terminal, no SSH, no working Git Version Control

Account: `pestonec` · Domain: `pestone.co.ke` is the account's **primary domain**, so its
document root is locked to `~/public_html`.

This host gives us **File Manager**, **FTP Accounts**, **MySQL/phpMyAdmin**, **MultiPHP
Manager** — no Terminal, and cPanel's Git Version Control doesn't actually work here. So:

- The app lives in `~/pestone-shop`; its `public/` is published into the locked
  `~/public_html` webroot.
- **Deploys don't sync a git tree over FTP** (14,000 vendor files that way took hours and
  never finished). Instead, CI zips the app and the web root into **two files**, FTPs just
  those (+ a tiny extractor script) into `public_html`, then hits a URL so PHP's
  `ZipArchive` unpacks them on the server in seconds.
- Artisan commands (migrate/seed/import) run through a **token-protected URL**
  (`App\Http\Controllers\DeployController`, route `/deploy/{token}`) instead of a shell.

Ongoing deploys: **`git push` → GitHub Actions builds the 2 zips → FTP-uploads them (~3
small files) → calls the extractor URL → calls the migrate/seed/cache URL.** One-time setup
below.

---

## 1. Database — already done
DB `pestonec_prime`, user `pestonec_admin`.

## 2. FTP account — already done
`admin@pestone.co.ke`, directory `/home/pestonec` (account root — required so it can reach
both `pestone-shop/` and `public_html/`).

## 3. GitHub Actions secrets — already set (by Claude, via `gh secret set`)

| Secret | Value |
|---|---|
| `FTP_HOST` | `ftp.pestone.co.ke` |
| `FTP_USERNAME` | `admin@pestone.co.ke` |
| `FTP_PASSWORD` | (the FTP account password) |
| `DEPLOY_TOKEN` | a long random string — **also goes in the server's `.env`**, see step 5 |

(`FTP_APP_DIR` / `FTP_WEBROOT_DIR` from an earlier attempt are no longer used — safe to
leave or delete.)

## 4. First deploy

Push to `main`, or re-run **"Deploy to cPanel (FTP + PHP extractor)"** from the repo's
**Actions** tab. Watch it — it should take well under a minute, not hours. The last step
("Run migrate / seed / cache") will fail harmlessly on this very first run because `.env`
doesn't exist on the server yet — that's expected.

## 5. Create `.env` (File Manager, one time)

Now that step 4 has created `/home/pestonec/pestone-shop/`, go there in **File Manager**
(Settings → Show Hidden Files), copy `.env.example` → `.env`, edit:

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

DEPLOY_TOKEN=<the same value stored as the DEPLOY_TOKEN GitHub secret>
WEB_ROOT=/home/pestonec/public_html
```

Leave `APP_KEY=` blank — the deploy route generates it.

## 6. Run the setup

Visit **`https://pestone.co.ke/deploy/<DEPLOY_TOKEN>`**. It runs, in order: key
generation, migrations (creates all tables), seeding (admin user + shop settings), the
560-product catalogue import, the `public_html/storage` symlink, and cache warm-up — and
prints a plain-text log. **Paste me that output.**

From now on every `git push` re-runs this automatically (safe to repeat — migrate/seed are
idempotent, and the catalogue only re-imports if you visit `?reimport=1`).

## 7. AutoSSL

cPanel → **SSL/TLS Status** → run **AutoSSL** for `pestone.co.ke`.

## Everyday updates

```bash
git add -A && git commit -m "..." && git push
```
GitHub Actions does the rest — watch it under the repo's **Actions** tab.

## Payments go-live

- **Daraja:** production Paybill/Till + passkey + consumer key/secret → `.env`,
  `MPESA_ENV=production`. Whitelist `https://pestone.co.ke/webhooks/mpesa` in the Daraja
  portal.
- **Pesapal:** production key/secret → `.env`, `PESAPAL_ENV=production`, then visit
  `/deploy/<token>?pesapal_ipn=1` once to register the IPN URL (check the log it prints for
  the `ipn_id`, and optionally save it as `PESAPAL_IPN_ID` in `.env`).
- Push (or re-run the Action) so `config:cache` picks up the new values.
