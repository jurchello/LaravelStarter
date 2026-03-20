#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
HOST="${E2E_HOST:-127.0.0.1}"
PORT="${E2E_PORT:-8011}"
APP_ENVIRONMENT="${APP_ENV:-testing}"
SKIP_UNIT_TESTS="${SKIP_UNIT_TESTS:-0}"
SKIP_BACKEND_TESTS="${SKIP_BACKEND_TESTS:-0}"
SKIP_E2E_TESTS="${SKIP_E2E_TESTS:-0}"

cleanup() {
    if [[ -n "${SERVER_PID:-}" ]] && kill -0 "$SERVER_PID" 2>/dev/null; then
        kill "$SERVER_PID"
        wait "$SERVER_PID" 2>/dev/null || true
    fi
}

trap cleanup EXIT INT TERM

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
    APP_ENV="$APP_ENVIRONMENT" php artisan test --env="$APP_ENVIRONMENT"
else
    echo "Skipping Laravel tests (SKIP_BACKEND_TESTS=1)."
fi

if [[ "$SKIP_E2E_TESTS" != "1" ]]; then
    while ss -ltn "( sport = :${PORT} )" | grep -q ":${PORT}"; do
        PORT=$((PORT + 1))
    done

    if [[ "${PORT}" != "${E2E_PORT:-8011}" ]]; then
        echo "Port ${E2E_PORT:-8011} is busy, using ${PORT} for e2e."
    fi

    echo "Starting Laravel server on ${HOST}:${PORT} (APP_ENV=${APP_ENVIRONMENT})..."
    cd "$ROOT_DIR"
    APP_ENV="$APP_ENVIRONMENT" php artisan config:clear > /tmp/app-e2e-config.log 2>&1 || true
    APP_ENV="$APP_ENVIRONMENT" php artisan cache:clear >> /tmp/app-e2e-config.log 2>&1 || true
    APP_ENV="$APP_ENVIRONMENT" php artisan serve --host="$HOST" --port="$PORT" --env="$APP_ENVIRONMENT" > /tmp/app-e2e-server.log 2>&1 &
    SERVER_PID=$!

    sleep 2

    echo "Running Playwright tests..."
    PLAYWRIGHT_REPORTER="${PLAYWRIGHT_REPORTER:-list}"
    PLAYWRIGHT_WORKERS="${PLAYWRIGHT_WORKERS:-1}"
    PLAYWRIGHT_BASE_URL="http://${HOST}:${PORT}" npx playwright test --reporter="${PLAYWRIGHT_REPORTER}" --workers="${PLAYWRIGHT_WORKERS}" "$@"
else
    echo "Skipping e2e tests (SKIP_E2E_TESTS=1)."
fi
