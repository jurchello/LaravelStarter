# runtime-state

## Responsibility

- Owns the shared frontend readiness contract for both site and admin surfaces.
- Tracks global app lifecycle and per-page lifecycle only.

## Public API

- `markAppBooting()`
- `markAppBasicReady()`
- `markAppFinalReady()`
- `setPageIdle(root)`
- `setPageLoading(root)`
- `setPageReady(root)`
- `setPageEmpty(root)`
- `setPageError(root)`
- `getAppState()`
- `getPageState(root)`
- `isAppBasicReady()`
- `isAppFinalReady()`
- `isPageReady(root)`
- `subscribe(callback)`

## Lifecycle

- `booting` is the earliest app state.
- `basic-ready` is the first stable app-ready state after system bootstrap.
- `final-ready` is the latest app-ready state after page bootstrap finishes.
- `idle` is the earliest page state.
- `loading` is the in-flight page state.
- `ready`, `empty`, and `error` are terminal initial page states.

## DOM Contract

- Global app attribute: `data-app-state`
- Per-page attribute: `data-page-state`
- `aria-busy` mirrors page loading automatically

## Events

- `app:basic-ready`
- `app:final-ready`
- `page:ready`
- `page:empty`
- `page:error`

## Notes

- Browser `DOMContentLoaded` is not tracked here; it is a platform concern, not application state.
- Keep `module.ts` thin — only re-exports.
- Keep `bootstrap.ts` focused on external wiring only.
