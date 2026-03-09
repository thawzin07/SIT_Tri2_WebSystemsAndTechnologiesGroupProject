# PulsePoint Fitness

PulsePoint Fitness is a full-stack gym membership management web application built with plain PHP 8+, MySQL 8+, Bootstrap 5, HTML5, CSS, and vanilla JavaScript.

## Features

- Public pages: Home, About, Plans, Trainers, Class Schedule, Locations, Contact, FAQ
- Authentication: Register, Member Login, Admin Login, Logout
- Member module: profile update, subscribe/renew/cancel membership, class booking/cancellation, waitlist join/leave, auto-promotion from waitlist
- Admin module: dashboard statistics and CRUD for users, plans, trainers, classes, locations, bookings, messages
- Security: PDO prepared statements, password hashing, session auth, role-based access control, CSRF protection, output escaping, server-side validation
- Access control: explicit role checks for `admin` and `member` routes

## Tech Stack

- Frontend: HTML5, Bootstrap 5, CSS, JavaScript
- Backend: PHP 8+
- Database: MySQL 8+

## Project Structure

- `public/` front controller and static assets
- `app/Controllers/` request handlers
- `app/Models/` data access classes
- `app/Views/` layouts, partials, pages, admin views
- `config/` app, DB, route config
- `database/migrations/` SQL schema
- `database/seeds/` SQL seed data
- `tests/` lightweight feature checks (route wiring baseline)

## Current Status

- Functional MVP/prototype for coursework requirements
- Booking flow includes transactional seat handling + waitlist auto-promotion
- Not production-grade yet: no CI/CD pipeline, limited automated testing, and no full observability stack

## Setup

1. Copy `.env.example` to your environment (or set equivalent env vars):
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
2. Create database and tables:
   - Run `database/migrations/001_init_schema.sql`
   - Run `database/migrations/002_waitlist_and_booking_integrity.sql`
3. Insert sample data:
   - Run `database/seeds/001_seed_data.sql`
4. Serve app from `public/` as web root.
   - Apache: point DocumentRoot to `.../GroupProject/public`
   - Or PHP built-in server: `php -S localhost:8000 -t public` (if PHP CLI installed)

## Quick Verification

- Optional route sanity check:
  - `php tests/FeatureTest.php`
- Confirm migration effects in DB:
  - `class_waitlist` table exists
  - new indexes exist on `classes` and `bookings`

## Seed Credentials

- Admin:
  - Email: `admin@pulsepoint.test`
  - Password: `Admin@123`
- Member:
  - Email: `member@pulsepoint.test`
  - Password: `Member@123`

## Notes

- This project is a fictional student demo for web systems coursework.
- No external backend framework is used.
