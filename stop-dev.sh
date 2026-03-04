#!/bin/bash
ROOT="$(cd "$(dirname "$0")" && pwd)"

echo "■ Stopping dev environment..."
cd "$ROOT"
docker compose down
echo "✔ Dev is down."
