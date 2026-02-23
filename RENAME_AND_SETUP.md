# Folder and database rename – e-maaree

Use these steps to rename the project folder and database so the codebase fully reflects ABQO Technology / e-maaree.

---

## Task 4a: Rename root folder from `lav_sms-master` to `e-maaree`

**Windows (PowerShell or CMD):**

1. Close the project in your IDE and any terminals using this folder.
2. Go to the **parent** directory (the folder that contains `lav_sms-master`):
   ```powershell
   cd "D:\BACK UP DATA\DATA STORAGE\Vibe Coding\School Management System"
   ```
3. Rename the folder:
   ```powershell
   ren "lav_sms-master" "e-maaree"
   ```
4. Reopen the project from the new path: `...\School Management System\e-maaree`.

**macOS / Linux (terminal):**

1. Close the project and any processes using it.
2. Go to the parent directory:
   ```bash
   cd "/path/to/parent/of/lav_sms-master"
   ```
3. Rename:
   ```bash
   mv lav_sms-master e-maaree
   ```
4. Reopen the project: `.../e-maaree`.

---

## Task 4b: Rename database from `lav_sms` to `emaaree_db` and update `.env`

**1. Create the new database (MySQL/MariaDB)**

Using MySQL CLI or a client (e.g. phpMyAdmin, HeidiSQL):

```sql
CREATE DATABASE emaaree_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**2. (Optional) Migrate data from old DB to new**

If you want to keep existing data:

- **Option A – dump and restore (recommended):**
  ```bash
  mysqldump -u root -p lav_sms > lav_sms_backup.sql
  mysql -u root -p emaaree_db < lav_sms_backup.sql
  ```
- **Option B – fresh install:**  
  Use the new empty `emaaree_db` and run migrations + seed (see below). No need to rename the old DB.

**3. Update `.env`**

Ensure these values (they should already be set by the deep clean):

```env
APP_NAME=e-maaree
APP_URL=http://localhost:8001

DB_DATABASE=emaaree_db
DB_USERNAME=root
DB_PASSWORD=
```

**4. Run migrations (and seed) on the new database**

From the project root (e.g. `e-maaree`):

```bash
php artisan migrate
php artisan db:seed
```

For tenants (if you use multi-tenancy):

```bash
php artisan tenancy:create "My School" school1.localhost --id=school1
```

**5. (Optional) Drop the old database**

Only after you have verified that `emaaree_db` works:

```sql
DROP DATABASE lav_sms;
```

---

## Summary

| Step | Action |
|------|--------|
| 1 | Rename folder `lav_sms-master` → `e-maaree` (from parent directory). |
| 2 | Create database `emaaree_db`. |
| 3 | Migrate data from `lav_sms` to `emaaree_db` (or use fresh migrations + seed). |
| 4 | Confirm `.env` has `DB_DATABASE=emaaree_db`, `APP_NAME=e-maaree`, `APP_URL=http://localhost:8001`. |
| 5 | Run `php artisan migrate` and `php artisan db:seed` (and tenant commands if needed). |

After this, the project folder and database will match the e-maaree / ABQO Technology branding.
