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
else
    echo "Creating ai-agents virtual environment (numpy>=2.1.3)..."
    python3 -m venv venv
    source venv/bin/activate
    pip install --upgrade pip
    pip install -r requirements.txt
    echo "✓ ai-agents venv created"
fi

# Start MCP Agent Server (port 8001)
echo "Starting MCP Agent Server on port 8001..."
python -m uvicorn main:app --host 0.0.0.0 --port 8001 --reload &
AGENT_PID=$!

# Activate ai-service virtual environment (numpy<2 for sentence-transformers compatibility)
if [ -d "../ai-service/venv" ]; then
    source ../ai-service/venv/bin/activate
    echo "✓ ai-service virtual environment activated"
else
    echo "Creating ai-service virtual environment (numpy<2)..."
    cd ..
    python3 -m venv ai-service/venv
    source ai-service/venv/bin/activate
    cd ai-agents
    pip install --upgrade pip
    pip install -r ../ai-service/requirements.txt
    echo "✓ ai-service venv created"
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
