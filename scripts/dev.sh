#!/usr/bin/env bash
# ============================================================
# scripts/dev.sh
# Satu baris perintah — seluruh stack dengan hot-reload otomatis
# Laravel + Reverb + Queue + Pail + Vite + AI Agents + AI RAG + Celery
# ============================================================
set -uo pipefail

# --- Lokasi project ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

AGENTS_DIR="$PROJECT_DIR/ai-agents"
RAG_DIR="$PROJECT_DIR/ai-service"

AGENTS_VENV_PYTHON="$AGENTS_DIR/venv/bin/python"
RAG_VENV_PYTHON="$RAG_DIR/venv/bin/python"

# --- Validasi venv ada ---
for label in "AI Agents" "AI RAG"; do
  case $label in
    "AI Agents") venv_py="$AGENTS_VENV_PYTHON" ;;
    "AI RAG")    venv_py="$RAG_VENV_PYTHON"   ;;
  esac
  if [ ! -x "$venv_py" ]; then
    echo "❌ $label: venv Python tidak ditemukan: $venv_py"
    echo "   Buat dengan: cd $(dirname "$venv_py")/.. && python3 -m venv venv && ./venv/bin/pip install -r requirements.txt"
    exit 1
  fi
  # Cek fastapi ada di venv
  if ! "$venv_py" -c "import fastapi" 2>/dev/null; then
    echo "❌ $label: modul 'fastapi' tidak ada di venv. Install dengan:"
    echo "   $venv_py -m pip install -r $(dirname "$venv_py")/../requirements.txt"
    exit 1
  fi
done

cd "$PROJECT_DIR"

echo "=========================================="
echo "🚀 Menjalankan SEMUA layanan dalam satu terminal"
echo "=========================================="
echo ""

# --- Bangun array perintah secara dinamis ---
AGENTS_CMD="cd \"$AGENTS_DIR\" && \"$AGENTS_VENV_PYTHON\" -m uvicorn main:app --host 0.0.0.0 --port 8001 --reload"
RAG_CMD="cd \"$RAG_DIR\" && \"$RAG_VENV_PYTHON\" -m uvicorn main:app --host 0.0.0.0 --port 5001 --reload"
CELERY_CMD="cd \"$AGENTS_DIR\" && \"$AGENTS_VENV_PYTHON\" -m celery -A worker.celery_app worker --loglevel=info --concurrency=2"

npx concurrently \
  -c "#93c5fd,#c4b5fd,#fb7185,#fdba74,#f472b6,#a78bfa,#34d399,#fbbf24" \
  --names="laravel,reverb,queue,pail,vite,agents,rag,celery" \
  --kill-others \
  "php artisan serve" \
  "php artisan reverb:start" \
  "php artisan queue:listen --tries=1 --timeout=0" \
  "php artisan pail --timeout=0" \
  "npm run dev" \
  "$AGENTS_CMD" \
  "$RAG_CMD" \
  "$CELERY_CMD"
