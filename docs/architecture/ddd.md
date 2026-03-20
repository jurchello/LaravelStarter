# DDD Architecture

The project is built on **Domain-Driven Design** with three layers:

```
app/
├── Domain/          ← business logic, pure PHP, no framework
├── Application/     ← use case orchestration
└── Infrastructure/  ← interface implementations (Eloquent, external services)
```

---

## Layers and responsibilities

### Domain

The core of the system. Has no knowledge of Laravel, Eloquent, or HTTP.

```
Domain/
└── {BoundedContext}/
    ├── Entities/       ← identity-bearing domain objects
    ├── ReadModels/     ← immutable repository projections and query results
    ├── Repositories/   ← interfaces (contracts)
    ├── Services/       ← stateless domain logic
    ├── Policies/       ← business access rules
    ├── ValueObjects/   ← immutable primitives with validation
    ├── Enums/          ← constrained value sets
    └── Dto/            ← data transfer between domain and application
```

**Rules:**
- Pure PHP only, `declare(strict_types=1)` everywhere
- `final readonly class` for Value Objects
- Private constructor + static factory methods in VOs
- Repositories are interfaces only — no Eloquent
- Repository contracts return Domain entities or Domain read models, never `App\Models\...`
- Domain contracts must not expose Laravel contracts like paginator, request, collection, or resource types
- No dependencies on Application or Infrastructure
- Keep the terminology strict:
  - `Entity` has explicit identity and lifecycle
  - `ValueObject` has no identity and is compared by value
  - `ReadModel` is a projection optimized for reads, listings, dashboards, and admin inspection flows
- If an object has no stable identity, it must not live in `Entities/`.
- An entity identity does not have to be a database auto-increment column, but it must be explicit in the model contract.
- If the object is only a repository projection or listing result, keep it in `ReadModels/` instead of `Entities/`.

### Aggregate Root

An aggregate root is a role in the model, not a mandatory folder.

- Use a separate folder only when it improves clarity for a large bounded context.
- In smaller contexts, keep aggregate roots inside `Entities/` and document the role clearly.
- Child objects that participate in the same consistency rules should be changed through the aggregate root boundary.

Example for this project:
- `AbTest` is the aggregate root for AB-testing lifecycle and variant configuration.
- Status transitions and variant mutation rules should stay inside the `AbTest` consistency boundary or in explicit domain policies that protect that boundary.
- `Role` in `AccessControl` is an entity because it has explicit identity; user access listings that combine role badges and super-admin state remain read models, not entities.

### Application

Orchestrates domain logic. Knows about the domain, not about HTTP or DB directly.

```
Application/
└── {BoundedContext}/
    ├── {VerbModel}Action.php   ← one use case per file, method execute()
    └── Exceptions/             ← application-level exceptions
```

**Rules:**
- Use case = one public method `execute(...)`
- `final readonly class`, all dependencies via constructor
- Works only with Domain entities and DTOs, never Eloquent models
- Throws its own exceptions (not HTTP exceptions)

### Infrastructure

Implements domain interfaces. Knows about Eloquent, external APIs, the filesystem.

```
Infrastructure/
└── {BoundedContext}/
    └── Persistence/    ← repository implementations
```

**Rules:**
- Class `implements` the corresponding interface from Domain
- Naming: `{Driver}{Entity}Repository` (e.g. `EloquentIdeaRepository`)
- Null Object Pattern for test or fallback implementations

---

## Patterns

### Repository
```php
// Domain — interface only
interface IdeaRepository
{
    public function findById(int $id): ?Idea;
    public function save(Idea $idea): void;
}

// Infrastructure — implementation
final class EloquentIdeaRepository implements IdeaRepository
{
    public function findById(int $id): ?Idea
    {
        $model = IdeaModel::query()->find($id);

        return $model === null ? null : $this->toEntity($model);
    }
}
```

### Value Object
```php
final readonly class Score
{
    private function __construct(
        public int $value, // 0–100
    ) {}

    public static function of(int $value): self
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidArgumentException('Score must be between 0 and 100.');
        }
        return new self($value);
    }
}
```

### Policy (business access rule)
```php
final readonly class IdeaEligibilityPolicy
{
    public function canRegenerate(Idea $idea): bool
    {
        // pure business logic, no HTTP
    }
}
```

### Use Case
```php
final readonly class CreateIdea
{
    public function __construct(
        private IdeaRepository $ideas,
        private IdeaEligibilityPolicy $policy,
    ) {}

    public function execute(IdeaDto $dto, User $user): Idea
    {
        if (! $this->policy->canCreate($user)) {
            throw new IdeaAccessDenied();
        }
        // ...
        $this->ideas->save($idea);
        return $idea;
    }
}
```

