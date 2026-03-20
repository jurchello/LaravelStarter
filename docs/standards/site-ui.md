# Site UI Standard

## Rendering model

- Public pages must render their primary content on the server with Blade.
- Public/auth pages must not depend on API-loaded content for core SEO-visible content.
- Authentication screens, landing pages, and account overview pages are server-rendered first.
- Site pages are SSR-first even when enhanced by JavaScript.
- Every site page must declare `data-site-page`.
- Every site page must declare `data-page-state` for the initial server-rendered state.

## Components

- Public pages must be composed from `x-site.*` components.
- Thin view components only: structure and classes, no business logic.
- Reusable primitives live under `resources/views/components/site`.
- Site pages must prefer existing kit primitives before introducing new markup patterns.
- The template baseline keeps `x-site.*` Bootstrap-native or visually neutral.
- Do not embed project-specific branding or decorative styling into the base site kit.
- New project-specific styling must arrive through a dedicated skin layer in the target project, not through the template baseline.

## Styling

- The template baseline must stay skin-free.
- Site views should be assembled from reusable `x-site.*` components plus Bootstrap classes.
- Brand colors, custom backgrounds, and project-specific visual language belong in a later project skin layer, not in the base template.
- The template must not ship inactive or legacy site-specific SCSS files that could leak an old visual language into a new project.
- Do not add ad hoc utility-heavy markup or one-off component styling in page views.
- If a missing public pattern is needed repeatedly, add it to the shared `x-site.*` kit first.
- Form controls such as checkboxes, radios, selects, and other repeated inputs must use `x-site.*` components when a matching primitive exists.
- Do not place inline `style=""`, `<style>`, or executable `<script>` markup inside site Blade views.

## Interactivity

- Public pages may enhance UX with JS, but must remain correct without API hydration.
- Site JS enhances existing HTML after bootstrap; it must not create the first visible screen from scratch.
- Transient feedback on public/auth pages must use toast notifications instead of inline success/info/error blocks that shift the layout.

## Special rules

- Public pages must remain crawlable and understandable without client-side API hydration.
- If impersonation is active, site pages must render a visible full-width warning banner with a return action to the original admin session.
- Site API documentation is generated automatically and exposed through `/docs/site-api` and `/docs/site-api.json`.
