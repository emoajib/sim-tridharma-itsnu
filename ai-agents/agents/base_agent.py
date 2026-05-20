import json
import logging
import requests
from abc import ABC, abstractmethod
from datetime import datetime, timezone

from sqlalchemy import text

from database import SessionLocal
from config import LARAVEL_URL, INTERNAL_KEY

logger = logging.getLogger("agent")


class BaseAgent(ABC):
    name: str = "base"
    version: str = "1.0.0"

    @abstractmethod
    def execute(self, data: dict) -> dict:
        ...

    def validate_input(self, data: dict, required_fields: list) -> list[str]:
        missing = [f for f in required_fields if f not in data or data[f] is None]
        return missing

    def log_execution(
        self,
        agent_name: str,
        triggered_by: str | None,
        input_data: dict,
        output_data: dict,
        status: str = "success",
        error_message: str | None = None,
    ):
        started_at = datetime.now(timezone.utc)
        finished_at = datetime.now(timezone.utc)
        duration_ms = int((finished_at - started_at).total_seconds() * 1000)

        log_data = {
            "agent_name": agent_name,
            "status": status,
            "started_at": started_at.isoformat(),
            "finished_at": finished_at.isoformat(),
            "duration_ms": duration_ms,
            "input_data": input_data,
            "output_data": output_data,
            "error_message": error_message,
            "triggered_by": triggered_by or "system",
        }

        # Try to call Laravel API first (to trigger events/observers)
        try:
            response = requests.post(
                f"{LARAVEL_URL}/api/internal/agents/log",
                json=log_data,
                headers={"X-Internal-Key": INTERNAL_KEY},
                timeout=5
            )
            if response.status_code == 200:
                logger.info(f"Successfully logged execution to Laravel for agent {agent_name}")
                return
            else:
                logger.warning(f"Laravel logging failed with status {response.status_code}: {response.text}")
        except Exception as e:
            logger.error(f"Failed to call Laravel logging API: {e}")

        # Fallback to direct DB insertion if API fails
        db = SessionLocal()
        try:
            db.execute(
                text("""
                    INSERT INTO agent_execution_log
                        (agent_name, status, started_at, finished_at, input_data, output_data, error_message, triggered_by, created_at, updated_at)
                    VALUES
                        (:agent_name, :status, :started_at, :finished_at, :input_data, :output_data, :error_message, :triggered_by, NOW(), NOW())
                """),
                {
                    "agent_name": agent_name,
                    "status": status,
                    "started_at": started_at,
                    "finished_at": finished_at,
                    "input_data": json.dumps(input_data),
                    "output_data": json.dumps(output_data),
                    "error_message": error_message,
                    "triggered_by": triggered_by,
                },
            )
            db.commit()
            logger.info(f"Logged execution directly to DB for agent {agent_name}")
        except Exception as e:
            logger.error(f"Failed to log execution directly to DB: {e}")
            db.rollback()
        finally:
            db.close()
