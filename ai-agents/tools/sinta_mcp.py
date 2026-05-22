"""
SINTA API MCP Tools
Wrapper for SINTA (Science and Technology Index) API
"""
import logging
import httpx
from typing import Optional

from mcp.server.fastmcp import FastMCP, Context
from pydantic import Field

from agents_mcp.config import SINTA_API_URL, SINTA_API_KEY

logger = logging.getLogger("mcp.sinta")

sinta_mcp = FastMCP(
    name="sinta-api",
    stateless_http=True,
    json_response=True,
)


async def _sinta_request(endpoint: str, params: dict = None) -> dict:
    """Make request to SINTA API"""
    url = f"{SINTA_API_URL}/{endpoint}"
    headers = {}
    if SINTA_API_KEY:
        headers["Authorization"] = f"Bearer {SINTA_API_KEY}"

    async with httpx.AsyncClient(timeout=30) as client:
        response = await client.get(url, params=params, headers=headers)
        response.raise_for_status()
        return response.json()


@sinta_mcp.tool()
async def sinta_search_author(
    nama: str = Field(description="Nama peneliti untuk dicari"),
    afiliasi: Optional[str] = Field(default=None, description="Filter berdasarkan afiliasi"),
    ctx: Context = None,
) -> dict:
    """
    Search for authors/researchers in SINTA database.
    Returns list of authors with profile information.
    """
    await ctx.info(f"Searching SINTA author: {nama}")

    params = {"q": nama}
    if afiliasi:
        params["afiliasi"] = afiliasi

    try:
        result = await _sinta_request("authors", params)
        return {
            "status": "success",
            "query": nama,
            "results": result.get("data", []),
            "total": result.get("total", 0),
        }
    except Exception as e:
        logger.error(f"SINTA author search failed: {e}")
        return {
            "status": "error",
            "message": str(e),
            "query": nama,
        }


@sinta_mcp.tool()
async def sinta_get_author_profile(
    author_id: str = Field(description="SINTA Author ID"),
    ctx: Context = None,
) -> dict:
    """
    Get detailed profile information for a SINTA author.
    Includes h-index, i10-index, total citations, etc.
    """
    await ctx.info(f"Fetching SINTA author profile: {author_id}")

    try:
        result = await _sinta_request(f"authors/{author_id}")
        return {
            "status": "success",
            "author_id": author_id,
            "profile": result.get("data", {}),
        }
    except Exception as e:
        logger.error(f"SINTA author profile fetch failed: {e}")
        return {
            "status": "error",
            "message": str(e),
            "author_id": author_id,
        }


@sinta_mcp.tool()
async def sinta_get_publications(
    author_id: str = Field(description="SINTA Author ID"),
    year_from: Optional[int] = Field(default=None, description="Filter tahun mulai"),
    year_to: Optional[int] = Field(default=None, description="Filter tahun sampai"),
    ctx: Context = None,
) -> dict:
    """
    Get publications for a SINTA author.
    Returns list of publications with citation data.
    """
    await ctx.info(f"Fetching SINTA publications for author: {author_id}")

    params = {}
    if year_from:
        params["year_from"] = year_from
    if year_to:
        params["year_to"] = year_to

    try:
        result = await _sinta_request(f"authors/{author_id}/publications", params)
        return {
            "status": "success",
            "author_id": author_id,
            "publications": result.get("data", []),
            "total": result.get("total", 0),
        }
    except Exception as e:
        logger.error(f"SINTA publications fetch failed: {e}")
        return {
            "status": "error",
            "message": str(e),
            "author_id": author_id,
        }


@sinta_mcp.tool()
async def sinta_get_affiliation(
    nama_institusi: str = Field(description="Nama institusi untuk dicari"),
    ctx: Context = None,
) -> dict:
    """
    Search for institution/affiliation in SINTA database.
    Returns institution profile and ranking.
    """
    await ctx.info(f"Searching SINTA affiliation: {nama_institusi}")

    try:
        result = await _sinta_request("affiliations", {"q": nama_institusi})
        return {
            "status": "success",
            "query": nama_institusi,
            "results": result.get("data", []),
            "total": result.get("total", 0),
        }
    except Exception as e:
        logger.error(f"SINTA affiliation search failed: {e}")
        return {
            "status": "error",
            "message": str(e),
            "query": nama_institusi,
        }
