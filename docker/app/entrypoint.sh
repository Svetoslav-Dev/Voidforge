#!/usr/bin/env sh
set -eu

if [ ! -f artisan ]; then
    echo "Laravel app not found in /var/www/html."
    echo "Run the bootstrap command to create it:"
    echo "docker compose run --rm app composer create-project laravel/laravel ."
    exit 0
fi

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

mkdir -p \
    storage/app/public \
    storage/certs \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    storage/tmp \
    bootstrap/cache

if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist
fi

if [ ! -L public/storage ] && [ -d storage/app/public ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

exec "$@"
