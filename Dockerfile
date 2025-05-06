FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip cron sqlite3 libsqlite3-dev libzip-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create app directory
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www

# Clone Laravel app
COPY . .
#RUN git clone https://github.com/ynizon/iptv.git .

# Copy crontab file
COPY crontab /etc/cron.d/laravel-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/laravel-cron

# Apply cron job
RUN crontab /etc/cron.d/laravel-cron

# Set proper permissions
RUN chown -R www-data:www-data /var/www

# Expose le port 9090
EXPOSE 9090

# Start cron + php command (use supervisord for production)
CMD ["sh", "-c", "cron && php-fpm"]

