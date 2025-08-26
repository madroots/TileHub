FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    libpng-dev \
    && docker-php-ext-install pdo pdo_mysql gd

WORKDIR /var/www/html

# Copy application files
COPY app/ /var/www/html/

# Set proper permissions for uploads directory
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

# Use the default entrypoint and command