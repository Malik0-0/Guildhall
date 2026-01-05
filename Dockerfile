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
    /tmp \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Clear all bootstrap cache files (removes dev-only packages like Pail)
RUN rm -rf bootstrap/cache/*.php

# Generate optimized autoloader (skip scripts to avoid Laravel initialization during build)
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts

# Expose ports
EXPOSE 80 8080

# Health check (optional - can be removed if not needed)
# HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
#     CMD php -r "file_get_contents('http://localhost/') ? exit(0) : exit(1);" || exit 1

# Create startup script to handle initialization
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh && \
    echo 'set -e' >> /usr/local/bin/start.sh && \
    echo '# Wait for services to be ready' >> /usr/local/bin/start.sh && \
    echo 'sleep 2' >> /usr/local/bin/start.sh && \
    echo '# Clear caches to remove dev dependencies' >> /usr/local/bin/start.sh && \
    echo 'rm -f bootstrap/cache/packages.php bootstrap/cache/services.php || true' >> /usr/local/bin/start.sh && \
    echo '# Start supervisor' >> /usr/local/bin/start.sh && \
    echo 'exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf' >> /usr/local/bin/start.sh && \
    chmod +x /usr/local/bin/start.sh

# Start supervisor (which manages PHP-FPM and Nginx)
CMD ["/usr/local/bin/start.sh"]

