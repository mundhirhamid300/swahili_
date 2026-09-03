FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends git libfreetype6-dev libjpeg62-turbo-dev libonig-dev libpng-dev libpq-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd mbstring pdo_pgsql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

COPY . .

RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && sed -ri "s!/var/www/html!/var/www/html/public!g" /etc/apache2/sites-available/000-default.conf

COPY docker/start-apache.sh /usr/local/bin/start-apache
COPY docker/apache-laravel.conf /etc/apache2/conf-available/laravel.conf
RUN chmod +x /usr/local/bin/start-apache
RUN a2enconf laravel

CMD ["/usr/local/bin/start-apache"]
