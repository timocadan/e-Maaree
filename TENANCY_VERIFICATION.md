# Multi-Tenancy Verification Plan

## Prerequisites

- Central DB migrated: `php artisan migrate` (creates `tenants`, `domains` on central).
- For local subdomain testing: add `127.0.0.1 school1.localhost` (and similar) to hosts file, or use a tool like Laravel Valet / ngrok with wildcard subdomains.

## 1. Create a tenant (school)

```bash
php artisan tenancy:create "School One" school1.localhost
```

Or with explicit id:

```bash
php artisan tenancy:create "School One" school1.localhost --id=school1
```

This creates the tenant, its database (`tenant_<id>` by default), runs `database/migrations/tenant/*` and `php artisan tenants:seed` (DatabaseSeeder) for that tenant.

## 2. Run migrations only for an existing tenant

```bash
php artisan tenants:migrate --tenants=<tenant_id>
```

Example: `php artisan tenants:migrate --tenants=school1`

## 3. Seed only for an existing tenant

```bash
php artisan tenants:seed --tenants=<tenant_id>
```

Example: `php artisan tenants:seed --tenants=school1`

## 4. Run for all tenants

```bash
php artisan tenants:migrate
php artisan tenants:seed
```

---

# Test Cases

## TC1: Central domain shows landlord page

1. Open `http://localhost` or `http://127.0.0.1`.
2. **Expected:** Central landing message (“Central application. Use a tenant subdomain…”). No login form.
3. **Failure:** Tenant dashboard or 404 → central_domains or RouteServiceProvider wiring wrong.

## TC2: Tenant subdomain shows login (isolation)

1. Create tenant: `php artisan tenancy:create "School A" school1.localhost --id=school1`.
2. Ensure `school1.localhost` resolves (hosts: `127.0.0.1 school1.localhost`).
3. Open `http://school1.localhost` (or `http://school1.localhost:8000` if using `php artisan serve` with correct host).
4. **Expected:** Tenant app: login page or redirect to login.
5. **Failure:** Central landing or “Tenant could not be identified” → domain not in `domains` table or central_domains including this host.

## TC3: Login on tenant A (Super Admin / Admin / Teacher / Student / Parent)

1. On `http://school1.localhost`, log in with a user seeded for that tenant (e.g. from DatabaseSeeder/UsersTableSeeder).
2. **Expected:** Dashboard for that role (Super Admin, Admin, Teacher, Student, Parent) for School A only.
3. **Failure:** Wrong dashboard or “tenant could not be identified” → tenancy not initialized or auth using wrong DB.

## TC4: Data isolation (two tenants)

1. Create second tenant: `php artisan tenancy:create "School B" school2.localhost --id=school2`.
2. Seed both (or seed separately): `php artisan tenants:seed --tenants=school1`, `php artisan tenants:seed --tenants=school2`.
3. Log in on `http://school1.localhost` as a user of School A. Note students/classes shown.
4. Open a new browser/profile, go to `http://school2.localhost`, log in as a user of School B.
5. **Expected:** School B sees only its own students/classes/users. No data from School A.
6. **Failure:** School B sees School A data → DB not switched per tenant (check bootstrappers, connection).

## TC5: Central domain does not serve tenant routes

1. Open `http://localhost/login` or `http://127.0.0.1/login`.
2. **Expected:** 404 or central-only behaviour, not tenant login page.
3. **Failure:** Tenant login page on central → PreventAccessFromCentralDomains or central route ordering wrong.

## TC6: Session / auth per tenant

1. Log in on `http://school1.localhost`.
2. In same browser open `http://school2.localhost`.
3. **Expected:** Not logged in on school2 (different subdomain = different session).
4. Log in on school2. Then go back to school1.
5. **Expected:** Still logged in on school1 (sessions are per host).

## TC7: Migrate and seed only one tenant

1. Create tenant: `php artisan tenancy:create "School C" school3.localhost --id=school3`.
2. Run `php artisan tenants:migrate --tenants=school3` and `php artisan tenants:seed --tenants=school3`.
3. **Expected:** Only school3 DB updated; school1/school2 unchanged.
4. Open `http://school3.localhost` and log in with seeded user.
5. **Expected:** App works; data present for School C only.

## TC8: Role-based access per tenant

1. On `http://school1.localhost`, log in as Super Admin. Access `/super_admin/settings`.
2. **Expected:** Allowed.
3. Log in as Teacher on same tenant. Access `/super_admin/settings`.
4. **Expected:** Denied (redirect or 403) per your role middleware.
5. Repeat for Admin, Student, Parent on same tenant. **Expected:** Access matches role (teamSA, teamSAT, my_parent, etc.).

---

# Compatibility (Laravel 8, PHP 8.3, stancl/tenancy 3.6)

- **Laravel 8:** stancl/tenancy 3.6 supports Laravel 8. Keep `$namespace` in RouteServiceProvider for controller route declarations.
- **PHP 8.3:** If you see deprecations or errors from dependencies (e.g. fruitcake/laravel-cors, fideloper/proxy), upgrade to Laravel 9/10 or replace with supported packages (e.g. Laravel CORS built-in, TrustProxies).
- **Session domain:** Do not set `SESSION_DOMAIN` to a wildcard (e.g. `.localhost`) if you want strict per-tenant sessions; leave it unset or set per environment.
- **APP_URL:** Set to central URL (e.g. `http://localhost`) for artisan and central domain; tenant URLs are determined by request host.
