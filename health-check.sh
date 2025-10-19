#!/bin/bash

# TV Watchlist Health Check Script
echo "TV Watchlist Database Health Check"
echo "=================================="

# Check if containers are running
echo "Container Status:"
docker ps --filter "name=database" --filter "name=watchlist-app" --format "table {{.Names}}\t{{.Status}}"

echo ""
echo "Database Connection Test:"

# Test database connection
DB_STATUS=$(docker exec database mongosh \
  --username api \
  --password password \
  --authenticationDatabase admin \
  --quiet \
  --eval "db.runCommand({ping: 1}).ok" 2>/dev/null)

if [ "$DB_STATUS" = "1" ]; then
    echo "Database connection: OK"
else
    echo "Database connection: FAILED"
    exit 1
fi

echo ""
echo "Data Summary:"

# Count episodes
EPISODE_COUNT=$(docker exec database mongosh \
  --username api \
  --password password \
  --authenticationDatabase admin \
  api \
  --quiet \
  --eval "db.Episode.countDocuments()" 2>/dev/null)

echo "Episodes in database: $EPISODE_COUNT"

# Count archived series
ARCHIVE_COUNT=$(docker exec database mongosh \
  --username api \
  --password password \
  --authenticationDatabase admin \
  api \
  --quiet \
  --eval "db.ArchivedSeries.countDocuments()" 2>/dev/null)

echo "Archived series: $ARCHIVE_COUNT"

# Test API
echo ""
echo "API Test:"
API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:10000/api/nextup" 2>/dev/null)

if [ "$API_STATUS" = "200" ]; then
    echo "API endpoint: OK"
else
    echo "API endpoint: FAILED"
fi

echo ""
echo "Collections in database:"
docker exec database mongosh \
  --username api \
  --password password \
  --authenticationDatabase admin \
  api \
  --quiet \
  --eval "db.getCollectionNames()" 2>/dev/null

echo ""
echo "Health check complete!"