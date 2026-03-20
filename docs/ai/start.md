# AI Navigation — Read Before Starting Any Task

Before working on any task, read all documents listed below. This is mandatory.

The only valid exception: documents that are clearly irrelevant to the specific task.
For example, if the task is purely backend, skip frontend-specific sections.
When in doubt — read it anyway. It takes less time than undoing a wrong assumption.

---

## Always read (no exceptions)

| Document | What it covers |
|----------|---------------|
| `docs/standards/conventions.md` | Language rules, PHP/TS conventions, naming, git |
| `docs/standards/project-context.md` | What this starter is, tech stack, key domains |
| `docs/architecture/ddd.md` | DDD layers, patterns, anti-patterns, architectural tests |

---

## Read when relevant

| Document | Read when... |
|----------|-------------|
| `docs/standards/testing-standards.md` | Writing or reviewing any tests (backend, frontend, e2e) |
| `docs/architecture/frontend.md` | Any frontend work (modules, TS logic, page wiring) |
| `docs/standards/dto-examples.md` | Creating or modifying DTOs, request handling |
| `docs/architecture/ai-agent.md` | Working on agent logic, LLM calls, budgets, orchestration, or external AI integrations |
| `docs/ideas/README.md` | Capturing product ideas in projects created from this starter |
| `docs/tasks/README.md` | Task tracking conventions and backlog structure |
| `docs/architecture/decisions.md` | Before making an architectural decision — check if it's already been decided |

---

## Key rules (summary from conventions.md)

- All code comments, docblocks, and TODOs must be in **English**
- All documentation must be in **English**
- `declare(strict_types=1)` in every PHP file
- DTOs use plain `readonly` classes — no raw `$request->all()`
- PHPStan level 5 — do not lower
- DDD is mandatory: Domain / Application / Infrastructure
- TDD is mandatory: write the test before the implementation
- No business logic in controllers or Eloquent models
- Use `php artisan make:domain` and `make:frontend-module` — never create boilerplate files manually
- No Laravel Facades in the Domain layer
- Do not run `npm run build` as a default verification step during normal development. The standard local workflow uses `npm run dev:all`. Run `npm run build` only when the user explicitly asks for a production-style build check.

## Business rules marker

When the user explicitly states that something is a business requirement — phrases like "це вимога", "це бізнесвимога", "це не обговорюється", "так має бути" — the AI must document it directly in the code using the `@business-rule` annotation at the most relevant location (constant, method docblock, class comment, or inline comment). Do not just follow it silently — make it visible in the codebase.

Example:
```php
// @business-rule: trial accounts cannot create more than 3 experiments
```

## AI behaviour rules

- **Minimal implementation.** Do not touch neighbouring code. Do not refactor, clean up, or improve anything that was not explicitly requested.
- **No guessing on architecture.** If a task requires an architectural decision that is not covered by the docs — stop and ask. Do not invent a solution.
- **No unsolicited explanations.** Go straight to the task. Provide explanations only when explicitly asked.
- **Code that violates any guideline is wrong even if it works.**
- **Propose documenting general rules.** If an instruction received during a task looks like a general requirement or best practice (not a one-off), propose adding it to the appropriate document (`conventions.md`, `ddd.md`, `frontend.md`, etc.) rather than just applying it silently.
