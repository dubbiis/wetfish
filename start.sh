#!/bin/bash

echo "========================================="
echo "  WetFish - Starting deployment..."
echo "========================================="

# Always regenerate .env from environment variables
echo "[1/7] Generating .env from environment variables..."
echo "APP_NAME=${APP_NAME:-WetFish}" > .env
echo "APP_ENV=${APP_ENV:-production}" >> .env
echo "APP_KEY=${APP_KEY}" >> .env
echo "APP_DEBUG=${APP_DEBUG:-true}" >> .env
echo "APP_URL=${APP_URL:-http://localhost:8080}" >> .env
echo "" >> .env
echo "DB_CONNECTION=${DB_CONNECTION:-mysql}" >> .env
echo "DB_HOST=${DB_HOST:-localhost}" >> .env
echo "DB_PORT=${DB_PORT:-3306}" >> .env
echo "DB_DATABASE=${DB_DATABASE:-wetfish_bd}" >> .env
echo "DB_USERNAME=${DB_USERNAME:-wetfish}" >> .env
echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
echo "" >> .env
echo "SESSION_DRIVER=file" >> .env
echo "CACHE_STORE=file" >> .env
echo "QUEUE_CONNECTION=sync" >> .env
echo "LOG_CHANNEL=stderr" >> .env
echo "       .env generated OK"
echo "       APP_URL=${APP_URL:-http://localhost:8080}"
echo "       DB_HOST=${DB_HOST:-localhost} / DB_DATABASE=${DB_DATABASE:-wetfish_bd}"

# Ensure storage directories exist
echo "[2/7] Creating storage directories..."
mkdir -p storage/app/public/products
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
echo "       Storage directories OK"

# Create storage symlink
echo "[3/7] Creating storage symlink..."
php artisan storage:link 2>/dev/null && echo "       Symlink created" || echo "       WARNING: storage:link failed (may already exist)"

# Run migrations
echo "[4/7] Running migrations..."
php artisan migrate --force && echo "       Migrations OK" || echo "       WARNING: Migration had issues, continuing..."

# Run seeders
echo "[5/7] Running seeders..."
php artisan db:seed --force && echo "       Seeders OK" || echo "       WARNING: Seeder had issues, continuing..."

# Cache config and views (NOT routes — Livewire full-page components need dynamic routing)
echo "[6/7] Caching config and views..."
php artisan config:cache && echo "       Config cached"
php artisan view:cache && echo "       Views cached"

# Verify build assets exist
echo "[7/7] Verifying build assets..."
if [ -f public_html/build/manifest.json ]; then
    echo "       manifest.json found OK"
else
    echo "       ERROR: manifest.json NOT found in public_html/build/"
    echo "       Listing public_html/build/:"
    ls -la public_html/build/ 2>/dev/null || echo "       public_html/build/ does not exist!"
fi

echo "========================================="
echo "  WetFish ready! Starting server..."
echo "  http://0.0.0.0:8080"
echo "========================================="
php -S 0.0.0.0:8080 -t public_html public_html/router.php
