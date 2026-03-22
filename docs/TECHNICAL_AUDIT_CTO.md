# e-maaree Technical Audit — Lead Technical Architect / CTO

**Date:** March 2026  
**Scope:** Full codebase scan for multi-tenancy, admission logic, branding, security, scalability, and technical debt.

---

## Task 1: System Audit & Explanation

### 1.1 Multi-Tenancy: Landlord vs Tenant Databases

**How it’s wired:**

- **Central (Landlord) app**
  - Served from **central domains** only: `config/tenancy.php` → `central_domains` (e.g. `127.0.0.1`, `localhost`, plus `CENTRAL_DOMAINS` from `.env`).
  - Uses the **default DB connection** from `config/database.php`: `mysql` with `DB_DATABASE` (e.g. **emaaree_db**). This is the “landlord” DB.
  - **Routes:** `routes/web.php` is loaded only for these domains (via `RouteServiceProvider` binding routes per central domain). Used for landlord dashboard, school CRUD, auth, and tenant lifecycle.

- **Tenant app**
  - Any request **not** to a central domain is treated as **tenant**. Middleware order (in `TenancyServiceProvider`): `PreventAccessFromCentralDomains` → `InitializeTenancyByDomain` (or subdomain/path variant). Tenant routes live in **`routes/tenant.php`** and are registered by `TenancyServiceProvider` (no domain constraint there; domain filtering is done by the middleware).
  - **Database:** `config/tenancy.php` → `database`:
    - `central_connection` = default `mysql` (landlord).
    - `template_tenant_connection` = `tenant_template` (same host/user/password as default; `database` value is overwritten per tenant).
    - Tenant DB name = **prefix + tenant id + suffix** → e.g. `tenant{uuid}` (prefix `tenant`, no suffix).
  - **Bootstrapping:** When tenancy is initialized, `DatabaseTenancyBootstrapper` switches the default DB connection to a **tenant** connection whose `database` is the tenant’s DB name. So all Eloquent and DB usage in that request run against that tenant DB. Cache and filesystem are also tenant-scoped via bootstrappers.

**Summary:** One landlord DB (e.g. **emaaree_db**) holds `tenants` and `domains`; each school is one tenant with its own DB (e.g. `tenant<uuid>`). Route and middleware split by host; Stancl’s bootstrappers switch DB/cache/filesystem per request.

---

### 1.2 Smart Admission & “Max+1” ID Generation

**Where it lives:**

- **Max+1 serial (per class):**  
  `App\Http\Controllers\AjaxController::get_next_admission_number($class_id)`  
  - Loads `StudentRecord` for the class, plucks `adm_no`, then parses each value for the **last numeric segment after the final `/`** (e.g. `XING/G8/009` → 9).  
  - Takes **max of those serials + 1**, zero-pads to 3 digits, and builds:  
    `{SCHOOL_ACRONYM}/{CLASS_SHORTNAME}/{NEXT_SERIAL}`  
    (e.g. `XING/G8/010`).  
  - So “Max+1” is **per class**, and deleted students don’t reuse serials as long as the format is consistent.

- **Admission number on create:**  
  `App\Http\Controllers\SupportTeam\StudentRecordController` (store):
  - If the request has `adm_no` containing `/`, it’s used as the username (and later `student_records.adm_no`).
  - Otherwise, username/adm_no is built as:  
    `Qs::getAppCode() . '/' . $ct . '/' . $sr['year_admitted'] . '/' . ($adm_no ?: mt_rand(1000, 99999))`.  
  - So the **frontend** (or API) typically calls `get_next_admission_number` for the class and can send that value as `adm_no`; the controller then uses it or falls back to the random segment.

**Summary:** “Smart Admission” is the combination of (1) the Ajax endpoint that suggests the next class-based serial (Max+1), and (2) the store logic that accepts a provided `adm_no` or generates one with a random tail. No separate “Smart Admission” service; it’s controller + Ajax.

---

### 1.3 CSS Branding: brand.css Override

**How it’s applied:**

- **Included after base theme:**  
  In `resources/views/partials/inc_top.blade.php` (tenant/central layout), **`brand.css`** is loaded **after** the main theme styles:
  - Bootstrap, Limitless layout/components/colors, then `qs.css`, then **`assets/css/brand.css`**.
- So **brand.css overrides** the original theme by **cascade and specificity**, not by a build step. Same in `central/layout.blade.php` and login partials.

**What it overrides (high level):**

