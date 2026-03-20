# Conventions

## Language

All code comments, TODOs, docblocks, and inline notes must be written in **English**.
All documentation files in `docs/` must also be written in **English**.
Comments are forbidden by default when self-documenting naming can express the intent.
Only add a code comment when one of these is true:
- the reason cannot be made obvious through naming and structure alone
- the comment records an explicit business requirement via `@business-rule`
- the comment explains a non-obvious external constraint or framework limitation

## PHP / Laravel

- DDD structure: `app/Domain/`, `app/Application/`, `app/Infrastructure/` — see `docs/architecture/ddd.md`
- DTOs for domain/application payloads live in `app/Domain/{Context}/Dto` and use `Dto` or `Data` suffixes
- HTTP input stays in controllers or FormRequests — no raw `$request->all()` in actions or services
- JSON API responses must be serialized through `app/Http/Resources/...`, not built inline with `response()->json([...])`
- API exception mapping must stay centralized. Keep `bootstrap/app.php` thin and place request classification and exception-to-response logic in dedicated HTTP support classes.
- PHPStan level 5 — do not lower without a strong reason
- `declare(strict_types=1)` in every PHP file
- No hardcoding or magic values — all statuses, types, and constants must be defined as enums or config values. Example: `Status::Pending`, not `'pending'`

## TypeScript

- Strict mode, `no-explicit-any` is forbidden
- Type imports via `import type`
- Files in `resources/js/`
- `service.ts` is the only place allowed to call shared HTTP clients
- DOM wiring belongs in page `connect.ts` or `bootstrap.ts`, never in `service.ts`
- All interactive elements must have `data-testid` for Playwright (format: `{context}-{element}-{action}`)
- See `docs/standards/testing-standards.md` for full frontend testing conventions

## Naming

- DB tables: `snake_case`, plural
- Models: `PascalCase`, singular
- Routes: `kebab-case`, plural — e.g. `/ideas`, `/ai-reports`
- Controllers, Repositories: same base name as the model — e.g. `IdeaController`, `IdeaRepository`
- Actions: `Verb + Model + Action` — e.g. `CreateIdeaAction`, `GenerateReportAction`
- JS/TS variables: `camelCase`
- One entity = one name across the entire codebase. Synonyms are forbidden.
  Example: if the concept is `Idea` — use `Idea` everywhere: model, repository, DTO, route, JS variable. Never mix with `Item`, `Entry`, `Record`, etc.
- FormRequests: `Verb + Model + Request` — e.g. `CreateIdeaRequest`, `UpdateIdeaRequest`
- DTO-like payloads must live in `app/Domain/{Context}/Dto`.
- Allowed DTO suffixes are `Dto` and `Data`. Do not invent alternative payload suffixes.
- Query/filter payloads belong in `ValueObjects` and should use explicit names such as `*Query`.
- Enums: `Model + Property` — e.g. `IdeaStatus`, `ReportFormat`
- Enums must live in the layer they describe: domain enums in `app/Domain/{Context}/Enums`, infrastructure enums in `app/Infrastructure/.../Enums`. Do not create a generic `app/Enums` dumping ground.

## Backward Compatibility

- No backward compatibility. Old code is deleted, new code is written in its place.
- No fallbacks unless explicitly requested. When a fallback is added, it must be clearly marked as such in the code.
- Default parameter values (`null`, `[]`, `false`, etc.) are forbidden without explicit permission. Defaults often hide real errors by making broken calls silently succeed.

## Code Size

- Files must not exceed 300–400 lines. If longer — split into smaller units.
- Methods must not exceed 20 lines. If longer — extract into private methods or separate classes.
- Self-documenting names are preferred over comments. A comment is needed only when intent cannot be expressed through naming.
- Do not accumulate unrelated CSS or JS concerns into one giant file. Split by shared surface layer or feature boundary.

## Blade Asset Rules

- Blade markup must not contain executable `<script>` tags.
- Blade markup must not contain `<style>` tags.
- Inline `style=""` attributes are forbidden for application UI code.
- JSON or other non-executable payload islands must use neutral HTML containers with `data-*` attributes instead of `<script>` tags.
- Scripts must be loaded through Vite entrypoints and TypeScript modules.
- Styles must live in managed asset files, either shared surface layers or explicit feature-scoped files.

