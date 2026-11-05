#!/bin/sh
# cron (the refreshes), PHP-FPM (the application), then nginx (the web server) in the foreground
set -e
cron
php-fpm -D
exec nginx -g 'daemon off;'
