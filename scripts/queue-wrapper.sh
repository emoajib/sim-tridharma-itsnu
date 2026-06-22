#!/usr/bin/env bash
# ============================================================
# scripts/queue-wrapper.sh
# Auto-restart Queue Listener when stopped by queue:restart
# (triggered by file-change watcher for hot reload)
#
# Laravel's queue:restart gracefully stops workers after their
# current job completes — this wrapper brings them back.
# ============================================================
set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

cd "$PROJECT_DIR"

echo "🔁 Queue wrapper started — will auto-restart on queue:restart signal"

while true; do
  php artisan queue:listen --tries=1 --timeout=0
  EXIT_CODE=$?
  echo "⚠️  Queue worker exited (code: $EXIT_CODE) — restarting in 1s..."
  sleep 1
done
