# Lending project

A full-stack **loan application and repayment** demo: customers apply for loans (with payslip upload), admins review and set terms, customers submit payments with optional receipts, and admins confirm payments against an amortization-style ledger. Amounts are shown in **PHP (₱)**.

## Stack

| Layer    | Technology                          |
| -------- | ----------------------------------- |
| API      | **Laravel 12**, PHP 8.2+, SQLite (default) |
| Auth     | **Laravel Sanctum** (Bearer tokens) |
| Frontend | **Nuxt 4**, **Vue 3**, **Pinia**, **Vuetify 4** |

Repository layout:

```
lending-project/
├── backend/    # Laravel API (`/api/*`)
└── frontend/   # Nuxt app (client + admin portals)
```

## Features (high level)

- **Public:** registration and login for customers; admin login.
- **Client portal:** dashboard, loan applications list (search + pagination), new application form, loan detail (summary, payslip preview, payment schedule hints, loan records, submit payment with optional receipt).
- **Admin portal:** loan applications (search, pagination, status/interest/notes, payslip preview), payments table (search, pagination, receipt preview, confirm/reject), activity logs.

## Requirements

- **PHP** 8.2+
- **Composer**
- **Node.js** 20+ (recommended for Nuxt 4)
- **SQLite** (default) or configure MySQL/PostgreSQL in `backend/.env`

## Backend setup

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed   # optional: demo admin + customer (see below)
php artisan serve     # http://127.0.0.1:8000
```

### Demo users (after `db:seed`)

| Role     | Identifier        | Password   |
| -------- | ----------------- | ---------- |
| Admin    | username `admin`  | `password` |
| Customer | email `client@example.com` | `password` |

Change these immediately in any shared or production environment.

### API base URL

JSON API lives under **`/api`**. Example: `GET http://127.0.0.1:8000/api/me` with `Authorization: Bearer {token}`.

## Frontend setup

```bash
cd frontend
npm install
```

Create a `.env` (optional) or rely on defaults. Nuxt reads:

| Variable                 | Purpose                         | Default              |
| ------------------------ | ------------------------------- | -------------------- |
| `NUXT_PUBLIC_API_BASE`   | Laravel origin (no `/api` suffix) | `http://127.0.0.1:8000` |

```bash
npm run dev    # http://localhost:3000 (typical)
```

Build for production:

```bash
npm run build
npm run preview   # optional local preview of build
```

Point `NUXT_PUBLIC_API_BASE` at your deployed API URL when hosting the frontend separately.

## Running both apps locally

1. Start Laravel: `cd backend && php artisan serve`
2. Start Nuxt: `cd frontend && npm run dev`
3. Open the Nuxt URL (e.g. `http://localhost:3000`). Register a customer or use the seeded demo account; use **Admin login** for the admin area.

## API routes (summary)

Public:

- `POST /api/register`, `POST /api/login`, `POST /api/admin/login`

Authenticated (`Authorization: Bearer`):

- `POST /api/logout`, `GET /api/me`
- **Customer:** `GET/POST /api/client/loans`, `GET /api/client/loans/{id}`, payslip/records/payments endpoints
- **Admin:** `GET/PATCH /api/admin/loans`, payslip image, `GET/PATCH /api/admin/payments`, receipt image, `GET /api/admin/logs`

See `backend/routes/api.php` for the canonical list.

## Security notes

- Do not commit real `.env` files or production secrets.
- Replace demo passwords and restrict CORS / Sanctum settings before production.
- Receipt and payslip binaries are served only to authenticated, authorized users.

## License

MIT (default Laravel application license unless you specify otherwise).
