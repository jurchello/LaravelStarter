# Page Delivery Standard

## Contract

The project standard is `SSR first + JS enhancement`.

Rules:

- Web routes render the first meaningful HTML state in Blade.
- API routes return JSON only.
- One controller action exposes one transport contract only.
- TypeScript enhances existing HTML after bootstrap.
- TypeScript may refresh, mutate, filter, paginate, or sort through API routes after bootstrap.
- TypeScript must not depend on inline JSON islands embedded in Blade to render the first screen.
- Page readiness is explicit through `data-page-state`.

## Runtime Readiness

Every JS-enhanced page uses the shared readiness contract:

- app state on `data-app-state`
- page state on `data-page-state`

App states, from earliest to latest:

- `booting`
- `basic-ready`
- `final-ready`

Page states:

- `idle`
- `loading`
- `ready`
- `empty`
- `error`

Rules:

- web routes render the initial meaningful state before `final-ready`
- system bootstrap and page bootstrap are two separate orchestration layers
- `core/connect.ts` owns system modules and `pages/.../connect.ts` owns page modules
- both layers use the same module-connection pattern
- page connectors may fetch fresh data after bootstrap, but they must not create the first visible screen from scratch
- page connectors own `loading`, `ready`, `empty`, and `error` transitions after bootstrap
- `final-ready` is emitted only after the current page bootstrap completes

## Delivery Order

Every page must follow the same order:

1. web controller prepares the initial read model
2. Blade renders the initial meaningful state
3. Blade sets `data-page-state`
4. page `connect.ts` uses the same orchestration pattern as `core/connect.ts`, but only for that page's modules
5. `pages/.../connect.ts` is reduced to enhancement and async refresh only
6. feature tests assert the server-rendered first screen

## Implementation Checklist

For each page:

1. Web controller prepares the initial read model.
2. Blade renders the initial meaningful state.
3. Blade sets `data-page-state` to `ready` or `empty` for the initial state.
4. API route remains the source for incremental refresh and mutations.
5. `connect.ts` owns only DOM wiring, async refresh, and readiness transitions after bootstrap.
6. Feature tests verify the server-rendered initial state.
7. Browser tests wait on explicit readiness, never on timing luck.
