#!/bin/sh
set -eu

if [ "$#" -eq 0 ] || [ "$1" = apache2-foreground ]; then
    : "${APP_KEY:?Set APP_KEY using the Docker setup in README.md}"
    runuser -u www-data -- php artisan migrate --force --no-interaction
fi

exec docker-php-entrypoint "$@"