### Null Object (for tests and fallback)
```php
final class NullTrendProvider implements TrendProvider
{
    public function fetch(string $keyword): TrendData
    {
        return TrendData::empty();
    }
}
```

### Bindings in AppServiceProvider
```php
$this->app->bind(IdeaRepository::class, EloquentIdeaRepository::class);
```

---

## TDD approach

- Test first, then implementation (red → green → refactor)
- **Unit tests** — for Value Objects, Services, Policies (no DB, no Laravel)
- **Feature tests** — for Use Cases via HTTP or directly with DI
- **Integration tests** — for Infrastructure (real DB, external services)
- For Infrastructure isolation → mock or Null Object implementation of the interface

```
tests/
├── Unit/
│   └── Domain/
│       └── {BoundedContext}/        ← VOs, Services, Policies — no DB, no Laravel
├── Feature/
│   └── {BoundedContext}/            ← Use Cases, HTTP controllers
└── Integration/
    └── {BoundedContext}/            ← Repository implementations, external APIs
```

### Testing strategy by layer

| Layer | Test type | What to test | Isolation |
|-------|-----------|--------------|-----------|
| Domain | Unit | Value Objects, Policies, domain Services | Pure PHP, no framework |
| Application | Unit | Use Cases with mocked repositories and real Domain entities | Mock/Null Object interfaces |
| Infrastructure | Integration | Eloquent repository implementations | Real test DB |
| HTTP / API | Feature | Controllers, routes, request/response | Full HTTP stack |

---

## Anti-patterns

### Fat Controllers
```php
// BAD: business logic directly in the controller
public function store(Request $request): JsonResponse
{
    $idea = Idea::create($request->all());
    // business logic here...
    return response()->json($idea);
}

// GOOD: controller only delegates to a use case
public function store(CreateIdeaRequest $request, CreateIdea $useCase): JsonResponse
{
    $idea = $useCase->execute(IdeaDto::from($request));
    return response()->json($idea);
}
```

### Laravel Facades in Domain
```php
// BAD: Domain depends on a Laravel Facade
final class ScoringService
{
    public function calculate(): Score
    {
        $rate = Cache::get('trend_weight'); // Illuminate in Domain — forbidden
    }
}

// GOOD: depend on an interface, inject in Infrastructure
final readonly class ScoringService
{
    public function __construct(
        private TrendProvider $trends,
    ) {}
}
```

### `$request->all()` in Actions
```php
// BAD: raw array leaks into the action
$action->execute($request->all());

// GOOD: always pass a typed DTO
$action->execute(IdeaDto::from($request));
```

### Business Logic in Eloquent Models
```php
// BAD: model encodes business rules
class Idea extends Model
{
    public function canRegenerate(): bool
    {
        return $this->status === 'scored' && $this->user->isPremium();
    }
}

// GOOD: move business rules to a Domain Policy
final readonly class IdeaEligibilityPolicy
{
    public function canRegenerate(Idea $idea, User $user): bool { ... }
}
```

---

## Architectural Tests

Verify that layer boundaries are not violated (e.g. Domain does not import Illuminate):

```php
// tests/Unit/ArchitectureTest.php (requires pest-plugin-arch or phparkitect)

test('domain layer has no Illuminate dependencies')
    ->expect('App\Domain')
    ->not->toUse('Illuminate');

test('domain layer has no Eloquent models')
    ->expect('App\Domain')
    ->not->toUse('Illuminate\Database\Eloquent\Model');

test('use cases have single public method')
    ->expect('App\Application')
    ->classes()
    ->toHaveMethod('execute');
```

---

## Bounded Contexts — folder structure

Each context has its own folder across all three layers:

```
app/
├── Domain/
│   ├── Ideas/
│   ├── Analysis/
│   ├── Trends/
│   └── Reports/
├── Application/
│   ├── Ideas/
│   ├── Analysis/
│   ├── Trends/
│   └── Reports/
└── Infrastructure/
    ├── Ideas/
    ├── Analysis/
    ├── Trends/
    └── Reports/
```

---

## Checklist for new functionality

- [ ] Define the bounded context
- [ ] Create Value Objects for key primitives
- [ ] Define the repository interface in Domain
- [ ] Write Unit tests for VOs / Policies / Services
- [ ] Implement the Use Case in Application
- [ ] Write a Feature test for the Use Case
- [ ] Implement the repository in Infrastructure
- [ ] Register the binding in AppServiceProvider
