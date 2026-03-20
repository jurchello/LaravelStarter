#!/usr/bin/env bash
set -e

HOT_FILE="public/hot"
VITE_URL="http://127.0.0.1:5173"

cleanup() {
  rm -f "$HOT_FILE"
}

trap cleanup INT TERM

echo "Starting LaravelStarter dev stack..."

# 1. Start containers
docker-compose up -d

# 2. Kill stale processes on dev ports
PID=$(lsof -ti:8011) && [ -n "$PID" ] && kill -9 $PID || true
PID=$(lsof -ti:8445) && [ -n "$PID" ] && kill -9 $PID || true
PID=$(lsof -ti:5173) && [ -n "$PID" ] && kill -9 $PID || true
rm -f "$HOT_FILE"
mkdir -p public
printf '%s\n' "$VITE_URL" > "$HOT_FILE"

# 3. Clear Laravel caches
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan route:clear
docker-compose exec php php artisan view:clear

# 4. Storage link
docker-compose exec php php artisan storage:link || true

# 5. Restart nginx
docker-compose restart nginx > /dev/null

# 6. Run in parallel
concurrently -n "DB,REDIS,MAILPIT,PHP,QUEUE,VITE" -c "blue,cyan,yellow,green,white,magenta" \
  "docker-compose logs --tail=50 -f db" \
  "docker-compose logs --tail=50 -f redis" \
  "docker-compose logs --tail=50 -f mailpit" \
  "docker-compose logs --tail=50 -f nginx php" \
  "docker-compose exec php php artisan queue:work --queue=email,default --tries=3 --backoff=5 --timeout=90" \
  "npm run dev -- --host 127.0.0.1 --port 5173 --strictPort"
