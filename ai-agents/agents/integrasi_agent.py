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
        logger.info("Syncing PD-DIKTI from reconciliation data...")
        rows = db.execute(
            text("""
                SELECT
                    COALESCE(COUNT(*), 0) as total,
                    COALESCE(SUM(CASE WHEN rs.status = 'pending' THEN 1 ELSE 0 END), 0) as conflicts
                FROM reconciliation_suggestions rs
                WHERE rs.source_type LIKE '%pddikti%' OR rs.source_type LIKE '%dosen%'
            """)
        ).fetchone()
        total = rows.total if rows else 0
        conflicts = rows.conflicts if rows else 0

        db.execute(
            text("""
                INSERT INTO integrasi_log_sinkron
                    (sumber, jumlah_ditarik, jumlah_konflik, status, jenis_data, mulai_pada)
                VALUES
                    ('PDDIKTI', :total, :conflicts, 'completed', 'dosen', NOW())
                ON CONFLICT DO NOTHING
            """),
            {"total": total, "conflicts": conflicts}
        )
        db.commit()

        return total, conflicts, [{"tabel": "dosen", "total": total, "konflik": conflicts}]

    def _sync_sinta(self, db) -> tuple:
        logger.info("Syncing SINTA from reconciliation data...")
        rows = db.execute(
            text("""
                SELECT
                    COALESCE(COUNT(*), 0) as total,
                    COALESCE(SUM(CASE WHEN rs.status = 'pending' THEN 1 ELSE 0 END), 0) as conflicts
                FROM reconciliation_suggestions rs
                WHERE rs.source_type LIKE '%sinta%' OR rs.source_type = 'import_publikasi'
            """)
        ).fetchone()
        total = rows.total if rows else 0
        conflicts = rows.conflicts if rows else 0

        db.execute(
            text("""
                INSERT INTO integrasi_log_sinkron
                    (sumber, jumlah_ditarik, jumlah_konflik, status, jenis_data, mulai_pada)
                VALUES
                    ('SINTA', :total, :conflicts, 'completed', 'publikasi', NOW())
                ON CONFLICT DO NOTHING
            """),
            {"total": total, "conflicts": conflicts}
        )
        db.commit()

        return total, conflicts, [{"tabel": "publikasi", "total": total, "konflik": conflicts}]

    def _sync_sister(self, db) -> tuple:
        logger.info("Syncing SISTER from reconciliation data...")
        rows = db.execute(
            text("""
                SELECT
                    COALESCE(COUNT(*), 0) as total,
                    COALESCE(SUM(CASE WHEN rs.status = 'pending' THEN 1 ELSE 0 END), 0) as conflicts
                FROM reconciliation_suggestions rs
                WHERE rs.source_type LIKE '%sister%' OR rs.source_type = 'import_pendidikan'
            """)
        ).fetchone()
        total = rows.total if rows else 0
        conflicts = rows.conflicts if rows else 0

        db.execute(
            text("""
                INSERT INTO integrasi_log_sinkron
                    (sumber, jumlah_ditarik, jumlah_konflik, status, jenis_data, mulai_pada)
                VALUES
                    ('SISTER', :total, :conflicts, 'completed', 'pendidikan', NOW())
                ON CONFLICT DO NOTHING
            """),
            {"total": total, "conflicts": conflicts}
        )
        db.commit()

        return total, conflicts, [{"tabel": "riwayat_pendidikan", "total": total, "konflik": conflicts}]

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
