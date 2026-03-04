#!/bin/bash
set -e
ROOT="$(cd "$(dirname "$0")" && pwd)"

echo "▶ Starting prod environment on port 10001..."
cd "$ROOT"
docker compose -f docker-compose.prod.yaml up -d
echo "✔ Prod is up → http://localhost:10001"
