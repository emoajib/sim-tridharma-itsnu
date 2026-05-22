#!/bin/bash

# MCP Server Launcher for AI Agents
# Uses separate virtual environments for each service

set -e

echo "=========================================="
echo "  MCP AI Agents Microservice Launcher"
echo "=========================================="

# Activate ai-agents virtual environment
if [ -d "venv" ]; then
    source venv/bin/activate
    echo "✓ ai-agents virtual environment activated"
fi

# Install dependencies if needed
if [ "$1" = "--install" ]; then
    echo "Installing ai-agents dependencies..."
    pip install -r requirements.txt
    echo "✓ ai-agents dependencies installed"
fi

# Start MCP Agent Server (port 8001)
echo "Starting MCP Agent Server on port 8001..."
python -m uvicorn main:app --host 0.0.0.0 --port 8001 --reload &
AGENT_PID=$!

# Activate ai-service virtual environment
if [ -d "../ai-service/venv" ]; then
    source ../ai-service/venv/bin/activate
    echo "✓ ai-service virtual environment activated"
fi

# Install dependencies if needed
if [ "$1" = "--install" ]; then
    echo "Installing ai-service dependencies..."
    pip install -r ../ai-service/requirements.txt
    echo "✓ ai-service dependencies installed"
fi

# Start MCP RAG Server (port 5001)
echo "Starting MCP RAG Server on port 5001..."
cd ../ai-service
python -m uvicorn main:app --host 0.0.0.0 --port 5001 --reload &
RAG_PID=$!

echo "=========================================="
echo "  MCP Servers Started"
echo "=========================================="
echo "  Agent Server: http://localhost:8001"
echo "  RAG Server:   http://localhost:5001"
echo "  Agent MCP:    http://localhost:8001/mcp"
echo "  RAG MCP:      http://localhost:5001/mcp"
echo "  Health Check: http://localhost:8001/health"
echo "  Health Check: http://localhost:5001/health"
echo "=========================================="
echo "  Press Ctrl+C to stop all servers"
echo "=========================================="

# Wait for processes
wait $AGENT_PID $RAG_PID
