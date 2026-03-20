# DTO Examples

Use plain `readonly` DTO classes.

---

## Plain DTO — parameter passing and validated payload transfer

A plain `readonly` class with no inheritance. Use for search, filters, command payloads, and
validated HTTP data passed into application actions.

```php
<?php declare(strict_types=1);

namespace App\Domain\{BoundedContext}\Dto;

final readonly class EntityFilterDto
{
    public function __construct(
        public ?string $keyword,
        public ?int $minScore,
        public ?string $status,
    ) {}
}
```

### Usage

```php
$validated = $request->validate([
    'title' => ['required', 'string', 'max:255'],
    'status' => ['required', 'string'],
    'score' => ['nullable', 'integer'],
]);

$dto = new EntityFilterDto(
    $validated['title'],
    $validated['score'] ?? null,
    $validated['status'],
);
```
