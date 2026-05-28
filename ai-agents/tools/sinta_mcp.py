"""
SINTA API MCP Tools
Wrapper for SINTA (Science and Technology Index) API v1
Real API endpoints: https://sinta.kemdiktisaintek.go.id/api/v1/
"""
import logging
import httpx
from typing import Optional

from mcp.server.fastmcp import FastMCP, Context
from pydantic import Field

from agents_mcp.config import SINTA_API_URL, SINTA_API_KEY, SINTA_API_KEY_HEADER

logger = logging.getLogger("mcp.sinta")

sinta_mcp = FastMCP(
    name="sinta-api",
    stateless_http=True,
    json_response=True,
)


async def _sinta_request(endpoint: str, params: dict = None) -> dict:
    """Make request to SINTA API v1"""
    base = SINTA_API_URL.rstrip("/")
    url = f"{base}/{endpoint.lstrip('/')}"
    headers = {}
    if SINTA_API_KEY:
        headers[SINTA_API_KEY_HEADER] = SINTA_API_KEY
        headers["Authorization"] = f"Bearer {SINTA_API_KEY}"
    async with httpx.AsyncClient(timeout=30) as client:
        response = await client.get(url, params=params, headers=headers)
        response.raise_for_status()
        return response.json()


def _extract_items(raw: dict, keys: list) -> list:
    """Extract items from response trying multiple keys"""
    for key in keys:
        val = raw.get(key)
        if val is not None:
            if isinstance(val, list):
                return val
            if isinstance(val, dict):
                return [val]
    return []


def _get_total(raw: dict, default: int = 0) -> int:
    """Extract total count from response"""
    return (raw.get("total") or raw.get("count") or
            raw.get("metadata", {}).get("total") or
            raw.get("meta", {}).get("total") or
            raw.get("pagination", {}).get("total") or default)


def _map_author(item: dict) -> dict:
    """Map SINTA author response to standardized format"""
    return {
        "sinta_id": str(item.get("id") or item.get("author_id") or ""),
        "nama": item.get("name") or item.get("nama") or "",
        "nama_depan": item.get("first_name") or item.get("nama_depan", ""),
        "nama_belakang": item.get("last_name") or item.get("nama_belakang", ""),
        "afiliasi": item.get("affiliation") or item.get("afiliasi") or item.get("university", ""),
        "affiliation_id": item.get("affiliation_id") or item.get("afiliasi_id", ""),
        "email": item.get("email", ""),
        "url": item.get("url", ""),
        "url_sinta": item.get("sinta_url", ""),
        "scopus_id": item.get("scopus_id", ""),
        "google_scholar_id": item.get("google_scholar_id", ""),
        "wos_id": item.get("wos_id", ""),
        "garuda_id": item.get("garuda_id", ""),
        "h_index_all": item.get("h_index_all") or item.get("hIndexAll", 0),
        "h_index_3yr": item.get("h_index_3yr") or item.get("hIndex3yr", 0),
        "i10_index_all": item.get("i10_index_all") or item.get("i10IndexAll", 0),
        "i10_index_3yr": item.get("i10_index_3yr") or item.get("i10Index3yr", 0),
        "citations_all": item.get("citations_all") or item.get("citationAll", 0),
        "citations_3yr": item.get("citations_3yr") or item.get("citation3yr", 0),
        "docs_count_all": item.get("docs_count_all") or item.get("docCountAll", 0),
        "docs_count_3yr": item.get("docs_count_3yr") or item.get("docCount3yr", 0),
        "score_overall": item.get("score_overall") or item.get("scoreOverall", 0),
        "score_3yr": item.get("score_3yr") or item.get("score3yr", 0),
        "is_active": True,
    }


