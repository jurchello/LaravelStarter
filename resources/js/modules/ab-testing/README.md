# ab-testing

## Responsibility

- Resolves assigned variants through `/api/ab/assign/{slug}`.
- Sends tracked experiment events through `/api/ab/event`.
- Does not own concerns that belong to other modules.

## Public API

- `init()`
- `getVariant()`
- `trackEvent()`

## Lifecycle

- `init.ts` is the lifecycle entrypoint consumed by `core/connect.ts`.
- `module.ts` is the thin public API facade — re-exports from `service.ts`.
- `bootstrap.ts` owns external wiring for `integrate()`.
- Deferred step (`defer()`): not used.

## Inbound Subscriptions

- None

## Outbound Dependencies

- `shared/http/webClient`

## Internal Structure

- `init.ts`
- `module.ts`
- `service.ts`
- `state.ts`
- `bootstrap.ts`

## Notes

- Keep `module.ts` thin — only re-exports.
- `bootstrap.ts` is intentionally empty because callers use the module API directly.
- Add stable, worth-preserving notes here only.
