import logging

from sqlalchemy import text

from agents.base_agent import BaseAgent
from database import SessionLocal

logger = logging.getLogger("agent.rekomendasi")


class RekomendasiAgent(BaseAgent):
    name = "rekomendasi"
    version = "1.0.0"

    def execute(self, data: dict) -> dict:
        if isinstance(data, list):
            data = {}
        missing = self.validate_input(data, ["prodi_id"])
        if missing:
            return {"status": "error", "message": f"Missing fields: {missing}"}

        prodi_id = data["prodi_id"]

        db = SessionLocal()
        try:
            rows = db.execute(
                text("""
                    SELECT
                        i.id,
                        i.kode_indikator,
                        i.nama_indikator,
                        i.target,
                        pi.skor_tercapai,
                        pi.status,
                        i.bobot
                    FROM m_indikator_akreditasi i
                    LEFT JOIN trx_pemenuhan_indikator pi
                        ON pi.indikator_id = i.id AND pi.prodi_id = :prodi_id
                    WHERE pi.status IN ('merah', 'kuning')
                    ORDER BY i.bobot DESC
                    LIMIT 10
                """),
                {"prodi_id": prodi_id},
            ).fetchall()

            rekomendasi_list = []
            for r in rows:
                prio = "Tinggi" if r.bobot >= 4 else ("Sedang" if r.bobot >= 2 else "Rendah")
                rekomendasi_list.append({
                    "indikator_id": r.id,
                    "kode": r.kode_indikator,
                    "nama": r.nama_indikator,
                    "status_saat_ini": r.status,
                    "skor_saat_ini": r.skor_tercapai,
                    "target": r.target,
                    "prioritas": prio,
                    "saran": f"Tingkatkan pemenuhan pada indikator {r.kode_indikator} untuk mencapai target {r.target}.",
                })

            result = {
                "prodi_id": prodi_id,
                "rekomendasi": rekomendasi_list,
                "total_rekomendasi": len(rekomendasi_list),
            }

            # Log to database
            for rec in rekomendasi_list:
                db.execute(
                    text("""
                        INSERT INTO agent_rekomendasi_log
                            (prodi_id, indikator_id, prioritas, saran, is_read, created_at, updated_at)
                        VALUES
                            (:prodi_id, :i_id, :prio, :saran, false, NOW(), NOW())
                    """),
                    {
                        "prodi_id": prodi_id,
                        "i_id": rec["indikator_id"],
                        "prio": rec["prioritas"],
                        "saran": rec["saran"],
                    },
                )
            db.commit()

            self.log_execution(self.name, "system", data, result)
            return result

        except Exception as e:
            logger.error(f"RekomendasiAgent execution failed: {e}", exc_info=True)
            db.rollback()
            return {"status": "error", "message": str(e)}
        finally:
            db.close()
