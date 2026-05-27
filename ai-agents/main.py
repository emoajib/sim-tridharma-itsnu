import logging
import os
from contextlib import asynccontextmanager

from fastapi import FastAPI, HTTPException, Header, Depends
from fastapi.middleware.cors import CORSMiddleware
from typing import Optional

from config import AGENT_API_PORT, require_env
from agents_mcp.tools import mcp

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(name)s] %(levelname)s: %(message)s")
logger = logging.getLogger("main")

API_KEY = require_env("AGENT_API_KEY")
AGENT_CORS_ORIGINS = os.getenv("AGENT_CORS_ORIGINS", "http://localhost:8000").split(",")


async def verify_api_key(x_api_key: Optional[str] = Header(None)):
    if not x_api_key or x_api_key != API_KEY:
        logger.warning(f"Unauthorized access attempt with key: {x_api_key}")
        raise HTTPException(status_code=401, detail="Invalid or missing API key")
    return x_api_key


@asynccontextmanager
async def lifespan(app: FastAPI):
    logger.info("Starting AI Agent microservice (MCP mode)...")
    yield
    logger.info("Shutting down AI Agent microservice...")


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


@app.get("/api/mcp/tools", dependencies=[Depends(verify_api_key)])
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


@app.post("/api/mcp/tools/call", dependencies=[Depends(verify_api_key)])
async def call_mcp_tool(data: dict):
    """Call an MCP tool via REST (proxy for PHP)"""
    tool_name = data.get("name")
    arguments = data.get("arguments", {})

    if not tool_name:
        raise HTTPException(status_code=400, detail="Missing 'name' in request body")

    try:
        result = await mcp.call_tool(tool_name, arguments)
        return {"result": result}
    except Exception as e:
        logger.error(f"MCP tool call failed: {tool_name}", exc_info=True)
        raise HTTPException(status_code=500, detail=str(e))


if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=AGENT_API_PORT, reload=True)
