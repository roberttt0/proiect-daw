FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    bash

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

WORKDIR /var/www

COPY . /var/www

RUN chown -R www-data:www-data /var/www || true

EXPOSE 9000
CMD ["php-fpm"]