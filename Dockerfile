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

# Create uploads directory with proper permissions
# Using consistent UID/GID (80:80) for both containers
# Note: In development, this directory will be mounted over, but the entrypoint script
# will ensure correct permissions are set at container startup
RUN mkdir -p /var/www/html/uploads && \
    chown -R 80:80 /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]