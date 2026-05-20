import os
from dotenv import load_dotenv

load_dotenv()


DATABASE_URL: str = os.getenv("DATABASE_URL", "postgresql://salsabil@localhost:5433/sim_tridharma_itsnu")
REDIS_URL: str = os.getenv("REDIS_URL", "redis://localhost:6379/0")
RABBITMQ_HOST: str = os.getenv("RABBITMQ_HOST", "localhost")
RABBITMQ_PORT: int = int(os.getenv("RABBITMQ_PORT", "5672"))
RABBITMQ_USER: str = os.getenv("RABBITMQ_USER", "guest")
RABBITMQ_PASSWORD: str = os.getenv("RABBITMQ_PASSWORD", "guest")
RABBITMQ_EXCHANGE: str = os.getenv("RABBITMQ_EXCHANGE", "akreditasi")
RABBITMQ_QUEUE: str = os.getenv("RABBITMQ_QUEUE", "agent_tasks")
AGENT_API_PORT: int = int(os.getenv("AGENT_API_PORT", "8001"))

LARAVEL_URL: str = os.getenv("LARAVEL_URL", "http://localhost:8000")
INTERNAL_KEY: str = os.getenv("AI_INTERNAL_KEY", "default-internal-key")