def _map_publication(item: dict) -> dict:
    """Map SINTA publication response to standardized format matching trx_publikasi"""
    return {
        "sinta_id": str(item.get("id") or ""),
        "judul_publikasi": item.get("title") or item.get("judul") or item.get("name", ""),
        "judul": item.get("title") or item.get("judul") or item.get("name", ""),
        "jenis_publikasi": item.get("type") or item.get("jenis") or item.get("publication_type", ""),
        "tahun": item.get("year") or item.get("tahun") or item.get("publication_year", ""),
        "link": item.get("url") or item.get("link") or item.get("doi", ""),
        "doi": item.get("doi", ""),
        "authors": item.get("authors") or item.get("penulis", ""),
        "author_list": item.get("author_list") or item.get("authors_list", []),
        "journal": item.get("journal") or item.get("jurnal") or item.get("publication_name", ""),
        "volume": item.get("volume", ""),
        "issue": item.get("issue", ""),
        "pages": item.get("pages") or item.get("halaman", ""),
        "publisher": item.get("publisher", ""),
        "issn": item.get("issn", ""),
        "citations": item.get("citations") or item.get("citation_count", 0),
        "cited_by": item.get("cited_by", []),
        "abstract": item.get("abstract") or item.get("abstrak", ""),
        "indexed_by": item.get("indexed_by") or item.get("indexing", []),
        "status_sinkron": "pending",
    }


def _map_research(item: dict) -> dict:
    """Map SINTA research response to standardized format matching trx_penelitian"""
    return {
        "sinta_id": str(item.get("id") or ""),
        "judul_penelitian": item.get("title") or item.get("judul") or item.get("name", ""),
        "judul": item.get("title") or item.get("judul") or item.get("name", ""),
        "tahun": item.get("year") or item.get("tahun") or item.get("research_year", ""),
        "tahun_pelaksanaan": item.get("year") or item.get("tahun") or item.get("research_year", ""),
        "skema": item.get("scheme") or item.get("skema") or item.get("research_type", ""),
        "jenis_penelitian": item.get("scheme") or item.get("skema") or item.get("research_type", ""),
        "sumber_dana": item.get("fund_source") or item.get("sumber_dana") or item.get("funding_source", ""),
        "jumlah_dana": item.get("fund") or item.get("jumlah_dana") or item.get("fund_amount", 0),
        "dana": item.get("fund") or item.get("jumlah_dana") or item.get("fund_amount", 0),
        "members": item.get("members") or item.get("anggota", []),
        "member_count": item.get("member_count") or item.get("jumlah_anggota", 0),
        "abstract": item.get("abstract") or item.get("abstrak", ""),
        "status_sinkron": "pending",
    }


def _map_community_service(item: dict) -> dict:
    """Map SINTA community service response to standardized format matching trx_pkm"""
    return {
        "sinta_id": str(item.get("id") or ""),
        "judul_pkm": item.get("title") or item.get("judul") or item.get("name", ""),
        "judul": item.get("title") or item.get("judul") or item.get("name", ""),
        "tahun": item.get("year") or item.get("tahun") or item.get("service_year", ""),
        "tahun_pelaksanaan": item.get("year") or item.get("tahun") or item.get("service_year", ""),
        "lokasi": item.get("location") or item.get("lokasi") or item.get("area", ""),
        "jenis_pkm": item.get("type") or item.get("jenis") or item.get("service_type", ""),
        "sumber_dana": item.get("fund_source") or item.get("sumber_dana") or item.get("funding_source", ""),
        "jumlah_dana": item.get("fund") or item.get("jumlah_dana") or item.get("fund_amount", 0),
        "dana": item.get("fund") or item.get("jumlah_dana") or item.get("fund_amount", 0),
        "members": item.get("members") or item.get("anggota", []),
        "member_count": item.get("member_count") or item.get("jumlah_anggota", 0),
        "abstract": item.get("abstract") or item.get("abstrak", ""),
        "status_sinkron": "pending",
    }


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
        return {"status": "error", "message": str(e), "query": nama}


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
        return {"status": "error", "message": str(e), "author_id": author_id}


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
        return {"status": "error", "message": str(e), "author_id": author_id}


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
        return {"status": "error", "message": str(e), "query": nama_institusi}


