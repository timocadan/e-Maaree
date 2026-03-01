# e-maaree

**Multi-tenant School Management System** for K–12 schools and colleges. Each school runs on its own subdomain with full data isolation.

**Developed by [ABQO Technology](https://web.facebook.com/timocadaan)**  
**Lead Developer:** Cumar Timocade

---

## Overview

e-maaree is built on **Laravel 8** and **[stancl/tenancy](https://tenancyforlaravel.com/)**. It provides:

- **Central (landlord) app** – landing, **SaaS Control Center** (Landlord Dashboard), and tenant provisioning
- **Tenant app** – full school management per subdomain (e.g. `school1.yourdomain.com`)
- **Roles:** Super Admin, Admin, Teacher, Accountant, Librarian, Student, Parent
- **Features:** Students, Classes, Sections, Subjects, Exams, Grades, Marks, Timetables, Payments, Pins, Dorms, **Levels (Class Types)**, and more

### Recent updates (e-maaree)

- **Super Admin – Manage Levels (Class Types)** – Define educational levels (e.g. Primary, Secondary). Used for classes, fees, and reports. Routes: `POST /super_admin/levels/{id}/update`, `GET /super_admin/levels/{id}/delete`.
- **Simplified Admit Student form** – Two-step wizard: **Step 1** – Personal data (name, address, gender, phone, DOB, **parent name**, **parent phone**). **Step 2** – Student data (class, section, year admitted, admission number). Dormitory, nationality/state/LGA, blood group, and passport photo are hidden for a lean MVP.
- **Smart Admission** – Parent is resolved by **parent phone**: if a user with `user_type = parent` and the given phone exists, the student is linked to that parent; otherwise a new parent user is created (name, phone, default password `123456`). No need to pick from a long parent list.

### Landlord Dashboard (SaaS Control Center)

- **School list** – view all tenants (schools) with ID, name, domain(s), status, and created date
- **Search** – filter schools by name, ID, or domain (instant search)
- **Suspend / Activate** – toggle school status (active ↔ suspended); suspended schools cannot access their dashboard and see a contact message (ABQO Technology)
- **Manage** – edit school name and domain, reset super admin password
- **Delete** – remove a school and its database (with confirmation)
- **Add New School** – create a tenant with ID, name, and domain

---

## Requirements

- PHP ^7.2 | ^8.0
- Composer
- Node.js & NPM (for front-end assets)
- MySQL / MariaDB
- Laravel 8.x requirements: [Laravel 8 Docs](https://laravel.com/docs/8.x)

---

## Installation

1. **Clone or download** the repository.
2. **Install PHP dependencies:**
   ```bash
   composer install
   ```
3. **Copy environment file and configure:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Edit `.env`: set `APP_NAME=e-maaree`, `APP_URL`, and database credentials (`DB_DATABASE=emaaree_db`, etc.).
4. **Create the database** (e.g. `emaaree_db`) and run migrations:
   ```bash
   php artisan migrate
   ```
5. **Seed central and (optionally) tenant data:**
   ```bash
   php artisan db:seed
   ```
6. **Create a tenant (school)** and run tenant migrations/seed:
   ```bash
   php artisan tenancy:create "My School" school1.localhost --id=school1
   ```
7. **NPM (optional, for assets):**
   ```bash
   npm install && npm run dev
   ```

---

## Default login (after seeding)

| Account   | Username    | Email                     | Password  |
|----------|-------------|---------------------------|-----------|
| Super Admin | superadmin | superadmin@emaaree.test   | password  |
| Admin    | admin       | admin@admin.com           | password  |
| Teacher  | teacher     | teacher@teacher.com       | password  |
| Parent   | parent      | parent@parent.com         | password  |
| Accountant | accountant | accountant@accountant.com | password  |
| Student  | student     | student@student.com       | password  |

*(Change default passwords in production.)*

---

## Running the app

- **Central (landlord):** Visit `APP_URL` (e.g. `http://localhost:8001`).
- **Tenant (school):** Use the tenant subdomain (e.g. `http://school1.localhost:8001`). Ensure the host resolves (e.g. `127.0.0.1 school1.localhost` in `hosts`).

```bash
php artisan serve --port=8001
```

---

## Project structure (branding)

- **Product name:** e-maaree  
- **Company:** ABQO Technology  
- **Lead developer:** Cumar Timocade – [Facebook](https://web.facebook.com/timocadaan)  
- **License:** MIT © 2026 ABQO Technology  

---

## Security

If you discover a security vulnerability, please report it to **ABQO Technology** responsibly. Do not open public issues for security-sensitive matters.

---

## Contributing

Contributions and suggestions are welcome via Pull Requests.

---

## License

This project is licensed under the MIT License – see the [LICENSE](LICENSE) file for details.  
Copyright (c) 2026 ABQO Technology.
