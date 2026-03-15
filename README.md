# PulsePoint Fitness

PulsePoint Fitness is a student full-stack gym membership web app built with plain PHP, MySQL, Bootstrap, CSS, and vanilla JavaScript.

## Features

- Public pages: Home, About, Plans, Trainers, Schedule, Locations, Contact, FAQ
- Auth: register, member login, admin login, logout
- Member module: profile, memberships, bookings, waitlist
- Admin module: dashboard + CRUD for users, plans, trainers, classes, locations, bookings, messages
- Security basics: prepared statements, password hashing, CSRF tokens, escaping, role checks

## Tech Stack

- PHP 8+
- MySQL 8+
- Bootstrap 5 + HTML/CSS/JavaScript

## Project Structure

- `public/` app entry and static assets
- `app/Controllers/` controllers
- `app/Models/` models
- `app/Views/` pages, layouts, partials
- `config/` app and route config
- `database/migrations/` schema migrations
- `database/seeds/` seed scripts

## Quick Setup

1. Copy env file:
```bash
cp .env.example .env
```
On Windows PowerShell:
```powershell
Copy-Item .env.example .env
```

2. Update `.env`:
- `APP_URL=http://localhost:8000`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_NAME=pulsepoint_fitness`
- `DB_USER=root`
- `DB_PASS=<your_password>`

3. Create database:
```sql
CREATE DATABASE IF NOT EXISTS pulsepoint_fitness;
```

4. Run SQL scripts in this order:

Migrations:
1. `database/migrations/001_init_schema.sql`
2. `database/migrations/002_waitlist_and_booking_integrity.sql`
3. `database/migrations/003_add_media_payment_notifications_qr.sql`

Seeds:
1. `database/seeds/001_seed_data.sql`
2. `database/seeds/002_seed_media_payment_qr_demo.sql`

5. Start the app:
```bash
php -S localhost:8000 -t public
```
If `php` is not in PATH (example XAMPP):
```powershell
& "C:\xampp\php\php.exe" -S localhost:8000 -t public
```

6. Open:
- `http://localhost:8000/`

## Demo Accounts

- Admin: `admin@pulsepoint.test` / `Admin@123`
- Member: `member@pulsepoint.test` / `Member@123`

## Notes

- This is a fictional coursework project.
- No external backend framework is used.
