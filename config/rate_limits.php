<?php

declare(strict_types=1);

return [
    'auth' => [
        'login' => [
            'max_attempts' => (int) env('RATE_LIMIT_AUTH_LOGIN_PER_MINUTE', 5),
            'decay_minutes' => 1,
        ],
        'password_reset' => [
            'max_attempts' => (int) env('RATE_LIMIT_AUTH_PASSWORD_RESET_PER_MINUTE', 3),
            'decay_minutes' => 1,
        ],
        'password_update' => [
            'max_attempts' => (int) env('RATE_LIMIT_AUTH_PASSWORD_UPDATE_PER_MINUTE', 5),
            'decay_minutes' => 1,
        ],
        'verification_send' => [
            'max_attempts' => (int) env('RATE_LIMIT_AUTH_VERIFICATION_SEND_PER_MINUTE', 3),
            'decay_minutes' => 1,
        ],
        'verification_verify' => [
            'max_attempts' => (int) env('RATE_LIMIT_AUTH_VERIFICATION_VERIFY_PER_MINUTE', 10),
            'decay_minutes' => 1,
        ],
        'social' => [
            'max_attempts' => (int) env('RATE_LIMIT_AUTH_SOCIAL_PER_MINUTE', 20),
            'decay_minutes' => 1,
        ],
    ],
    'admin' => [
        'search' => [
            'max_attempts' => (int) env('RATE_LIMIT_ADMIN_SEARCH_PER_MINUTE', 60),
            'decay_minutes' => 1,
        ],
        'mutation' => [
            'max_attempts' => (int) env('RATE_LIMIT_ADMIN_MUTATION_PER_MINUTE', 30),
            'decay_minutes' => 1,
        ],
        'impersonation' => [
            'max_attempts' => (int) env('RATE_LIMIT_ADMIN_IMPERSONATION_PER_MINUTE', 10),
            'decay_minutes' => 1,
        ],
    ],
    'site_api' => [
        'ab_assign' => [
            'max_attempts' => (int) env('RATE_LIMIT_SITE_AB_ASSIGN_PER_MINUTE', 120),
            'decay_minutes' => 1,
        ],
        'ab_event' => [
            'max_attempts' => (int) env('RATE_LIMIT_SITE_AB_EVENT_PER_MINUTE', 120),
            'decay_minutes' => 1,
        ],
    ],
];
