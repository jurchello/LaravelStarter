# LaravelStarter

Reusable Laravel SaaS-style template with session auth, admin panel, AB testing, generated API docs / Swagger, Docker-based local stack, and architecture guardrails.

Target runtime baseline: PHP 8.4 and Node 24 LTS.

Included out of the box:

- session-based auth with registration, login, password reset, email verification, Google sign-in, and admin impersonation
- public site pages plus a separate admin panel with reusable Blade UI components
- admin panel management for dashboard metrics, users, roles, feature flags, AB tests, assignments, events, analytics, mail previews, and API docs / Swagger access
- AB testing module with test, variant, assignment, event, and analytics flows
- feature flags with Laravel Pennant runtime integration and admin management UI
- roles, permissions, managed route-permission sync, and admin-only areas
- site and admin API endpoints with generated API documentation / Swagger
- Docker-based local stack with Nginx, PHP-FPM, MariaDB, Redis, Mailpit, queues, and Vite
- Laravel Reverb realtime layer with Laravel Echo on the frontend
- GitHub Actions CI for backend and frontend quality checks on push and pull request
- backend, frontend, and architecture tests
- DDD-oriented structure, project docs, standards, and generator stubs for future modules

## Start

```bash
cp .env.example .env
cp .env.testing.example .env.testing
composer install
npm install
php artisan key:generate
npm run dev:all
php artisan migrate --seed
```

For a clean local bootstrap:

- create `.env` from `.env.example`
- create `.env.testing` from `.env.testing.example`
- run `php artisan key:generate` only for `.env`
- keep the committed test template key only for local/testing use; generate a different key if your test setup needs it

What to rename before real work starts:

- `APP_NAME` in [.env.example](/home/yurii/projects/LaravelStarter/.env.example)
- `APP_URL` in [.env.example](/home/yurii/projects/LaravelStarter/.env.example)
- DB credentials, root password, and database names in [.env.example](/home/yurii/projects/LaravelStarter/.env.example), [.env.testing.example](/home/yurii/projects/LaravelStarter/.env.testing.example), and [docker-compose.yml](/home/yurii/projects/LaravelStarter/docker-compose.yml)
- exposed ports in [docker-compose.yml](/home/yurii/projects/LaravelStarter/docker-compose.yml) if `8011`, `3309`, `6382`, `1025`, or `8025` conflict locally
- mail sender defaults such as `MAIL_FROM_ADDRESS`
- optional Google OAuth credentials in [.env.example](/home/yurii/projects/LaravelStarter/.env.example)

Default local URLs after startup:

- Site: `http://127.0.0.1:8011`
- Dashboard: `http://127.0.0.1:8011/dashboard`
- Admin panel: `http://127.0.0.1:8011/management`
- Admin feature flags: `http://127.0.0.1:8011/management/feature-flags`
- Admin UI kit: `http://127.0.0.1:8011/management/ui-kit`
- Admin mail previews: `http://127.0.0.1:8011/management/mail-previews` (`local` / `testing` only)
- Site API docs: `http://127.0.0.1:8011/docs/site-api`
- Admin API docs: `http://127.0.0.1:8011/docs/admin-api`
- Mailpit inbox: `http://127.0.0.1:8025`
- Health live: `http://127.0.0.1:8011/health/live`
- Health ready: `http://127.0.0.1:8011/health/ready`
- Vite dev server: `http://127.0.0.1:5173`
- Reverb WebSocket server: `ws://127.0.0.1:8080`

Default local admin seed:

- email: `admin@example.com`
- password: `12345678`

Default access-control bootstrap:

- roles: `Admin`, `Manager`, `Developer`
- the seeded local super-admin is marked by `is_admin = true`
- users do not receive roles by default
- managed route permissions are synced from named routes with `php artisan permissions:sync`
- route sync only creates/removes managed permissions; role-to-permission mapping is maintained from the admin panel
- logs are written in JSON format to the normal Laravel log files, with `request_id` included on HTTP requests
- feature flags are managed from the database, while Laravel Pennant stores resolved runtime state in the `features` table

Important:

- The default super-admin seed is skipped automatically in `production`.
- Default roles are still seeded in every environment when `DatabaseSeeder` runs.
- If you need a production admin, create it explicitly through a project-specific process instead of `DatabaseSeeder`.
- Rate limiting is configured centrally in [config/rate_limits.php](/home/yurii/projects/LaravelStarter/config/rate_limits.php) and can be overridden with env values.
- `LOG_STACK=daily` is the default and both `daily` and `single` channels use JSON formatting.
- `scripts/deploy.sh` runs `php artisan permissions:sync --force` during deploy.

## Local Stack

`npm run dev:all` starts:

- `db` on `3309`
- `redis` on `6382`
- `mailpit` on `1025` SMTP and `8025` UI
- `nginx/php` on `8011`
- `reverb` on `8080`
- `queue:work` for `email,default`
- `vite` on `5173`

It also clears Laravel caches, recreates the storage link when needed, and tails all container logs.

Auth email delivery:

- email verification and password reset notifications are queued
- both use the `email` queue by default
- local `npm run dev:all` already runs a queue worker for `email,default`

Optional Google sign-in:

- set `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI`
- the login and registration pages show the Google button only when all three values are configured
- Google sign-in is a site session-auth flow; it does not change the admin auth model

## Deploy

Minimum deploy flow:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Production notes:

- use real DB/Redis/mail credentials
- set the final domain in `APP_URL`
- run `php artisan reverb:start` under a process manager as a dedicated long-running process
- do not rely on default seeded credentials
- keep `/docs/site-api` and `/docs/admin-api` behind the verified-admin gate outside local environments

## Test

```bash
cp .env.testing.example .env.testing
npm test
npm run test:backend
npm run test:php
npm run test:unit
```

Useful focused runs:

```bash
php artisan test tests/Architecture
php artisan test tests/Feature/Health
php artisan test tests/Feature/AdminPanel
```

Quality gates:

```bash
npm run check
npm run check:full
```

## Architecture

- DDD: [docs/architecture/ddd.md](docs/architecture/ddd.md)
- Anti-chaos rules: [docs/architecture/anti-chaos.md](docs/architecture/anti-chaos.md)
- Frontend boundaries: [docs/architecture/frontend.md](docs/architecture/frontend.md)
- Conventions: [docs/standards/conventions.md](docs/standards/conventions.md)
- Admin UI standard: [docs/standards/admin-ui.md](docs/standards/admin-ui.md)
- Admin kit inventory: [docs/standards/admin-kit-inventory.md](docs/standards/admin-kit-inventory.md)
- Site UI standard: [docs/standards/site-ui.md](docs/standards/site-ui.md)
