FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql gd zip

WORKDIR /var/www/html

# Copy application files
COPY app/ /var/www/html/

# Copy entrypoint script
COPY app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set proper permissions for uploads directory to allow both containers to access
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 777 /var/www/html/uploads

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]