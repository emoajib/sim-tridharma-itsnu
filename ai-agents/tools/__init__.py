"""
External API MCP Tools for Akreditasi System
"""
from tools.sinta_mcp import (
    sinta_mcp,
    sinta_search_author,
    sinta_get_author_profile,
    sinta_get_publications,
    sinta_get_affiliation,
    fetch_authors,
    fetch_publications,
    fetch_researches,
    fetch_community_services,
)
from tools.pddikti_mcp import (
    pddikti_mcp,
    pddikti_get_universitas,
    pddikti_get_prodi,
    pddikti_get_dosen,
    pddikti_get_akreditasi_prodi,
    pddikti_get_mahasiswa,
    fetch_dosen,
    fetch_prodi,
)
