# syntax=docker/dockerfile:1

# --- Stage 1: install PHP dependencies with Composer -------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Los scripts ejecutan artisan, que necesita la app completa: se difieren a
# runtime. Sin dev-deps (Pint, PHPStan, etc.) en la imagen de producción.
RUN composer install \
    --no-dev --no-scripts --no-interaction \
    --prefer-dist --no-progress --optimize-autoloader

# --- Stage 2: runtime --------------------------------------------------------
FROM php:8.4-cli-alpine

# pdo_mysql + mbstring son las extensiones que Laravel/MariaDB necesitan más
# allá de las que trae la imagen; oniguruma es dependencia de compilación de
# mbstring y se elimina tras compilar.
RUN apk add --no-cache oniguruma \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring \
    && apk del .build-deps

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN chmod +x docker/entrypoint.sh \
    && mkdir -p storage/framework/cache storage/framework/sessions \
               storage/framework/views storage/logs bootstrap/cache \
    # Descarta cualquier manifiesto de paquetes/config cacheado del contexto de
    # build: podría listar providers dev-only (p. ej. Pail) que --no-dev no
    # instaló. Laravel lo regenera en el primer arranque desde vendor real.
    && rm -f bootstrap/cache/*.php \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000
ENTRYPOINT ["docker/entrypoint.sh"]
