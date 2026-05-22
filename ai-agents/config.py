import os
import sys
import numpy as np
from dotenv import load_dotenv

load_dotenv()


def require_env(name: str, default: str | None = None) -> str:
    value = os.getenv(name, default)
    if value is None or value == "" or "change-me" in value.lower() or "default-" in value.lower():
        print(f"FATAL: Environment variable {name} is not set or has an insecure default value.")
        print(f"Set it in your .env file.")
        sys.exit(1)
    return value


def calculate_prediction(historical_scores: list[float]) -> dict:
    """
    Shared prediction formula used by both sync agents and MCP tools.
    Input: list of weighted scores per period (last 3 max).
    Output: predicted score + probabilities for 3 categories.
    """
    if not historical_scores:
        return {
            "skor_prediksi": 0,
            "probabilitas": {"unggul": 0.05, "baik_sekali": 0.1, "baik": 0.85},
            "trend_factor": 1.0,
            "trend_analysis": "Stagnan",
        }

    base_score = historical_scores[-1]

    trend_factor = 1.0
    if len(historical_scores) >= 2:
        x = np.arange(len(historical_scores))
        y = np.array(historical_scores)
        slope, _ = np.polyfit(x, y, 1)
        trend_factor = 1.0 + (slope / 100.0)

    skor_final = base_score * trend_factor

    prob_unggul = min(0.95, max(0.05, (skor_final - 300) / 100)) if skor_final > 300 else 0.05
    prob_baik_sekali = min(0.9, max(0.1, (skor_final - 200) / 150))
    prob_baik = 1.0 - prob_unggul - prob_baik_sekali

    total_p = prob_unggul + prob_baik_sekali + prob_baik
    if total_p > 0:
        prob_unggul /= total_p
        prob_baik_sekali /= total_p
        prob_baik /= total_p

    trend_analysis = "Positif" if trend_factor > 1 else ("Negatif" if trend_factor < 1 else "Stagnan")

    return {
        "skor_prediksi": round(skor_final, 2),
        "probabilitas": {
            "unggul": round(prob_unggul, 2),
            "baik_sekali": round(prob_baik_sekali, 2),
            "baik": round(prob_baik, 2),
        },
        "trend_factor": round(trend_factor, 4),
        "trend_analysis": trend_analysis,
    }


DATABASE_URL: str = os.getenv("DATABASE_URL", "postgresql://salsabil@localhost:5433/sim_tridharma_itsnu")
REDIS_URL: str = os.getenv("REDIS_URL", "redis://localhost:6379/0")
RABBITMQ_HOST: str = os.getenv("RABBITMQ_HOST", "localhost")
RABBITMQ_PORT: int = int(os.getenv("RABBITMQ_PORT", "5672"))
RABBITMQ_USER: str = os.getenv("RABBITMQ_USER", "guest")
RABBITMQ_PASSWORD: str = os.getenv("RABBITMQ_PASSWORD", "guest")
RABBITMQ_EXCHANGE: str = os.getenv("RABBITMQ_EXCHANGE", "akreditasi")
RABBITMQ_QUEUE: str = os.getenv("RABBITMQ_QUEUE", "agent_tasks")
AGENT_API_PORT: int = int(os.getenv("AGENT_API_PORT", "8001"))

LARAVEL_URL: str = os.getenv("LARAVEL_URL", "http://localhost:8000")
INTERNAL_KEY: str = require_env("AI_INTERNAL_KEY")
DOCS_OUTPUT_DIR: str = os.getenv("DOCS_OUTPUT_DIR", os.path.join(os.path.dirname(__file__), "output", "docs"))
