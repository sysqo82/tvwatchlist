#!/bin/sh

# Start cron daemon immediately in the background
service cron start 2>/dev/null || true

echo "Starting PHP-FPM..."
exec docker-php-entrypoint "$@"