- **Primary / red:** `.text-primary`, `.bg-primary`, `.btn-primary` → `#D32F2F` (and hover `#b71c1c`).
- **Sidebar:** `.sidebar-dark` → background `#1A1A1A`; active item `#D32F2F`; hover/open states `#2c2c2c`.
- **Navbar:** `.navbar-dark` → `#1A1A1A`; hover/active tint with red.
- **Content:** `.content-wrapper`, `.content`, `.page-header-light` → white; body/cards tuned for contrast.
- **Dashboard:** `.dashboard-stat-card.bg-brand-*` for red/black/teal/slate.
- **Links/buttons:** Generic content links and `.btn-link` → red.
- **Landlord:** Schools table wrapper and dropdown positioning.
- **Levels / wizard:** Action link colors, validation labels (positioning to avoid layout jump).

**Summary:** Brand is applied by loading **one override stylesheet** after the theme; no design tokens or CSS variables. To change brand globally, edit `public/assets/css/brand.css` (or the built path if you add a pipeline later).

---

## Task 2: “Blind Spots” — What a Chat-Based AI Might Miss

### 2.1 Technical Risks, Outdated Packages, Spaghetti Code

**Packages and versions (from `composer.json`):**

- **Laravel 8** and **PHP 7.2|8.0** — Laravel 8 is EOL; PHP 7.2 is long EOL. Security and compatibility risk.
- **stancl/tenancy 3.6** — Old 3.x; current 3.x targets Laravel 10+. You’re on an unmaintained combo (Laravel 8 + tenancy 3.6).
- **laravel/ui ^3.0** — Old; Laravel 10+ typically uses other stacks (e.g. Breeze/Jetstream).
- **fideloper/proxy** — Replaced by Laravel’s built-in `TrustProxies`; package may be redundant.
- **fruitcake/laravel-cors** — Laravel 9+ has CORS in framework; this can be removed on upgrade.
- **facade/ignition** — Old error page stack; Laravel 9+ uses Flare/spatie and different error handling.
- **hashids/hashids ^4.1** — Used in `Qs::hash` / `Qs::decodeHash` (see below).

**Spaghetti / consistency:**

- **No pagination anywhere.** All list endpoints use `->get()` (e.g. `StudentRecord`, `User`, payments, promotions, students by class). With 100+ schools and large classes, this will not scale and can cause timeouts and high memory.
- **Global route binding:** `RouteServiceProvider::boot()` binds **every** route parameter named `id` to `Qs::decodeHash($value)`. So any `{id}` in tenant routes is decoded; if a route expects a raw integer or another meaning, it can break or be confusing. Works today but is a hidden global rule.
- **Duplicate / legacy references:** e.g. `\App\User` in one controller vs `App\Models\User` elsewhere; some `User::` vs `$this->user` repo usage. Small but increases confusion and refactor risk.
- **Level delete is GET:** `Route::get('/levels/{level_id}/delete', ...)` — Destructive action on GET is bad practice (CSRF, prefetch, links). Should be DELETE with a form or JS and CSRF.

**Hashids salt is date-based (critical):**

- In `Qs::hash()` and `Qs::decodeHash()` the Hashids salt is **`date('dMY').'CJ'`** (e.g. `06Mar2026CJ`). So the same numeric ID produces a **different hash every day**. Any link or stored hash (e.g. in email, receipt, bookmark) **stops working after midnight**. This is a functional bug for “print receipt” links, password reset links that encode IDs, or any long-lived reference.

**Recommendation:** Use a **fixed salt** (e.g. `config('app.key')` or a dedicated `HASHIDS_SALT` in `.env`) so hashes are stable across days. Keep the rest of the logic the same.

---

### 2.2 Security: Authentication and Multi-Tenancy

**What’s in place:**

- Tenant routes are behind `web` + `InitializeTenancyByDomain` + `PreventAccessFromCentralDomains` + `tenant.active`. So only non-central hosts get tenant routes, and inactive/suspended tenants get the suspended view.
- Student-only access to payments/marks is enforced in controllers (e.g. `PaymentController` invoice/receipts, `MarkController`) by comparing `Auth::id()` to the resource’s `student_id` (with hashed IDs decoded first). Policy layer is minimal; logic is in controllers.
- `Authenticate` middleware redirects unauthenticated users to `route('login')`. No `AuthenticateSession` in the `web` stack (so no concurrent-session invalidation).
- Login uses Laravel’s `AuthenticatesUsers`; `username()` allows login by email or username. No visible lockout in the snippet (throttling is usually in the trait).

**Risks:**

