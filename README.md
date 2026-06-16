# UzMart — Multivendor E-Commerce API (Backend)

Laravel 8 REST API for the **UzMart** multivendor marketplace. It powers the customer web app, mobile apps, seller panel, admin panel, and delivery driver workflows from a single backend.

---

## Features

- **Multivendor marketplace** — multiple shops, products, categories, brands, and reviews
- **Role-based access** — Admin, Manager, Seller, Moderator, Deliveryman, Customer
- **Orders & payments** — Stripe, PayPal, Razorpay, Paystack, Mercado Pago, Iyzico, and more
- **Delivery** — zones, pricing by region/country/city/area, parcel orders, deliveryman app
- **Localization** — multi-language UI translations and multi-currency support
- **Marketing** — coupons, discounts, cashback, referrals, banners, blogs, stories
- **Notifications** — Firebase push, email (SMTP/SendGrid), SMS (Twilio/Vonage)
- **Real-time** — Laravel WebSockets support
- **File storage** — AWS S3 / MinIO compatible object storage
- **Reports** — sales, orders, products, stock, and category analytics

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Framework | Laravel 8.x |
| PHP | 8.0+ (8.2 recommended) |
| Database | MySQL 5.7+ / MariaDB |
| Auth | Laravel Sanctum (Bearer tokens) |
| Permissions | Spatie Laravel Permission |
| Cache / Queue | File, Redis (optional) |
| Storage | S3-compatible (AWS, MinIO) |
| Payments | Stripe, PayPal, Razorpay, Paystack, Mercado Pago, Iyzico |
| Exports | Maatwebsite Excel, DomPDF |

---

## Requirements

- PHP **8.0+** with extensions: `curl`, `dom`, `fileinfo`, `gd`, `json`, `simplexml`, `zip`, `pdo_mysql`
- Composer **2.x**
- MySQL or MariaDB
- Node.js (optional, for asset builds)
- **MinIO or AWS S3** for image/file uploads (recommended for local dev)

---

## Quick Start

### 1. Clone and install dependencies

```bash
cd admin_backend
composer install
```

### 2. Environment setup

Create your environment file and generate an app key:

```bash
# Create .env from the template below, or copy your existing config
php artisan key:generate
```

Configure at minimum:

```env
APP_NAME=uzmart
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uzmart
DB_USERNAME=root
DB_PASSWORD=

# S3 / MinIO (required for product & shop images)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=uzmart
AWS_URL=http://127.0.0.1:9000/uzmart
AWS_ENDPOINT=http://127.0.0.1:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
IMG_HOST=http://127.0.0.1:9000/uzmart/

# Frontend URLs (used in emails, redirects, CORS)
FRONT_URL=http://localhost:3000/
ADMIN_URL=http://localhost:3001/
```

### 3. Create the database

```sql
CREATE DATABASE uzmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Run migrations and seeders

**Option A — one command (recommended for first install):**

```bash
php artisan install:project
```

This runs migrations, `storage:link`, seeders, and clears caches.

**Option B — step by step:**

```bash
php artisan migrate
php artisan storage:link
php artisan db:seed
php artisan optimize:clear
php artisan cache:clear
```

### 5. Start the development server

```bash
php artisan serve
```

API base URL: **http://127.0.0.1:8000/api/v1**

---

## Demo Accounts

Created by `UserSeeder` after `php artisan db:seed`:

| Role | Email | Password |
|------|-------|----------|
| Admin / Seller | `owner@githubit.com` | `githubit` |
| Manager | `manager@githubit.com` | `manager` |
| Seller | `sellers@githubit.com` | `seller` |
| Customer | `user@githubit.com` | `user123` |
| Moderator | `moderator@githubit.com` | `moderator` |
| Deliveryman | `delivery@githubit.com` | `delivery` |

### Login example

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "owner@githubit.com",
  "password": "githubit"
}
```

Response includes `access_token`. Use it on protected routes:

```http
Authorization: Bearer {access_token}
```

---

## API Overview

All routes are prefixed with `/api/v1`.

| Prefix | Purpose | Auth |
|--------|---------|------|
| `/auth/*` | Register, login, verify, password reset | Public |
| `/rest/*` | Public storefront data (products, shops, settings, translations) | Public |
| `/dashboard/admin/*` | Admin panel operations | Sanctum + Admin/Manager role |
| `/dashboard/seller/*` | Seller shop management | Sanctum + Seller role |
| `/dashboard/user/*` | Customer account, cart, orders | Sanctum + User role |
| `/dashboard/deliveryman/*` | Delivery driver app | Sanctum + Deliveryman role |
| `/install/*` | Web installer (first-time setup) | Public |

### Commonly used public endpoints

```http
GET  /api/v1/rest/settings
GET  /api/v1/rest/languages/active
GET  /api/v1/rest/currencies/active
GET  /api/v1/rest/translations/paginate?lang=en
GET  /api/v1/rest/products/paginate?lang=en
GET  /api/v1/rest/shops/paginate?lang=en
```

### Admin dashboard examples

```http
GET  /api/v1/dashboard/admin/statistics?lang=en&time=subMonth
GET  /api/v1/dashboard/admin/currencies?lang=en
GET  /api/v1/dashboard/admin/shops?lang=en&page=1&perPage=10
```

