# Anti-Chaos Rules

These rules exist to stop the template from degrading into an inconsistent codebase over time.

They are not optional style preferences. They define where code is allowed to live, how it is named, and where logic is forbidden.

## Goal

- Keep the DDD boundaries stable across new projects built from this template.
- Prevent random dumping grounds such as `app/Services`, page-local business logic, or unnamed payload objects.
- Make architecture drift visible through automated tests instead of relying on memory.

## Service placement

- Pure business services belong only in `app/Domain/{Context}/Services`.
- Framework-aware adapters belong only in `app/Infrastructure/{Context}/...`.
- Use case orchestration belongs only in `app/Application/{Context}`.
- Do not create a generic `app/Services` directory.
- Do not create ad hoc service classes directly under controllers, views, or unrelated namespaces.

## DTO and payload naming

- DTO-like payload classes must live only in `app/Domain/{Context}/Dto`.
- The only allowed suffixes for these payload classes are `Dto` and `Data`.
- Do not introduce alternative suffixes such as `Payload`, `Params`, `Input`, `Attributes`, or `Body`.
- Query and filter objects belong in `ValueObjects` and should use explicit names such as `*Query`.
- HTTP validation objects belong in `app/Http/Requests` and must use the `*Request` suffix.

## Use case contract

- Application use cases belong in `app/Application/{Context}`.
- Every application use case must end with `Action`.
- Every application use case must expose a single public `execute(...)` method.
- Application exceptions belong in `app/Application/{Context}/Exceptions`.
- Do not place controllers, presenters, or transport mapping logic inside the application layer.

## Logic boundaries

- Domain contains business rules, entities, value objects, policies, read models, and repository contracts.
- Domain must not depend on Laravel, HTTP, Eloquent, controllers, or infrastructure implementations.
- Application orchestrates domain work and may depend on domain contracts, but not on controllers or Blade.
- Infrastructure implements domain contracts and may use Laravel, Eloquent, Redis, Queue, filesystem, or third-party SDKs.
- Controllers are delivery adapters only and must delegate business work to actions or domain services.
- Jobs and queued notifications are transport adapters only and must stay thin. They may call actions or framework notifications, but must not absorb business logic.
- Queue names must be grouped by operational concern, not by individual job class. Use coarse queues such as `email`, `exports`, or `default`; do not dump everything into one queue and do not create one queue per job type without a real need.
- Filesystem access must go through storage contracts and infrastructure adapters. Do not scatter raw `Storage::disk(...)` calls through controllers, actions, or domain code.
- Blade views are for structure and data binding only. Business logic must not be implemented in Blade.

## Entity identity rule

- Anything placed in `Entities/` must have explicit identity.
- Identity does not have to be an auto-increment database column, but it must be part of the object contract.
- Objects without identity must not be treated as entities.
- Repository projections, listings, dashboards, and paginated wrappers belong in `ReadModels/`, not in `Entities/`.

## Asset boundaries

- Blade must not contain executable `<script>` tags.
- Blade must not contain `<style>` tags.
- Inline `style=""` attributes are forbidden for application UI code.
- JSON payload islands must use neutral HTML containers with `data-*` attributes instead of `<script>` tags.
- TypeScript belongs in `resources/js/...`.
- Styles belong in managed asset files, grouped either by shared surface layer or explicit feature scope.
- Do not dump unrelated CSS or JS concerns into one giant catch-all file.

## Frontend structure

- Page connectors live in `resources/js/pages/.../connect.ts`.
- Business or data logic lives in `resources/js/modules/.../service.ts`.
- HTTP client usage belongs only in `service.ts`.
- DOM wiring belongs in `bootstrap.ts` or page connectors, never in service files.
- Shared system concerns such as toast and i18n remain shared modules, not page-local rewrites.
- Admin pages must use `x-admin.*` and site pages must use `x-site.*` when a matching kit component already exists.
- Admin HTML must render the first meaningful screen state. Interactive refresh and mutation flows must use `/management/api/*`, not Blade-owned JSON payloads.
- Do not embed admin domain data in Blade with `@json`, `json_encode()`, or similar inline JSON islands.
- Initial meaningful page content must be server-rendered in Blade when the controller can produce it. Client-side enhancement may refresh or mutate that state afterwards, but it must not be responsible for the first meaningful render.
- API exception rendering is centralized in the global handler. Controllers may validate and delegate, but they must not hand-roll JSON error payloads for business failures or unexpected exceptions.
- Structural site-only actions may remain web flows when they restore or tear down session state for server-rendered UI. The impersonation-exit banner is one explicit allowed exception.

## Transport boundaries

- One controller action must expose one transport contract only.
- An action may return `View` or `JsonResponse`, never both conditionally.
- Do not branch on `expectsJson()`, `wantsJson()`, or similar transport negotiation inside the same action to choose between Blade and JSON.
- If both HTML and JSON are needed, define separate routes and separate controller actions.

## Security config placement

- Rate limiting policy is operational configuration, not admin-managed content.
- Named rate limiters must be defined centrally and backed by config or env values.
- Do not scatter literal throttle numbers through route files when a named limiter exists.
- Permission definitions for the admin surface are route-driven and must be synced from named routes.
- Managed permission-bearing routes must always have names. Missing route names are an architecture error.

## Growth rules

- If a reusable pattern is missing, add it to the shared kit first and only then use it in a page.
- If a new admin kit pattern is introduced, it must later be synced to the template project after the user provides the path.
- If a rule cannot be enforced automatically yet, document it immediately and add an architecture test as soon as the codebase allows it.

## Enforcement

The template must enforce these rules in two ways:

- documentation in `docs/architecture` and `docs/standards`
- automated architecture tests in `tests/Architecture`

If a new project change violates these rules, fix the architecture or update the rules deliberately. Do not silently drift.
