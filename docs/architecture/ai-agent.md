# AI Agent Architecture

Reference architecture for AI-enabled features in projects created from this starter.

This document is intentionally generic. It describes patterns for orchestration, provider isolation, budget control, and traceability without assuming a specific product domain.

---

## Goals

- keep AI flows explicit and testable
- isolate external providers behind infrastructure adapters
- control cost, latency, and retries
- persist task state for long-running workflows
- make prompts, outputs, and decisions observable

---

## High-Level Flow

```text
User input
  ->
Application action / orchestrator
  ->
Domain workflow + policies
  ->
Infrastructure adapters (LLM, search, embeddings, queues, storage)
  ->
Persisted result / follow-up task / user-facing response
```

Use a single orchestrator per workflow. Do not spread branching AI logic across controllers, jobs, and models without one clear entry point.

---

## Layering

### Application

Coordinates the workflow:

- validates the use case input
- calls domain services in the right order
- invokes AI-capable infrastructure through interfaces
- returns DTOs or dispatches follow-up jobs

### Domain

Owns business rules:

- what can be generated or analyzed
- when AI is allowed to run
- what counts as acceptable output
- how confidence, limits, and approvals work

Domain code must not depend directly on SDKs, HTTP clients, or Laravel facades.

### Infrastructure

Contains integrations:

- LLM providers
- vector / embedding services
- search or retrieval backends
- file storage
- queue transports
- telemetry and logging

Keep provider-specific payloads here. Convert them to project DTOs before returning upward.

---

## Recommended Components

### Orchestrator

One application service that owns the full workflow.

Example responsibilities:

- prepare context
- choose the right provider strategy
- call one or more AI steps
- validate output shape
- persist artifacts
- trigger async continuation when needed

### Prompt Builder

Build prompts from structured input, not inline strings scattered through the codebase.

Keep prompt templates versioned and easy to test.

### Output Validator

Every AI response should be validated before it affects the rest of the system.

Typical checks:

- required fields exist
- enum values are valid
- text length is within limits
- scores are within expected ranges
- unsafe or malformed output is rejected

### Budget Policy

Track usage by workflow, user, or task.

At minimum, define:

- per-request token or cost cap
- retry policy
- fallback behavior when the preferred provider fails
- rules for stopping expensive chains early

### Task Store

Long-running AI workflows should have persisted state.

Typical statuses:

```text
pending -> running -> waiting_input -> running -> completed
                                      -> failed
                                      -> cancelled
                                      -> budget_exceeded
```

---

## Provider Strategy

Do not couple the application layer to one vendor.

Prefer an internal contract such as:

```php
interface GeneratesStructuredOutput
{
    public function generate(array $messages, string $schema): array;
}
```

This allows:

- switching providers later
- using a cheaper model for draft steps
- mocking the provider in tests
- running fallback chains without rewriting business logic

---

## Sync vs Async

Use synchronous execution only for short, bounded flows.

Use queues or background jobs when:

- generation may take more than a few seconds
- multiple external calls are involved
- retries are required
- users may need progress tracking

Controllers should start the workflow, not own it.

---

## Retrieval and Context

If AI output depends on project data:

- collect context in application or infrastructure services
- normalize it before prompt construction
- keep retrieval logic separate from generation logic

Do not hide ad hoc database access inside prompt builders.

---

## Caching

Cache only when reuse is safe.

Good candidates:

- embeddings
- deterministic enrichment results
- repeated classification requests
- expensive external retrieval results with TTL

Do not cache outputs that are user-specific unless the cache key includes the relevant scope.

---

## Observability

At minimum, record:

- workflow name
- provider and model
- attempt count
- duration
- token / cost estimate when available
- final status

Sensitive prompt content should be redacted or stored carefully.

---

## Testing

Test AI workflows at three levels:

1. Domain tests for policies and decision rules.
2. Application tests with mocked provider interfaces.
3. Integration tests for adapter formatting and error handling.

Do not make routine test runs depend on real provider calls.

---

## Anti-Patterns

- controllers building prompts directly
- domain services calling SDKs
- unvalidated JSON from the model flowing into the app
- one provider hardcoded everywhere
- retries without budget limits
- hidden side effects inside prompt construction
- product-critical state kept only in memory

---

## Decision Rule

If a project created from this starter has no AI features, this document can stay unused.

If the project does include AI workflows, adapt this file to the actual domain before implementation grows.
