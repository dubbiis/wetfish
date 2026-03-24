#!/bin/bash

echo "========================================="
echo "  WetFish - Starting deployment..."
echo "========================================="
echo "  PWD: $(pwd)"
echo "  PHP: $(php -v | head -1)"

# Always regenerate .env from environment variables
echo "[1/7] Generating .env..."
cat > /app/.env <<EOF
APP_NAME=${APP_NAME:-WetFish}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
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

OPENAI_API_KEY=${OPENAI_API_KEY:-}
EOF
echo "       .env OK"

# Permissions
echo "[2/7] Setting permissions..."
chmod -R 775 /app/storage /app/bootstrap/cache
echo "       OK"

# Ensure storage directories exist
echo "[3/7] Creating storage directories..."
mkdir -p /app/storage/app/public/products
mkdir -p /app/storage/framework/{sessions,views,cache}
mkdir -p /app/storage/logs
echo "       OK"

# Clear caches
echo "[4/7] Clearing caches..."
php artisan config:clear
php artisan cache:clear
echo "       OK"

# Migrations
echo "[5/7] Running migrations..."
php artisan migrate --force && echo "       OK" || echo "       WARNING: issues"

# Seeders
echo "[6/7] Running seeders..."
php artisan db:seed --force && echo "       OK" || echo "       WARNING: issues"

# Verify assets
echo "[7/7] Verifying build..."
echo "       public_html contents:"
ls -la /app/public_html/ 2>/dev/null
echo "       build/ contents:"
ls -la /app/public_html/build/ 2>/dev/null
echo "       manifest.json first 5 lines:"
head -5 /app/public_html/build/manifest.json 2>/dev/null || echo "       manifest.json NOT FOUND"

# Test that PHP can actually serve a request
echo ""
echo "========================================="
echo "  Starting server on 0.0.0.0:8080"
echo "========================================="
exec php -S 0.0.0.0:8080 -t /app/public_html /app/public_html/router.php
