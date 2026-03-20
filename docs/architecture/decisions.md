# Architecture Decision Records

Short log of key decisions made during development.
Add a new entry whenever a non-obvious architectural choice is made.

---

## ADR-001 — i18n loaded via async API endpoint, not Blade inject

**Decision:** The `i18n` module fetches translations from `GET /api/i18n` asynchronously on page load.

**Rejected:** Injecting `window.__i18n` via a Blade `<script>` tag in `<head>`.

**Reason:** Blade inject happens at render time and is available immediately, but there is no guarantee it will be present when JS modules initialise in all load scenarios. The async approach is explicit and reliable — the orchestrator waits for `i18n.init()` to resolve before any dependent module starts.

---

## ADR-002 — HTTP clients initialised synchronously before the orchestrator

**Decision:** `ensureHttpReady()` (from `shared/http/bootstrap.ts`) is called synchronously before the shared module-spec runner starts.

**Reason:** The `i18n` module makes an HTTP request during its own `init()`. The axios clients must be ready before the first module starts. Modeling `http` as a regular module spec would work with `dependsOn`, but `shared/http` requires no lifecycle (`integrate`, `defer`) and is infrastructure, not a feature module.

---

## ADR-003 — One DTO per entity, no separate Create/Update DTOs

**Decision:** One explicit readonly `EntityDto` per entity. Controllers and requests map validated fields into the DTO explicitly.

**Rejected:** Separate `CreateEntityDto` and `UpdateEntityDto` classes.

**Reason:** Separate DTOs for create/update duplicate the field definitions and diverge over time. A single explicit DTO keeps the contract stable without hidden magic methods.

---

## ADR-004 — Frontend is module-based, not full DDD

**Decision:** Frontend follows the `modules/{feature}/service.ts` pattern (logic separated from DOM/HTTP). No `ui/application/domain/infra` layer split.

**Reason:** The frontend will not be complex enough to justify full Clean Architecture layers. The module pattern provides sufficient separation for testability without over-engineering.

---

## ADR-005 — `GET /api/i18n` is public (no auth)

**Decision:** The i18n endpoint requires no authentication.

**Reason:** Translations are not private data and must be available to guest users before they authenticate. Putting the endpoint behind any auth wall would break the guest experience.

---

## ADR-006 — Real-time push events are signals only

**Decision:** WebSocket / SSE / Telegram webhook payloads are treated as notifications that new data exists — they do not carry the data itself.

**Pattern:** `push event received → trigger fetch → update state via normal data flow`.

**Reason:** Using push payloads as data transport creates dual code paths (push vs fetch), makes state management unpredictable, and is harder to test.

---

## ADR-007 — Admin uses session-authenticated API, not bearer tokens

**Decision:** The admin panel is API-driven through `/management/api/*`, but it remains session-authenticated on the `web` middleware stack.

**Reason:** The admin panel is same-origin, server-rendered as a shell, and does not benefit from bearer-token complexity while it lives inside the monolith. This keeps CSRF, session regeneration, and admin-only web flows simple without blocking a future split.

---

## ADR-008 — Leaving impersonation stays a web flow

**Decision:** Starting impersonation from the admin panel is an admin API mutation. Leaving impersonation remains a web form action from the site banner.

**Reason:** The banner is structural UI rendered on server-controlled site pages, and leaving impersonation restores the original session context. Keeping that exit path as a web flow is an intentional exception, not an accidental gap in the admin API model.

---

## ADR-009 — Google sign-in terminates in the normal site session flow

**Decision:** Google OAuth is implemented through Socialite, but it still signs users into the same `User` model and `web` session used by email/password auth.

**Reason:** Social sign-in is an alternative entrypoint into the same site identity, not a separate auth surface. This keeps dashboard access, email verification semantics, A/B assignment stitching, and future admin separation coherent.
