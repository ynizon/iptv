#!/bin/sh
# Prepare /var/www (usually a volume) before starting the app.
# Existing files are never overwritten: code, .env and database are only created when missing.
set -e

cd /var/www

# Empty volume: copy the app shipped in the image (with its .git, so git pull/push work)
if [ ! -f artisan ]; then
    echo "[entrypoint] /var/www is empty, copying the app from the image"
    tar -C /opt/iptv -cf - . | tar -C /var/www -xf - --skip-old-files
fi

git config --global --add safe.directory /var/www

if [ ! -f .env ]; then
    echo "[entrypoint] creating .env"
    cp .env.example .env
    php artisan key:generate --force
fi

[ -d vendor ] || composer install --no-interaction
[ -d node_modules ] || npm install --legacy-peer-deps

new_db=0
if [ ! -f database/database.sqlite ]; then
    echo "[entrypoint] creating database/database.sqlite"
    touch database/database.sqlite
    new_db=1
fi

php artisan migrate --force
if [ "$new_db" = 1 ]; then
    php artisan db:seed --force
fi

exec "$@"
