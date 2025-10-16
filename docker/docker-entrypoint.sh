#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
  set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    composer install --prefer-dist --no-progress --no-suggest -o --no-interaction --ignore-platform-reqs
    chmod -R 777 var
    
    # Nuclear option: Clear all cache and fix permissions every startup
    echo "Clearing cache and fixing MongoDB hydrator permissions..."
    rm -rf /var/www/html/var/cache/* 2>/dev/null || true
    mkdir -p /var/www/html/var/cache/dev /var/www/html/var/cache/prod
    chown -R www-data:www-data /var/www/html/var/cache
    chmod -R 755 /var/www/html/var/cache
    
    ./bin/console assets:install
    ./bin/console doctrine:mongodb:schema:update || echo "Warning: failed to update DB indexes"
fi

# Add hydrator permission fix to cron (every minute)
echo "* * * * * www-data /bin/bash -c 'chown -R www-data:www-data /var/www/html/var/cache 2>/dev/null; chmod -R 755 /var/www/html/var/cache 2>/dev/null' >/dev/null 2>&1" >> /etc/crontab

service nginx start
service cron start

exec docker-php-entrypoint "$@"
