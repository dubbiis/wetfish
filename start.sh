#!/bin/bash
set -e

# Generate .env from environment variables if it doesn't exist
if [ ! -f .env ]; then
    echo "APP_NAME=${APP_NAME:-WetFish}" > .env
    echo "APP_ENV=${APP_ENV:-production}" >> .env
    echo "APP_KEY=${APP_KEY}" >> .env
    echo "APP_DEBUG=${APP_DEBUG:-false}" >> .env
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
fi

# Create storage link if it doesn't exist
php artisan storage:link 2>/dev/null || true

# Run migrations and seed (don't abort on failure)
php artisan migrate --force || echo "WARNING: Migration had issues, continuing..."
php artisan db:seed --force || echo "WARNING: Seeder had issues, continuing..."

# Cache config and routes for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the server
php -S 0.0.0.0:8080 -t public_html public_html/router.php
