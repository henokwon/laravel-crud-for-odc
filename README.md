# Laravel CRUD for ODC

[![Authorization CI](https://github.com/henokwon/laravel-crud-for-odc/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/henokwon/laravel-crud-for-odc/actions/workflows/ci.yml)

## Authorization continuous integration

Pull requests and pushes to `master` run administrative authorization feature
tests against SQLite in memory. The badge reports the default branch workflow
status and becomes available after the workflow runs there.

CI uses PHP 8.1 to match the existing Composer lockfile, with Composer 2.11.3.
This compatibility check is not a recommendation to deploy an old PHP runtime;
runtime and framework upgrades remain separate work.

```bash
composer install --no-interaction --prefer-dist --no-progress --no-scripts --no-plugins
php artisan package:discover --ansi
php vendor/bin/phpunit --filter AdministrativeAuthorizationTest
```

The selected tests configure an in-memory database, test-only app key and
isolated sessions/cache. They cover administrative access, password ownership
and current-password checks, and CSRF enforcement. This workflow does not run
the complete legacy suite, frontend build or live service checks.
For operator configuration, see [SECURITY.md](SECURITY.md).

## Features
- Laravue stack CRUD project 
- Back-end
-------------

## Requirements

------------

- PHP >= 7.3.0
- Laravel = 8
- Vue.js = 2.6.12
- Fileinfo PHP Extension

## How to install

-------------

1. Move to the Directory ```cd laravel-crud-for-odc```
2. Install composer ```composer install```
3. Copy .env file ```cp .env.example .env```
4. Generate key ```php artisan key:generate```
5. Create database and edit ```.env```
6. Migrate database ```php artisan migrate```
8. Install npm ```npm install```
9. Watch file ```npm run watch```
