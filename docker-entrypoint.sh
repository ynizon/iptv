#!/bin/sh
# The code comes from the image, everything that must be kept lives in /data, the only volume:
#   /data/.env              settings (APP_KEY, TMDB_KEY, M3U...)
#   /data/database.sqlite   playlists, catalog, users, favorites, history
#   /data/images/           cache of the posters and channel logos
#   /data/storage/          logs, sessions, m3u8 files
# Existing files are never overwritten: they are only created when missing.
set -e

DATA=/data
cd /var/www

mkdir -p "$DATA/images"

# Laravel storage: the folders of the image, the first time
if [ ! -d "$DATA/storage/framework" ]; then
    echo "[entrypoint] creating $DATA/storage"
    mkdir -p "$DATA/storage"
    cp -a storage/. "$DATA/storage/"
fi
mkdir -p "$DATA/storage/app/public" "$DATA/storage/framework/cache/data" "$DATA/storage/framework/sessions" \
    "$DATA/storage/framework/views" "$DATA/storage/logs"

# The application reads and writes its data through these links
rm -rf storage public/images/playlist
ln -s "$DATA/storage" storage
ln -s "$DATA/images" public/images/playlist
ln -sf "$DATA/.env" .env

if [ ! -f "$DATA/.env" ]; then
    echo "[entrypoint] creating $DATA/.env"
    cp .env.example "$DATA/.env"
    php artisan key:generate --force
fi
# In .env and not as a container variable: the cron jobs do not get those
if ! grep -q '^DB_DATABASE=' "$DATA/.env"; then
    printf '\nDB_DATABASE=%s/database.sqlite\n' "$DATA" >> "$DATA/.env"
fi

new_db=0
if [ ! -f "$DATA/database.sqlite" ]; then
    echo "[entrypoint] creating $DATA/database.sqlite"
    touch "$DATA/database.sqlite"
    new_db=1
fi

php artisan migrate --force
if [ "$new_db" = 1 ]; then
    php artisan db:seed --force
fi

exec "$@"
