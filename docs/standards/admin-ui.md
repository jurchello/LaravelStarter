# Admin UI Standard

## Architecture

- Admin pages are Blade shells only.
- Admin data loads through authenticated admin API endpoints.
- Page interactivity lives in TypeScript page connectors and modules.
- Styling lives in SCSS layers, not in page templates.
- Admin pages must be composed from `x-admin.*` components for shared structure.
- The admin panel is kit-first: pages assemble existing kit primitives instead of inventing local markup patterns.

## Canonical references

- The living visual catalog for admin components is `/management/ui-kit`.
- The canonical inventory of `x-admin.*` components is `docs/standards/admin-kit-inventory.md`.
- Reusable admin Blade components live under `resources/views/components/admin`.
- Shared admin styles live in `resources/css/admin-panel`.
- Admin page shells live under `resources/views/admin-panel`.

## Rendering rules

- Do not render admin business data directly inside Blade views.
- Blade may render only page structure, headings, placeholders, and endpoint configuration via `data-*`.
- Every admin page must declare `data-admin-page`.
- Admin views must not duplicate raw button/card/table/header markup when a matching `x-admin.*` component exists.
- Admin CRUD/list pages must prefer shell + API hydration rather than server-rendering data-heavy tables.
- Filters for data-heavy admin pages must live in their own dedicated filter section above the data surface, not inside the same section as the table/grid itself.

## Styling rules

- Use only shared SCSS layers: `tokens`, `base`, `shell`, `components`, `pages`.
- Admin density is compact by default: avoid oversized paddings, tall controls, and decorative whitespace.
- Sidebar navigation must be built from grouped sections and may contain nested subitems.
- Tables must favor information density: compact row height, restrained cell padding, and horizontal scrolling only when truly necessary.
- Do not introduce one-off button, table, badge, card, or layout styles inside page views.
- If a new visual pattern is needed, add it to the shared component layer first.
- Do not place inline `style=""`, `<style>`, or executable `<script>` markup inside admin Blade views.

## Mandatory usage rules

- If a matching `x-admin.*` component already exists, it must be used.
- Do not recreate existing kit patterns with raw Blade markup.
- Do not add page-local admin CSS for patterns that belong in the shared kit.
- Do not add admin-only utility-class blobs inside page views instead of kit components.
- Any new admin page must start from existing kit primitives and only then request a new primitive if truly missing.
- Form controls such as checkboxes, toggles, radio options, selects, and multiselects must use the matching `x-admin.*` components instead of raw HTML controls.

## When the kit is missing something

- The first step is to add or extend the missing `x-admin.*` component.
- Only after the component exists may it be used in the page.
- New component work must include:
  - Blade component markup
  - shared SCSS support
  - inclusion in `/management/ui-kit` when the pattern is user-visible
  - test updates when behavior or visibility rules are affected
- Do not solve a missing component by writing one-off markup directly in the page.

## Template sync rule

- Admin kit changes are not local-only by default.
- If a new admin component or admin design-system rule is introduced here, the same change must also be applied to the template project.
- Before touching the template project, ask the user for the template project path.
- After the path is provided, sync the relevant admin components, SCSS, docs, and any required routes/layout updates there as well.

## Frontend rules

- Page entrypoints live in `resources/js/pages/admin-panel/.../connect.ts`.
- Data access lives in `resources/js/modules/.../service.ts`.
- `service.ts` is the only place allowed to talk to HTTP clients.
- DOM wiring belongs in page connectors, not in service files.
- Page connectors may bind UI behavior to stable kit markup, but they must not become a second design system.
- Admin pages are shell-first. Blade may expose endpoints, ids, and tiny bootstrap hints, but interactive admin data must come from `/management/api/*`.
- Do not embed large domain payloads in admin Blade via `@json`, `json_encode()`, or similar inline JSON islands.
- Data-heavy admin pages must update only their data regions when filters, sorting, or pagination change.
- Role and permission management pages follow the same shell + API + partial-rerender contract as every other data-heavy admin page.
- Search filters must auto-apply while typing with debounce and autocomplete; do not require an explicit Apply button for standard list filtering.
- Transient admin feedback must use the shared toast flow instead of inline success/error/info blocks inside page content.
- Do not introduce inline status messages that cause admin layouts to jump after user actions.

## API rules

- All admin API responses must use the envelope:
  - `data`
  - `meta`
  - `errors`
- Session-authenticated admin API endpoints live under `/management/api/...`.
- Admin API documentation is generated automatically and exposed through `/docs/admin-api` and `/docs/admin-api.json`.
- Admin role and permission management must remain session-authenticated admin API, not a separate public API surface.

## Security and visibility rules

- Admin URLs must behave as non-existent for anyone except a verified admin.
- Guests, non-admins, and unverified admins must receive `404` for admin HTML routes and admin API routes.
- Admin impersonation must remain admin-only.
- Rate limiting for admin search, mutation, and impersonation flows must be configured centrally through named limiters and config values, not scattered literal throttle numbers.
- Admin route permissions are derived from route names and synced via `php artisan permissions:sync`.
- Every managed admin route must have a route name; unnamed managed routes are invalid.
- `is_admin === true` is the superadmin bypass and ignores role/permission restrictions.
- Public/site pages must display a full-width warning banner while impersonation is active, with an explicit return action.

## Testing rules

- Feature tests validate Blade shell and admin API envelope.
- Vitest covers admin `service.ts`.
- Playwright uses only `data-testid`.
- Add or update feature tests when:
  - admin route visibility changes
  - impersonation behavior changes
  - admin shell contract changes
  - a new user-facing admin pattern becomes canonical
