# Omtbiz

Laravel 13 and Vue 3/Vite application for Omtbiz accounting workflows.

## Local Requirements

- PHP 8.5 with the extensions required by Laravel, Passport, DOMPDF, Intervention Image, and Spatie Media Library.
- Composer 2.
- Node.js 24 and npm 11.
- MySQL 8 or MariaDB with a local database named `omtbiz`.

## Local Setup

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan passport:keys
php artisan migrate:fresh --seed
npm ci
npm run production
php artisan serve
```

On Windows PowerShell, if script execution blocks `npm.ps1`, run npm commands through `cmd`:

```bash
cmd /c npm ci
cmd /c npm run production
```

## Laravel 13 Upgrade Checks

After changing backend dependencies, refresh the lock file with all related dependencies:

```bash
composer update laravel/framework laravel/tinker phpunit/phpunit nunomaduro/collision spatie/laravel-ignition laravel/passport laravel/ui --with-all-dependencies
```

Run these checks before merging upgrade work:

```bash
composer validate
php artisan --version
php artisan config:clear
php artisan package:discover
php artisan route:list
cmd /c npm ci
cmd /c npm run production
```

For smoke testing, verify `/api/ping`, login/token refresh, `/api/bootstrap`, and the main customer, invoice, receipt, voucher, and report views against a seeded MySQL database.
