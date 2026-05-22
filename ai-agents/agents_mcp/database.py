"""
PostgreSQL Database Connection for MCP Tools
Async connection pool for database operations
"""
import asyncpg
import logging
from typing import Optional
from contextlib import asynccontextmanager

from agents_mcp.config import DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD

logger = logging.getLogger("mcp.db")

_pool: Optional[asyncpg.Pool] = None


async def init_db_pool():
    """Initialize asyncpg connection pool"""
    global _pool
    if _pool is not None:
        return _pool

    logger.info(f"Initializing PostgreSQL connection pool: {DB_HOST}:{DB_PORT}/{DB_NAME}")
    _pool = await asyncpg.create_pool(
        host=DB_HOST,
        port=int(DB_PORT),
        database=DB_NAME,
        user=DB_USER,
        password=DB_PASSWORD,
        min_size=2,
        max_size=10,
        command_timeout=60,
    )
    logger.info("PostgreSQL connection pool initialized")
    return _pool


async def close_db_pool():
    """Close asyncpg connection pool"""
    global _pool
    if _pool:
        await _pool.close()
        _pool = None
        logger.info("PostgreSQL connection pool closed")


@asynccontextmanager
async def get_db_connection():
    """Get a database connection from the pool"""
    if _pool is None:
        await init_db_pool()

    async with _pool.acquire() as conn:
        yield conn


async def execute_query(query: str, params: list = None) -> list[dict]:
    """Execute a SELECT query and return results as list of dicts"""
    async with get_db_connection() as conn:
        rows = await conn.fetch(query, *(params or []))
        return [dict(row) for row in rows]


async def execute_command(query: str, params: list = None) -> int:
    """Execute an INSERT/UPDATE/DELETE query and return affected rows"""
    async with get_db_connection() as conn:
        result = await conn.execute(query, *(params or []))
        # Parse affected rows from result string like "INSERT 0 1"
        parts = result.split()
        return int(parts[-1]) if parts else 0


async def get_table_schema(table_name: str) -> list[dict]:
    """Get schema information for a table"""
    query = """
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = $1
        ORDER BY ordinal_position
    """
    return await execute_query(query, [table_name])


async def list_tables() -> list[str]:
    """List all tables in the public schema"""
    query = """
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
        ORDER BY table_name
    """
    rows = await execute_query(query)
    return [row["table_name"] for row in rows]
