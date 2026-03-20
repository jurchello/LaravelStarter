#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/app"            # TODO: set actual path on server
LOGFILE="/var/log/app-deploy.log" # TODO: set actual log file name
GITHUB_REPO="git@github.com:TODO/TODO.git" # TODO: set repository URL
BRANCH="${1:-main}"

echo -e "${GREEN}=== Deploy Script ===${NC}"
echo "$(date): Starting deploy..."

# Function to log and print
log_and_print() {
    echo -e "$1"
    echo "$(date): $1" >> $LOGFILE
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: Not in Laravel project directory${NC}"
    exit 1
fi

log_and_print "${YELLOW}1. Fetching latest code from GitHub (${BRANCH})...${NC}"
git fetch origin $BRANCH
git reset --hard origin/$BRANCH

log_and_print "${YELLOW}2. Installing Composer dependencies...${NC}"
/usr/local/bin/composer install --no-dev --optimize-autoloader

log_and_print "${YELLOW}3. Installing NPM dependencies...${NC}"
/usr/bin/npm ci

log_and_print "${YELLOW}4. Building frontend assets...${NC}"
/usr/bin/npm run build

log_and_print "${YELLOW}4.1 Waiting for Vite manifest...${NC}"

MANIFEST="public/build/manifest.json"
MAX_WAIT=60
WAITED=0

while [ ! -f "$MANIFEST" ]; do
    if [ $WAITED -ge $MAX_WAIT ]; then
        echo "❌ Timeout waiting for Vite manifest ($MANIFEST)"
        exit 1
    fi
    sleep 1
    WAITED=$((WAITED + 1))
done

log_and_print "${GREEN}✓ Vite manifest found${NC}"

log_and_print "${YELLOW}5. Running database migrations...${NC}"
/usr/bin/php artisan migrate --force

log_and_print "${YELLOW}6. Syncing permissions...${NC}"
/usr/bin/php artisan permissions:sync --force

log_and_print "${YELLOW}7. Clearing caches...${NC}"
/usr/bin/php artisan config:clear
/usr/bin/php artisan cache:clear 2>/dev/null || echo "Cache clear failed (permissions)"
/usr/bin/php artisan route:clear
/usr/bin/php artisan view:clear

log_and_print "${YELLOW}8. Restarting queue workers...${NC}"
/usr/bin/php artisan queue:restart

log_and_print "${GREEN}✅ Deploy completed successfully!${NC}"
echo "$(date): Deploy completed successfully" >> $LOGFILE
