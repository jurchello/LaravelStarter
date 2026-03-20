# API Documentation

This project uses `dedoc/scramble` to generate OpenAPI documentation from the actual Laravel routes and controllers.

## Canonical docs endpoints

- Site API UI: `/docs/site-api`
- Site API JSON: `/docs/site-api.json`
- Admin API UI: `/docs/admin-api`
- Admin API JSON: `/docs/admin-api.json`

## Scope

- `Site API` documents routes under `/api/...`.
- `Admin API` documents routes under `/management/api/...`.
- Admin HTML pages and site HTML pages are not part of the OpenAPI output.

## Access

- In `local`, the docs are accessible for local development.
- Outside `local`, docs access must be granted only to a verified admin through the `viewApiDocs` gate.

## Rules

- Do not hand-maintain endpoint markdown files as the primary source of truth.
- Add or update real routes, request validation, response DTOs, and Scramble metadata instead.
- When a new API surface is introduced, expose it through the appropriate generated docs entry rather than creating an ad hoc documentation page.
- Keep the API envelope consistent across documented endpoints:
  - `data`
  - `meta`
  - `errors`
