# syntax=docker/dockerfile:1
#
# Imagen de producción de Hampi para Render (plan gratuito) u otro hosting
# con Docker. Tres etapas: assets con Node, dependencias con Composer y la
# imagen final con PHP 8.3 + Apache + OPcache.

############################
# 1) Assets (Vite + Tailwind)
############################
FROM node:22-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json vite.config.js ./
RUN npm ci --no-audit --no-fund

# Tailwind escanea vistas y clases usadas en PHP.
COPY resources ./resources
COPY app ./app
COPY public ./public
RUN npm run build

############################
# 2) Dependencias PHP
############################
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-progress \
        --ignore-platform-reqs

############################
# 3) Imagen final
############################
FROM php:8.3-apache

# Extensiones: MySQL y PostgreSQL (cualquiera de las dos bases gratuitas),
# GD con WebP para reducir fotos, intl para fechas en español y OPcache.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev libzip-dev libpq-dev \
        libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
        ca-certificates unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_pgsql intl gd opcache exif zip bcmath \
    && a2enmod rewrite headers expires deflate \
    && rm -rf /var/lib/apt/lists/* \
    && php -m | grep -qi '^gd$' && php -m | grep -qi '^intl$' && php -m | grep -qi '^pdo_mysql$'

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-hampi.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/mpm.conf /etc/apache2/mods-available/mpm_prefork.conf

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan package:discover --ansi \
    && rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public \
    && chown -R www-data:www-data storage bootstrap/cache \
    && sed -i "s/\r$//" docker/entrypoint.sh \
    && chmod +x docker/entrypoint.sh

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PORT=10000

EXPOSE 10000

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["apache2-foreground"]
