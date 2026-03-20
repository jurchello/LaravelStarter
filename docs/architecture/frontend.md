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

No business rules here. Tested via feature coverage around the surrounding flow.

### Server-rendered initial content

Blade must deliver the first meaningful screen state whenever the page can be rendered on the server.

- Controllers prepare the initial read model
- Blade renders the initial HTML
- TypeScript enhances that HTML after bootstrap
- TypeScript may fetch newer data after bootstrap, but it must not require inline JSON islands from the page to start

This keeps first paint meaningful and improves resilience without JavaScript.

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
│   ├── connect.ts              ← system-module orchestrator
│   └── module-connect.ts       ← shared module-spec runner
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
    ├── connect.ts              ← page orchestrator (routes to the current page)
    ├── module-connect.ts       ← page-module adapter over the shared module runner
    └── {surface}/{page}/
        └── connect.ts          ← page-module orchestrator for one page
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

The frontend has two explicit orchestration layers and both use the same module-connection pattern:

- `core/connect.ts` orchestrates system modules
- `pages/{surface}/{page}/connect.ts` orchestrates page modules

The difference is only scope, not orchestration style.

System modules may still use `init.ts`, but page-level admin and site flows bootstrap through page `connect.ts` files that use the same module-spec runner.

Reusable modules may export up to three functions from `init.ts`:

```ts
export const init = (): void | Promise<void> => { /* setup, data loading */ }
export const integrate = (): void => { /* wire events via bootstrap.ts */ }
export const defer = (): void => { /* optional: non-critical lazy work */ }
```

Both orchestration layers call modules respecting `dependsOn` order, then run `defer()` after all modules are ready.

Shared runtime readiness is tracked separately from browser lifecycle:

- browser `DOMContentLoaded` starts bootstrap
- `data-app-state="booting"` is the earliest app lifecycle state
- `data-app-state="basic-ready"` is reached after system bootstrap
- `data-app-state="final-ready"` is reached after entrypoint/page bootstrap completes
- `data-page-state` is owned by the current page root and tracks `idle`, `loading`, `ready`, `empty`, or `error`

---

## System modules

Three modules are auto-loaded on every page by the orchestrator. Do not list them in per-page `connect.ts`:

| Module | Location |
|--------|----------|
| `i18n` | `modules/i18n/` |
| `realtime` | `modules/realtime/` |
| `toast` | `modules/toast/` |
| `shared/http` | `shared/http/` (initialised before the module runner, not modeled as a module spec) |

The shared `toast` module is the canonical transient-feedback channel for both site and admin surfaces.
Do not add page-local inline success/info/error messaging for event-driven feedback when a toast is appropriate.
For the template baseline, the `site` surface is Bootstrap-neutral: reusable view components define structure, while project branding is expected to arrive later through a dedicated skin layer.

The shared `realtime` module owns Echo / Reverb wiring and socket identity propagation.
Pages do not construct Echo clients directly; they only subscribe to channels through the module facade.
Private channel authentication remains session-based through the normal Laravel broadcasting auth endpoint.

---

## Two-stage bootstrap

Every browser entrypoint follows the same order:

1. `DOMContentLoaded`
2. `core/connect.ts` connects system modules
3. `pages/connect.ts` locates the current page root and dispatches to one page `connect.ts`
4. the selected page `connect.ts` connects that page's modules
5. only then may the app emit `final-ready`

This keeps site and admin surfaces structurally identical.

## Per-page connect.ts

Page `connect.ts` files are not special-case scripts; they are page-scoped module orchestrators built on the same pattern as the system orchestrator.

Rules:

- one page `connect.ts` per page surface
- page `connect.ts` owns only that page's modules
- DOM wiring belongs here, not in `service.ts`
- if a page has no active JS modules yet, it still keeps its own `connect.ts` as the stable orchestration boundary
- do not bypass the page orchestrator from entrypoints with ad hoc per-page `switch` logic

Page connectors own page readiness:

- mark page `loading` before async page bootstrap
- mark page `ready`, `empty`, or `error` after the initial page result is known
- do not mark global app `final-ready` before page bootstrap completes

```ts
// pages/site/login/connect.ts
import { connectPageModules, definePageModule } from '@/pages/module-connect'

export function connectSiteLoginPage(root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('site-login', () => {
            // page-specific DOM wiring and async enhancement
        }),
    ])
}
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

`bootstrap.ts` and page `connect.ts` are not the place for business logic. If logic or HTTP contract parsing ends up there, move it to `service.ts`.
