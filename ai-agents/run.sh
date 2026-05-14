#!/bin/bash

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR" || exit 1

VENV_DIR="$PROJECT_DIR/venv"

if [ ! -d "$VENV_DIR" ]; then
    echo "[INFO] Virtual environment not found. Creating..."
    python3 -m venv "$VENV_DIR"
fi

source "$VENV_DIR/bin/activate"

if [ -f requirements.txt ]; then
    pip install -r requirements.txt -q
fi

echo "[INFO] Starting Celery worker..."
celery -A worker.celery_app worker --loglevel=info --concurrency=4 &
CELERY_PID=$!
echo "[INFO] Celery worker PID: $CELERY_PID"

echo "[INFO] Starting FastAPI server..."
python -m uvicorn main:app --host 0.0.0.0 --port 8001 --reload &
UVICORN_PID=$!
echo "[INFO] FastAPI server PID: $UVICORN_PID"

echo ""
echo "=========================================="
echo "  AI Agent Microservice is running"
echo "  FastAPI : http://localhost:8001"
echo "  Celery  : PID $CELERY_PID"
echo "=========================================="
echo ""

cleanup() {
    echo "[INFO] Shutting down..."
    kill "$CELERY_PID" 2>/dev/null
    kill "$UVICORN_PID" 2>/dev/null
    wait
    echo "[INFO] All services stopped."
}

trap cleanup SIGINT SIGTERM

wait