## Feedback UX

- Transient feedback must use toast notifications, not inline HTML blocks.
- Do not render inline success/info/warning/error feedback that shifts layout after the page has loaded.
- When feedback is transient or event-driven, dispatch it through the shared toast module.
- Persistent, structural UI such as impersonation banners or non-dismissible policy notices may remain inline only when they are part of the page layout itself.

## Security Configuration

- Rate limiting is configured through named limiters and config/env values.
- Do not introduce raw throttle numbers in route files when a named limiter exists.
- Roles and permissions are operational access-control data; manage them through the admin panel, not through scattered seed-only hacks.
- Managed admin permissions are synced from named routes through `php artisan permissions:sync`.
- Permission sync creates and removes managed permissions only. It must not silently assign permissions to roles.
- If a managed route has no name, the sync command must fail.
- Superadmin access is represented by `is_admin === true`, not by a role name.

## Localisation (i18n)

Two levels — both sourced from backend `lang/` files (single source of truth):

**1. Static text in Blade** — rendered server-side at page render time:
```blade
<h1>{{ __('ideas.title') }}</h1>
```
Use for: page titles, labels, static UI strings. No JS involved.

**2. Dynamic text in JS** — for strings generated at runtime (toasts, validation errors, dynamic messages):

The `i18n` module fetches translations asynchronously from `GET /api/i18n` (public endpoint, no auth). It is the first module initialised by the orchestrator. All other modules that need translations declare `dependsOn: ['i18n']` and use the exported `trans()` function.

No translation strings hardcoded in JS/TS files.

## Real-time / Push Events

Push events (WebSocket, SSE, Telegram webhook, etc.) are signals only — they notify that new data exists. They do not transport the data itself.

Pattern: `push event received → trigger fetch → update state via normal data flow`. Never update UI state directly from push payload.

## Caching & Storage

Caching and client-side storage are **forbidden by default** and require explicit approval:
- Backend cache (Redis, file, DB-based)
- Frontend in-memory state that survives re-renders
- Browser storage: `localStorage`, `sessionStorage`, cookies, IndexedDB

Allowed only when there is a clear, justified reason. When approved, the cache must have an explicit TTL and be limited to static or reference data. Never cache mutable user data without special permission.

## Design Principles

- Follow **SOLID** principles in all code.
- Explicit > Implicit: prefer clear, readable code over clever shortcuts.
- Composition > Inheritance: build behaviour by combining small focused units, not deep class hierarchies.

## Scaffolding

Use Artisan commands to generate boilerplate — do not create files manually:

```bash
# Backend: scaffold a full DDD domain (Domain + Application + Infrastructure + HTTP + Model + Migration + Tests)
php artisan make:domain {Name}

# Frontend: scaffold a module (init/module/service/state/bootstrap/README)
php artisan make:frontend-module {name} [--dir=modules] [--no-page]
```

Examples:
```bash
php artisan make:domain Idea
php artisan make:frontend-module ideas
php artisan make:frontend-module http --dir=shared/http --no-page
```

## Architecture

- DDD: Domain / Application / Infrastructure — see `docs/architecture/ddd.md`
- TDD: write the test first, then the implementation

## Formatting

Formatting is automated. Manual code formatting is forbidden.
- PHP: Laravel Pint
- TypeScript/JS: ESLint + Prettier
- Never adjust indentation, spacing, or style by hand — run the formatter.

## Authentication

- The admin surface is session-authenticated and API-driven: HTML is shell-only, `/management/api/*` is fetched through `webClient`, and CSRF/session auth stays on the `web` middleware stack.
- Optional social sign-in on the site surface must also terminate in the normal session-authenticated `User` model. Do not introduce a parallel token-only identity flow for Google or other OAuth providers.
- `apiClient` exists for token-authenticated APIs when a bearer-token surface is added. It must not be used by the current admin panel.
- Public routes such as `GET /api/i18n` stay outside auth middleware.
- `webClient` is for same-origin web and session-authenticated JSON routes.

## Git

- Commit messages in English
- Branch names in English, `kebab-case`: `feature/order-creation`, `fix/payment-bug`
