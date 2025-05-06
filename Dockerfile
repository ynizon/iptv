FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip cron sqlite3 libsqlite3-dev libzip-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Clone Laravel app
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN git clone https://github.com/ynizon/iptv.git /app
RUN mv /app/* /var/www/

# Create app directory
WORKDIR /var/www

# Install dependencies
RUN composer install --no-interaction

# Copy crontab file
COPY crontab /etc/cron.d/laravel-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/laravel-cron

# Apply cron job
RUN crontab /etc/cron.d/laravel-cron

# Create Laravel .env
RUN cp /app/.env.example /var/www/.env && php artisan key:generate

# Ensure SQLite database file exists
RUN touch /var/www/database/database.sqlite

# Set proper permissions
RUN chown -R www-data:www-data /app

#Expose the port
EXPOSE 80 443

# Start cron + php command (use supervisord for production)
CMD ["sh", "-c", "cd /var/www && php artisan key:generate && php artisan migrate && cron && php artisan serve --host=0.0.0.0 --port=443"]

