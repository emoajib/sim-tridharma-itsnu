import logging
from datetime import datetime, timezone

from sqlalchemy import text

from agents.base_agent import BaseAgent
from database import SessionLocal

logger = logging.getLogger("agent.integrasi")


class IntegrasiAgent(BaseAgent):
    name = "integrasi"
    version = "1.0.0"

    def execute(self, data: dict) -> dict:
        missing = self.validate_input(data, ["sumber"])
        if missing:
            return {"status": "error", "message": f"Missing fields: {missing}"}

        sumber = data["sumber"]

        if sumber not in ("pddikti", "sinta", "sister"):
            return {"status": "error", "message": f"Sumber '{sumber}' tidak dikenal. Gunakan: pddikti, sinta, sister"}

        db = SessionLocal()
        try:
            total_ditarik = 0
            total_konflik = 0
            detail = []

            if sumber == "pddikti":
                total_ditarik, total_konflik, detail = self._sync_pddikti(db)
            elif sumber == "sinta":
                total_ditarik, total_konflik, detail = self._sync_sinta(db)
            elif sumber == "sister":
                total_ditarik, total_konflik, detail = self._sync_sister(db)

            self._log_sinkronisasi(db, sumber, total_ditarik, total_konflik)

            result = {
                "status": "completed",
                "sumber": sumber,
                "total_ditarik": total_ditarik,
                "total_konflik": total_konflik,
                "detail": detail,
            }

            self.log_execution(self.name, None, data, result)
            return result

        except Exception as e:
            logger.error(f"Integrasi error: {e}", exc_info=True)
            result = {"status": "error", "message": str(e)}
            self.log_execution(self.name, None, data, result, status="error", error_message=str(e))
            return result
        finally:
            db.close()

    def _sync_pddikti(self, db) -> tuple:
        logger.info("Simulating PD-DIKTI sync...")
        result = db.execute(
            text("""
                SELECT COUNT(*) FROM v_sync_pddikti_dosen
            """)
        ).scalar() or 0
        conflicts = db.execute(
            text("""
                SELECT COUNT(*) FROM v_sync_pddikti_dosen
                WHERE nidn IN (
                    SELECT nidn FROM dosen GROUP BY nidn HAVING COUNT(*) > 1
                )
            """)
        ).scalar() or 0
        return result, conflicts, [{"tabel": "dosen", "total": result, "konflik": conflicts}]

    def _sync_sinta(self, db) -> tuple:
        logger.info("Simulating SINTA sync...")
        result = db.execute(
            text("""
                SELECT COUNT(*) FROM v_sync_sinta_publikasi
            """)
        ).scalar() or 0
        return result, 0, [{"tabel": "publikasi", "total": result, "konflik": 0}]

    def _sync_sister(self, db) -> tuple:
        logger.info("Simulating SISTER sync...")
        result = db.execute(
            text("""
                SELECT COUNT(*) FROM v_sync_sister_riwayat
            """)
        ).scalar() or 0
        conflicts = db.execute(
            text("""
                SELECT COUNT(*) FROM v_sync_sister_riwayat
                WHERE dosen_id IN (
                    SELECT dosen_id FROM riwayat_pendidikan GROUP BY dosen_id, jenjang HAVING COUNT(*) > 1
                )
            """)
        ).scalar() or 0
        return result, conflicts, [{"tabel": "riwayat_pendidikan", "total": result, "konflik": conflicts}]

    def _log_sinkronisasi(self, db, sumber: str, total_ditarik: int, total_konflik: int):
        try:
            db.execute(
                text("""
                    INSERT INTO integrasi_log_sinkron
                        (sumber, jumlah_ditarik, jumlah_konflik, status, mulai_pada, jenis_data)
                    VALUES
                        (:sumber, :jumlah_ditarik, :jumlah_konflik, 'completed', :mulai_pada, :jenis_data)
                """),
                {
                    "sumber": sumber,
                    "jumlah_ditarik": total_ditarik,
                    "jumlah_konflik": total_konflik,
                    "mulai_pada": datetime.now(timezone.utc),
                    "jenis_data": sumber,
                },
            )
            db.commit()
        except Exception as e:
            logger.error(f"Failed to log sinkronisasi: {e}")
            db.rollback()
