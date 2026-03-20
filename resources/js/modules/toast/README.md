# toast

## Responsibility

- Owns transient toast notifications for site and admin surfaces.
- Does not own persistent structural notices such as impersonation banners.

## Public API

- `init()`
- `notify()`
- `dismiss()`
- `emitNotify()`
- `subscribeToUpdates()`

## Lifecycle

- `init.ts` is the lifecycle entrypoint consumed by `core/connect.ts`.
- `module.ts` is the thin public API facade — re-exports from `service.ts`.
- `bootstrap.ts` owns external wiring for `integrate()`.
- Deferred step (`defer()`): not used.

## Inbound Subscriptions

- `window` custom event: `app:toast:notify`

## Outbound Dependencies

- None

## Internal Structure

- `init.ts`
- `module.ts`
- `service.ts`
- `state.ts`
- `bootstrap.ts`

## Notes

- Keep `module.ts` thin — only re-exports.
- Keep `bootstrap.ts` focused on external wiring only.
- Add stable, worth-preserving notes here only.
