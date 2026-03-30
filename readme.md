# e-maaree

**Multi-tenant school management system** for K–12 and colleges. One codebase; each school runs on its own subdomain with isolated data.

- **Vendor:** [ABQO Technology](https://web.facebook.com/timocadaan)  
- **License:** MIT  
- **Laravel:** 8.x · **PHP:** 7.2 | 8.0 · **Tenancy:** [stancl/tenancy](https://tenancyforlaravel.com/) 3.x  

---

## Table of contents

- [Tech stack](#tech-stack)
- [Architecture](#architecture)
- [Features & modules](#features--modules)
- [Authentication](#authentication)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the application](#running-the-application)
- [Local development quick reference](#local-development-quick-reference)
- [Project structure](#project-structure)
- [Conventions for developers](#conventions-for-developers)
- [Default credentials](#default-credentials)
- [Testing](#testing)
- [Deployment](#deployment)
- [Security & license](#security--license)

---

## Tech stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 8.x, PHP 7.2\|8.0 |
| Tenancy | stancl/tenancy 3.6 |
| Database | MySQL / MariaDB |
| PDF | barryvdh/laravel-dompdf |
| IDs in URLs | hashids/hashids |
| Frontend | Laravel UI, Bootstrap, jQuery, DataTables (with Buttons extension) |

---

## Architecture

- **Central (landlord) app**  
  Served from `APP_URL` (e.g. `http://localhost:8001`). Handles landing, login, and the **Landlord Dashboard** (create/suspend/activate/edit/delete tenant schools). Routes live in `routes/web.php`.

- **Tenant (school) app**  
  One tenant per subdomain (e.g. `school1.localhost:8001`). Full school management: students, classes, sections, subjects, exams, grades, marks, timetables, payments, pins, dorms, levels (class types), etc. Routes live in `routes/tenant.php` and are loaded only for tenant domains (middleware: `InitializeTenancyByDomain`, `PreventAccessFromCentralDomains`, `tenant.active`).

- **Roles (tenant)**  
  Super Admin, Admin, Teacher, Accountant, Librarian, Student, Parent. Access is enforced in controllers and via the `Qs` helper (e.g. `Qs::userIsTeamSA()`).

---

## Features & modules

- **Attendance** — Weekly grid, reports, and dashboard integration (tenant migrations under `database/migrations/tenant/` for `attendances` and related settings).
- **Marks & tabulation** — Tabulation views, printable marksheets (including annual layouts), roster-style print, assessment locking driven from system settings, and refined web/print styling for marksheets.
- **Parents** — Dedicated parent user management for staff, plus parent portal views (e.g. children list) aligned with the main layout.
- **Students** — Profile/record routes for students; marksheet access where configured without extra PIN steps for students/parents as applicable.
- **UI** — Sidebar/top navigation, support team dashboard, and shared layout updates for a consistent minimal look.

---

## Authentication

Sign-in uses a **`username`** field (not email as the primary login identifier). The login form posts `username` and `password`; `App\Http\Controllers\Auth\LoginController` uses `LoginController::username()` so Laravel’s authentication resolves the user by `username`.

- **Central (landlord)** users: the `users` table includes a `username` column (see migration `2026_03_30_140000_add_username_to_central_users_table.php`). Run `php artisan migrate` on the central database after pulling updates.
- **Tenant** users: usernames are stored on the tenant `users` table. For existing deployments, you can optionally run `Database\Seeders\UserUsernameSeeder` in tenant context to normalize usernames and align passwords with your policy (run only when you intend to bulk-update users).

---

## Requirements

- PHP ^7.2 or ^8.0
- Composer
- Node.js & NPM (for front-end assets if you build them)
- MySQL or MariaDB
- [Laravel 8 requirements](https://laravel.com/docs/8.x#server-requirements) (BCmath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML)

---

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url> e-maaree && cd e-maaree
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Edit `.env`: set `APP_NAME`, `APP_URL`, and database credentials (see [Configuration](#configuration)).

4. **Database**
   - Create the database (e.g. `emaaree_db`).
   - Run central migrations (includes landlord `users.username` and any other central tables):
   ```bash
   php artisan migrate
   ```

5. **Seed central (and optional tenant) data**
   ```bash
   php artisan db:seed
   ```

6. **Create a tenant school**
   ```bash
   php artisan tenancy:create "My School" school1.localhost --id=school1
   ```
   This creates the tenant DB and runs tenant migrations (including attendance and related tables). Optionally seed tenants as per your seeders. For existing tenants after pulling new code, run tenant migrations from your deployment process (e.g. `php artisan tenants:migrate` if you use the package’s artisan commands).

7. **Front-end assets (optional)**
   ```bash
   npm install && npm run dev
   ```
   If you do not build assets, the project may still rely on pre-built assets under `public/`.

---

## Configuration

| Variable | Purpose |
|----------|--------|
| `APP_NAME` | Application name (e.g. `e-maaree`). |
| `APP_URL` | Base URL for the **central** app (e.g. `http://localhost:8001`). |
| `APP_DEBUG` | Set to `false` in production. |
| `DB_DATABASE` | Central (landlord) database name. |
| `DB_USERNAME`, `DB_PASSWORD` | MySQL credentials for central DB. |

Tenant databases are created by stancl/tenancy (separate DB per tenant). For local tenant access, ensure the tenant subdomain resolves (e.g. in `hosts`: `127.0.0.1 school1.localhost`). Do not set `SESSION_DOMAIN` for local development with port-based URLs to avoid 419 errors.

---

## Running the application

- **Central (landlord):** open `APP_URL` (e.g. `http://localhost:8001`).
- **Tenant (school):** open the tenant subdomain (e.g. `http://school1.localhost:8001`).

```bash
php artisan serve --port=8001
```

---

## Local development quick reference

| Step | Command or note |
|------|------------------|
| Serve app | `php artisan serve --port=8001` — use the same port as `APP_URL` (e.g. `http://localhost:8001`). |
| Tenant host | Add e.g. `127.0.0.1 school1.localhost` to your OS `hosts` file so `http://school1.localhost:8001` resolves. |
| Central DB after `git pull` | `php artisan migrate` (adds/updates central tables such as `users.username`). |
| Existing tenants after `git pull` | `php artisan tenants:migrate` — or target one tenant: `php artisan tenants:migrate --tenants=school1` (see [stancl docs](https://tenancyforlaravel.com/) / `TENANCY_VERIFICATION.md`). |
| First landlord user | Register on the **central** URL (`/register`), then set `username` on that user (login uses **username**; registration does not fill it). Example: `php artisan tinker` → `App\User::where('email', 'your@email')->update(['username' => 'admin']);` |

---

## Project structure

| Path | Description |
|------|-------------|
| `app/Http/Controllers/Central/` | Landlord-only controllers (e.g. `LandlordController`). |
| `app/Http/Controllers/` | Tenant-facing controllers (e.g. `SupportTeam`, `SuperAdmin`). |
| `app/Helpers/Qs.php` | Global helper: hashing, settings, role checks, redirects (see [Conventions](#conventions-for-developers)). |
| `routes/web.php` | Central routes (landlord dashboard, auth). |
| `routes/tenant.php` | Tenant routes (school management). |
| `resources/views/` | Blade templates; `layouts/master.blade.php`, `partials/`, tenant views under `pages/` (e.g. `support_team/`, `parent/`). |
| `config/` | Laravel and tenancy config. Tenant DB connection is handled by stancl/tenancy. |

---

## Conventions for developers

- **IDs in URLs**  
  Use `Qs::hash($id)` when generating URLs and `Qs::decodeHash($str)` when resolving IDs from routes. This keeps internal IDs non-sequential in the UI.

- **Settings / system name**  
  `Qs::getSetting('key')` and `Qs::getSystemName()` read from the tenant’s settings (used in PDF/Excel titles, etc.).

- **Role checks**  
  Use `Qs::userIsSuperAdmin()`, `Qs::userIsTeamSA()`, `Qs::userIsTeacher()`, `Qs::userIsParent()`, etc., for access control and UI visibility.

- **Blade**  
  Main layout: `layouts/master.blade.php`. Content: `@yield('content')`. Scripts: `@yield('scripts')`. Global scripts (DataTables, etc.) are loaded from `partials/inc_bottom.blade.php`.

- **DataTables**  
  Export buttons (Excel, PDF) are wired in views (e.g. student list) via DataTables Buttons (HTML5). Tables using only search/sort use class `datatable-basic` and are initialized in the same view’s `@section('scripts')` with a dom string that omits the button column (no `B`).

- **Tenancy**  
  Do not mix central and tenant logic. Central routes and controllers stay in `web.php` and `Central/`; all school-specific logic stays in `tenant.php` and the rest of `app/`.

---

## Default credentials

Use the **Username** column on the login form (not necessarily the full email).

After tenant seeding (e.g. `TenantDatabaseSeeder` / `php artisan db:seed` in tenant context, depending on your setup):

| Role | Username | Email | Password |
|------|----------|--------|----------|
| Super Admin | superadmin | superadmin@emaaree.test | password |
| Admin | admin | admin@admin.com | password |
| Teacher | teacher | teacher@teacher.com | password |
| Parent | parent | parent@parent.com | password |
| Accountant | accountant | accountant@accountant.com | password |
| Student | student | student@student.com | password |

**Central (landlord)** — there is no default landlord user in `DatabaseSeeder`. Create the first account via **`/register`** on the central domain (`APP_URL`), then set a **`username`** on that row (required for login). You can use `php artisan tinker` as in [Local development quick reference](#local-development-quick-reference). Each central user must have a unique `username`.

**Change all default passwords before any production or shared environment.**

---

## Testing

```bash
composer test
# or
./vendor/bin/phpunit
```

(Adjust if your project uses a different test command or suite.)

---

## Deployment

- Set `APP_ENV=production`, `APP_DEBUG=false`, and a strong `APP_KEY`.
- Configure production DB and run `php artisan migrate --force` for central (tenant migrations run when tenants are created or via your deployment process).
- Run `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` as appropriate.
- Ensure `storage` and `bootstrap/cache` are writable; run `php artisan storage:link` if needed.
- Point the web server to the `public` directory. Ensure the central domain and all tenant subdomains resolve to this application.

---

## Security & license

- **Security:** Report vulnerabilities to ABQO Technology in private. Do not open public issues for security-sensitive matters.
- **Contributing:** Contributions are welcome via pull requests.
- **License:** MIT. See [LICENSE](LICENSE). Copyright (c) 2026 ABQO Technology.
