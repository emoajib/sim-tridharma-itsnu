import logging
import random

import numpy as np
from sqlalchemy import text

from agents.base_agent import BaseAgent
from database import SessionLocal

logger = logging.getLogger("agent.prediksi")


class PrediksiAgent(BaseAgent):
    name = "prediksi"
    version = "1.0.0"

    def execute(self, data: dict) -> dict:
        missing = self.validate_input(data, ["prodi_id", "periode_id"])
        if missing:
            return {"status": "error", "message": f"Missing fields: {missing}"}

        prodi_id = data["prodi_id"]
        periode_id = data["periode_id"]

        db = SessionLocal()
        try:
            rows = db.execute(
                text("""
                    SELECT pi.skor_tercapai, i.bobot, i.kategori
                    FROM trx_pemenuhan_indikator pi
                    JOIN indikator i ON i.id = pi.indikator_id
                    WHERE pi.prodi_id = :prodi_id AND pi.periode_id = :periode_id
                """),
                {"prodi_id": prodi_id, "periode_id": periode_id},
            ).fetchall()

            if not rows:
                result = {
                    "skor_prediksi": 0.0,
                    "confidence_interval": [0.0, 0.0],
                    "prob_unggul": 0.0,
                    "prob_baik_sekali": 0.0,
                    "prob_baik": 0.0,
                }
                self.log_execution(self.name, None, data, result)
                return result

            total_bobot = sum(r.bobot for r in rows if r.bobot)
            if total_bobot == 0:
                total_bobot = 1.0

            weighted_sum = sum(
                (r.skor_tercapai or 0) * (r.bobot or 1) for r in rows
            ) / total_bobot

            n_iter = 1000
            simulated = []
            for _ in range(n_iter):
                variation = random.uniform(-0.05, 0.05)
                sim_skor = sum(
                    (r.skor_tercapai or 0) * (1 + variation) * (r.bobot or 1)
                    for r in rows
                ) / total_bobot
                simulated.append(sim_skor)

            arr = np.array(simulated)
            mean_pred = float(np.mean(arr))
            lower = float(np.percentile(arr, 2.5))
            upper = float(np.percentile(arr, 97.5))

            prob_unggul = float(np.mean(arr >= 3.5))
            prob_baik_sekali = float(np.mean((arr >= 2.8) & (arr < 3.5)))
            prob_baik = float(np.mean((arr >= 2.0) & (arr < 2.8)))

            result = {
                "skor_prediksi": round(mean_pred, 4),
                "confidence_interval": [round(lower, 4), round(upper, 4)],
                "prob_unggul": round(prob_unggul, 4),
                "prob_baik_sekali": round(prob_baik_sekali, 4),
                "prob_baik": round(prob_baik, 4),
            }

            self.log_execution(self.name, None, data, result)
            return result

        except Exception as e:
            logger.error(f"Prediksi error: {e}", exc_info=True)
            result = {"status": "error", "message": str(e)}
            self.log_execution(self.name, None, data, result, status="error", error_message=str(e))
            return result
        finally:
            db.close()
