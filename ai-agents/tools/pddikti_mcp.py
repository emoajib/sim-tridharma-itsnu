"""
PDDIKTI API MCP Tools
Wrapper for PDDIKTI (Pangkalan Data Pendidikan Tinggi) API
Supports SISTER API (api-sister.kemdikbud.go.id) and legacy PDDIKTI API
"""
import logging
import httpx
from typing import Optional

from mcp.server.fastmcp import FastMCP, Context
from pydantic import Field

from agents_mcp.config import PDDIKTI_API_URL, PDDIKTI_API_KEY, SISTER_API_URL, SISTER_API_KEY

logger = logging.getLogger("mcp.pddikti")

pddikti_mcp = FastMCP(
    name="pddikti-api",
    stateless_http=True,
    json_response=True,
)

_SISTER_API_KEY = SISTER_API_KEY or PDDIKTI_API_KEY


async def _sister_request(endpoint: str, params: dict = None) -> dict:
    """Make request to SISTER API (primary)"""
    url = f"{SISTER_API_URL}/{endpoint}"
    headers = {}
    if __SISTER_API_KEY:
        headers["Authorization"] = f"Bearer {__SISTER_API_KEY}"
    async with httpx.AsyncClient(timeout=30, verify=False) as client:
        response = await client.get(url, params=params, headers=headers)
        response.raise_for_status()
        return response.json()


async def _pddikti_request(endpoint: str, params: dict = None) -> dict:
    """Make request to legacy PDDIKTI API (fallback)"""
    url = f"{PDDIKTI_API_URL}/{endpoint}"
    headers = {}
    if PDDIKTI_API_KEY:
        headers["Authorization"] = f"Bearer {PDDIKTI_API_KEY}"
    async with httpx.AsyncClient(timeout=30) as client:
        response = await client.get(url, params=params, headers=headers)
        response.raise_for_status()
        return response.json()


async def _try_fetch(endpoint: str, params: dict = None, sister_endpoint: str = None) -> dict:
    """Try SISTER API first, fall back to PDDIKTI API"""
    errors = []
    if sister_endpoint:
        try:
            return await _sister_request(sister_endpoint, params)
        except Exception as e:
            errors.append(f"sister: {e}")
            logger.warning(f"SISTER API failed ({sister_endpoint}), trying PDDIKTI: {e}")
    try:
        return await _pddikti_request(endpoint, params)
    except Exception as e:
        errors.append(f"pddikti: {e}")
        raise Exception(f"All APIs failed: {'; '.join(errors)}")


def _map_dosen_sister(item: dict) -> dict:
    """Map SISTER API response to standardized dosen format matching m_dosen schema"""
    return {
        "nidn": item.get("nidn") or item.get("nip") or "",
        "nip": item.get("nip") or item.get("nip_pns", ""),
        "nuptk": item.get("nuptk", ""),
        "nama_depan": (item.get("nama") or "").split(" ")[0] if item.get("nama") else "",
        "nama_belakang": " ".join((item.get("nama") or "").split(" ")[1:]) if item.get("nama") and len((item.get("nama") or "").split(" ")) > 1 else "",
        "nama": item.get("nama", ""),
        "gelar_depan": item.get("gelar_depan", ""),
        "gelar_belakang": item.get("gelar_belakang", ""),
        "tempat_lahir": item.get("tempat_lahir", ""),
        "tanggal_lahir": item.get("tanggal_lahir", ""),
        "jenis_kelamin": item.get("jenis_kelamin", ""),
        "email": item.get("email", ""),
        "telepon": item.get("telepon", ""),
        "status_aktivitas": item.get("status_aktivitas", "aktif"),
        "status_pegawai": item.get("status_pegawai", ""),
        "ikatan_kerja": item.get("ikatan_kerja", ""),
        "pendidikan_terakhir": item.get("pendidikan_terakhir", ""),
        "jabatan_fungsional": item.get("jabatan_fungsional", ""),
        "prodi_id": item.get("prodi_id") or item.get("id_prodi", ""),
        "prodi_nama": item.get("prodi_nama", ""),
        "sinta_id": item.get("sinta_id", ""),
        "id_sdm": item.get("id_sdm", ""),
        "npsn": item.get("npsn", ""),
        "id_universitas": item.get("id_universitas", ""),
        "nama_universitas": item.get("nama_universitas", ""),
        "is_active": True,
    }


