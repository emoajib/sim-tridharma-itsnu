"""
@deprecated Celery/RabbitMQ worker — digantikan oleh MCP tools langsung.
Agent dipanggil via MCP protocol oleh MCPClientService (PHP) → agents_mcp/tools.py (Python).
File ini hanya dipertahankan sebagai referensi. Tidak digunakan dalam runtime aktif.
"""
from celery import Celery

from config import REDIS_URL

celery_app = Celery("akreditasi_agents", broker=REDIS_URL, backend=REDIS_URL)

celery_app.conf.update(
    task_serializer="json",
    accept_content=["json"],
    result_serializer="json",
    timezone="Asia/Jakarta",
    enable_utc=True,
)


@celery_app.task
def run_verification(data: dict) -> dict:
    from agents.verifikasi_agent import VerifikasiAgent
    agent = VerifikasiAgent()
    return agent.execute(data)


@celery_app.task
def run_prediction(data: dict) -> dict:
    from agents.prediksi_agent import PrediksiAgent
    agent = PrediksiAgent()
    return agent.execute(data)


@celery_app.task
def run_peringatan(data: dict) -> dict:
    from agents.peringatan_agent import PeringatanAgent
    agent = PeringatanAgent()
    return agent.execute(data)


@celery_app.task
def run_generator(data: dict) -> dict:
    from agents.generator_agent import GeneratorAgent
    agent = GeneratorAgent()
    return agent.execute(data)


@celery_app.task
def run_integrasi(data: dict) -> dict:
    from agents.integrasi_agent import IntegrasiAgent
    agent = IntegrasiAgent()
    return agent.execute(data)


@celery_app.task
def run_prediction(data: dict) -> dict:
    from agents.prediksi_agent import PrediksiAgent
    agent = PrediksiAgent()
    return agent.execute(data)


@celery_app.task
def run_recommendation(data: dict) -> dict:
    from agents.rekomendasi_agent import RekomendasiAgent
    agent = RekomendasiAgent()
    return agent.execute(data)


@celery_app.task
def run_warning(data: dict) -> dict:
    from agents.peringatan_agent import PeringatanAgent
    agent = PeringatanAgent()
    return agent.execute(data)


@celery_app.task
def run_generation(data: dict) -> dict:
    from agents.generator_agent import GeneratorAgent
    agent = GeneratorAgent()
    return agent.execute(data)


@celery_app.task
def run_integration(data: dict) -> dict:
    from agents.integrasi_agent import IntegrasiAgent
    agent = IntegrasiAgent()
    return agent.execute(data)
