# Deploying to cPanel (no SSH) — clone the private GitHub repo

Account: `pestonec` · Domain: `pestone.co.ke` (document root → the app's `/public`)
GitHub: **private** repo `ericx00/pestone-shop`

Flow: you `git push` to GitHub from your PC → in cPanel you click **Update from Remote**
then **Deploy HEAD Commit**, which runs `.cpanel.yml` (composer install, migrate, cache).

---

## 0. A safe token for cPanel (do this first)

cPanel needs a credential to clone a **private** repo over HTTPS. Don't use a broad
personal token — make a narrow one:

GitHub → **Settings → Developer settings → Personal access tokens → Fine-grained tokens → Generate**
- **Resource owner:** ericx00
- **Repository access:** *Only select repositories* → `pestone-shop`
- **Permissions → Repository → Contents:** **Read-only**
- **Expiration:** 90 days (regenerate later)

Copy the `github_pat_…` value. This is the `TOKEN` used below.

## 1. cPanel → Git™ Version Control → Create

| Field | Value |
|---|---|
| **Clone a Repository** | **ON** |
| **Clone URL** | `https://ericx00:TOKEN@github.com/ericx00/pestone-shop.git` |
| **Repository Path** | `repositories/pestone-shop` (or `pestone-shop1` — doesn't matter) |
| **Repository Name** | `Pestone shop` |

Click **Create**. cPanel clones `main` to `/home/pestonec/repositories/<path>`.
(If you made an empty `repositories/pestone-shop` earlier, delete that entry from the list first.)

## 2. Server prep (cPanel UI, no terminal needed)

1. **MultiPHP Manager** → set `pestone.co.ke` to **PHP 8.3+**.
   **MultiPHP INI Editor** → enable `pdo_mysql, mbstring, curl, gd, intl, zip, bcmath, fileinfo, openssl`.
2. **MySQL Databases** → create `pestonec_shop` + a user with ALL privileges. Note the login.

## 3. Create `.env` in the repo folder

Use **File Manager** (Settings → Show Hidden Files) in
`/home/pestonec/repositories/pestone-shop1/`: copy `.env.example` → `.env` and edit it.
The deploy script copies this to the live folder on the **first** deploy only and never
overwrites it afterwards. Leave `APP_KEY=` blank — the deploy generates it.

Minimum to change:

```
APP_NAME="Pestone Technologies"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pestone.co.ke
APP_KEY=            # generated in step 4 (or run `php artisan key:generate` via the cPanel Terminal if you have it)

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=pestonec_shop
DB_USERNAME=pestonec_shopuser
DB_PASSWORD=********

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp                 # use a cPanel mailbox
MAIL_HOST=mail.pestone.co.ke
MAIL_PORT=465
MAIL_USERNAME=sales@pestone.co.ke
MAIL_PASSWORD=********
MAIL_FROM_ADDRESS="sales@pestone.co.ke"
MAIL_FROM_NAME="Pestone Technologies"

ADMIN_EMAIL=you@pestone.co.ke
ADMIN_PASSWORD=pick-a-strong-one

MPESA_ENV=sandbox                 # switch to production when live
MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_SHORTCODE=
MPESA_PASSKEY=

PESAPAL_ENV=sandbox
PESAPAL_CONSUMER_KEY=
PESAPAL_CONSUMER_SECRET=
PESAPAL_IPN_ID=
```

## 4. First deploy (no terminal needed)

cPanel → Git Version Control → **Manage** → **Pull or Deploy** tab → **Deploy HEAD Commit**.

`.cpanel.yml` does everything: copies code to `/home/pestonec/pestone-shop`, seeds `.env`
there, `composer install`, **generates `APP_KEY`**, `migrate --force`, `db:seed` (settings +
admin user), **`catalog:import`** (only if the products table is empty), `storage:link`, and
caches config/routes/views. Watch the log panel for errors.

If `composer install` times out or runs out of memory on your plan, tell me — I'll commit the
`vendor/` folder so the deploy skips that step.

## 5. Point the domain at `/public`

**Domains** → `pestone.co.ke` → set **Document Root** to `/home/pestonec/pestone-shop/public`.
Run **AutoSSL**. If the host won't let you change the document root, put in
`/home/pestonec/public_html/index.php`:
```php
<?php require '/home/pestonec/pestone-shop/public/index.php';
```
and copy `/home/pestonec/pestone-shop/public/.htaccess` to `public_html/.htaccess`.

## 6. Cron (cPanel → Cron Jobs)

```
* * * * * /usr/local/bin/ea-php83 /home/pestonec/pestone-shop/artisan schedule:run >/dev/null 2>&1
* * * * * /usr/local/bin/ea-php83 /home/pestonec/pestone-shop/artisan queue:work --stop-when-empty --max-time=55 >/dev/null 2>&1
```

## 7. Everyday updates

```bash
# on your PC
git add -A && git commit -m "..."
git push
```
Then in cPanel → Git Version Control → **Manage → Pull or Deploy**:
**Update from Remote**, then **Deploy HEAD Commit**.

(Optional automation later: a GitHub Action can call cPanel's UAPI
`VersionControlDeployment::create` with a cPanel API token to skip the two clicks.)

## 8. Payments go-live

- **Daraja:** production app → Paybill/Till, passkey, consumer key/secret into `.env`,
  `MPESA_ENV=production`. Whitelist `https://pestone.co.ke/webhooks/mpesa` in the portal.
- **Pesapal:** production key/secret into `.env`, `PESAPAL_ENV=production`, then run
  `php artisan pesapal:register-ipn` and put the printed id in `PESAPAL_IPN_ID`.
- Re-deploy so `config:cache` picks up the new values.
