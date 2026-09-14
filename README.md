# Student Management System — Laravel + Livewire

A practice project built to learn and demonstrate Laravel + Livewire: a real Livewire data table (live search, sort, filter, pagination — no page reloads), breadcrumb navigation, required-field validation that actually blocks submission on the server (not just in the browser), role-based privileges, activity logging via a real database trigger, and a database VIEW + transaction-based "stored procedure equivalent" for safe course enrollment.

## Features

- **Live data table** — search, column sorting, course/status filters, and pagination, all powered by Livewire with no custom JavaScript or page reloads
- **Required-field validation** — `$this->validate()` runs server-side before any save; if a required field is missing, nothing is written to the database and the form re-renders with inline error messages
- **Breadcrumbs** — a single reusable `<x-breadcrumbs>` Blade component used across every page
- **Role-based privileges** — admin / staff / viewer roles enforced through Laravel Gates, checked in three layers: route middleware, component `mount()`, and Blade `@can` directives
- **Activity logs, two ways on purpose** — student creates/updates are logged automatically by a real `AFTER INSERT` / `AFTER UPDATE` database trigger; deletes, logins, and user management are logged from application code, since a delete trigger can't reliably capture *who* performed the delete
- **Enrollment "stored procedure"** — `EnrollmentService` wraps a capacity check and the enrollment insert in one `DB::transaction()` with `lockForUpdate()`, giving the same atomicity a stored procedure would provide (SQLite, used here for development, doesn't support real stored procedures — `database/mysql_examples.sql` shows the equivalent real MySQL procedure)
- **A database VIEW** — `student_enrollment_summary` joins students and courses and computes seats taken, so any page or report can query it directly instead of repeating the join

## Tech stack

Laravel 11, Livewire 3, Blade, SQLite (for local development), MySQL syntax reference included for production-style database objects (views/procedures/triggers).

## Demo accounts

| Email | Password | Role | Can do |
|---|---|---|---|
| admin@example.com | admin123 | admin | Everything — manage students, users, view logs |
| staff@example.com | staff123 | staff | Create/edit students only |
| viewer@example.com | viewer123 | viewer | Read-only student list |

## Getting started

```bash
git clone https://github.com/mhmddirany/School-student-manager.git
cd School-student-manager

composer install
cp .env.example .env
php artisan key:generate

# SQLite database file
touch database/database.sqlite
# (On Windows PowerShell, use: New-Item database/database.sqlite)

# Make sure .env has: DB_CONNECTION=sqlite

php artisan migrate:fresh --seed
php artisan serve
```

Then open **http://localhost:8000**.

## What's where
