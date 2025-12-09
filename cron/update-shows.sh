#!/bin/bash

# Update Shows Script
# This script runs the Symfony command to update all shows from TVDB

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo "=== Starting Show Update at $(date) ==="

# Run the command inside the Docker container
docker exec watchlist-app php /var/www/html/bin/console app:update-shows

echo "=== Update completed at $(date) ==="
