# PulsePoint Fitness - Local Setup Guide

## 1) Prerequisites

- Git
- PHP 8.2+ (CLI available in PATH)
- MySQL 8+ (or MariaDB compatible)
- VS Code with PHP Debug extension (`xdebug.php-debug`)
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

Start your database server (MySQL/MariaDB).

## 5) Check Your Ports

App port (PHP built-in server):

1. Default app URL is `http://localhost:8000`
2. Keep `APP_URL` in `.env` aligned with this value
3. `3306` is the MySQL port, not the website URL port

MySQL port:

1. Check your MySQL server config (`my.ini` / `my.cnf`) or client connection settings
2. Default is usually `3306`

## 6) Configure Environment

Create `.env` in project root by copying `.env.example`.

Set values to match your machine:

```env
APP_NAME=PulsePoint Fitness
APP_URL=http://localhost:8000
APP_ENV=development
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=pulsepoint_fitness
DB_USER=root
DB_PASS=<your_mysql_password>
```

If your MySQL password is empty, use:

```env
DB_PASS=
```

## 7) Create Database and Tables

1. Create database `pulsepoint_fitness` (if not created yet)
2. Run SQL files against this database using your SQL client (MySQL CLI, Workbench, DBeaver, etc.)
3. Run files in this order:

- `database/migrations/001_init_schema.sql`
- `database/migrations/002_waitlist_and_booking_integrity.sql`
- `database/seeds/001_seed_data.sql` (recommended)

Example CLI commands:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS pulsepoint_fitness"
mysql -u root -p pulsepoint_fitness < database/migrations/001_init_schema.sql
mysql -u root -p pulsepoint_fitness < database/migrations/002_waitlist_and_booking_integrity.sql
mysql -u root -p pulsepoint_fitness < database/seeds/001_seed_data.sql
```

## 8) Run the App (No XAMPP)

Option A: terminal

```bash
php -S localhost:8000 -t public
```

Option B: VS Code launch profile

1. Open Run and Debug
2. Select `PHP: Serve current project (localhost:8000)`
3. Press F5

## 9) Debug in VS Code (launch.json)

1. Ensure Xdebug is installed for your PHP runtime
2. Keep `.vscode/launch.json` profile `PHP: Serve current project (localhost:8000)` selected
3. Set breakpoints and run with F5
4. Browse `http://localhost:8000` to hit breakpoints

## 10) Verify Setup

Open:

- `http://localhost:8000/`
- `http://localhost:8000/about`

Expected:

- Pages load without DB errors
- `class_waitlist` table exists in your DB client

## 11) Seed Login Accounts

Admin:

- Email: `admin@pulsepoint.test`
- Password: `Admin@123`

Member:

- Email: `member@pulsepoint.test`
- Password: `Member@123`

## 12) Daily Workflow

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

## 13) Common Issues

`localhost refused to connect`:

- PHP built-in server not started
- Wrong app URL or port in `.env`

`Database connection failed`:

- Wrong `DB_PORT`, `DB_USER`, or `DB_PASS`
- MySQL not started or not listening on configured host/port

`Breakpoints not hit`:

- Xdebug not installed/enabled for the PHP executable used by VS Code
- `xdebug.client_port` does not match launch.json port (`9003`)
- VS Code is not running `PHP: Serve current project (localhost:8000)`

`Table 'class_waitlist' doesn't exist`:

- `002_waitlist_and_booking_integrity.sql` not executed

`Access denied for user`:

- Wrong MySQL password in `.env`

## 14) What Not to Commit

Do not commit:

- `.env`
- Local-only credentials
