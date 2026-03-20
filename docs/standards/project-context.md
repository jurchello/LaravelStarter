# Project Context

## Stack

- **Backend:** Laravel 13, PHP 8.4
- **Frontend:** Vite, TypeScript, Node 24 LTS
- **DB:** MariaDB 10.11
- **Cache / Queues / Sessions:** Redis
- **Server:** Nginx + PHP-FPM (Docker)
- **Static analysis:** PHPStan (larastan), ESLint
- **Tests:** PHPUnit, Playwright (e2e)
- **DTO:** plain `readonly` DTO classes
- **Feature flags runtime:** Laravel Pennant

## Project Description

Laravel starter for products that need a structured backend, typed frontend modules, automated testing, and clear project documentation from day one.

This repository is intentionally generic. Product-specific business context should be documented in `docs/ideas/` and `docs/tasks/` after creating a new project from the starter.

## Typical Domains

- **Users** — authentication, roles, access control
- **Experiments** — A/B tests, assignments, tracking, analysis, guest-to-user assignment stitching
- **Feature Flags** — operational toggles, gradual rollout, admin management
- **Localization** — translation loading and client delivery
- **Operations** — queues, caching, observability, deployment
