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
        if isinstance(data, list):
            data = {}
        missing = self.validate_input(data, ["prodi_id", "periode_id"])
        if missing:
            return {"status": "error", "message": f"Missing fields: {missing}"}

        prodi_id = data["prodi_id"]
        periode_id = data["periode_id"]

        db = SessionLocal()
        try:
            # Fetch retrospective data (TS-2 to TS) for LAMEMBA 2.0 compliance
            rows = db.execute(
                text("""
                    SELECT pi.periode_id, pi.skor_tercapai, i.bobot, i.kriteria
                    FROM trx_pemenuhan_indikator pi
                    JOIN m_indikator_akreditasi i ON i.id = pi.indikator_id
                    WHERE pi.prodi_id = :prodi_id 
                      AND pi.periode_id <= :periode_id
                    ORDER BY pi.periode_id DESC
                """),
                {"prodi_id": prodi_id, "periode_id": periode_id},
            ).fetchall()

            if not rows:
                return {
                    "status": "warning",
                    "message": "Data indikator tidak ditemukan untuk prodi ini.",
                }

            # Group by periode to calculate trend
            period_scores = {}
            for r in rows:
                p_id = r.periode_id
                if p_id not in period_scores:
                    period_scores[p_id] = {'skor': 0, 'bobot': 0}
                period_scores[p_id]['skor'] += r.skor_tercapai * r.bobot
                period_scores[p_id]['bobot'] += r.bobot

            # Calculate historical scores
            historical_scores = []
            for p_id in sorted(period_scores.keys()):
                ts = period_scores[p_id]
                historical_scores.append(ts['skor'] / ts['bobot'] if ts['bobot'] > 0 else 0)

            # Keep only last 3 periods (TS-2, TS-1, TS)
            historical_scores = historical_scores[-3:]
            
            # Base score is the latest
            base_score = historical_scores[-1] if historical_scores else 0

            # Calculate trend factor (prospektif)
            trend_factor = 1.0
            if len(historical_scores) >= 2:
                # Simple linear trend
                x = np.arange(len(historical_scores))
                y = np.array(historical_scores)
                slope, _ = np.polyfit(x, y, 1)
                # If slope is positive, slight boost. If negative, slight penalty.
                trend_factor = 1.0 + (slope / 100.0)

            skor_final = base_score * trend_factor

            # Calculate probabilities based on trend-adjusted score
            prob_unggul = min(0.95, max(0.05, (skor_final - 300) / 100)) if skor_final > 300 else 0.05
            prob_baik_sekali = min(0.9, max(0.1, (skor_final - 200) / 150))
            prob_baik = 1.0 - prob_unggul - prob_baik_sekali

            # Normalize probabilities
            total_p = prob_unggul + prob_baik_sekali + prob_baik
            prob_unggul /= total_p
            prob_baik_sekali /= total_p
            prob_baik /= total_p

            result = {
                "skor_prediksi": round(skor_final, 2),
                "probabilitas": {
                    "unggul": round(prob_unggul, 2),
                    "baik_sekali": round(prob_baik_sekali, 2),
                    "baik": round(prob_baik, 2),
                },
                "confidence_interval": 4.5,
                "trend_analysis": "Positif" if trend_factor > 1 else ("Negatif" if trend_factor < 1 else "Stagnan"),
                "historical_data_points": len(historical_scores)
            }

            # Log to database
            db.execute(
                text("""
                    INSERT INTO agent_prediction_history
                        (prodi_id, periode_id, skor_prediksi, probabilitas_unggul, probabilitas_baik_sekali, probabilitas_baik, confidence_interval, created_at, updated_at)
                    VALUES
                        (:prodi_id, :periode_id, :skor, :p_unggul, :p_bs, :p_baik, :ci, NOW(), NOW())
                """),
                {
                    "prodi_id": prodi_id,
                    "periode_id": periode_id,
                    "skor": result["skor_prediksi"],
                    "p_unggul": result["probabilitas"]["unggul"],
                    "p_bs": result["probabilitas"]["baik_sekali"],
                    "p_baik": result["probabilitas"]["baik"],
                    "ci": result["confidence_interval"],
                },
            )
            db.commit()

            self.log_execution(self.name, "system", data, result)
            return result

        except Exception as e:
            logger.error(f"PrediksiAgent execution failed: {e}", exc_info=True)
            db.rollback()
            self.log_execution(self.name, None, data, {"status": "error", "message": str(e)}, status="error", error_message=str(e))
            return {"status": "error", "message": str(e)}
        finally:
            db.close()
