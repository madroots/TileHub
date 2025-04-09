FROM php:8.3-fpm

RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y \
    libpng-dev \
    && docker-php-ext-install gd

WORKDIR /var/www/html