---

## Project Structure

```
admin_backend/
├── app/
│   ├── Http/Controllers/API/v1/   # REST, Auth, Dashboard controllers
│   ├── Models/                    # Eloquent models
│   ├── Repositories/              # Data access layer
│   ├── Services/                  # Business logic
│   └── Http/Middleware/           # Auth, roles, license checks
├── database/
│   ├── migrations/                # Schema migrations
│   └── seeders/                   # Demo & reference data
├── routes/
│   └── api.php                    # All API route definitions
├── resources/lang/                # Translation source files
├── public/                        # Web root (images, imports)
├── storage/                       # Logs, cache, uploads
└── config/                        # App, database, filesystem config
```

---

## Seeders

| Seeder | Description |
|--------|-------------|
| `LanguageSeeder` | Default languages |
| `CurrencySeeder` | Default currencies |
| `TranslationSeeder` | UI translation strings |
| `WebFrontendTranslationSeeder` | Extra keys for the web storefront |
| `UserSeeder` | Demo users and default shop |
| `FakeDataSeeder` | Sample products, shops, orders, locations |
| `CategorySeeder` | Product categories |

Run individual seeders:

```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=FakeDataSeeder
php artisan db:seed --class=WebFrontendTranslationSeeder
```

---

## Storage (S3 / MinIO)

Product images, shop logos, and uploads are stored on S3-compatible storage.

1. Start MinIO (or configure AWS S3 credentials in `.env`)
2. Create a bucket named `uzmart` (or match `AWS_BUCKET`)
3. Make the bucket public for image URLs:

```bash
php artisan s3:public
```

Set `IMG_HOST` in `.env` to your public bucket URL so image links resolve correctly in API responses.

---

## Docker

A `Dockerfile` is included for containerized development:

```bash
docker build -t uzmart-api .
docker run -p 8000:8000 --env-file .env uzmart-api
```

The entrypoint runs `composer install`, migrations (if enabled), and `php artisan serve` on port **8000**.

---

## Frontend Integration

Point your frontends to this API:

| App | `.env` variable | Example |
|-----|-----------------|---------|
| Web storefront | `NEXT_PUBLIC_BASE_URL` | `http://127.0.0.1:8000/api/` |
| Admin panel | API base URL in admin config | `http://127.0.0.1:8000/api/` |

The web storefront loads UI translations from:

```http
GET /api/v1/rest/translations/paginate?lang=en
```

Content translations (product titles, shop names) use the `lang` query parameter on each REST request.

---

## Useful Artisan Commands

```bash
php artisan serve                    # Start dev server
php artisan migrate                  # Run migrations
php artisan db:seed                  # Seed database
php artisan install:project          # Full first-time install
php artisan cache:clear              # Clear application cache
php artisan config:clear             # Clear config cache
php artisan optimize:clear           # Clear all caches
php artisan storage:link             # Link public/storage
php artisan queue:work               # Process queued jobs (if QUEUE_CONNECTION=redis)
php artisan websockets:serve         # Start WebSocket server
```

---

## Troubleshooting

### Admin dashboard returns 500

Often caused by a missing license cache after `cache:clear` or seeding. Restart the server — the app re-initializes the license cache on boot. If it persists:

```bash
php artisan cache:clear
php artisan serve
```

### Login returns 400 Bad Request

Send JSON with `Content-Type: application/json`:

```json
{ "email": "owner@githubit.com", "password": "githubit" }
```

Email must exist in the database and be verified (`email_verified_at` set). Run `php artisan db:seed --class=UserSeeder` if needed.

### Images not loading

- Confirm MinIO/S3 is running and the bucket exists
- Check `AWS_*` and `IMG_HOST` in `.env`
- Run `php artisan s3:public`

### Translations show as raw keys (e.g. `sign.in`)

Seed web frontend translations:

```bash
php artisan db:seed --class=WebFrontendTranslationSeeder
php artisan cache:clear
```

### New country not appearing in city dropdown

Countries need a delivery price record. New countries created after the fix auto-create one. For existing data:

```bash
php artisan db:seed --class=WebFrontendTranslationSeeder
```

Or add the country again through Admin → Delivery Prices.

### CORS errors from frontend

Set `FRONT_URL` and `ADMIN_URL` in `.env` to match your frontend origins. Laravel CORS is configured in `config/cors.php`.

---

## Testing

```bash
php artisan test
# or
./vendor/bin/phpunit
```

---

## Security Notes

- Never commit `.env` to version control
- Set `APP_DEBUG=false` in production
- Use strong `APP_KEY` (run `php artisan key:generate`)
- Restrict database and S3 credentials to the application server
- Enable HTTPS in production (`APP_URL` should use `https://`)

---

## License

This project is based on the **UzMart** multivendor e-commerce marketplace (CodeCanyon). Use according to your purchase license terms.

---

## Support

- Check `storage/logs/` for error details when `APP_DEBUG=true`
- API responses use a consistent JSON format: `{ status, message, data }`
- Error codes are defined in `app/Helpers/ResponseError.php` and `resources/lang/en/errors.php`
