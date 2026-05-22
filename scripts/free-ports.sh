#!/usr/bin/env bash
# ============================================================
# scripts/free-ports.sh
# Cari port yang tidak digunakan untuk setiap service di aplikasi ini.
# Jalankan sekali jika port default bentrok.
# ============================================================

find_free_port() {
  local start=$1
  local p=$start
  while lsof -i :"$p" -sTCP:LISTEN -n -P >/dev/null 2>&1; do
    p=$((p + 1))
  done
  echo "$p"
}

echo "🔍 Mencari port yang tersedia..."
echo ""

LARAVEL_PORT=$(find_free_port 8000)
VITE_PORT=$(find_free_port 5173)
AGENTS_PORT=$(find_free_port 8001)
RAG_PORT=$(find_free_port 5001)

echo "┌──────────────────────────────────────────────────────┐"
echo "│  Service       │ Port Tersedia                       │"
echo "├────────────────┼──────────────────────────────────────┤"
printf "│  %-13s │ %-36s │\n" "Laravel"    "http://localhost:$LARAVEL_PORT"
printf "│  %-13s │ %-36s │\n" "Vite"       "http://localhost:$VITE_PORT"
printf "│  %-13s │ %-36s │\n" "AI Agents"  "http://localhost:$AGENTS_PORT"
printf "│  %-13s │ %-36s │\n" "AI RAG"     "http://localhost:$RAG_PORT"
echo "└──────────────────────────────────────────────────────┘"
echo ""
echo "Gunakan opsi --port dengan artisan atau environment variable:"
echo "  php artisan serve --port=$LARAVEL_PORT"
echo "  AGENT_API_PORT=$AGENTS_PORT  (di ai-agents/.env)"
echo "  AI_SERVICE_PORT=$RAG_PORT    (di environment)"
echo ""
echo "Atau jalankan dengan composer dev (sudah menggunakan port default):"
echo "  composer dev"
echo ""
