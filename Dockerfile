FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip cron sqlite3 libsqlite3-dev libzip-dev nodejs npm vim \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# The application lives in the image: to update, pull the new image and recreate the container
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN rm -rf /var/www && git clone https://github.com/ynizon/iptv.git /var/www

WORKDIR /var/www

# Install dependencies
RUN composer install --no-interaction

# Install JS dependencies and compile the assets (no Vite dev server in the container)
RUN npm install --legacy-peer-deps && npm run build

# Install the cron jobs as root crontab (not in /etc/cron.d, whose format needs a user column)
COPY crontab /etc/laravel-crontab
RUN crontab /etc/laravel-crontab

# Link the application to /data (.env, database, pictures, logs) and migrate, at startup
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# The only folder to share: everything that must be kept
VOLUME /data

#Expose the port
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]

# Start cron + php command (use supervisord for production)
CMD ["sh", "-c", "cron && exec php artisan serve --host=0.0.0.0 --port=80"]
