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
- **Client portal:** dashboard (counts + **next payment due** for approved/active loans), loan applications list (search + pagination), new application form, loan detail (summary, payslip preview, payment schedule hints, loan records, submit payment with optional receipt).
- **Admin portal:** overview dashboard (KPIs, loans-by-status, payment pipeline, activity snapshot), loan applications (search, pagination, status/interest/notes, payslip preview), payments table (search, pagination, receipt preview, confirm/reject), activity logs.

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

**Customer** (`customer` middleware):

| Method | Path | Description |
| ------ | ---- | ------------- |
| `GET` | `/api/client/dashboard` | Summary counts and upcoming **next payment due** per approved/active loan (see `ClientDashboardController`) |
| `GET` | `/api/client/loans` | List the signed-in customer’s applications |
| `POST` | `/api/client/loans` | Submit a new application (incl. payslip payload) |
| `GET` | `/api/client/loans/{loanDetail}` | Loan detail (+ records & payments when serialized) |
| `GET` | `/api/client/loans/{loanDetail}/payslip` | Binary payslip image |
| `GET` | `/api/client/loans/{loanDetail}/records` | Amortization-style ledger rows |
| `POST` | `/api/client/loans/{loanDetail}/payments` | Submit a payment (optional receipt image) |

**Admin** (`admin` middleware):

| Method | Path | Description |
| ------ | ---- | ------------- |
| `GET` | `/api/admin/dashboard` | Aggregated stats (applications by status, portfolio exposure, payment counts, activity, customers) |
| `GET` | `/api/admin/loans` | List all loan applications |
| `GET` | `/api/admin/loans/{loanDetail}/payslip` | Binary payslip image |
| `PATCH` | `/api/admin/loans/{loanDetail}` | Update status, interest, admin note |
| `GET` | `/api/admin/payments` | List customer-submitted payments |
| `GET` | `/api/admin/payments/{loanPayment}/receipt` | Binary receipt image |
| `PATCH` | `/api/admin/payments/{loanPayment}` | Confirm or reject a payment |
| `GET` | `/api/admin/logs` | Activity log entries |

See `backend/routes/api.php` for the canonical list.

## Security notes

- Do not commit real `.env` files or production secrets.
- Replace demo passwords and restrict CORS / Sanctum settings before production.
- Receipt and payslip binaries are served only to authenticated, authorized users.

## License

MIT (default Laravel application license unless you specify otherwise).