@sinta_mcp.tool()
async def fetch_authors(
    nama: str = Field(default="", description="Nama dosen untuk dicari"),
    afiliasi: Optional[str] = Field(default=None, description="Filter afiliasi"),
    page: int = Field(default=1, description="Page number", ge=1),
    limit: int = Field(default=50, description="Results per page", ge=1, le=200),
    fetch_all: bool = Field(default=False, description="Fetch all pages"),
    ctx: Context = None,
) -> dict:
    """
    Fetch authors/dosen data from SINTA API.
    Returns structured data with SINTA profile, h-index, citations.
    Maps to m_dosen.sinta_id for matching with local data.
    """
    await ctx.info(f"Fetching SINTA authors: q={nama}, page={page}")

    params = {"q": nama, "limit": limit, "offset": (page - 1) * limit}
    if afiliasi:
        params["affiliation"] = afiliasi

    try:
        raw = await _sinta_request("authors/search", params)
        items = _extract_items(raw, ["data", "results", "authors", "list"])
        total = _get_total(raw, len(items))
        mapped = [_map_author(item) for item in items]

        if fetch_all and total and len(mapped) < total:
            all_items = list(mapped)
            current_offset = (page - 1) * limit + len(mapped)
            while current_offset < total:
                p = dict(params)
                p["offset"] = current_offset
                try:
                    more_raw = await _sinta_request("authors/search", p)
                    more_items = _extract_items(more_raw, ["data", "results", "authors", "list"])
                    all_items.extend([_map_author(it) for it in more_items])
                    current_offset += len(more_items)
                except Exception as e:
                    logger.warning(f"fetch_authors page fail at {current_offset}: {e}")
                    break
            return {
                "status": "success",
                "results": all_items,
                "total": len(all_items),
                "page": 1,
                "pages": 1,
            }

        return {
            "status": "success",
            "results": mapped,
            "total": total,
            "page": page,
            "limit": limit,
        }
    except Exception as e:
        logger.error(f"fetch_authors failed: {e}")
        return {"status": "error", "message": str(e)}


@sinta_mcp.tool()
async def fetch_publications(
    author_id: str = Field(description="SINTA Author ID"),
    year_from: Optional[int] = Field(default=None, description="Filter: tahun mulai"),
    year_to: Optional[int] = Field(default=None, description="Filter: tahun sampai"),
    page: int = Field(default=1, description="Page number", ge=1),
    limit: int = Field(default=50, description="Results per page", ge=1, le=200),
    fetch_all: bool = Field(default=False, description="Fetch all pages"),
    ctx: Context = None,
) -> dict:
    """
    Fetch publications for a SINTA author.
    Returns structured data matching trx_publikasi schema.
    Includes DOI, citations, journal info for import into the system.
    """
    await ctx.info(f"Fetching publications for author {author_id}, page={page}")

    params = {"limit": limit, "offset": (page - 1) * limit}
    if year_from:
        params["year_from"] = year_from
    if year_to:
        params["year_to"] = year_to

    try:
        raw = await _sinta_request(f"authors/{author_id}/publications", params)
        items = _extract_items(raw, ["data", "results", "publications", "list"])
        total = _get_total(raw, len(items))
        mapped = [_map_publication(item) for item in items]

        if fetch_all and total and len(mapped) < total:
            all_items = list(mapped)
            current_offset = (page - 1) * limit + len(mapped)
            while current_offset < total:
                p = dict(params)
                p["offset"] = current_offset
                try:
                    more_raw = await _sinta_request(f"authors/{author_id}/publications", p)
                    more_items = _extract_items(more_raw, ["data", "results", "publications", "list"])
                    all_items.extend([_map_publication(it) for it in more_items])
                    current_offset += len(more_items)
                except Exception as e:
                    logger.warning(f"fetch_publications page fail at {current_offset}: {e}")
                    break
            return {
                "status": "success",
                "author_id": author_id,
                "publications": all_items,
                "total": len(all_items),
                "page": 1,
                "pages": 1,
            }

        return {
            "status": "success",
            "author_id": author_id,
            "publications": mapped,
            "total": total,
            "page": page,
            "limit": limit,
        }
    except Exception as e:
        logger.error(f"fetch_publications failed for {author_id}: {e}")
        return {"status": "error", "message": str(e), "author_id": author_id}


