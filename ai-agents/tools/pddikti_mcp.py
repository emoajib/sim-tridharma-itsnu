"""
PDDIKTI API MCP Tools
Wrapper for PDDIKTI (Pangkalan Data Pendidikan Tinggi) API
"""
import logging
import httpx
from typing import Optional

from mcp.server.fastmcp import FastMCP, Context
from pydantic import Field

from agents_mcp.config import PDDIKTI_API_URL, PDDIKTI_API_KEY

logger = logging.getLogger("mcp.pddikti")

pddikti_mcp = FastMCP(
    name="pddikti-api",
    stateless_http=True,
    json_response=True,
)


async def _pddikti_request(endpoint: str, params: dict = None) -> dict:
    """Make request to PDDIKTI API"""
    url = f"{PDDIKTI_API_URL}/{endpoint}"
    headers = {}
    if PDDIKTI_API_KEY:
        headers["Authorization"] = f"Bearer {PDDIKTI_API_KEY}"

    async with httpx.AsyncClient(timeout=30) as client:
        response = await client.get(url, params=params, headers=headers)
        response.raise_for_status()
        return response.json()


@pddikti_mcp.tool()
async def pddikti_get_universitas(
    nama: Optional[str] = Field(default=None, description="Filter nama universitas"),
    kode: Optional[str] = Field(default=None, description="Kode universitas"),
    ctx: Context = None,
) -> dict:
    """
    Get university data from PDDIKTI.
    Returns university profile and statistics.
    """
    await ctx.info(f"Fetching PDDIKTI universitas: {nama or kode}")

    params = {}
    if nama:
        params["nama"] = nama
    if kode:
        params["kode"] = kode

    try:
        result = await _pddikti_request("universitas", params)
        return {
            "status": "success",
            "results": result.get("data", []),
            "total": result.get("total", 0),
        }
    except Exception as e:
        logger.error(f"PDDIKTI universitas fetch failed: {e}")
        return {
            "status": "error",
            "message": str(e),
        }


@pddikti_mcp.tool()
async def pddikti_get_prodi(
    universitas_id: Optional[str] = Field(default=None, description="ID universitas"),
    nama: Optional[str] = Field(default=None, description="Filter nama prodi"),
    jenjang: Optional[str] = Field(default=None, description="Jenjang: S1, S2, S3, D3, D4"),
    ctx: Context = None,
) -> dict:
    """
    Get study program (prodi) data from PDDIKTI.
    Returns prodi list with accreditation status.
    """
    await ctx.info(f"Fetching PDDIKTI prodi: {nama}")

    params = {}
    if universitas_id:
        params["universitas_id"] = universitas_id
    if nama:
        params["nama"] = nama
    if jenjang:
        params["jenjang"] = jenjang

    try:
        result = await _pddikti_request("prodi", params)
        return {
            "status": "success",
            "results": result.get("data", []),
            "total": result.get("total", 0),
        }
    except Exception as e:
        logger.error(f"PDDIKTI prodi fetch failed: {e}")
        return {
            "status": "error",
            "message": str(e),
        }


@pddikti_mcp.tool()
async def pddikti_get_dosen(
    prodi_id: Optional[str] = Field(default=None, description="ID prodi"),
    nidn: Optional[str] = Field(default=None, description="NIDN dosen"),
    nama: Optional[str] = Field(default=None, description="Nama dosen"),
    ctx: Context = None,
) -> dict:
    """
    Get lecturer (dosen) data from PDDIKTI.
    Returns lecturer profile with teaching data.
    """
    await ctx.info(f"Fetching PDDIKTI dosen: {nama or nidn}")

    params = {}
    if prodi_id:
        params["prodi_id"] = prodi_id
    if nidn:
        params["nidn"] = nidn
    if nama:
        params["nama"] = nama

    try:
        result = await _pddikti_request("dosen", params)
        return {
            "status": "success",
            "results": result.get("data", []),
            "total": result.get("total", 0),
        }
    except Exception as e:
        logger.error(f"PDDIKTI dosen fetch failed: {e}")
        return {
            "status": "error",
            "message": str(e),
        }


@pddikti_mcp.tool()
async def pddikti_get_akreditasi_prodi(
    prodi_id: str = Field(description="ID prodi"),
    ctx: Context = None,
) -> dict:
    """
    Get accreditation status for a study program.
    Returns accreditation rating, valid period, and certificate info.
    """
    await ctx.info(f"Fetching PDDIKTI akreditasi prodi: {prodi_id}")

    try:
        result = await _pddikti_request(f"prodi/{prodi_id}/akreditasi")
        return {
            "status": "success",
            "prodi_id": prodi_id,
            "akreditasi": result.get("data", {}),
        }
    except Exception as e:
        logger.error(f"PDDIKTI akreditasi fetch failed: {e}")
        return {
            "status": "error",
            "message": str(e),
            "prodi_id": prodi_id,
        }


@pddikti_mcp.tool()
async def pddikti_get_mahasiswa(
    prodi_id: Optional[str] = Field(default=None, description="ID prodi"),
    angkatan: Optional[int] = Field(default=None, description="Tahun angkatan"),
    status: Optional[str] = Field(default=None, description="Status: aktif, lulus, keluar, dropout"),
    ctx: Context = None,
) -> dict:
    """
    Get student (mahasiswa) data from PDDIKTI.
    Returns student list with enrollment status.
    """
    await ctx.info(f"Fetching PDDIKTI mahasiswa for prodi: {prodi_id}")

    params = {}
    if prodi_id:
        params["prodi_id"] = prodi_id
    if angkatan:
        params["angkatan"] = angkatan
    if status:
        params["status"] = status

    try:
        result = await _pddikti_request("mahasiswa", params)
        return {
            "status": "success",
            "results": result.get("data", []),
            "total": result.get("total", 0),
        }
    except Exception as e:
        logger.error(f"PDDIKTI mahasiswa fetch failed: {e}")
        return {
            "status": "error",
            "message": str(e),
        }
