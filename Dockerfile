FROM php:8.3-fpm

# Install necessary PHP extensions
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y \
    libpng-dev \
    && docker-php-ext-install gd

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY app /var/www/html

# Copy entrypoint script from the docker/ directory
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Set executable permissions for the entrypoint script
RUN chmod +x /usr/local/bin/entrypoint.sh

# Create uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

# Set the entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start PHP-FPM by default
CMD ["php-fpm"]