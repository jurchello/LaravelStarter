@extends('admin-panel.layouts.admin')

@section('content')
    <x-admin.page data-admin-page="ui-kit" data-testid="admin-ui-kit-page">
        <x-admin.page-header
            title="Admin UI Kit"
            subtitle="Living catalog of the admin design system: navigation, data display, forms, workflows, overlays, and utility states."
        >
            <x-admin.button href="{{ route('admin.dashboard') }}">Back to dashboard</x-admin.button>
        </x-admin.page-header>

        <x-admin.surface padded>
            <x-admin.breadcrumbs>
                <x-admin.breadcrumb href="{{ route('admin.dashboard') }}">Admin</x-admin.breadcrumb>
                <x-admin.breadcrumb active>UI Kit</x-admin.breadcrumb>
            </x-admin.breadcrumbs>

            <x-admin.topbar class="uikit-stack">
                <x-admin.badge variant="accent">Ready</x-admin.badge>
                <x-admin.status-pill variant="success">Healthy</x-admin.status-pill>
                <x-admin.permission-badge variant="accent">super-admin</x-admin.permission-badge>
                <x-admin.user-chip name="Admin User" meta="admin@example.com" />
            </x-admin.topbar>
        </x-admin.surface>

        <x-admin.surface padded>
            <x-admin.toolbar
                title="Buttons, alerts, filters, and navigation"
                subtitle="Core interaction primitives for admin pages."
            />

            <div class="admin-stat-grid">
                <x-admin.panel title="Buttons and badges">
                    <div class="admin-form-actions uikit-actions-start">
                        <x-admin.button>Default</x-admin.button>
                        <x-admin.button variant="primary">Primary</x-admin.button>
                        <x-admin.badge variant="muted">Muted badge</x-admin.badge>
                        <x-admin.badge variant="accent">Accent badge</x-admin.badge>
                        <x-admin.boolean-pill :value="true" />
                        <x-admin.boolean-pill :value="false" />
                    </div>
                </x-admin.panel>

                <x-admin.panel title="Tabs and chips">
                    <x-admin.tabs>
                        <x-admin.tab href="#" active>Overview</x-admin.tab>
                        <x-admin.tab href="#">Members</x-admin.tab>
                        <x-admin.tab href="#">Billing</x-admin.tab>
                    </x-admin.tabs>

                    <x-admin.applied-filters class="uikit-stack-sm">
                        <x-admin.filter-chip active>Active</x-admin.filter-chip>
                        <x-admin.filter-chip>Pending</x-admin.filter-chip>
                        <x-admin.filter-chip>Archived</x-admin.filter-chip>
                    </x-admin.applied-filters>

                    <div class="uikit-stack-sm">
                        <x-admin.sort-button>Name</x-admin.sort-button>
                        <x-admin.sort-button active direction="asc">Joined</x-admin.sort-button>
                        <x-admin.sort-button active direction="desc">Status</x-admin.sort-button>
                    </div>
                </x-admin.panel>

                <x-admin.panel title="Actions">
                    <x-admin.action-menu label="Open menu">
                        <x-admin.action-item href="#">View</x-admin.action-item>
                        <x-admin.action-item href="#">Duplicate</x-admin.action-item>
                        <x-admin.action-item variant="danger">Delete</x-admin.action-item>
                    </x-admin.action-menu>
                </x-admin.panel>
            </div>

            <div class="uikit-stack">
                <x-admin.alert title="Info">Compact admin surfaces now use tighter spacing and smaller controls.</x-admin.alert>
                <x-admin.alert variant="success" title="Success">Verified admins can access admin routes.</x-admin.alert>
                <x-admin.alert variant="warning" title="Warning">Non-admins and guests now see 404 for all admin URLs.</x-admin.alert>
                <x-admin.flash variant="danger">Danger flash sample</x-admin.flash>
            </div>

            <x-admin.pagination>
                <span>Page 1 of 12</span>
                <div class="admin-pagination__controls">
                    <x-admin.pagination-link disabled>Previous</x-admin.pagination-link>
                    <x-admin.pagination-link active>1</x-admin.pagination-link>
                    <x-admin.pagination-link href="#">2</x-admin.pagination-link>
                    <x-admin.pagination-link href="#">Next</x-admin.pagination-link>
                </div>
            </x-admin.pagination>
        </x-admin.surface>

        <x-admin.filter-section
            title="Filter Section"
            subtitle="Canonical separate section for live filters above data-heavy lists."
        >
            <x-admin.table-filters>
                <x-admin.search placeholder="Search users" list-id="ui-kit-filter-search">
                    <datalist id="ui-kit-filter-search">
                        <option value="Alice Admin"></option>
                        <option value="alice@example.com"></option>
                    </datalist>
                </x-admin.search>
                <x-admin.select>
                    <option>All roles</option>
                    <option>Admins</option>
                    <option>Users</option>
                </x-admin.select>
            </x-admin.table-filters>
        </x-admin.filter-section>

        <x-admin.surface padded>
            <x-admin.toolbar
                title="Stats, charts, and utility indicators"
                subtitle="Compact cards for overview screens."
            />

            <div class="admin-stat-grid">
                <x-admin.stat-card label="Verified admins" value="12" tone="accent" />
                <x-admin.stat-card label="Active projects" value="48" tone="success" />
                <x-admin.stat-card label="Queued audits" value="7" tone="warning" />
            </div>

            <x-admin.metric-grid class="uikit-stack">
                <x-admin.key-stat label="Conversion" value="5.4%" />
                <x-admin.key-stat label="Weekly growth" value="+12%" />
                <x-admin.stat-trend direction="up" value="18.2%" />
                <x-admin.progress :value="73" :max="100" />
            </x-admin.metric-grid>

            <x-admin.chart-card title="Revenue pulse" subtitle="Placeholder chart surface">
                <x-admin.skeleton :lines="4" />
            </x-admin.chart-card>

            <div class="admin-stat-grid">
                <x-admin.chart-card title="Line chart"><x-admin.line-chart label="Line chart preview" /></x-admin.chart-card>
                <x-admin.chart-card title="Area chart"><x-admin.area-chart label="Area chart preview" /></x-admin.chart-card>
                <x-admin.chart-card title="Bar chart"><x-admin.bar-chart label="Bar chart preview" /></x-admin.chart-card>
                <x-admin.chart-card title="Histogram"><x-admin.histogram label="Histogram preview" /></x-admin.chart-card>
                <x-admin.chart-card title="Pie chart"><x-admin.pie-chart label="Pie chart preview" /></x-admin.chart-card>
                <x-admin.chart-card title="Donut chart"><x-admin.donut-chart label="Donut chart preview" /></x-admin.chart-card>
            </div>

        </x-admin.surface>

        <x-admin.surface padded>
            <x-admin.toolbar
                title="Tables and data grids"
                subtitle="Dense patterns for long lists with many columns."
            />

            <x-admin.table-toolbar>
                <x-admin.table-filters>
                    <x-admin.search placeholder="Search users" />
                    <x-admin.date-range fromValue="2026-03-01" toValue="2026-03-19" />
                    <x-admin.selection-counter :count="3" label="rows selected" />
                </x-admin.table-filters>
                <x-admin.bulk-actions>
                    <x-admin.button>Export</x-admin.button>
                    <x-admin.button variant="primary">Create user</x-admin.button>
                </x-admin.bulk-actions>
            </x-admin.table-toolbar>

            <x-admin.table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><x-admin.user-chip name="Admin User" meta="admin@example.com" /></td>
                        <td>admin@example.com</td>
                        <td><x-admin.status-pill variant="success">Verified</x-admin.status-pill></td>
                        <td><x-admin.permission-badge variant="accent">super-admin</x-admin.permission-badge></td>
                        <td>Mar 19, 2026</td>
                        <td>
                            <x-admin.row-actions>
                                <x-admin.button>View</x-admin.button>
                                <x-admin.button>Impersonate</x-admin.button>
                            </x-admin.row-actions>
                        </td>
                    </tr>
                    <tr>
                        <td><x-admin.user-chip name="Ops Manager" meta="ops@example.com" /></td>
                        <td>ops@example.com</td>
                        <td><x-admin.status-pill variant="warning">Pending</x-admin.status-pill></td>
                        <td><x-admin.permission-badge>editor</x-admin.permission-badge></td>
                        <td>Mar 18, 2026</td>
                        <td>
                            <x-admin.row-actions>
                                <x-admin.button>Edit</x-admin.button>
                                <x-admin.button>Reset</x-admin.button>
                            </x-admin.row-actions>
                        </td>
                    </tr>
                </tbody>
            </x-admin.table>

            <div class="admin-stat-grid">
                <x-admin.table-loading />
                <x-admin.table-empty />
            </div>

            <x-admin.data-grid>
                <x-admin.data-grid-toolbar>
                    <strong>Data grid</strong>
                    <x-admin.data-grid-summary>Showing 2 of 128 items</x-admin.data-grid-summary>
                </x-admin.data-grid-toolbar>

                <x-admin.data-grid-filters>
                    <x-admin.filter-chip active>Published</x-admin.filter-chip>
                    <x-admin.filter-chip>Draft</x-admin.filter-chip>
                    <x-admin.filter-chip>Archived</x-admin.filter-chip>
                </x-admin.data-grid-filters>

                <x-admin.data-grid-selection-bar>
                    <x-admin.selection-counter :count="2" label="items selected" />
                    <x-admin.data-grid-actions>
                        <x-admin.button>Archive</x-admin.button>
                        <x-admin.button variant="primary">Publish</x-admin.button>
                    </x-admin.data-grid-actions>
                </x-admin.data-grid-selection-bar>

                <x-admin.data-grid-table>
                    <thead>
                        <x-admin.data-grid-row>
                            <x-admin.data-grid-cell head>ID</x-admin.data-grid-cell>
                            <x-admin.data-grid-cell head>Title</x-admin.data-grid-cell>
                            <x-admin.data-grid-cell head>Status</x-admin.data-grid-cell>
                            <x-admin.data-grid-cell head>Owner</x-admin.data-grid-cell>
                        </x-admin.data-grid-row>
                    </thead>
                    <tbody>
                        <x-admin.data-grid-row>
                            <x-admin.data-grid-cell>#1088</x-admin.data-grid-cell>
                            <x-admin.data-grid-cell>Spring launch page</x-admin.data-grid-cell>
                            <x-admin.data-grid-cell><x-admin.status-pill variant="success">Published</x-admin.status-pill></x-admin.data-grid-cell>
                            <x-admin.data-grid-cell>Marketing</x-admin.data-grid-cell>
                        </x-admin.data-grid-row>
                        <x-admin.data-grid-row>
                            <x-admin.data-grid-cell>#1092</x-admin.data-grid-cell>
                            <x-admin.data-grid-cell>Partner portal refresh</x-admin.data-grid-cell>
                            <x-admin.data-grid-cell><x-admin.status-pill variant="warning">Review</x-admin.status-pill></x-admin.data-grid-cell>
                            <x-admin.data-grid-cell>Operations</x-admin.data-grid-cell>
                        </x-admin.data-grid-row>
                    </tbody>
                </x-admin.data-grid-table>
            </x-admin.data-grid>
        </x-admin.surface>

        <x-admin.surface padded>
            <x-admin.toolbar
                title="Forms"
                subtitle="Field, input, selection, and editor primitives."
            />

            <x-admin.form>
                <x-admin.form-section title="Project basics" description="Core text and select controls.">
                    <x-admin.form-grid :columns="2">
                        <x-admin.field label="Name">
                            <x-admin.input value="LaravelStarter" />
                        </x-admin.field>
                        <x-admin.field label="Environment">
                            <x-admin.select>
                                <option>Production</option>
                                <option selected>Staging</option>
                            </x-admin.select>
                        </x-admin.field>
                        <x-admin.field label="Slug">
                            <x-admin.slug-input prefix="/projects/" value="laravel-starter" />
                        </x-admin.field>
                        <x-admin.field label="Admin password">
                            <x-admin.password-input value="12345678" />
                        </x-admin.field>
                        <x-admin.field label="Summary">
                            <x-admin.textarea rows="4">Compact admin system with SSR site and API-backed backoffice.</x-admin.textarea>
                        </x-admin.field>
                        <x-admin.field label="Asset">
                            <x-admin.file-upload label="Upload file" />
                        </x-admin.field>
                    </x-admin.form-grid>
                </x-admin.form-section>

                <x-admin.form-section title="Selection controls" description="Compact toggles, radio groups, and helper text.">
                    <x-admin.radio-group label="Access level">
                        <x-admin.radio-option name="access_level" value="owner" checked label="Owner" />
                        <x-admin.radio-option name="access_level" value="editor" label="Editor" />
                    </x-admin.radio-group>

                    <x-admin.toggle checked label="Require verified email" />
                    <x-admin.checkbox checked label="Send onboarding email" />
                    <x-admin.form-help>Use compact controls by default across admin forms.</x-admin.form-help>
                </x-admin.form-section>

                <x-admin.form-section title="Tags and editor" description="Token input and rich text shell.">
                    <x-admin.tags-input>
                        <x-admin.tag>laravel</x-admin.tag>
                        <x-admin.tag>admin</x-admin.tag>
                        <x-admin.tag>kit</x-admin.tag>
                    </x-admin.tags-input>
                    <x-admin.rich-text-shell>
                        <p>Rich text placeholder body.</p>
                    </x-admin.rich-text-shell>
                </x-admin.form-section>

                <x-admin.form-actions>
                    <x-admin.button>Cancel</x-admin.button>
                    <x-admin.button variant="primary">Save changes</x-admin.button>
                </x-admin.form-actions>
            </x-admin.form>
        </x-admin.surface>

        <x-admin.surface padded>
            <x-admin.toolbar
                title="Resource and workflow patterns"
                subtitle="Detail views, revisions, notes, comments, audit logs, and process steps."
            />

            <x-admin.resource-header eyebrow="Resource" title="Project: LaravelStarter" subtitle="Canonical detail layout for admin resources.">
                <x-admin.resource-actions>
                    <x-admin.button>Edit</x-admin.button>
                    <x-admin.button variant="primary">Publish</x-admin.button>
                </x-admin.resource-actions>
            </x-admin.resource-header>

            <x-admin.resource-layout>
                <div>
                    <x-admin.resource-section title="Metadata" description="Description and status surfaces.">
                        <x-admin.resource-meta>
                            <x-admin.status-pill variant="success">Published</x-admin.status-pill>
                            <x-admin.permission-badge variant="accent">internal</x-admin.permission-badge>
                        </x-admin.resource-meta>

                        <x-admin.description-list>
                            <x-admin.description-item label="Owner" value="Admin User" />
                            <x-admin.description-item label="Locale" value="uk" />
                            <x-admin.description-item label="Updated">Mar 19, 2026 11:40</x-admin.description-item>
                        </x-admin.description-list>
                    </x-admin.resource-section>

                    <x-admin.resource-section title="Notes and comments">
                        <x-admin.note>Keep admin spacing compact and information-dense.</x-admin.note>
                        <x-admin.comment-thread>
                            <x-admin.comment author="Admin User" meta="2 minutes ago">Sidebar supports grouped and nested items.</x-admin.comment>
                            <x-admin.comment author="Ops Manager" meta="just now">Tables are now considerably tighter.</x-admin.comment>
                        </x-admin.comment-thread>
                    </x-admin.resource-section>

                    <x-admin.resource-section title="Revision history">
                        <x-admin.revision-list>
                            <x-admin.revision-item title="Compact density applied" meta="Mar 19, 2026">
                                Reduced shell, surface, and table spacing.
                            </x-admin.revision-item>
                            <x-admin.revision-item title="Admin URLs hidden" meta="Mar 19, 2026">
                                Guests and non-admins now receive 404.
                            </x-admin.revision-item>
                        </x-admin.revision-list>
                    </x-admin.resource-section>
                </div>

                <x-admin.resource-sidebar>
                    <x-admin.detail-card title="Key stats">
                        <x-admin.key-stat label="Users" value="128" />
                        <x-admin.key-stat label="Roles" value="4" />
                    </x-admin.detail-card>

                    <x-admin.detail-card title="Workflow">
                        <x-admin.stepper>
                            <x-admin.step title="Draft" completed />
                            <x-admin.step title="Review" active />
                            <x-admin.step title="Publish" />
                        </x-admin.stepper>
                    </x-admin.detail-card>
                </x-admin.resource-sidebar>
            </x-admin.resource-layout>
        </x-admin.surface>

        <x-admin.surface padded>
            <x-admin.toolbar
                title="Activity, audit, and utilities"
                subtitle="Operational components for admin workflows."
            />

            <div class="admin-stat-grid">
                <x-admin.panel title="Timeline">
                    <x-admin.timeline>
                        <x-admin.timeline-item title="Project created" time="09:10">Created by Admin User.</x-admin.timeline-item>
                        <x-admin.timeline-item title="UI Kit published" time="10:25">Living catalog added to admin panel.</x-admin.timeline-item>
                    </x-admin.timeline>
                </x-admin.panel>

                <x-admin.panel title="Activity feed">
                    <x-admin.activity-feed>
                        <x-admin.activity-item title="Invited a new admin" meta="5m ago">Invitation email queued.</x-admin.activity-item>
                        <x-admin.activity-item title="Role updated" meta="12m ago">Editor promoted to manager.</x-admin.activity-item>
                    </x-admin.activity-feed>
                </x-admin.panel>

                <x-admin.panel title="Audit log">
                    <x-admin.audit-log>
                        <x-admin.audit-log-item title="users.update" meta="Admin User">Changed `is_admin` from `0` to `1`.</x-admin.audit-log-item>
                        <x-admin.audit-log-item title="settings.update" meta="System">Updated compact admin tokens.</x-admin.audit-log-item>
                    </x-admin.audit-log>
                </x-admin.panel>
            </div>

            <div class="admin-stat-grid">
                <x-admin.panel title="Search and states">
                    <x-admin.search placeholder="Search anything" />
                    <x-admin.loading-state label="Hydrating admin API payload" />
                    <x-admin.empty-search />
                    <x-admin.not-found-state />
                </x-admin.panel>

                <x-admin.panel title="Structured lists">
                    <x-admin.data-list>
                        <x-admin.data-item label="Runtime">PHP 8.4</x-admin.data-item>
                        <x-admin.data-item label="Framework">Laravel 13</x-admin.data-item>
                        <x-admin.data-item label="UI">Blade + SCSS + TS</x-admin.data-item>
                    </x-admin.data-list>
                    <x-admin.code-block>GET /management/api/users?page=1</x-admin.code-block>
                </x-admin.panel>

                <x-admin.panel title="Pickers and calendar">
                    <x-admin.entity-picker>
                        <x-admin.user-chip name="Editor User" meta="editor@example.com" />
                        <x-admin.user-chip name="Support User" meta="support@example.com" />
                    </x-admin.entity-picker>
                    <x-admin.multi-select name="ui_kit_segments[]" size="4">
                        <option selected>billing</option>
                        <option selected>ops</option>
                        <option>growth</option>
                        <option>platform</option>
                    </x-admin.multi-select>
                    <x-admin.calendar-block title="Release window">
                        <p>Mon-Fri, 10:00-16:00</p>
                    </x-admin.calendar-block>
                </x-admin.panel>
            </div>
        </x-admin.surface>

        <x-admin.surface padded>
            <x-admin.toolbar
                title="Overlays and transient UI"
                subtitle="Dialog, drawer, off-canvas, toast, and spotlight patterns."
            />

            <x-admin.toast-stack>
                <x-admin.toast title="Saved" variant="success">Resource updated successfully.</x-admin.toast>
                <x-admin.toast title="Sync pending" variant="warning">Background sync will retry in 2 minutes.</x-admin.toast>
            </x-admin.toast-stack>

            <x-admin.sticky-action-bar>
                <x-admin.button>Discard</x-admin.button>
                <x-admin.button variant="primary">Apply changes</x-admin.button>
            </x-admin.sticky-action-bar>

            <x-admin.modal title="Modal preview" :open="false">
                <p>Modal body preview.</p>
            </x-admin.modal>

            <x-admin.confirm-dialog
                title="Confirm preview"
                description="Preview of destructive confirmation dialog."
                confirmLabel="Delete"
                cancelLabel="Keep"
                :open="false"
            />

            <x-admin.drawer title="Drawer preview" :open="false">
                <p>Drawer body preview.</p>
            </x-admin.drawer>

            <x-admin.offcanvas-form title="Off-canvas form preview" :open="false">
                <x-admin.form>
                    <x-admin.field label="Name">
                        <x-admin.input value="Preview resource" />
                    </x-admin.field>
                </x-admin.form>
            </x-admin.offcanvas-form>

            <x-admin.spotlight :open="false">
                <x-admin.action-item href="#">Open users directory</x-admin.action-item>
            </x-admin.spotlight>
        </x-admin.surface>
    </x-admin.page>
@endsection
