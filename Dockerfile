FROM php:8.3-fpm
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y \
    libpng-dev \
    && docker-php-ext-install gd

WORKDIR /var/www/html

COPY app /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php-fpm"]