import logging

from sqlalchemy import text

from agents.base_agent import BaseAgent
from database import SessionLocal

logger = logging.getLogger("agent.rekomendasi")


class RekomendasiAgent(BaseAgent):
    name = "rekomendasi"
    version = "1.0.0"

    def execute(self, data: dict) -> dict:
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
                    FROM indikator i
                    LEFT JOIN trx_pemenuhan_indikator pi
                        ON pi.indikator_id = i.id AND pi.prodi_id = :prodi_id
                    WHERE pi.status IN ('merah', 'kuning')
                    ORDER BY
                        CASE pi.status WHEN 'merah' THEN 0 ELSE 1 END,
                        i.bobot DESC
                """),
                {"prodi_id": prodi_id},
            ).fetchall()

            recommendations = []
            for r in rows:
                skor = r.skor_tercapai or 0
                target = r.target or 0
                gap = target - skor

                priority = "high" if r.status == "merah" else "medium"

                recommendations.append({
                    "indikator_id": r.id,
                    "kode": r.kode_indikator,
                    "nama": r.nama_indikator,
                    "skor_saat_ini": skor,
                    "target": target,
                    "gap": round(gap, 2),
                    "status": r.status,
                    "priority": priority,
                    "rekomendasi": self._generate_recomendation(r.nama_indikator, skor, target, gap, priority),
                })

            result = {"rekomendasi": recommendations, "total": len(recommendations)}
            self.log_execution(self.name, None, data, result)
            return result

        except Exception as e:
            logger.error(f"Rekomendasi error: {e}", exc_info=True)
            result = {"status": "error", "message": str(e)}
            self.log_execution(self.name, None, data, result, status="error", error_message=str(e))
            return result
        finally:
            db.close()

    def _generate_recomendation(self, nama: str, skor: float, target: float, gap: float, priority: str) -> str:
        if priority == "high":
            return f"Segera tingkatkan capaian '{nama}' (gap {gap:.2f} dari target {target:.2f}) — prioritas utama"
        return f"Tingkatkan capaian '{nama}' (gap {gap:.2f} dari target {target:.2f})"