- **Hash salt:** As above; date-based salt breaks links and any stored hashes. Not “auth” per se but weakens security and reliability of any flow that stores or sends hashes.
- **GET for delete:** Levels delete via GET; should be DELETE + CSRF.
- **Mass assignment:** Controllers use `$req->all()` in several places (e.g. Grade, Section, TimeTable, Payment, Promotion). If a request is forged with extra keys and models are not fully guarded, that can be a risk. Form Requests and `$fillable` are used in many places but not everywhere; worth a pass to ensure no `Model::create($request->all())` on sensitive models.
- **Tenant isolation:** Once tenancy is initialized, the default connection is the tenant DB, so Eloquent is tenant-scoped. No raw central-DB queries were seen in tenant controllers. Remaining risk is any future code that explicitly uses `DB::connection('mysql')` in tenant context; that would need review.
- **Session fixation / concurrent sessions:** `AuthenticateSession` is commented out in `Kernel`. For high-value roles (admin, accountant), consider re-enabling or equivalent to invalidate other sessions on password change.

**Summary:** No obvious tenant cross-access or auth bypass from the current scan. The main actionable items are: fix hash salt, change level delete to DELETE, and tighten mass assignment and session handling where needed.

---

### 2.3 Where an IDE-Aware Agent Is More Efficient

- **Refactors:** Can safely rename `decodeHash` usages, add pagination, or introduce a fixed hash salt across all call sites in one pass.
- **Terminal:** Can run `composer update --dry-run`, `php artisan route:list`, `php artisan migrate:status`, and fix env or config that would cause 500s (e.g. missing `APP_KEY`, wrong DB name).
- **Preventing 500s:** Can check that `Route::bind('id')` never receives a non-hash value where a hash is expected, and that all `decodeHash` results are validated (e.g. numeric and in range) before DB use. Can add null checks and type casts (e.g. `(int)`) where IDs are compared to `Auth::id()`.
- **Consistency:** Can replace `\App\User` with `App\Models\User`, and standardize on Form Requests and `$fillable` for all mass assignment.

---

## Task 3: Top 3 Backend Improvements for 100+ Schools

1. **Introduce pagination (and optional cursor) for all list endpoints**  
   Replace `->get()` on students by class, payment lists, promotions, users, exams, etc., with `->paginate(15)` or `->simplePaginate(15)`, and update views to use paginator links. Add index/constraints where needed (e.g. `student_records(my_class_id, user_id)`). This reduces memory and response time and avoids timeouts as data grows.

2. **Stabilize Hashids and harden ID handling**  
   Use a **fixed salt** (e.g. `config('app.key')` or `env('HASHIDS_SALT')`) in `Qs::hash`/`Qs::decodeHash` so links and stored hashes remain valid across days. Validate decode result (e.g. single integer, > 0) and use `(int)` when comparing to `Auth::id()` or foreign keys. This prevents 500s and broken receipts/links.

3. **Upgrade Laravel and PHP, then tenancy**  
   Plan a staged upgrade: PHP 8.1+ → Laravel 9 → Laravel 10, then bump stancl/tenancy to a version compatible with Laravel 10+. This restores security fixes and compatibility. Do it in a branch with full regression tests; run tenant migrations and central migrations after each step. Optionally move tenant creation/deletion to queued jobs so landlord stays responsive under load.

---

## What’s Broken or Ugly (Not Yet Discussed)

- **Hash salt is date-based:** Same ID has a different hash every day; receipts and any bookmarked or emailed links break after midnight. Fix: use a fixed salt.
- **Level delete is GET:** `/levels/{level_id}/delete` is GET; should be DELETE with CSRF.
- **No pagination:** Every list is full `get()`; at scale this will timeout and bloat memory.
- **Global `id` binding:** All `{id}` route parameters are decoded with `Qs::decodeHash`; if a route ever needs a non-hash id (e.g. numeric from another system), it will be wrong. Document or narrow the binding to specific route names/prefixes.
- **AuthenticateSession disabled:** Other sessions are not invalidated on password change; consider re-enabling or custom logic for sensitive roles.
- **Laravel 8 + PHP 7.2|8.0 + tenancy 3.6:** EOL stack; no security or compatibility fixes. Upgrade path is necessary for production at 100+ schools.
- **Mixed User reference:** `\App\User` in one place vs `App\Models\User`; normalize to the model and avoid root-namespace references.
- **Tenant creation/deletion are synchronous** (`shouldBeQueued(false)` in `TenancyServiceProvider`). For many tenants, this can make landlord requests slow; consider queuing for production.

This audit is a snapshot; run tests and a security review before large-scale rollout.
