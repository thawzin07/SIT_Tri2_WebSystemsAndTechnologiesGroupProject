# PulsePoint Fitness

## Team Members

- Thaw Zin Htun
- Chia Yu Wei
- Aiken Choo Hong Yi
- Tan Jian Yan
- Mohamad Danish Bin Mohammad



## Overview

PulsePoint Fitness is a full-stack gym management web application built with plain PHP, MySQL, Bootstrap, CSS, and vanilla JavaScript. It supports public browsing, member self-service flows, and full admin operations.

## Key Features

- Public pages: home, plans, schedule, trainers, locations, about, FAQ, contact
- Authentication: member/admin login, registration, logout
- Member module: profile updates, memberships, class booking and waitlist, billing history, invoice download
- Admin module: dashboard and CRUD for users, plans, trainers, classes, locations, bookings, and messages
- Payments: Stripe checkout + webhook reconciliation flow
- Notifications: queued email and Telegram support for payment events
- Chatbot: OpenAI-powered gym assistant
- Security controls: PDO prepared statements, CSRF protection, password hashing, output escaping, role-based access checks

## Tech Stack

- Backend: PHP 8+, MySQL 8+
- Frontend: HTML5, Bootstrap 5, CSS, vanilla JavaScript
- Integrations: Stripe API, OpenAI API, PHPMailer, Telegram Bot API
- Deployment: Google Cloud VM + GitHub Actions (`vm_hosting` branch workflow)

## Repository Structure

- `app/Controllers/` request handlers
- `app/Models/` data access layer
- `app/Services/` business and integration services
- `app/Views/` layouts, partials, and page templates
- `config/` app, database, routes, payments, OpenAI config
- `public/` front controller and static assets
- `database/` SQL bundles and archived migration/seed history
- `.github/workflows/deploy-vm.yml` VM deployment workflow

## Local Setup

1. Install prerequisites:
- PHP 8+ (`pdo_mysql`, `curl`, `fileinfo`, `mbstring`, `openssl`)
- MySQL 8+
- Composer

2. Install dependencies:
```bash
composer install
```

3. Create environment file:
```bash
cp .env.example .env
```
PowerShell:
```powershell
Copy-Item .env.example .env
```

4. Update `.env` (minimum required values):
- `APP_URL=http://localhost:8000`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_NAME=pulsepoint_fitness`
- `DB_USER=<your_db_user>`
- `DB_PASS=<your_db_password>`

5. Import database:
- Recommended single import:
  - `database/all_migrations_and_seeds.sql`
- Alternative split files:
  - `database/migration.sql`
  - `database/seed.sql`

6. Start local server:
```bash
php -S localhost:8000 -t public public/router.php
```
PowerShell example (XAMPP):
```powershell
& "C:\xampp\php\php.exe" -S localhost:8000 -t public public/router.php
```

7. Open:
- `http://localhost:8000`

## Optional Integrations (Environment Variables)

- Stripe:
  - `STRIPE_SECRET_KEY`
  - `STRIPE_PUBLISHABLE_KEY`
  - `STRIPE_WEBHOOK_SECRET`
  - `STRIPE_CURRENCY` (default: `sgd`)
  - `STRIPE_PROMO_CODES`
- OpenAI chatbot:
  - `OPENAI_API_KEY`
  - `OPENAI_MODEL`
  - `OPENAI_MAX_OUTPUT_TOKENS`
  - `OPENAI_TIMEOUT_SECONDS`
- Notifications:
  - `SMTP_USERNAME`
  - `SMTP_PASSWORD`
  - `TELEGRAM_BOT_TOKEN` (optional, for Telegram notifications)

## Notification Worker

- Run once:
```bash
php run_notifications.php
```
- Run continuously:
```bash
php start_worker.php
```

## Demo Credentials

- Admin: `admin@pulsepoint.test` / `Admin@123`
- Member: `member@pulsepoint.test` / `Member@123`

## Deployment Workflow (VM)

- Develop in `thawzin-dev`
- Merge into `main`
- Merge `main` into `vm_hosting`
- Push `vm_hosting` to trigger `.github/workflows/deploy-vm.yml`

Required GitHub secrets:

- `VM_HOST`
- `VM_USER`
- `VM_SSH_KEY`
- `APP_DIR`

## Academic Note

- This project is a fictional coursework system for INF1005.
