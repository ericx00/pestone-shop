# Pestone Technologies — Online Shop

E-commerce + B2B storefront for Pestone Technologies Ltd. Laravel 13 · Filament 5 admin ·
M-Pesa (Daraja) + Pesapal payments · catalogue imported from the DN Solutions price list
with a configurable markup.

## Requirements

- PHP 8.3+ (8.4 tested) with `pdo_mysql`, `mbstring`, `curl`, `gd`, `intl`, `zip`, `bcmath`, `fileinfo`, `openssl`
- Composer 2
- MySQL 8 (or MariaDB 10.6+); SQLite is fine for local dev
- Node 20+ only to rebuild front-end assets (the build is committed for shared hosting)

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# point DB_* at MySQL, or leave DB_CONNECTION=sqlite and: touch database/database.sqlite
php artisan migrate --seed          # settings + admin user
php artisan catalog:import --fresh  # ~560 products from database/data/catalog.json
npm install && npm run build        # or: npm run dev
php artisan serve
```

Admin panel: `/admin` — log in with `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env`.

## Key commands

| Command | Purpose |
|---|---|
| `php artisan catalog:import` | Re-import the catalogue JSON (keeps admin-edited prices) |
| `php artisan catalog:import --file=path/to/pricelist.xlsx` | Re-parse a new supplier spreadsheet |
| `php artisan catalog:import --markup=12` | Import with a different markup % |
| `php artisan pesapal:register-ipn` | Register the Pesapal IPN URL, prints `PESAPAL_IPN_ID` |
| `php artisan test` | Run the feature test suite |

## How pricing works

- The supplier "Sale Price" is stored as **`cost_price`** (Pestone's ex-VAT cost).
- On import, **`price` = cost × (1 + markup%) × (1 + VAT%)**, rounded — so the VAT-inclusive
  shelf price still yields the full markup as margin. Markup and VAT are in **Shop settings**.
- Editing a price in the admin sets **`price_locked`**, and future imports won't touch it.
- **Offers** apply a percentage or fixed discount on top, scoped to all / category / brand /
  specific products, with an optional schedule and a badge ("HOT DEAL", "-15%").
- Approved **B2B** accounts see an ex-VAT price (a set `b2b_price`, or a % off net retail).

## Payments

`App\Services\Payments` — a `PaymentGateway` interface with `MpesaGateway` (Daraja STK push)
and `PesapalGateway` (hosted checkout) drivers, chosen by `PaymentManager`. Enabled methods
are in Shop settings; credentials in `.env`. Webhooks: `POST /webhooks/mpesa`,
`GET|POST /webhooks/pesapal` (CSRF-exempt, idempotent, verified against the gateway API).

## Deployment

See [`DEPLOY.md`](DEPLOY.md) for the cPanel + Git "push to deploy" setup.
