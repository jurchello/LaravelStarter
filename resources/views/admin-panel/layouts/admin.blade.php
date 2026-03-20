<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — {{ config('app.name') }}</title>
    @vite([
        'resources/css/admin-panel/layout.scss',
        'resources/js/admin-panel/layout.ts',
    ])
    @stack('head')
</head>
<body>
@include('partials.flashed-toasts')
@php
    $sidebarGroups = [
        [
            'label' => 'Overview',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => route('admin.dashboard'),
                    'active' => request()->routeIs('admin.dashboard'),
                    'testid' => 'admin-nav-dashboard',
                ],
            ],
        ],
        [
            'label' => 'Management',
            'items' => [
                [
                    'label' => 'AB Tests',
                    'route' => route('admin.ab-tests.index'),
                    'active' => request()->routeIs('admin.ab-tests.*'),
                    'testid' => 'admin-nav-ab-tests',
                    'children' => [
                        [
                            'label' => 'All Tests',
                            'route' => route('admin.ab-tests.index'),
                            'active' => request()->routeIs('admin.ab-tests.index'),
                            'testid' => 'admin-nav-ab-tests-all',
                        ],
                        [
                            'label' => 'Create Test',
                            'route' => route('admin.ab-tests.create'),
                            'active' => request()->routeIs('admin.ab-tests.create'),
                            'testid' => 'admin-nav-ab-tests-create',
                        ],
                    ],
                ],
                [
                    'label' => 'Feature Flags',
                    'route' => route('admin.feature-flags.index'),
                    'active' => request()->routeIs('admin.feature-flags.*'),
                    'testid' => 'admin-nav-feature-flags',
                    'children' => [
                        [
                            'label' => 'All Flags',
                            'route' => route('admin.feature-flags.index'),
                            'active' => request()->routeIs('admin.feature-flags.index'),
                            'testid' => 'admin-nav-feature-flags-all',
                        ],
                    ],
                ],
                [
                    'label' => 'Users',
                    'route' => route('admin.users.index'),
                    'active' => request()->routeIs('admin.users.*'),
                    'testid' => 'admin-nav-users',
                    'children' => [
                        [
                            'label' => 'All Users',
                            'route' => route('admin.users.index'),
                            'active' => request()->routeIs('admin.users.index'),
                            'testid' => 'admin-nav-users-directory',
                        ],
                    ],
                ],
                [
                    'label' => 'Roles',
                    'route' => route('admin.roles.index'),
                    'active' => request()->routeIs('admin.roles.*'),
                    'testid' => 'admin-nav-roles',
                    'children' => [
                        [
                            'label' => 'All Roles',
                            'route' => route('admin.roles.index'),
                            'active' => request()->routeIs('admin.roles.index'),
                            'testid' => 'admin-nav-roles-all',
                        ],
                    ],
                ],
            ],
        ],
        [
            'label' => 'System',
            'items' => [
                [
                    'label' => 'API Docs',
                    'route' => route('docs.site.ui'),
                    'active' => request()->is('docs/site-api*') || request()->is('docs/admin-api*'),
                    'testid' => 'admin-nav-api-docs',
                    'children' => [
                        [
                            'label' => 'Site API',
                            'route' => route('docs.site.ui'),
                            'active' => request()->is('docs/site-api*'),
                            'testid' => 'admin-nav-api-docs-site',
                        ],
                        [
                            'label' => 'Admin API',
                            'route' => route('docs.admin.ui'),
                            'active' => request()->is('docs/admin-api*'),
                            'testid' => 'admin-nav-api-docs-admin',
                        ],
                    ],
                ],
                [
                    'label' => 'UI Kit',
                    'route' => route('admin.ui-kit'),
                    'active' => request()->routeIs('admin.ui-kit'),
                    'testid' => 'admin-nav-ui-kit',
                ],
            ],
        ],
    ];

    if (\App\Infrastructure\Shared\Support\Environment::isLocalOrTesting()) {
        $sidebarGroups[] = [
            'label' => 'Development',
            'items' => [
                [
                    'label' => 'Mail Previews',
                    'route' => route('admin.mail-previews.index'),
                    'active' => request()->routeIs('admin.mail-previews.*'),
                    'testid' => 'admin-nav-mail-previews',
                ],
            ],
        ];
    }
@endphp
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <span class="admin-brand-mark">LS</span>
            <div>
                <strong>{{ config('app.name') }}</strong>
                <p>Admin console</p>
            </div>
        </div>
        <nav class="admin-menu" data-testid="admin-nav">
            @foreach ($sidebarGroups as $group)
                <x-admin.sidebar-group :label="$group['label']" class="admin-sidebar-group--plain">
                    @foreach ($group['items'] as $item)
                        <div class="admin-menu-node {{ ! empty($item['children']) ? 'has-children' : '' }} {{ $item['active'] ? 'is-open' : '' }}"
                             @if (! empty($item['children'])) data-admin-menu-node @endif>
                            @if (! empty($item['children']))
                                <button type="button"
                                        class="admin-menu-item admin-menu-toggle {{ $item['active'] ? 'is-active' : '' }}"
                                        data-admin-menu-toggle
                                        aria-expanded="{{ $item['active'] ? 'true' : 'false' }}"
                                        data-testid="{{ $item['testid'] }}">
                                    <span class="admin-menu-item__label">{{ $item['label'] }}</span>
                                    <span class="admin-menu-item__meta">{{ count($item['children']) }}</span>
                                </button>

                                <div class="admin-submenu" @if (! $item['active']) hidden @endif>
                                    @foreach ($item['children'] as $child)
                                        <a class="admin-submenu-item {{ $child['active'] ? 'is-active' : '' }}"
                                           href="{{ $child['route'] }}"
                                           data-testid="{{ $child['testid'] }}">
                                            <span class="admin-submenu-item__label">{{ $child['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <a class="admin-menu-item {{ $item['active'] ? 'is-active' : '' }}"
                                   href="{{ $item['route'] }}"
                                   data-testid="{{ $item['testid'] }}">
                                    <span class="admin-menu-item__label">{{ $item['label'] }}</span>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </x-admin.sidebar-group>
            @endforeach
        </nav>
        <div class="admin-sidebar-footer">
            <div class="admin-profile-card">
                <p class="admin-profile-name">{{ auth()->user()->name }}</p>
                <p class="admin-profile-email">{{ auth()->user()->email }}</p>
            </div>
            <a class="admin-menu-item" href="{{ url('/') }}" target="_blank" data-testid="admin-nav-back-to-site">Back to site</a>
        </div>
    </aside>

    <main class="admin-main">
        <section class="admin-content">
            @yield('content')
        </section>
    </main>
</div>

<div class="toast-container" data-admin-toast-container></div>

@stack('scripts')
</body>
</html>
