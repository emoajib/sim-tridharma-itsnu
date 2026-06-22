#!/usr/bin/env bash
# ============================================================
# scripts/watcher.sh
# File watcher — runs as a concurrently-managed sidecar
#
# Uses npx onchange (cross-platform: macOS + Linux + WSL):
#   1. database/migrations/*.php → auto-run php artisan migrate
#   2. app/ config/ routes/ → restart queue + reverb
#
# onchange uses chokidar under the hood — native fs events with
# polling fallback. No native dependencies needed.
# ============================================================
set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_DIR"

# Track child PIDs for clean shutdown
PID_MIGRATIONS=""
PID_PHP=""

cleanup() {
  echo ""
  echo "🛑 Stopping file watchers..."
  kill "$PID_MIGRATIONS" 2>/dev/null || true
  kill "$PID_PHP" 2>/dev/null || true
  wait 2>/dev/null || true
  exit 0
}
trap cleanup SIGINT SIGTERM

echo "🔍 File watcher started — monitoring changes..."

# ──────────────────────────────────────────────────
# 1. Database migrations → auto-run migrate
#    --delay 500: debounce rapid changes (e.g. git pull)
#    -v: verbose — print which file triggered
#    --no-exit: keep watching after each command
# ──────────────────────────────────────────────────
npx --yes onchange \
  --delay 500 \
  -v \
  'database/migrations/*.php' \
  -- \
  bash -c 'php artisan migrate --force 2>&1' &
PID_MIGRATIONS=$!

# ──────────────────────────────────────────────────
# 2. PHP code changes → restart queue + reverb
#    queue:restart gracefully stops workers (awaits current job)
#    pkill reverb:start → wrapper auto-restarts it in 1s
# ──────────────────────────────────────────────────
npx --yes onchange \
  --delay 1000 \
  -v \
  'app/**/*.php' \
  'config/**/*.php' \
  'routes/**/*.php' \
  -- \
  bash -c '
    # Ignore our own pkill signal (only reverb should be killed)
    trap "" SIGTERM SIGINT
    echo "🔄 PHP files changed — reloading services..."
    php artisan queue:restart 2>/dev/null
    pkill -f "artisan reverb:start" 2>/dev/null || true
    echo "✅ Queue + Reverb reload triggered"
  ' &
PID_PHP=$!

# Wait forever (both processes run until killed)
wait
