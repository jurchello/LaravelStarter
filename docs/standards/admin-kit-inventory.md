# Admin Kit Inventory

Canonical inventory of `x-admin.*` components that must be used across the admin panel.

## How to use this document

- Look for an existing component here before introducing a new pattern.
- If the component already exists, use it instead of recreating the markup locally.
- If the pattern is missing, extend the kit first and only then build the page.
- Update `/management/ui-kit` whenever a new user-visible admin pattern is added.
- When a new admin kit pattern is introduced, sync it to the template project after the user provides the template path.

## Page Shell and Layout

- `x-admin.page`: root shell for an admin page.
- `x-admin.page-header`: page header with title, subtitle, and actions.
- `x-admin.surface`: base section surface.
- `x-admin.filter-section`: canonical separate section for live filters above data-heavy lists.
- `x-admin.toolbar`: compact section toolbar for metadata and actions.
- `x-admin.panel`: inner panel with heading/body structure.
- `x-admin.topbar`: compact horizontal utility row.
- `x-admin.sidebar-group`: grouped sidebar navigation cluster.
- `x-admin.breadcrumbs`, `x-admin.breadcrumb`: breadcrumb navigation.

## Navigation and Actions

- `x-admin.button`: canonical action button.
- `x-admin.dropdown`: generic dropdown shell.
- `x-admin.action-menu`: grouped actions menu.
- `x-admin.action-item`: single dropdown or action-menu item.
- `x-admin.tabs`, `x-admin.tab`: switches between related sections.
- `x-admin.pagination`, `x-admin.pagination-link`: paging controls.
- `x-admin.command-bar`: top bar for batch or command actions.
- `x-admin.bulk-actions`: grouped bulk actions.
- `x-admin.row-actions`: compact actions inside a table row.
- `x-admin.sticky-action-bar`: sticky action zone for long forms or editor flows.

## Status, Messaging, and Feedback

- `x-admin.badge`: short status or label chip.
- `x-admin.status-pill`: success/warning/danger/muted state pill.
- `x-admin.boolean-pill`: yes/no or enabled/disabled state pill.
- `x-admin.permission-badge`: role, permission, or access label.
- `x-admin.alert`: inline message block.
- `x-admin.flash`: flash-style wrapper.
- `x-admin.toast-stack`, `x-admin.toast`: transient feedback.
- `x-admin.loading-state`: inline loading indicator.
- `x-admin.skeleton`: placeholder skeleton block.
- `x-admin.empty-state`: base empty state.
- `x-admin.empty-search`: empty state after search or filtering.
- `x-admin.not-found-state`: missing resource state.

## Tables and Dense Data

- `x-admin.table`: canonical compact table.
- `x-admin.table-toolbar`: top section of a list or table page.
- `x-admin.table-filters`: search and filters zone.
- `x-admin.table-loading`: table loading state.
- `x-admin.table-empty`: table empty state.
- `x-admin.sort-button`: sortable header control.
- `x-admin.selection-counter`: selected-items counter.

## Data Grid Layer

- `x-admin.data-grid`: container for richer list or grid flows.
- `x-admin.data-grid-toolbar`: top grid controls.
- `x-admin.data-grid-filters`: grid filters layer.
- `x-admin.data-grid-summary`: summary or meta block.
- `x-admin.data-grid-selection-bar`: selection actions layer.
- `x-admin.data-grid-table`: table wrapper inside a data grid.
- `x-admin.data-grid-row`: row wrapper.
- `x-admin.data-grid-cell`: cell or header cell wrapper.
- `x-admin.data-grid-actions`: action zone in the grid toolbar or selection layer.

## Search, Filters, and Query Controls

- `x-admin.search`: standard search field.
- `x-admin.search` with autocomplete suggestions and debounced live-query behavior is the preferred search pattern for admin lists.
- `x-admin.filters`: filter group wrapper.
- `x-admin.filter-chip`: toggle-like filter chip.
- `x-admin.applied-filters`: list of applied filters.
- `x-admin.date-range`: lightweight date range control.
- `x-admin.date-picker`: single-day picker control.
- `x-admin.date-range-picker`: canonical paired date-range picker.

## Forms and Fields

