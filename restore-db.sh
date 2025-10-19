#!/bin/bash

# MongoDB Restore Script for TV Watchlist
# Usage: ./restore-db.sh [backup_folder_name]

if [ $# -eq 0 ]; then
    echo "Usage: $0 <backup_folder_name>"
    echo "Available backups:"
    ls -la ./backups/ | grep tvwatchlist_backup
    exit 1
fi

BACKUP_NAME=$1
BACKUP_PATH="./backups/$BACKUP_NAME"

if [ ! -d "$BACKUP_PATH" ]; then
    echo "❌ Backup folder not found: $BACKUP_PATH"
    exit 1
fi

echo "🔄 Restoring MongoDB from backup: $BACKUP_NAME"

# Drop existing database first (be careful!)
echo "⚠️  Dropping existing 'api' database..."
docker exec database mongosh \
  --username api \
  --password password \
  --authenticationDatabase admin \
  --eval "db.getSiblingDB('api').dropDatabase()"

# Restore from backup
docker exec database mongorestore \
  --username api \
  --password password \
  --authenticationDatabase admin \
  --db api \
  "/backups/$BACKUP_NAME/api"

if [ $? -eq 0 ]; then
    echo "✅ Database restored successfully!"
    echo "📊 Collections in database:"
    docker exec database mongosh \
      --username api \
      --password password \
      --authenticationDatabase admin \
      api \
      --eval "db.getCollectionNames()"
else
    echo "❌ Restore failed!"
    exit 1
fi