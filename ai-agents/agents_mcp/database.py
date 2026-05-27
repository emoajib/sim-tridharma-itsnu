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


import re
_IDENTIFIER_RE = re.compile(r'^[a-zA-Z_][a-zA-Z0-9_]*$')


def _validate_identifier(name: str, context: str = "identifier"):
    """Validate a SQL identifier (table/column name) for safety.
    Raises ValueError if invalid — prevents SQL injection via identifiers."""
    if not _IDENTIFIER_RE.match(name):
        raise ValueError(f"Invalid {context}: '{name}'. Must contain only letters, numbers, and underscores.")


async def execute_query_safe(
    table_name: str,
    columns: list[str] | None = None,
    where: dict | None = None,
    limit: int = 100,
    order_by: str | None = None,
) -> list[dict]:
    """
    Execute a safe SELECT query using structured parameters — NO raw SQL accepted.
    All identifiers are whitelist-validated, all values are parameterized.

    Args:
        table_name: Table to query (validated against information_schema whitelist)
        columns: Column names to select (default: all). Each validated as safe identifier.
        where: Dict of {column: value} for WHERE conditions. Values are parameterized.
        limit: Max rows (1-1000). Default 100.
        order_by: Column to order by, optionally with ASC/DESC (e.g. 'name' or 'id DESC').

    Returns:
        List of dicts (rows)

    Raises:
        ValueError: If table_name not in whitelist, or invalid column/identifier names.
    """
    # 1. Whitelist-validate table_name against information_schema
    allowed_tables = await list_tables()
    if table_name not in allowed_tables:
        raise ValueError(
            f"Table '{table_name}' is not in the allowed tables list. "
            f"Must be one of the {len(allowed_tables)} tables in the public schema."
        )

    # 2. Build column list — validate each column name
    if columns:
        for col in columns:
            _validate_identifier(col, f"column '{col}'")
        cols = ", ".join(f'"{c}"' for c in columns)
    else:
        cols = "*"

    # 3. Build WHERE clause — fully parameterized
    params: list = []
    where_parts: list[str] = []
    if where:
        for key, value in where.items():
            _validate_identifier(key, f"WHERE column '{key}'")
            where_parts.append(f'"{key}" = ${len(params) + 1}')
            params.append(value)
    where_clause = " AND ".join(where_parts) if where_parts else "TRUE"

    # 4. Build ORDER BY — validate column, allow optional ASC/DESC
    order_clause = ""
    if order_by:
        parts = order_by.strip().split(None, 1)
        col = parts[0]
        _validate_identifier(col, f"ORDER BY column '{col}'")
        direction = ""
        if len(parts) > 1:
            direction = parts[1].upper()
            if direction not in ("ASC", "DESC"):
                raise ValueError(f"Invalid ORDER BY direction: '{direction}'. Must be ASC or DESC.")
        order_clause = f' ORDER BY "{col}" {direction}' if direction else f' ORDER BY "{col}"'

    # 5. Cap limit between 1 and 1000
    safe_limit = max(1, min(limit, 1000))

    # 6. Build and execute query
    query = f'SELECT {cols} FROM "{table_name}" WHERE {where_clause}{order_clause} LIMIT {safe_limit}'
    return await execute_query(query, params)
