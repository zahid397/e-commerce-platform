FROM node:24-bookworm-slim AS assets

WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci --include=dev --no-audit --no-fund
COPY vite.config.js ./
COPY resources/ ./resources/
RUN npm run build

FROM php:8.3-apache-bookworm

WORKDIR /var/www/html
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libjpeg62-turbo-dev libpng-dev libwebp-dev unzip util-linux \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev --no-scripts --prefer-dist --no-interaction --no-progress

COPY . .
COPY --from=assets /build/public/build ./public/build
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php-uploads.ini /usr/local/etc/php/conf.d/99-ecommerce.ini
COPY docker/entrypoint.sh /usr/local/bin/ecommerce-entrypoint
RUN mkdir -p bootstrap/cache storage/app/private storage/app/public \
        storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs \
    && COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --no-dev --optimize --no-interaction \
    && chown -R www-data:www-data bootstrap/cache storage \
    && ln -s ../storage/app/public public/storage \
    && chmod 0755 /usr/local/bin/ecommerce-entrypoint

EXPOSE 80
ENTRYPOINT ["ecommerce-entrypoint"]
CMD ["apache2-foreground"]
