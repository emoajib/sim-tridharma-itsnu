"""
OAuth 2.1 Authentication for MCP Server
Supports JWT verification and API key fallback during migration
"""
import os
import logging
from typing import Optional

import jwt
from fastapi import HTTPException, Header

from agents_mcp.config import (
    OAUTH_ENABLED,
    OAUTH_JWT_ISSUER,
    OAUTH_JWT_AUDIENCE,
    OAUTH_REQUIRED_SCOPES,
)

logger = logging.getLogger("mcp.auth")

# Required secrets — crash on startup if missing
API_KEY = os.environ.get("AGENT_API_KEY")
JWT_SECRET = os.environ.get("JWT_SECRET")
JWT_ALGORITHM = os.getenv("JWT_ALGORITHM", "HS256")

# Startup validation
if not API_KEY:
    raise RuntimeError("AGENT_API_KEY environment variable is required")
if OAUTH_ENABLED and not JWT_SECRET:
    raise RuntimeError("JWT_SECRET environment variable is required when OAUTH_ENABLED=true")


async def verify_mcp_auth(
    authorization: Optional[str] = Header(None),
    x_api_key: Optional[str] = Header(None),
) -> dict:
    """
    Verify MCP request authentication.
    Supports OAuth 2.1 Bearer token (JWT) or API key fallback.
    """
    # Try OAuth 2.1 Bearer token first
    if authorization and authorization.startswith("Bearer "):
        token = authorization[7:]
        if OAUTH_ENABLED:
            try:
                payload = jwt.decode(
                    token,
                    JWT_SECRET,
                    algorithms=[JWT_ALGORITHM],
                    audience=OAUTH_JWT_AUDIENCE,
                    issuer=OAUTH_JWT_ISSUER,
                )
                return {
                    "auth_type": "oauth",
                    "subject": payload.get("sub", ""),
                    "scopes": payload.get("scope", "").split(),
                }
            except jwt.ExpiredSignatureError:
                raise HTTPException(status_code=401, detail="Token expired")
            except jwt.InvalidTokenError as e:
                logger.warning(f"JWT verification failed: {e}")
                raise HTTPException(status_code=401, detail="Invalid token")

    # Fallback to API key (migration period only)
    if x_api_key and x_api_key == API_KEY:
        logger.debug("API key authentication (fallback)")
        return {
            "auth_type": "api_key",
            "subject": "api_key_user",
            "scopes": OAUTH_REQUIRED_SCOPES,
        }

    logger.warning("Unauthorized access attempt")
    raise HTTPException(status_code=401, detail="Authentication required")
