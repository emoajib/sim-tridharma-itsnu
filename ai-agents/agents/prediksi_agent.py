# Vetted by AI - Manual Review Required by Senior Engineer/Manager
import logging

from sqlalchemy import text

from agents.base_agent import BaseAgent
from config import calculate_prediction
from database import SessionLocal

logger = logging.getLogger("agent.prediksi")


class PrediksiAgent(BaseAgent):
    name = "prediksi"
    version = "1.1.0"

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
            # 1. Get indicator scores
            rows = db.execute(
                text("""
                    SELECT pi.periode_id, pi.nilai, i.bobot, i.kriteria
                    FROM trx_pemenuhan_indikator pi
                    JOIN m_indikator_akreditasi i ON i.id = pi.indikator_id
                    WHERE pi.prodi_id = :prodi_id 
                      AND pi.periode_id <= :periode_id
                    ORDER BY pi.periode_id DESC
                """),
                {"prodi_id": prodi_id, "periode_id": periode_id},
            ).fetchall()

            if not rows:
                result = {
                    "status": "warning",
                    "message": "Data indikator tidak ditemukan untuk prodi ini.",
                }
                self.log_execution(self.name, "system", data, result, status="warning")
                return result

            # 2. Get Budget Data (RKAT)
            budget_rows = db.execute(
                text("""
                    SELECT SUM(estimasi_biaya) as total_biaya, periode_id
                    FROM trx_usulan_rkat
                    WHERE prodi_id = :prodi_id AND status = 'approved'
                    GROUP BY periode_id
                """),
                {"prodi_id": prodi_id}
            ).fetchall()
            
            budgets = {b.periode_id: float(b.total_biaya) for b in budget_rows}

            period_scores = {}
            for r in rows:
                p_id = r.periode_id
                if p_id not in period_scores:
                    period_scores[p_id] = {'skor': 0, 'bobot': 0}
                period_scores[p_id]['skor'] += float(r.nilai) * r.bobot
                period_scores[p_id]['bobot'] += r.bobot

            historical_scores = []
            for p_id in sorted(period_scores.keys()):
                ts = period_scores[p_id]
                historical_scores.append(ts['skor'] / ts['bobot'] if ts['bobot'] > 0 else 0)

            historical_scores = historical_scores[-3:]

            pred = calculate_prediction(historical_scores)
            
            # 3. Analyze Budget Correlation
            budget_impact = "netral"
            if periode_id in budgets:
                current_budget = budgets[periode_id]
                prev_budget = sum(budgets.values()) / len(budgets) if budgets else current_budget
                if current_budget > prev_budget * 1.2:
                    budget_impact = "positif (peningkatan investasi)"
                    pred["skor_prediksi"] += 0.5 # Small boost for high investment
                elif current_budget < prev_budget * 0.8:
                    budget_impact = "negatif (pengurangan anggaran)"
                    pred["skor_prediksi"] -= 0.3

            result = {
                "skor_prediksi": round(pred["skor_prediksi"], 2),
                "probabilitas": pred["probabilitas"],
                "confidence_interval": 4.5,
                "trend_analysis": pred["trend_analysis"],
                "historical_data_points": len(historical_scores),
                "budget_analysis": budget_impact,
            }

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

