"""
Multi-Agent Orchestrator - Coordinates AI agent execution plans.
Uses mcp-agent Worker/Orchestrator pattern for LLM-driven multi-agent workflows.
"""
import logging
from typing import Optional

logger = logging.getLogger("mcp.orchestrator")

ORCHESTRATION_PLANS = {
    "peringatan": {
        "description": "Check all warnings for a prodi (BKD, kalibrasi, akreditasi, RKAT)",
        "tools": ["peringatan_check"],
        "fallback_message": "Orchestrator not available, use tools directly.",
    },
    "prediksi": {
        "description": "Full accreditation prediction with budget analysis",
        "tools": ["prediksi_skor"],
        "fallback_message": "Orchestrator not available, use tools directly.",
    },
    "verifikasi": {
        "description": "Verify all documents for a prodi",
        "tools": ["verifikasi_dokumen"],
        "fallback_message": "Orchestrator not available, use tools directly.",
    },
    "rekomendasi": {
        "description": "Generate recommendations based on indicator gaps",
        "tools": ["rekomendasi_generate"],
        "fallback_message": "Orchestrator not available, use tools directly.",
    },
}

list_plans = ORCHESTRATION_PLANS
