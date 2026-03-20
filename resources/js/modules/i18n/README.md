# i18n

## Responsibility

- Loads the public translation dictionary from `GET /api/i18n`.
- Exposes runtime translation lookup for modules that need dynamic strings.
- Does not own concerns that belong to other modules.

## Public API

- `init()`
- `trans()`

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
- `bootstrap.ts` is intentionally empty because this module has no DOM wiring.
- Add stable, worth-preserving notes here only.
