#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
APP_ENVIRONMENT="${TEST_APP_ENV:-testing}"
TEST_DB_CONNECTION="${TEST_DB_CONNECTION:-sqlite}"
TEST_DB_DATABASE="${TEST_DB_DATABASE:-${ROOT_DIR}/database/database.sqlite}"
TEST_CACHE_STORE="${TEST_CACHE_STORE:-array}"
TEST_QUEUE_CONNECTION="${TEST_QUEUE_CONNECTION:-sync}"
TEST_SESSION_DRIVER="${TEST_SESSION_DRIVER:-file}"
TEST_MAIL_MAILER="${TEST_MAIL_MAILER:-array}"
SKIP_UNIT_TESTS="${SKIP_UNIT_TESTS:-0}"
SKIP_BACKEND_TESTS="${SKIP_BACKEND_TESTS:-0}"

prepare_testing_database() {
    if [[ "${APP_ENVIRONMENT}" != "testing" ]] || [[ "${TEST_DB_CONNECTION}" != "sqlite" ]]; then
        return
    fi

    mkdir -p "$(dirname "${TEST_DB_DATABASE}")"
    touch "${TEST_DB_DATABASE}"
}

if [[ "$SKIP_UNIT_TESTS" != "1" ]]; then
    echo "Running Vitest unit tests..."
    cd "$ROOT_DIR"
    npx vitest run
else
    echo "Skipping Vitest tests (SKIP_UNIT_TESTS=1)."
fi

if [[ "$SKIP_BACKEND_TESTS" != "1" ]]; then
    echo "Running Laravel tests..."
    cd "$ROOT_DIR"
    APP_ENV="$APP_ENVIRONMENT" \
    DB_CONNECTION="${TEST_DB_CONNECTION}" \
    DB_DATABASE="${TEST_DB_DATABASE}" \
    CACHE_STORE="${TEST_CACHE_STORE}" \
    QUEUE_CONNECTION="${TEST_QUEUE_CONNECTION}" \
    SESSION_DRIVER="${TEST_SESSION_DRIVER}" \
    MAIL_MAILER="${TEST_MAIL_MAILER}" \
    php artisan test --env="$APP_ENVIRONMENT"
else
    echo "Skipping Laravel tests (SKIP_BACKEND_TESTS=1)."
fi
