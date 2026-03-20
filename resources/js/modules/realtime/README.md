# realtime

## Responsibility

- Owns the frontend realtime transport boundary via Laravel Echo and Reverb.
- Does not own concerns that belong to page modules or domain-specific services.

## Public API

- `init()`
- `getSocketId()`
- `subscribeToPrivateEvent()`

## Lifecycle

- `init.ts` is the lifecycle entrypoint consumed by `core/connect.ts`.
- `module.ts` is the thin public API facade — re-exports from `service.ts`.
- `bootstrap.ts` owns external wiring for `integrate()`.
- Deferred step (`defer()`): not used.

## Inbound Subscriptions

- Reads runtime configuration from Vite env.

## Outbound Dependencies

- Depends on `shared/http` to propagate the current Echo socket id onto same-origin mutation requests.

## Internal Structure

- `init.ts`
- `module.ts`
- `service.ts`
- `state.ts`
- `bootstrap.ts`

## Notes

- Keep `module.ts` thin — only re-exports.
- Keep `bootstrap.ts` focused on external wiring only.
- Pages subscribe to private channels through this module; they do not construct Echo clients directly.
