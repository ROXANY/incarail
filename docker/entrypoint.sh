#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64' .env; then
    php artisan key:generate --force
fi

if [ ! -f storage/oauth-private.key ]; then
    php artisan passport:keys --force
fi

php artisan migrate --seed --force

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
