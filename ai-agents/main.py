import asyncio
import logging
import os
import threading
from contextlib import asynccontextmanager

from fastapi import FastAPI, HTTPException, Header, Depends
from fastapi.middleware.cors import CORSMiddleware
from typing import Optional

from config import AGENT_API_PORT, require_env
from rabbitmq import RabbitMQConsumer
from agents_mcp.tools import mcp

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(name)s] %(levelname)s: %(message)s")
logger = logging.getLogger("main")

consumer: RabbitMQConsumer | None = None

API_KEY = require_env("AGENT_API_KEY")
AGENT_CORS_ORIGINS = os.getenv("AGENT_CORS_ORIGINS", "http://localhost:8000").split(",")


async def verify_api_key(x_api_key: Optional[str] = Header(None)):
    if not x_api_key or x_api_key != API_KEY:
        logger.warning(f"Unauthorized access attempt with key: {x_api_key}")
        raise HTTPException(status_code=401, detail="Invalid or missing API key")
    return x_api_key


@asynccontextmanager
async def lifespan(app: FastAPI):
    global consumer
    logger.info("Starting AI Agent microservice...")
    consumer = RabbitMQConsumer()
    
    # Run RabbitMQ consumer in a separate thread
    thread = threading.Thread(target=consumer.start_consuming, daemon=True)
    thread.start()
    
    yield
    logger.info("Shutting down AI Agent microservice...")
    if consumer:
        consumer.stop()


app = FastAPI(
    title="AI Agent Microservice - Akreditasi",
    version="1.0.0",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=AGENT_CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Mount MCP server into FastAPI
mcp_app = mcp.streamable_http_app()
app.mount("/mcp", mcp_app)
logger.info("MCP server mounted at /mcp")


@app.get("/health")
async def health():
    return {"status": "ok", "agent": "akreditasi-ai", "mcp": "available"}


@app.get("/api/mcp/tools")
async def list_mcp_tools():
    """List all available MCP tools (for debugging)"""
    tools = await mcp.list_tools()
    return {
        "tools": [
            {
                "name": tool.name,
                "description": tool.description,
                "input_schema": tool.inputSchema,
            }
            for tool in tools
        ]
    }


@app.post("/api/v1/agents/{agent_name}/run", dependencies=[Depends(verify_api_key)])
async def run_agent(agent_name: str, data: dict):
    import asyncio
    from agents import get_agent
    loop = asyncio.get_running_loop()
    agent = get_agent(agent_name)
    if agent is None:
        raise HTTPException(status_code=404, detail=f"Agent '{agent_name}' not found")
    
    result = await loop.run_in_executor(None, agent.execute, data)
    return {"agent": agent_name, "result": result}


if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=AGENT_API_PORT, reload=True)