def _map_dosen_pddikti(item: dict) -> dict:
    """Map legacy PDDIKTI API response to standardized dosen format"""
    return {
        "nidn": item.get("nidn") or item.get("nip") or "",
        "nip": item.get("nip", ""),
        "nuptk": item.get("nuptk", ""),
        "nama_depan": (item.get("nama") or "").split(" ")[0] if item.get("nama") else (item.get("nama_depan", "")),
        "nama_belakang": " ".join((item.get("nama") or "").split(" ")[1:]) if item.get("nama") and len((item.get("nama") or "").split(" ")) > 1 else (item.get("nama_belakang", "")),
        "nama": item.get("nama", ""),
        "gelar_depan": item.get("gelar_depan", ""),
        "gelar_belakang": item.get("gelar_belakang", ""),
        "tempat_lahir": item.get("tempat_lahir", ""),
        "tanggal_lahir": item.get("tanggal_lahir", ""),
        "jenis_kelamin": item.get("jenis_kelamin", ""),
        "email": item.get("email", ""),
        "telepon": item.get("telepon", ""),
        "status_aktivitas": item.get("status_aktivitas", "aktif"),
        "status_pegawai": item.get("status_pegawai", ""),
        "ikatan_kerja": item.get("ikatan_kerja", ""),
        "pendidikan_terakhir": item.get("pendidikan_terakhir", ""),
        "jabatan_fungsional": item.get("jabatan_fungsional", ""),
        "prodi_id": item.get("prodi_id", ""),
        "prodi_nama": item.get("prodi_nama", ""),
        "sinta_id": item.get("sinta_id", ""),
        "id_sdm": item.get("id_sdm", ""),
        "is_active": True,
    }


def _map_prodi_sister(item: dict) -> dict:
    """Map SISTER prodi response to standardized format matching m_prodi schema"""
    return {
        "kode_prodi": item.get("kode_prodi") or item.get("id_prodi", ""),
        "nama_prodi": item.get("nama_prodi", ""),
        "jenjang": item.get("jenjang", ""),
        "akreditasi": item.get("akreditasi", ""),
        "sk_akreditasi": item.get("sk_akreditasi", ""),
        "tanggal_kadaluarsa": item.get("tanggal_kadaluarsa") or item.get("tgl_kadaluarsa", ""),
        "id_universitas": item.get("id_universitas", ""),
        "nama_universitas": item.get("nama_universitas", ""),
        "fakultas_id": item.get("fakultas_id", ""),
        "fakultas_nama": item.get("fakultas_nama", ""),
        "is_active": True,
    }


def _map_prodi_pddikti(item: dict) -> dict:
    """Map legacy PDDIKTI prodi response to standardized format"""
    return {
        "kode_prodi": item.get("kode_prodi") or item.get("id_prodi", ""),
        "nama_prodi": item.get("nama_prodi", ""),
        "jenjang": item.get("jenjang", ""),
        "akreditasi": item.get("akreditasi", ""),
        "sk_akreditasi": item.get("sk_akreditasi", ""),
        "tanggal_kadaluarsa": item.get("tanggal_kadaluarsa", ""),
        "fakultas_id": item.get("fakultas_id", ""),
        "fakultas_nama": item.get("fakultas_nama", ""),
        "is_active": True,
    }


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
        return {"status": "error", "message": str(e)}


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
        return {"status": "error", "message": str(e)}


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
        return {"status": "error", "message": str(e)}


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
        return {"status": "error", "message": str(e), "prodi_id": prodi_id}


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
        return {"status": "error", "message": str(e)}


