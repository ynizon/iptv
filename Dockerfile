FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip cron sqlite3 libsqlite3-dev libzip-dev nodejs npm vim \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Clone Laravel app (kept with its .git, copied to /var/www at startup if the volume is empty)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN git clone https://github.com/ynizon/iptv.git /opt/iptv

WORKDIR /opt/iptv

# Install dependencies
RUN composer install --no-interaction

# Install SSL
RUN npm install @vitejs/plugin-basic-ssl --legacy-peer-deps

# Install the cron jobs as root crontab (not in /etc/cron.d, whose format needs a user column)
COPY crontab /etc/laravel-crontab
RUN crontab /etc/laravel-crontab

# Prepare /var/www (code, .env, database, migrations) at startup
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www

#Expose the port
EXPOSE 80 443 5173 8000

ENTRYPOINT ["docker-entrypoint.sh"]

# Start cron + php command (use supervisord for production)
CMD ["sh", "-c", "cd /var/www && cron && php artisan serve --host=0.0.0.0 --port=80 & npm run dev & wait"]
