# syntax=docker/dockerfile:1

FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json vite.config.js ./
RUN npm ci

COPY resources ./resources
COPY public ./public
RUN npm run build


FROM php:8.4-fpm-alpine AS app

RUN apk add --no-cache \
        git \
        unzip \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        libwebp \
        icu-libs \
        curl

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
        icu-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        zip \
        bcmath \
        intl \
        exif \
        gd \
        opcache \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --optimize-autoloader

COPY --chown=www-data:www-data . .
COPY --from=frontend --chown=www-data:www-data /app/public/ ./public/

RUN php artisan package:discover --ansi \
    && mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD php -r 'exit(0);'

CMD ["php-fpm"]
