#!/bin/bash
set -e
ROOT="$(cd "$(dirname "$0")" && pwd)"

echo "▶ Starting dev environment on port 10000..."
cd "$ROOT"
docker compose up -d
echo "✔ Dev is up → http://localhost:10000"
