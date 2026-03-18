#!/bin/bash

echo "========================================="
echo "  WetFish - Starting deployment..."
echo "========================================="

# Always regenerate .env from environment variables
echo "[1/8] Generating .env..."
cat > /app/.env <<EOF
APP_NAME=${APP_NAME:-WetFish}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-true}
APP_URL=${APP_URL:-http://localhost:8080}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-wetfish_bd}
DB_USERNAME=${DB_USERNAME:-wetfish}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
EOF
echo "       .env OK (APP_URL=${APP_URL:-http://localhost:8080}, DB_HOST=${DB_HOST:-localhost})"

# Permissions
echo "[2/8] Setting permissions..."
chmod -R 775 /app/storage /app/bootstrap/cache
echo "       Permissions OK"

# Ensure storage directories exist
echo "[3/8] Creating storage directories..."
mkdir -p /app/storage/app/public/products
mkdir -p /app/storage/framework/{sessions,views,cache}
mkdir -p /app/storage/logs
echo "       Storage dirs OK"

# Clear caches before migration
echo "[4/8] Clearing caches..."
php artisan config:clear
php artisan cache:clear
echo "       Caches cleared"

# Storage link
echo "[5/8] Creating storage symlink..."
php artisan storage:link 2>/dev/null && echo "       Symlink OK" || echo "       WARNING: storage:link failed"

# Migrations
echo "[6/8] Running migrations..."
php artisan migrate --force && echo "       Migrations OK" || echo "       WARNING: Migration issues, continuing..."

# Seeders
echo "[7/8] Running seeders..."
php artisan db:seed --force && echo "       Seeders OK" || echo "       WARNING: Seeder issues, continuing..."

# Verify assets
echo "[8/8] Verifying build assets..."
if [ -f /app/public_html/build/manifest.json ]; then
    echo "       manifest.json OK"
else
    echo "       ERROR: manifest.json NOT found!"
    ls -la /app/public_html/build/ 2>/dev/null || echo "       build/ directory missing!"
fi

echo "========================================="
echo "  WetFish ready! Port 8080"
echo "========================================="
exec php -S 0.0.0.0:8080 -t /app/public_html /app/public_html/router.php
