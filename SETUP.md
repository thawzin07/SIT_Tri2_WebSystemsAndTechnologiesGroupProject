# PulsePoint Fitness - Local Setup Guide

## 1) Prerequisites

- Git
- XAMPP (Apache + MySQL + phpMyAdmin)
- A browser

## 2) Get the Project

```bash
git clone <repo-url>
cd GroupProject
```

## 3) Use Your Own Branch

Branch naming format:

- `<name>-dev`

Commands:

```bash
git checkout main
git pull origin main
git checkout <name>-dev
```

If your branch does not exist yet:

```bash
git checkout -b <name>-dev
git push -u origin <name>-dev
```

## 4) Start Local Services

In XAMPP Control Panel:

- Start `Apache`
- Start `MySQL`

## 5) Check Your Ports

Apache port:

1. XAMPP -> Apache -> `Config` -> `httpd.conf`
2. Find `Listen` (example: `Listen 8081`)
3. Your app URL is `http://localhost:<apache_port>`

MySQL port:

1. XAMPP -> MySQL -> `Config` -> `my.ini`
2. Find `port=` under `[mysqld]` (example: `3307`)

## 6) Configure Environment

Create `.env` in project root by copying `.env.example`.

Set values to match your machine:

```env
APP_NAME=PulsePoint Fitness
APP_URL=http://localhost:<apache_port>
APP_ENV=development
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=<mysql_port>
DB_NAME=pulsepoint_fitness
DB_USER=root
DB_PASS=<your_mysql_password>
```

If your MySQL password is empty, use:

```env
DB_PASS=
```

## 7) Create Database and Tables

Open phpMyAdmin.

1. Create database `pulsepoint_fitness` (if not created yet)
2. Open database -> `SQL` tab
3. Run files in this order:

- `database/migrations/001_init_schema.sql`
- `database/migrations/002_waitlist_and_booking_integrity.sql`
- `database/seeds/001_seed_data.sql` (recommended)

## 8) Verify Setup

Open:

- `http://localhost:<apache_port>/`
- `http://localhost:<apache_port>/about`

Expected:

- Pages load without DB errors
- `class_waitlist` table exists in phpMyAdmin

## 9) Seed Login Accounts

Admin:

- Email: `admin@pulsepoint.test`
- Password: `Admin@123`

Member:

- Email: `member@pulsepoint.test`
- Password: `Member@123`

## 10) Daily Workflow

Before coding:

```bash
git checkout main
git pull origin main
git checkout <name>-dev
git merge main
```

After coding:

```bash
git add .
git commit -m "your message"
git push origin <name>-dev
```

Then open PR:

- Source: `<name>-dev`
- Target: `main`

## 11) Common Issues

`localhost refused to connect`:

- Apache not started
- Wrong Apache port in URL

`Database connection failed`:

- Wrong `DB_PORT`, `DB_USER`, or `DB_PASS`
- MySQL not started

`Table 'class_waitlist' doesn't exist`:

- `002_waitlist_and_booking_integrity.sql` not executed

`Access denied for user`:

- Wrong MySQL password in `.env`

## 12) What Not to Commit

Do not commit:

- `.env`
- Local-only credentials
