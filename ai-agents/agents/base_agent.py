import logging
from abc import ABC, abstractmethod
from datetime import datetime, timezone

from sqlalchemy import text

from database import SessionLocal

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
        task_id: str | None,
        input_data: dict,
        output_data: dict,
        status: str = "success",
        error_message: str | None = None,
    ):
        db = SessionLocal()
        try:
            db.execute(
                text("""
                    INSERT INTO agent_execution_log
                        (agent_name, task_id, input_data, output_data, status, error_message, executed_at)
                    VALUES
                        (:agent_name, :task_id, :input_data, :output_data, :status, :error_message, :executed_at)
                """),
                {
                    "agent_name": agent_name,
                    "task_id": task_id,
                    "input_data": str(input_data),
                    "output_data": str(output_data),
                    "status": status,
                    "error_message": error_message,
                    "executed_at": datetime.now(timezone.utc),
                },
            )
            db.commit()
        except Exception as e:
            logger.error(f"Failed to log execution: {e}")
            db.rollback()
        finally:
            db.close()
