#!/bin/bash
# ai-agents/run.sh
# NOTE: Celery/RabbitMQ deprecated — replaced by MCP direct HTTP calls.
# This script now only starts the FastAPI server (MCP tools endpoint).

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

echo "[INFO] Starting FastAPI server (MCP tools)..."
python -m uvicorn main:app --host 0.0.0.0 --port 8001 --reload &
UVICORN_PID=$!
echo "[INFO] FastAPI server PID: $UVICORN_PID"

echo ""
echo "=========================================="
echo "  AI Agent Microservice is running"
echo "  FastAPI : http://localhost:8001"
echo "  (Celery deprecated — use MCP /api/agents/{agent}/run)"
echo "=========================================="
echo ""

cleanup() {
    echo "[INFO] Shutting down..."
    kill "$UVICORN_PID" 2>/dev/null
    wait
    echo "[INFO] All services stopped."
}

trap cleanup SIGINT SIGTERM

wait
