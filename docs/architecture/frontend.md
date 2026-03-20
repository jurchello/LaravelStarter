# Frontend Architecture

## Core principle

Business logic and transport logic must be separated from DOM wiring so that data flows can be tested without a browser.

This is the primary architectural constraint. Everything else follows from it.

---

## Layer responsibilities

### `service.ts` — data and feature logic (testable)

Pure or mostly pure functions/classes responsible for feature rules and HTTP transport contracts.

- No DOM access
- No Blade rendering
- No direct event listeners
- HTTP clients are allowed here when the module is a data-access module

Tested with **Vitest**, no browser required.

### `bootstrap.ts` or page `connect.ts` — integration (not unit-tested)

The only place allowed to:
- Listen to DOM events
- Read and write the page
- Call `service.ts`
- Apply results back to the UI

No business rules here. Tested via Playwright (e2e) or feature coverage around the surrounding flow.

### `shared/http/` — HTTP client

Always **axios**, never `fetch`. See `shared/http/clients.ts`.

Two clients: `webClient` (CSRF, session-authenticated web and same-origin JSON routes) and `apiClient` (Bearer token APIs when a token-authenticated surface exists).
Initialised synchronously before the orchestrator starts — no module may create its own axios instance.
`service.ts` is the only place allowed to call these clients.

---

## Folder structure

```
resources/js/
├── core/
│   └── connect.ts              ← module orchestrator
├── modules/                    ← feature modules
│   └── {feature}/
│       ├── init.ts             ← exports init(), integrate(), defer()
│       ├── module.ts           ← thin public API facade
│       ├── service.ts          ← logic + HTTP transport contract — no DOM
│       ├── state.ts            ← state + subscriber pattern
│       ├── bootstrap.ts        ← wiring: events → service → UI
│       └── README.md
├── shared/
│   └── http/                   ← webClient, apiClient (see clients.ts)
└── pages/
    └── {page}/
        └── connect.ts          ← declarative module list for this page
```

---

## Asset boundaries

- Do not place executable `<script>` tags or `<style>` tags inside Blade page markup.
- Do not rely on inline `style=""` attributes for application UI behavior or visual styling.
- Scripts belong in `resources/js/...` and must enter pages through Vite-managed bundles.
- Styles belong in shared surface layers or explicit feature files, never as inline markup.
- Shared surface styling is allowed only for true design-system concerns such as `admin-panel` or the neutral `site` baseline.
- Feature-specific CSS and JS must stay scoped to the relevant surface or module instead of being dumped into a single catch-all asset file.
- If an asset file grows beyond a reasonable feature boundary, split it by module, page, or shared primitive layer.
- Do not merge unrelated admin, site, and feature styles into one giant stylesheet.
- Shared surface files such as `admin-panel/layout.scss` are allowed only for true shared system styling.
- Everything else must live in feature-scoped or primitive-scoped files.

---

## Module lifecycle

System modules may still use `init.ts`, but page-level admin and site flows are allowed to bootstrap directly from `pages/.../connect.ts` when that keeps the boundary thinner.

Reusable modules may export up to three functions from `init.ts`:

```ts
export const init = (): void | Promise<void> => { /* setup, data loading */ }
export const integrate = (): void => { /* wire events via bootstrap.ts */ }
export const defer = (): void => { /* optional: non-critical lazy work */ }
```

The orchestrator calls modules respecting `dependsOn` order, then runs `defer()` after all modules are ready.

---

## System modules

Three modules are auto-loaded on every page by the orchestrator. Do not list them in per-page `connect.ts`:

| Module | Location |
|--------|----------|
| `i18n` | `modules/i18n/` |
| `toast` | `modules/toast/` |
| `shared/http` | `shared/http/` (initialised before orchestrator, not in runModules) |

The shared `toast` module is the canonical transient-feedback channel for both site and admin surfaces.
Do not add page-local inline success/info/error messaging for event-driven feedback when a toast is appropriate.
For the template baseline, the `site` surface is Bootstrap-neutral: reusable view components define structure, while project branding is expected to arrive later through a dedicated skin layer.

---

## Per-page connect.ts

Two page-entry styles are allowed:

- declarative module registration through `connectModules(...)`
- direct page-specific bootstrap such as `initAdminUsersPage(root)`

In both styles the rule is the same: DOM wiring belongs here, not in `service.ts`.

```ts
// pages/ideas/connect.ts
import { connectModules } from '@/core/connect'
import * as ideas from '@/modules/ideas/init'

connectModules([
    { id: 'ideas', ...ideas },
])
```

---

## Dependency direction

```
connect.ts / bootstrap.ts  →  service.ts  →  shared/http/
connect.ts / bootstrap.ts  →  shared/types/
```

---

## Testing strategy

| What | Tool | Requires browser |
|------|------|-----------------|
| `service.ts` logic | Vitest | No |
| DOM-aware integration helpers | Vitest + jsdom when needed | No (jsdom) |
| Full user flow | Playwright | Yes |

`bootstrap.ts` and page `connect.ts` are not the place for business logic. If logic or HTTP contract parsing ends up there, move it to `service.ts`.
