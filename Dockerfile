FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    nginx \
    build-base \
    libpq-dev \
    libzip-dev \
    unzip \
    mysql-client \
    git \
    curl \
    bash \
    icu-dev \
    autoconf \
    openssl-dev \
    sqlite-dev

RUN docker-php-ext-install -j$(nproc) pdo_mysql zip intl pdo_sqlite \
    && apk add --no-cache openssl-dev \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

EXPOSE 8000

CMD ["php-fpm"]