# Testing Standards

---

## Backend

### DDD + TDD is mandatory

- Write the test first, then the implementation (red → green → refactor)
- Every new class in Domain or Application must have a test before it ships
- No raw DB access in Domain tests — use Null Object or mock the repository interface

### Test types by layer

| Layer | Suite | Tools | DB |
|-------|-------|-------|----|
| Domain (VO, Policies, Domain Services) | Unit | PHPUnit | None |
| Application (Use Cases) | Unit | PHPUnit | Mocked via interface |
| Infrastructure (Repositories) | Integration | PHPUnit | Real (`app_testing`) |
| HTTP / Routes / Controllers | Feature | PHPUnit | Real (`app_testing`) |

### PHPStan

Level 5 is the minimum. Do not lower without a strong reason. Run before every PR merge.

---

## Frontend

### Unit tests — Vitest

`service.ts` contains all testable logic. Test it with Vitest — no browser required.

```ts
// modules/ideas/service.test.ts
import { scoreIdea } from './service'

test('low competition increases score', () => {
    const result = scoreIdea({ demand: 8, competition: 2, trend: 7 })
    expect(result).toBeGreaterThan(75)
})
```

Rules:
- Test `service.ts` — it contains all logic
- Do not test `bootstrap.ts` with unit tests
- Mock `shared/http` clients in all unit tests

### `data-testid` convention

All interactive and key elements must have `data-testid`:

```
{context}-{element}-{action}
```

Examples:
- `login-form-submit` — submit button on the login form
- `idea-card-analyze` — analyze button on an idea card
- `report-modal-close` — close button in the report modal

**Rule: add `data-testid` while writing the template — not after.**

### SSR + enhancement rule

Admin and site pages that expose meaningful initial content must render that first content on the server.

Rules:

- Blade renders the initial screen state
- frontend modules enhance existing HTML after hydration
- modules may fetch fresh data after bootstrap, but they must not depend on inline JSON payloads embedded into the page
- API routes remain the source for incremental updates, filters, mutations, and refreshes
- do not ship empty shell-only list pages when the first screen can be rendered on the server

---

## API Contract

All API responses must follow this envelope:

```json
{
  "data": { ... },
  "meta": { "page": 1, "total": 100 },
  "errors": []
}
```

Rules:
- Never return raw arrays at the top level
- `errors` is always present (empty array when no errors)
- `meta` is optional, include when paginating
- Document all endpoints in OpenAPI / Swagger

---

## Quality Tooling Checklist

| Tool | Purpose | When to run |
|------|---------|-------------|
| PHPStan level 5 | Static analysis | CI, pre-merge |
| Laravel Pint | PHP code style | CI, pre-commit |
| ESLint (strict) | TS code quality | CI, pre-commit |
| Prettier | TS/CSS formatting | Pre-commit |
| Vitest | Unit tests for frontend | CI |
| PHPUnit | Backend tests | CI |

---

## Test Backdoors — forbidden

A **test backdoor** is any function, variable, or export that:
- lives in production code
- has no legitimate use in the running application
- exists only so tests can manipulate internal state

### Examples

```ts
// BAD — setModuleState only ever called from tests
export const setModuleState = (next: State): void => { moduleState = next }

// BAD — resetSequence exists nowhere in production flow
export const resetSequence = (): void => { sequence = 0 }

// BAD — __test_only hook
export const __forceLocale = (locale: string): void => { currentLocale = locale }
```

### Why they are forbidden

1. **Pollute the public API.** Any export is a contract. Consumers may depend on it.
2. **Signal a design problem.** If you need a backdoor, the code is not designed for isolation.
3. **Create false confidence.** Tests pass against a state that production code never produces.
4. **Fragile.** If the backdoor is removed (it's "unused in prod"), all tests that relied on it break silently.

### How to recognise one

> If you search the entire codebase and a function is **only called inside test files**, it is a backdoor.

### How to fix it

The root cause is always **global mutable state**. The fix is to move state into a class so each test gets a fresh instance:

```ts
// BAD — global mutable state + backdoor
let moduleState: State = { items: [] }
export const setModuleState = (s: State) => { moduleState = s }  // ← backdoor

// GOOD — state is instance-scoped, tests create a fresh instance
export class ToastService {
    private items: Item[] = []
    notify(payload: Payload): void { ... }
    getState(): State { return { items: this.items.slice() } }
}

const service = new ToastService()                         // production singleton
export const notify = (p: Payload) => service.notify(p)   // public API unchanged

// test — no backdoor needed
beforeEach(() => { service = new ToastService() })
```

Constructor parameters replace forced state injection:

```ts
// BAD
setI18nState({ locale: 'uk', dictionary: { hello: 'Hello' } })

// GOOD — pass initial state via constructor
const service = new I18nService('uk', { hello: 'Hello' })
```

### Rule

> **If it is only called in tests, it does not belong in production code.**

---

## Anti-patterns Catalog

### Backend

| Anti-pattern | What to do instead |
|---|---|
| Business logic in controllers | Move to Use Case in Application layer |
| `$request->all()` in Action | Pass a typed DTO |
| Laravel Facades in Domain | Depend on an interface; bind in AppServiceProvider |
| Logic in Eloquent models | Domain Policy or Domain Service |
| Skip test because "it's obvious" | Write the test, it's not obvious in 6 months |
| Feature test hits live external API | Mock the HTTP client or use `Http::fake()` |

### Frontend

| Anti-pattern | What to do instead |
|---|---|
| HTTP call inside page wiring code | Move to `service.ts` |
| Logic in `bootstrap.ts` | Move to `service.ts` |
| No `data-testid` on key elements | Add while writing the template, always |
| `any` type in TypeScript | Define a proper interface |
| `setModuleState()` / `resetX()` for tests | Class-based service; tests use `new Service()` |
| Module-level `let state = ...` | Private instance field in the service class |