@sinta_mcp.tool()
async def fetch_researches(
    author_id: str = Field(description="SINTA Author ID"),
    year_from: Optional[int] = Field(default=None, description="Filter: tahun mulai"),
    year_to: Optional[int] = Field(default=None, description="Filter: tahun sampai"),
    page: int = Field(default=1, description="Page number", ge=1),
    limit: int = Field(default=50, description="Results per page", ge=1, le=200),
    fetch_all: bool = Field(default=False, description="Fetch all pages"),
    ctx: Context = None,
) -> dict:
    """
    Fetch research/penelitian data for a SINTA author.
    Returns structured data matching trx_penelitian schema.
    Includes funding scheme, amount, members for import.
    """
    await ctx.info(f"Fetching researches for author {author_id}, page={page}")

    params = {"limit": limit, "offset": (page - 1) * limit}
    if year_from:
        params["year_from"] = year_from
    if year_to:
        params["year_to"] = year_to

    try:
        raw = await _sinta_request(f"authors/{author_id}/researches", params)
        items = _extract_items(raw, ["data", "results", "researches", "research", "list"])
        total = _get_total(raw, len(items))
        mapped = [_map_research(item) for item in items]

        if fetch_all and total and len(mapped) < total:
            all_items = list(mapped)
            current_offset = (page - 1) * limit + len(mapped)
            while current_offset < total:
                p = dict(params)
                p["offset"] = current_offset
                try:
                    more_raw = await _sinta_request(f"authors/{author_id}/researches", p)
                    more_items = _extract_items(more_raw, ["data", "results", "researches", "research", "list"])
                    all_items.extend([_map_research(it) for it in more_items])
                    current_offset += len(more_items)
                except Exception as e:
                    logger.warning(f"fetch_researches page fail at {current_offset}: {e}")
                    break
            return {
                "status": "success",
                "author_id": author_id,
                "researches": all_items,
                "total": len(all_items),
                "page": 1,
                "pages": 1,
            }

        return {
            "status": "success",
            "author_id": author_id,
            "researches": mapped,
            "total": total,
            "page": page,
            "limit": limit,
        }
    except Exception as e:
        logger.error(f"fetch_researches failed for {author_id}: {e}")
        return {"status": "error", "message": str(e), "author_id": author_id}


@sinta_mcp.tool()
async def fetch_community_services(
    author_id: str = Field(description="SINTA Author ID"),
    year_from: Optional[int] = Field(default=None, description="Filter: tahun mulai"),
    year_to: Optional[int] = Field(default=None, description="Filter: tahun sampai"),
    page: int = Field(default=1, description="Page number", ge=1),
    limit: int = Field(default=50, description="Results per page", ge=1, le=200),
    fetch_all: bool = Field(default=False, description="Fetch all pages"),
    ctx: Context = None,
) -> dict:
    """
    Fetch community service (PKM) data for a SINTA author.
    Returns structured data matching trx_pkm schema.
    Includes location, funding, members for import.
    """
    await ctx.info(f"Fetching community services for author {author_id}, page={page}")

    params = {"limit": limit, "offset": (page - 1) * limit}
    if year_from:
        params["year_from"] = year_from
    if year_to:
        params["year_to"] = year_to

    try:
        raw = await _sinta_request(f"authors/{author_id}/community_services", params)
        items = _extract_items(raw, ["data", "results", "community_services", "services", "list"])
        total = _get_total(raw, len(items))
        mapped = [_map_community_service(item) for item in items]

        if fetch_all and total and len(mapped) < total:
            all_items = list(mapped)
            current_offset = (page - 1) * limit + len(mapped)
            while current_offset < total:
                p = dict(params)
                p["offset"] = current_offset
                try:
                    more_raw = await _sinta_request(f"authors/{author_id}/community_services", p)
                    more_items = _extract_items(more_raw, ["data", "results", "community_services", "services", "list"])
                    all_items.extend([_map_community_service(it) for it in more_items])
                    current_offset += len(more_items)
                except Exception as e:
                    logger.warning(f"fetch_community_services page fail at {current_offset}: {e}")
                    break
            return {
                "status": "success",
                "author_id": author_id,
                "community_services": all_items,
                "total": len(all_items),
                "page": 1,
                "pages": 1,
            }

        return {
            "status": "success",
            "author_id": author_id,
            "community_services": mapped,
            "total": total,
            "page": page,
            "limit": limit,
        }
    except Exception as e:
        logger.error(f"fetch_community_services failed for {author_id}: {e}")
        return {"status": "error", "message": str(e), "author_id": author_id}
