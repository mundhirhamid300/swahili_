#!/bin/sh
set -eu

: "${PORT:=10000}"

sed -ri "s/^Listen [0-9]+$/Listen ${PORT}/; s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" \
    /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

php artisan migrate --force --no-interaction

exec apache2-foreground
