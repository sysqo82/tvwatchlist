#!/bin/bash

# MongoDB Backup Script for TV Watchlist (runs inside container)
# This script creates a backup of your MongoDB data

BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="tvwatchlist_backup_$DATE"

echo "Creating MongoDB backup: $BACKUP_NAME"

# Create backup using mongodump
mongodump \
  --username api \
  --password password \
  --authenticationDatabase admin \
  --db api \
  --out "$BACKUP_DIR/$BACKUP_NAME"

if [ $? -eq 0 ]; then
    echo "✅ Backup created successfully in: $BACKUP_DIR/$BACKUP_NAME"
    echo "📁 Backup contains:"
    ls -la "$BACKUP_DIR/$BACKUP_NAME/api/" 2>/dev/null || echo "Backup directory created"
else
    echo "❌ Backup failed!"
    exit 1
fi

# Optional: Clean up old backups (keep last 7 days)
find "$BACKUP_DIR" -name "tvwatchlist_backup_*" -type d -mtime +7 -exec rm -rf {} \; 2>/dev/null

echo "🔄 Old backups cleaned up (keeping last 7 days)"
