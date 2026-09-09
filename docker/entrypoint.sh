#!/bin/sh
set -e

if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

php artisan key:generate --ansi --force || true
php artisan storage:link || true

chown -R www-data:www-data storage bootstrap/cache || true

exec "$@"