- `x-admin.form`: base form wrapper.
- `x-admin.form-section`: form section wrapper.
- `x-admin.form-grid`: grid for form controls.
- `x-admin.form-sidebar`: supportive sidebar for form pages.
- `x-admin.form-help`: helper or explanatory text.
- `x-admin.form-actions`: bottom row of form actions.
- `x-admin.field`: label + control + hint/error wrapper.
- `x-admin.field-error`: canonical error output.
- `x-admin.input`: text-like input.
- `x-admin.password-input`: password field.
- `x-admin.slug-input`: slug control with prefix.
- `x-admin.select`: select field.
- `x-admin.textarea`: multiline field.
- `x-admin.checkbox`: checkbox control.
- `x-admin.toggle`: switch-like boolean control.
- `x-admin.radio-group`, `x-admin.radio-option`: choose-one controls.
- `x-admin.file-upload`: upload trigger shell.
- `x-admin.tags-input`, `x-admin.tag`: tags or token UI.
- `x-admin.multi-select`: multi-value selection area.
- `x-admin.entity-picker`: related-entity picker.
- `x-admin.rich-text-shell`: shell for rich text editors.
- `x-admin.inline-edit`: compact inline editing.

## Stats, Summary, and Analytics

- `x-admin.stat-card`: large summary metric card.
- `x-admin.metric-grid`: grid for metrics and compact stats.
- `x-admin.key-stat`: key/value metric pair.
- `x-admin.stat-trend`: up/down/neutral trend indicator.
- `x-admin.progress`: progress bar.
- `x-admin.chart-card`: chart surface.
- `x-admin.line-chart`: line chart shell for time-series data.
- `x-admin.area-chart`: filled time-series chart shell.
- `x-admin.bar-chart`: categorical bar chart shell.
- `x-admin.histogram`: distribution chart shell.
- `x-admin.pie-chart`: pie chart shell.
- `x-admin.donut-chart`: donut chart shell.
- `x-admin.sparkline`: compact trend shell for dense dashboards.
- `x-admin.calendar-block`: time, calendar, or schedule block.

## Resource Detail and Workflow

- `x-admin.resource-header`: detail-page or settings-section header.
- `x-admin.resource-layout`: main detail layout.
- `x-admin.resource-sidebar`: secondary column for a detail page.
- `x-admin.resource-section`: detail-page section.
- `x-admin.resource-actions`: action group for a resource page.
- `x-admin.resource-meta`: meta or status row.
- `x-admin.resource-status`: compact status summary block.
- `x-admin.detail-card`: supporting detail panel.
- `x-admin.description-list`, `x-admin.description-item`: structured key/value details.
- `x-admin.data-list`, `x-admin.data-item`: simplified structured list.
- `x-admin.note`: short note or callout section.
- `x-admin.comment-thread`, `x-admin.comment`: comments or discussion UI.
- `x-admin.revision-list`, `x-admin.revision-item`: revision history.
- `x-admin.stepper`, `x-admin.step`: workflow steps.

## Activity, History, and Audit

- `x-admin.timeline`, `x-admin.timeline-item`: event sequence.
- `x-admin.activity-feed`, `x-admin.activity-item`: activity stream.
- `x-admin.audit-log`, `x-admin.audit-log-item`: audit entries.
- `x-admin.diff-block`: diff or code-like change output.
- `x-admin.code-block`: preformatted code or endpoint snippet.

## Overlays and Temporary Surfaces

- `x-admin.modal`: blocking dialog.
- `x-admin.confirm-dialog`: confirm/cancel dialog for destructive flows.
- `x-admin.drawer`: side drawer.
- `x-admin.offcanvas-form`: side form workflow.
- `x-admin.spotlight`: command or search overlay.

## Identity and User Display

- `x-admin.avatar`: avatar or initials.
- `x-admin.user-chip`: compact user identity block.

## When to add a new admin component

Add a new `x-admin.*` component when at least one of these is true:

- the pattern will repeat on two or more pages
- a page starts introducing custom local markup for a common UI scenario
- the pattern needs shared SCSS rules, states, or an accessibility contract
- the pattern logically extends an existing component family in the kit

## What is not allowed

- Do not duplicate an existing kit pattern with raw Blade markup.
- Do not create local button, table, filter, panel, or modal styles inside a page view.
- Do not add a temporary page-specific component when the pattern is already a kit concern.
- Do not add a new admin pattern in this project while ignoring the template-sync requirement.

## Definition of done for a new kit component

- A Blade component exists under `resources/views/components/admin`.
- Shared SCSS support exists under `resources/css/admin-panel`.
- The component fits the existing tokens, density, and naming rules.
- If it is a user-visible pattern, it is shown on `/management/ui-kit`.
- Documentation is updated when the component introduces a new canonical group or rule.
