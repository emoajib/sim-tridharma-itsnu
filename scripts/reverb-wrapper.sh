#!/usr/bin/env bash
# ============================================================
# scripts/reverb-wrapper.sh
# Auto-restart Reverb WebSocket server when killed
# (triggered by file-change watcher for hot reload)
# ============================================================
set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

cd "$PROJECT_DIR"

echo "🔁 Reverb wrapper started — will auto-restart on exit"

while true; do
  php artisan reverb:start
  EXIT_CODE=$?
  echo "⚠️  Reverb exited (code: $EXIT_CODE) — restarting in 1s..."
  sleep 1
done