@pddikti_mcp.tool()
async def fetch_dosen(
    prodi_id: Optional[str] = Field(default=None, description="Filter by prodi ID"),
    nidn: Optional[str] = Field(default=None, description="Filter by NIDN"),
    nama: Optional[str] = Field(default=None, description="Filter by nama"),
    page: int = Field(default=1, description="Page number", ge=1),
    limit: int = Field(default=50, description="Results per page", ge=1, le=200),
    fetch_all: bool = Field(default=False, description="Fetch all pages (overrides page/limit)"),
    ctx: Context = None,
) -> dict:
    """
    Fetch dosen (lecturer) data from PDDIKTI/SISTER with pagination.
    Returns structured data matching m_dosen schema. Supports multi-page batch fetch.
    Primary source: SISTER API (api-sister.kemdikbud.go.id), falls back to PDDIKTI.
    """
    await ctx.info(f"Fetching dosen data: prodi={prodi_id}, nidn={nidn}, page={page}, limit={limit}")

    params = {"limit": limit, "offset": (page - 1) * limit}
    if prodi_id:
        params["id_prodi"] = prodi_id
    if nidn:
        params["nidn"] = nidn
    if nama:
        params["nama"] = nama

    try:
        raw = await _try_fetch("dosen", params, sister_endpoint="dosen")
        raw_items = raw.get("data") or raw.get("results") or raw.get("dosen") or raw.get("list") or []
        if isinstance(raw_items, dict):
            raw_items = [raw_items]

        total = raw.get("total") or raw.get("count") or raw.get("metadata", {}).get("total", len(raw_items))

        mapped = [_map_dosen_sister(item) for item in raw_items]

        if fetch_all and total and len(mapped) < total:
            all_items = list(mapped)
            current_offset = (page - 1) * limit + len(mapped)
            while current_offset < total:
                fetch_params = dict(params)
                fetch_params["offset"] = current_offset
                try:
                    more_raw = await _try_fetch("dosen", fetch_params, sister_endpoint="dosen")
                    more_items = more_raw.get("data") or more_raw.get("results") or more_raw.get("dosen") or more_raw.get("list") or []
                    if isinstance(more_items, dict):
                        more_items = [more_items]
                    all_items.extend([_map_dosen_sister(it) for it in more_items])
                    current_offset += len(more_items)
                except Exception as e:
                    logger.warning(f"Failed to fetch page at offset {current_offset}: {e}")
                    break
            return {
                "status": "success",
                "results": all_items,
                "total": len(all_items),
                "page": 1,
                "pages": 1,
                "source": "sister" if _SISTER_API_KEY else "pddikti",
            }

        return {
            "status": "success",
            "results": mapped,
            "total": total if isinstance(total, int) else len(mapped),
            "page": page,
            "limit": limit,
            "source": "sister" if _SISTER_API_KEY else "pddikti",
        }
    except Exception as e:
        logger.error(f"fetch_dosen failed: {e}")
        return {"status": "error", "message": str(e)}


@pddikti_mcp.tool()
async def fetch_prodi(
    kode_prodi: Optional[str] = Field(default=None, description="Filter by kode prodi"),
    nama: Optional[str] = Field(default=None, description="Filter by nama prodi"),
    jenjang: Optional[str] = Field(default=None, description="Jenjang: S1, S2, S3, D3, D4"),
    page: int = Field(default=1, description="Page number", ge=1),
    limit: int = Field(default=50, description="Results per page", ge=1, le=200),
    fetch_all: bool = Field(default=False, description="Fetch all pages"),
    ctx: Context = None,
) -> dict:
    """
    Fetch prodi (study program) data from PDDIKTI/SISTER with pagination.
    Returns structured data matching m_prodi schema.
    Primary source: SISTER API, falls back to PDDIKTI.
    """
    await ctx.info(f"Fetching prodi data: kode={kode_prodi}, page={page}")

    params = {"limit": limit, "offset": (page - 1) * limit}
    if kode_prodi:
        params["id_prodi"] = kode_prodi
    if nama:
        params["nama"] = nama
    if jenjang:
        params["jenjang"] = jenjang

    try:
        raw = await _try_fetch("prodi", params, sister_endpoint="prodi")
        raw_items = raw.get("data") or raw.get("results") or raw.get("prodi") or raw.get("list") or []
        if isinstance(raw_items, dict):
            raw_items = [raw_items]

        total = raw.get("total") or raw.get("count") or raw.get("metadata", {}).get("total", len(raw_items))
        mapped = [_map_prodi_sister(item) for item in raw_items]

        if fetch_all and total and len(mapped) < total:
            all_items = list(mapped)
            current_offset = (page - 1) * limit + len(mapped)
            while current_offset < total:
                fetch_params = dict(params)
                fetch_params["offset"] = current_offset
                try:
                    more_raw = await _try_fetch("prodi", fetch_params, sister_endpoint="prodi")
                    more_items = more_raw.get("data") or more_raw.get("results") or more_raw.get("prodi") or more_raw.get("list") or []
                    if isinstance(more_items, dict):
                        more_items = [more_items]
                    all_items.extend([_map_prodi_sister(it) for it in more_items])
                    current_offset += len(more_items)
                except Exception as e:
                    logger.warning(f"Failed to fetch prodi page at offset {current_offset}: {e}")
                    break
            return {
                "status": "success",
                "results": all_items,
                "total": len(all_items),
                "page": 1,
                "pages": 1,
                "source": "sister" if _SISTER_API_KEY else "pddikti",
            }

        return {
            "status": "success",
            "results": mapped,
            "total": total if isinstance(total, int) else len(mapped),
            "page": page,
            "limit": limit,
            "source": "sister" if _SISTER_API_KEY else "pddikti",
        }
    except Exception as e:
        logger.error(f"fetch_prodi failed: {e}")
        return {"status": "error", "message": str(e)}
