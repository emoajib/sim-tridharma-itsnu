import asyncio
import logging
import os
from contextlib import asynccontextmanager

from fastapi import FastAPI, HTTPException, Header
from fastapi.middleware.cors import CORSMiddleware
from typing import Optional

from config import AGENT_API_PORT
from rabbitmq import RabbitMQConsumer

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(name)s] %(levelname)s: %(message)s")
logger = logging.getLogger("main")

consumer: RabbitMQConsumer | None = None

API_KEY = os.getenv("AGENT_API_KEY", "default-secret-key-change-in-production")


@asynccontextmanager
async def lifespan(app: FastAPI):
    global consumer
    logger.info("Starting AI Agent microservice...")
    consumer = RabbitMQConsumer()
    loop = asyncio.get_event_loop()
    await loop.run_in_executor(None, consumer.start_consuming)
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
    allow_origins=["http://localhost:8000", "http://localhost:5173"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


async def verify_api_key(x_api_key: Optional[str] = Header(None)):
    if x_api_key != API_KEY:
        raise HTTPException(status_code=401, detail="Invalid or missing API key")


@app.get("/health")
async def health():
    return {"status": "ok", "agent": "akreditasi-ai"}


@app.post("/api/v1/agents/{agent_name}/run")
async def run_agent(agent_name: str, data: dict, x_api_key: Optional[str] = Header(None)):
    await verify_api_key(x_api_key)
    
    from agents import get_agent
    agent = get_agent(agent_name)
    if agent is None:
        raise HTTPException(status_code=404, detail=f"Agent '{agent_name}' not found")
    result = agent.execute(data)
    return {"agent": agent_name, "result": result}


if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=AGENT_API_PORT, reload=True)
