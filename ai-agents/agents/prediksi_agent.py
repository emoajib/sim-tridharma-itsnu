# Vetted by AI - Manual Review Required by Senior Engineer/Manager
import logging
import numpy as np
from scipy import stats

from sqlalchemy import text

from agents.base_agent import BaseAgent
from config import calculate_prediction
from database import SessionLocal

logger = logging.getLogger("agent.prediksi")


class PrediksiAgent(BaseAgent):
    name = "prediksi"
    version = "1.2.0"  # Updated version for Monte Carlo implementation

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

            # 3. Monte Carlo Simulation with 1000 samples
            if len(historical_scores) >= 2:
                # Calculate mean and standard deviation from historical data
                mean_score = np.mean(historical_scores)
                std_score = np.std(historical_scores, ddof=1)  # Sample standard deviation
                
                # Generate 1000 Monte Carlo samples
                np.random.seed(42)  # For reproducible results
                mc_samples = np.random.normal(mean_score, std_score, 1000)
                
                # Calculate statistics from Monte Carlo samples
                mc_mean = np.mean(mc_samples)
                mc_std = np.std(mc_samples, ddof=1)
                
                # Calculate 95% confidence interval
                ci_lower = np.percentile(mc_samples, 2.5)
                ci_upper = np.percentile(mc_samples, 97.5)
                
                # Calculate probabilities for each category based on MC samples
                unggul_count = np.sum(mc_samples >= 350)  # Assuming 350+ is unggul
                baik_sekali_count = np.sum((mc_samples >= 300) & (mc_samples < 350))  # 300-349
                baik_count = np.sum(mc_samples < 300)  # Below 300
                
                prob_unggul = unggul_count / len(mc_samples)
                prob_baik_sekali = baik_sekali_count / len(mc_samples)
                prob_baik = baik_count / len(mc_samples)
                
                # Use the mean of MC samples as the predicted score
                skor_prediksi = mc_mean
                trend_analysis = "Stagnan"  # Will be updated below
                
                # Calculate trend factor from historical data
                if len(historical_scores) >= 2:
                    x = np.arange(len(historical_scores))
                    y = np.array(historical_scores)
                    slope, _ = np.polyfit(x, y, 1)
                    trend_factor = 1.0 + (slope / 100.0)
                    trend_analysis = "Positif" if trend_factor > 1 else ("Negatif" if trend_factor < 1 else "Stagnan")
                    
                    # Apply trend factor to MC mean
                    skor_prediksi = mc_mean * trend_factor
                    
                    # Recalculate probabilities with trend-adjusted score
                    # Simplified approach: adjust the distributions
                    adjusted_samples = mc_samples * trend_factor
                    unggul_count = np.sum(adjusted_samples >= 350)
                    baik_sekali_count = np.sum((adjusted_samples >= 300) & (adjusted_samples < 350))
                    baik_count = np.sum(adjusted_samples < 300)
                    
                    prob_unggul = unggul_count / len(adjusted_samples)
                    prob_baik_sekali = baik_sekali_count / len(adjusted_samples)
                    prob_baik = baik_count / len(adjusted_samples)
                    
                    # Update MC mean with trend
                    mc_mean = np.mean(adjusted_samples)
                    mc_std = np.std(adjusted_samples, ddof=1)
                    ci_lower = np.percentile(adjusted_samples, 2.5)
                    ci_upper = np.percentile(adjusted_samples, 97.5)
            else:
                # Not enough data for Monte Carlo, fall back to original method
                pred = calculate_prediction(historical_scores)
                skor_prediksi = pred["skor_prediksi"]
                prob_unggul = pred["probabilitas"]["unggul"]
                prob_baik_sekali = pred["probabilitas"]["baik_sekali"]
                prob_baik = pred["probabilitas"]["baik"]
                trend_analysis = pred["trend_analysis"]
                ci_lower = skor_prediksi - 4.5
                ci_upper = skor_prediksi + 4.5
                mc_mean = skor_prediksi
                mc_std = 0

            # 3.5 IKU Achievement Prediction
            iku_rows = db.execute(
                text("""
                    SELECT ci.target, ci.capaian, i.kode_iku, i.satuan, i.bobot as iku_bobot
                    FROM trx_cascading_iku ci
                    JOIN m_indikator_iku i ON i.id = ci.iku_id
                    WHERE ci.unit_type = 'Prodi' AND ci.unit_id = :prodi_id
                      AND ci.periode_id = :periode_id AND ci.target > 0
                """),
                {"prodi_id": prodi_id, "periode_id": periode_id},
            ).fetchall()

            iku_predictions = []
            for ir in iku_rows:
                pct = (float(ir.capaian) / float(ir.target)) * 100
                iku_predictions.append({
                    "kode_iku": ir.kode_iku,
                    "target": float(ir.target),
                    "capaian": float(ir.capaian),
                    "capaian_persen": round(pct, 2),
                    "satuan": ir.satuan,
                    "status": "tercapai" if pct >= 100 else ("on_track" if pct >= 75 else "perlu_perhatian"),
                })

            # 4. Analyze Budget Correlation
            budget_impact = "netral"
            if periode_id in budgets:
                current_budget = budgets[periode_id]
                prev_budget = sum(budgets.values()) / len(budgets) if budgets else current_budget
                if current_budget > prev_budget * 1.2:
                    budget_impact = "positif (peningkatan investasi)"
                    skor_prediksi += 0.5 # Small boost for high investment
                    # Adjust confidence interval slightly
                    ci_lower += 0.5
                    ci_upper += 0.5
                elif current_budget < prev_budget * 0.8:
                    budget_impact = "negatif (pengurangan anggaran)"
                    skor_prediksi -= 0.3
                    # Adjust confidence interval slightly
                    ci_lower -= 0.3
                    ci_upper -= 0.3

            # Determine final category based on highest probability
            probabilities = {
                "unggul": prob_unggul,
                "baik_sekali": prob_baik_sekali,
                "baik": prob_baik
            }
            predicted_category = max(probabilities, key=probabilities.get)

            result = {
                "skor_prediksi": round(skor_prediksi, 2),
                "probabilitas": {
                    "unggul": round(prob_unggul, 2),
                    "baik_sekali": round(prob_baik_sekali, 2),
                    "baik": round(prob_baik, 2),
                },
                "confidence_interval": round((ci_upper - ci_lower) / 2, 2),  # Half-width as before
                "confidence_interval_details": {
                    "lower": round(ci_lower, 2),
                    "upper": round(ci_upper, 2)
                },
                "trend_analysis": trend_analysis,
                "historical_data_points": len(historical_scores),
                "budget_analysis": budget_impact,
                "method": "monte_carlo",
                "mc_samples": 1000,
                "mc_mean": round(mc_mean, 2),
                "mc_std": round(mc_std, 2),
                "iku_predictions": iku_predictions
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

