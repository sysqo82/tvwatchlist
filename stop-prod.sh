#!/bin/bash
ROOT="$(cd "$(dirname "$0")" && pwd)"

echo "■ Stopping prod environment..."
cd "$ROOT"
docker compose -f docker-compose.prod.yaml down
echo "✔ Prod is down."
