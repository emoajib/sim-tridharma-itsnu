"""
MCP Configuration for AI Agents Microservice
"""
import os
from dotenv import load_dotenv

load_dotenv()

# MCP Server Configuration
MCP_SERVER_NAME = os.getenv("MCP_SERVER_NAME", "akreditasi-agents")
MCP_SERVER_VERSION = os.getenv("MCP_SERVER_VERSION", "1.0.0")
MCP_TRANSPORT = os.getenv("MCP_TRANSPORT", "streamable-http")
MCP_STATELESS = os.getenv("MCP_STATELESS", "true").lower() == "true"

# OAuth Configuration
OAUTH_ENABLED = os.getenv("OAUTH_ENABLED", "false").lower() == "true"
OAUTH_JWT_ISSUER = os.getenv("OAUTH_JWT_ISSUER", "")
OAUTH_JWT_AUDIENCE = os.getenv("OAUTH_JWT_AUDIENCE", "akreditasi-mcp")
OAUTH_REQUIRED_SCOPES = os.getenv("OAUTH_REQUIRED_SCOPES", "read,write").split(",")

# Database Configuration (for PostgreSQL MCP tool)
DB_HOST = os.getenv("DB_HOST", "localhost")
DB_PORT = os.getenv("DB_PORT", "5432")
DB_NAME = os.getenv("DB_NAME", "sim_tridharma_itsnu")
DB_USER = os.getenv("DB_USER", "postgres")
DB_PASSWORD = os.getenv("DB_PASSWORD", "")

# Redis Configuration (for MCP Tasks backend)
REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/0")

# External API Configuration
SINTA_API_URL = os.getenv("SINTA_API_URL", "https://sinta.kemdiktisaintek.go.id/api")
SINTA_API_KEY = os.getenv("SINTA_API_KEY", "")
PDDIKTI_API_URL = os.getenv("PDDIKTI_API_URL", "https://api-pddikti.rone.dev")
PDDIKTI_API_KEY = os.getenv("PDDIKTI_API_KEY", "")
