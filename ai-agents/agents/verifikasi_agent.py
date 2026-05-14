import hashlib
import logging
import os

from sqlalchemy import text

from agents.base_agent import BaseAgent
from database import SessionLocal

logger = logging.getLogger("agent.verifikasi")


class VerifikasiAgent(BaseAgent):
    name = "verifikasi"
    version = "1.0.0"

    def execute(self, data: dict) -> dict:
        missing = self.validate_input(data, ["dosen_id", "kegiatan_type", "kegiatan_id"])
        if missing:
            return {"status": "error", "message": f"Missing fields: {missing}"}

        dosen_id = data["dosen_id"]
        kegiatan_type = data["kegiatan_type"]
        kegiatan_id = data["kegiatan_id"]

        db = SessionLocal()
        try:
            file_record = db.execute(
                text("""
                    SELECT id, file_path, file_size, file_hash
                    FROM file_kegiatan
                    WHERE dosen_id = :dosen_id
                      AND kegiatan_type = :kegiatan_type
                      AND kegiatan_id = :kegiatan_id
                """),
                {"dosen_id": dosen_id, "kegiatan_type": kegiatan_type, "kegiatan_id": kegiatan_id},
            ).fetchone()

            if file_record is None:
                result = {
                    "status": "invalid",
                    "catatan": "File tidak ditemukan untuk kegiatan ini",
                    "confidence": 1.0,
                }
                self.log_execution(self.name, None, data, result)
                return result

            file_id, file_path, file_size, file_hash = file_record

            issues = []

            if not file_path or not os.path.isfile(file_path):
                issues.append("File fisik tidak ditemukan di penyimpanan")
            elif file_size is None or file_size <= 0:
                issues.append("Ukuran file tidak valid (0 byte atau null)")

            if file_hash:
                duplicate = db.execute(
                    text("""
                        SELECT COUNT(*) FROM file_kegiatan
                        WHERE file_hash = :file_hash AND id != :file_id
                    """),
                    {"file_hash": file_hash, "file_id": file_id},
                ).scalar()
                if duplicate and duplicate > 0:
                    issues.append(f"Hash file duplikat ditemukan ({duplicate} file lain)")

            if issues:
                result = {
                    "status": "need_review",
                    "catatan": "; ".join(issues),
                    "confidence": 0.7,
                }
            else:
                result = {
                    "status": "valid",
                    "catatan": "File terverifikasi",
                    "confidence": 0.95,
                }

            self.log_execution(self.name, None, data, result)
            return result

        except Exception as e:
            logger.error(f"Verifikasi error: {e}", exc_info=True)
            result = {"status": "error", "catatan": str(e), "confidence": 0.0}
            self.log_execution(self.name, None, data, result, status="error", error_message=str(e))
            return result
        finally:
            db.close()
