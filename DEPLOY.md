# Deploying to cPanel — private repo + push-to-deploy

Goal: one `git push` publishes to a **private GitHub repo** *and* lands on the cPanel
server, which then runs `.cpanel.yml` automatically.

Account: `pestonec` · Domain: `pestone.co.ke` (document root → the app's `/public`)
GitHub identity: `ericx00`

---

## 1. One-time server prep (in cPanel)

1. **PHP version** — *MultiPHP Manager* → set `pestone.co.ke` to **PHP 8.3+** (8.4 preferred).
   *MultiPHP INI Editor* → enable `pdo_mysql, mbstring, curl, gd, intl, zip, bcmath, fileinfo, openssl`.
2. **Database** — *MySQL Databases* → create DB `pestonec_shop` + a user, grant ALL. Note the
   credentials.
3. **SSH access** — *SSH Access* (some hosts require a support ticket). Note the **host** and
   **port** (often 22).
4. **Authorise a deploy key**
   ```bash
   ssh-keygen -t ed25519 -f ~/.ssh/pestone_deploy -C pestone-deploy
   ```
   *SSH Access → Manage SSH Keys → Import Key* (paste the **public** key), then **Authorize** it.
   Test: `ssh -i ~/.ssh/pestone_deploy -p <port> pestonec@<host>`

## 2. Create the repositories

**cPanel → Git™ Version Control → Create**
- Clone a Repository: **OFF**
- Repository Path: `/home/pestonec/repositories/pestone-shop`
- Repository Name: `Pestone Shop`

**GitHub** — create a **private** repo `ericx00/pestone-shop` (no README/…).

## 3. Wire up the fan-out remote (local machine)

```bash
cd C:/Users/user/pestone-shop
git remote add origin git@github.com:ericx00/pestone-shop.git
# push goes to BOTH GitHub and cPanel:
git remote set-url --add --push origin git@github.com:ericx00/pestone-shop.git
git remote set-url --add --push origin ssh://pestonec@<host>:<port>/home/pestonec/repositories/pestone-shop.git
git push -u origin main
```

`git remote -v` should show two `(push)` URLs. Every `git push` now delivers to GitHub, then
to cPanel; cPanel updates its working copy and runs `.cpanel.yml`.

## 4. First deploy: create `.env` on the server

After the first push, `.cpanel.yml` will have copied the code to
`/home/pestonec/pestone-shop`. SSH in and:

```bash
cd /home/pestonec/pestone-shop
cp .env.example .env
php artisan key:generate
# edit .env:
#   APP_ENV=production   APP_DEBUG=false   APP_URL=https://pestone.co.ke
#   DB_CONNECTION=mysql  DB_DATABASE=pestonec_shop  DB_USERNAME=...  DB_PASSWORD=...
#   SESSION_DRIVER=database  QUEUE_CONNECTION=database  CACHE_STORE=database
#   MAIL_* (use the cPanel mailbox / SMTP)
#   ADMIN_EMAIL / ADMIN_PASSWORD
#   MPESA_* and PESAPAL_*  (start with *_ENV=sandbox)
php artisan migrate --force --seed
php artisan catalog:import --fresh
php artisan storage:link
```

## 5. Point the domain at `/public`

*Domains* (or *Subdomains*) → set the **Document Root** for `pestone.co.ke` to
`/home/pestonec/pestone-shop/public`. Run *AutoSSL*.

> If your host won't let you change the document root, instead put this in
> `/home/pestonec/public_html/index.php`:
> `<?php require '/home/pestonec/pestone-shop/public/index.php';`
> and copy `/home/pestonec/pestone-shop/public/.htaccess` into `public_html/`.

## 6. Cron (cPanel → Cron Jobs)

```
* * * * * /usr/local/bin/php /home/pestonec/pestone-shop/artisan schedule:run >/dev/null 2>&1
* * * * * /usr/local/bin/php /home/pestonec/pestone-shop/artisan queue:work --stop-when-empty --max-time=55 >/dev/null 2>&1
```

## 7. Payments go-live

1. Safaricom Daraja: create a production app, get the **Paybill/Till**, **passkey**,
   consumer key/secret. Set `MPESA_ENV=production` and the values in `.env`.
   Callback URL is `https://pestone.co.ke/webhooks/mpesa` (whitelist it in the Daraja portal).
2. Pesapal: production consumer key/secret → `.env`, `PESAPAL_ENV=production`, then
   `php artisan pesapal:register-ipn` and paste the printed `PESAPAL_IPN_ID` into `.env`.
3. `php artisan config:cache`

## Everyday workflow

```bash
git switch -c feature/x     # work on a branch
# ...commits...
git switch main && git merge feature/x
git push                    # → GitHub + cPanel, auto-deploys via .cpanel.yml
```

Watch the deploy log in **cPanel → Git Version Control → Manage → Pull or Deploy → (log)**.

## Fallback (host forbids SSH push)

Use `.github/workflows/deploy.yml` (already in the repo): add repo secrets `SSH_HOST`,
`SSH_PORT`, `SSH_USER`, `SSH_KEY`, then every push to `main` runs the same deploy steps over
SSH. Same `git push` → live result.
