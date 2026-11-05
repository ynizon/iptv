FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip cron nginx sqlite3 libsqlite3-dev libzip-dev nodejs npm vim \
    libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_sqlite gd opcache

# PHP (OPcache) and PHP-FPM settings, nginx site
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-iptv.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-iptv.conf
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# The application lives in the image: to update, pull the new image and recreate the container
ENV COMPOSER_ALLOW_SUPERUSER=1
# (from /: the base image works in /var/www/html, which this removes)
RUN cd / && rm -rf /var/www && git clone https://github.com/ynizon/iptv.git /var/www

WORKDIR /var/www

# Install dependencies (with a class map: faster autoload)
RUN composer install --no-interaction --optimize-autoloader

# Install JS dependencies and compile the assets (no Vite dev server in the container)
RUN npm install --legacy-peer-deps && npm run build

# Install the cron jobs as crontab of www-data, the user of PHP-FPM, so that both can write the same files
# (not in /etc/cron.d, whose format needs a user column)
COPY crontab /etc/laravel-crontab
RUN crontab -u www-data /etc/laravel-crontab

# Link the application to /data (.env, database, pictures, logs) and migrate, at startup
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
COPY docker/start.sh /usr/local/bin/docker-start.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh /usr/local/bin/docker-start.sh

# The only folder to share: everything that must be kept
VOLUME /data

#Expose the port
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]

# cron, PHP-FPM and nginx
CMD ["docker-start.sh"]
