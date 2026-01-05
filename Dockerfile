# Multi-stage Dockerfile for Laravel 12.0 Application
# Stage 1: Build stage - Install dependencies and build assets
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install Node dependencies
RUN npm ci

# Copy source files needed for build
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Build assets
RUN npm run build

# Stage 2: PHP dependencies stage
FROM composer:2.7 AS composer-stage

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev dependencies for production)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Stage 3: Production stage
FROM php:8.2-fpm-alpine AS production

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    postgresql-dev \
    mysql-client \
    nginx \
    supervisor \
    && docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    zip \
    mbstring \
    exif \
    pcntl \
    opcache \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Install Redis extension
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev $PHPIZE_DEPS

# Copy PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy Nginx configuration
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy Supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy composer dependencies first
COPY --from=composer-stage --chown=www-data:www-data /app/vendor ./vendor

# Copy application files (excluding vendor and node_modules which are in .dockerignore)
COPY --chown=www-data:www-data . .

# Copy built assets from node-builder
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

# Create necessary directories and set permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    database \
    /tmp \
    /var/lib/nginx/tmp \
    /var/log/nginx \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database \
    && chown -R www-data:www-data /var/lib/nginx /var/log/nginx \
    && chmod -R 755 /var/lib/nginx /var/log/nginx

# Clear all bootstrap cache files (removes dev-only packages like Pail)
RUN rm -rf bootstrap/cache/*.php

# Generate optimized autoloader (skip scripts to avoid Laravel initialization during build)
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts

# Expose ports (Railway will route to port 80)
# Note: Railway auto-detects EXPOSE, but you may need to set PORT=80 in Railway settings
EXPOSE 80

# Health check (optional - can be removed if not needed)
# HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
#     CMD php -r "file_get_contents('http://localhost/') ? exit(0) : exit(1);" || exit 1

# Create startup script to handle initialization  
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh && \
    echo '# Clear caches to remove dev dependencies (before Laravel loads)' >> /usr/local/bin/start.sh && \
    echo 'rm -rf bootstrap/cache/*.php 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo '# Generate APP_KEY if not set (required for Laravel)' >> /usr/local/bin/start.sh && \
    echo 'cd /var/www/html' >> /usr/local/bin/start.sh && \
    echo 'if [ -z "$APP_KEY" ] || ! grep -q "^APP_KEY=" .env 2>/dev/null; then' >> /usr/local/bin/start.sh && \
    echo '  if [ ! -f .env ]; then' >> /usr/local/bin/start.sh && \
    echo '    touch .env' >> /usr/local/bin/start.sh && \
    echo '  fi' >> /usr/local/bin/start.sh && \
    echo '  if ! grep -q "^APP_KEY=" .env 2>/dev/null; then' >> /usr/local/bin/start.sh && \
    echo '    echo "Warning: APP_KEY not set, generating a new one..."' >> /usr/local/bin/start.sh && \
    echo '    php artisan key:generate --force 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo '    echo "Note: Set APP_KEY as environment variable in Railway for production!"' >> /usr/local/bin/start.sh && \
    echo '  fi' >> /usr/local/bin/start.sh && \
    echo 'fi' >> /usr/local/bin/start.sh && \
    echo '# Ensure database directory exists and has proper permissions' >> /usr/local/bin/start.sh && \
    echo 'mkdir -p /var/www/html/database' >> /usr/local/bin/start.sh && \
    echo 'chown -R www-data:www-data /var/www/html/database 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo 'chmod -R 775 /var/www/html/database 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo '# Create SQLite database file if it does not exist and set permissions' >> /usr/local/bin/start.sh && \
    echo 'if [ ! -f /var/www/html/database/database.sqlite ]; then' >> /usr/local/bin/start.sh && \
    echo '  touch /var/www/html/database/database.sqlite 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo '  chown www-data:www-data /var/www/html/database/database.sqlite 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo '  chmod 666 /var/www/html/database/database.sqlite 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo 'fi' >> /usr/local/bin/start.sh && \
    echo '# Always fix permissions on existing database file (in case it was copied with wrong perms)' >> /usr/local/bin/start.sh && \
    echo 'if [ -f /var/www/html/database/database.sqlite ]; then' >> /usr/local/bin/start.sh && \
    echo '  chown www-data:www-data /var/www/html/database/database.sqlite 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo '  chmod 666 /var/www/html/database/database.sqlite 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo 'fi' >> /usr/local/bin/start.sh && \
    echo '# Run database migrations (only if using PostgreSQL/MySQL, skip for SQLite)' >> /usr/local/bin/start.sh && \
    echo 'if [ "$DB_CONNECTION" != "sqlite" ]; then' >> /usr/local/bin/start.sh && \
    echo '  echo "Running database migrations..."' >> /usr/local/bin/start.sh && \
    echo '  php artisan migrate --force --no-interaction 2>/dev/null || echo "Migration failed or already up to date"' >> /usr/local/bin/start.sh && \
    echo 'fi' >> /usr/local/bin/start.sh && \
    echo '# Create storage link if it does not exist' >> /usr/local/bin/start.sh && \
    echo 'if [ ! -L /var/www/html/public/storage ]; then' >> /usr/local/bin/start.sh && \
    echo '  php artisan storage:link 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo 'fi' >> /usr/local/bin/start.sh && \
    echo '# Clear and cache config for production' >> /usr/local/bin/start.sh && \
    echo 'php artisan config:clear 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo 'php artisan cache:clear 2>/dev/null || true' >> /usr/local/bin/start.sh && \
    echo '# Start supervisor (PHP-FPM and Nginx will start first)' >> /usr/local/bin/start.sh && \
    echo 'exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf' >> /usr/local/bin/start.sh && \
    chmod +x /usr/local/bin/start.sh

# Start supervisor (which manages PHP-FPM and Nginx)
CMD ["/usr/local/bin/start.sh"]

