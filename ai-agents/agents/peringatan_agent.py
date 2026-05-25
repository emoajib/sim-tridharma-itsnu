# Vetted by AI - Manual Review Required by Senior Engineer/Manager
import logging
from datetime import date, datetime
import numpy as np
from scipy import stats

from sqlalchemy import text

from agents.base_agent import BaseAgent
from database import SessionLocal

logger = logging.getLogger("agent.peringatan")


class PeringatanAgent(BaseAgent):
    name = "peringatan"
    version = "1.2.0"  # Updated version for smart/anomaly detection implementation

    def execute(self, data: dict) -> dict:
        if isinstance(data, list):
            data = {}
        prodi_id = data.get("prodi_id")
        db = SessionLocal()
        try:
            warnings = []
            warnings.extend(self._check_bkd(db, prodi_id))
            warnings.extend(self._check_kalibrasi(db, prodi_id))
            warnings.extend(self._check_akreditasi(db, prodi_id))
            warnings.extend(self._check_rkat_absorption(db, prodi_id))
            # Add smart anomaly detection checks
            warnings.extend(self._check_smart_rkat_anomaly(db, prodi_id))
            warnings.extend(self._check_smart_bkd_anomaly(db, prodi_id))
            # IKU cascade achievement check
            warnings.extend(self._check_iku_cascading(db, prodi_id))

            for w in warnings:
                db.execute(
                    text("""
                        INSERT INTO agent_peringatan_log (prodi_id, dosen_id, jenis_peringatan, tingkat, pesan, created_at, updated_at)
                        VALUES (:prodi_id, :dosen_id, :jenis, :tingkat, :pesan, NOW(), NOW())
                    """),
                    {
                        "prodi_id": prodi_id,
                        "dosen_id": w.get("dosen_id"),
                        "jenis": w.get("kategori", "general"),
                        "tingkat": w["level"],
                        "pesan": f"{w['judul']}: {w['deskripsi']}",
                    },
                )
            db.commit()

            result = {"warnings": warnings, "total": len(warnings), "prodi_id": prodi_id}
            self.log_execution(self.name, None, data, result)
            return result

        except Exception as e:
            logger.error(f"Peringatan error: {e}", exc_info=True)
            result = {"status": "error", "message": str(e)}
            self.log_execution(self.name, None, data, result, status="error", error_message=str(e))
            return result
        finally:
            db.close()

    def _check_rkat_absorption(self, db, prodi_id: int | None) -> list:
        """Check for low RKAT budget absorption (Dana Mandek) - Original static threshold version."""
        if not prodi_id:
            return []
             
        sql = """
            SELECT pagu_total, terpakai, periode_id
            FROM trx_rkat_pagu
            WHERE unit_type = 'Prodi' AND unit_id = :prodi_id
            ORDER BY created_at DESC LIMIT 1
        """
        row = db.execute(text(sql), {"prodi_id": prodi_id}).first()
         
        if not row or row.pagu_total <= 0:
            return []
             
        absorption = (float(row.terpakai) / float(row.pagu_total)) * 100
        today = date.today()
        month = today.month
         
        warnings = []
        # Logic: If after June (month > 6) and absorption < 40%, it's a warning
        if month > 6 and absorption < 40:
            warnings.append({
                "level": "warning" if absorption > 20 else "critical",
                "kategori": "rkat",
                "judul": "Dana RKAT Mandek",
                "deskripsi": f"Penyerapan anggaran baru {absorption:.1f}% pada bulan ke-{month}. Segera lakukan eksekusi program.",
                "dosen_id": None,
            })
        elif absorption < 10 and month > 3:
            warnings.append({
                "level": "warning",
                "kategori": "rkat",
                "judul": "Penyerapan RKAT Rendah",
                "deskripsi": f"Penyerapan anggaran sangat rendah ({absorption:.1f}%) di kuartal pertama.",
                "dosen_id": None,
            })
             
        return warnings

    def _check_smart_rkat_anomaly(self, db, prodi_id: int | None) -> list:
        """Smart RKAT absorption analysis using anomaly detection and trend analysis."""
        if not prodi_id:
            return []
        
        try:
            # Get historical RKAT absorption data for the last 12 months
            sql = """
                SELECT 
                    pagu_total, 
                    terpakai, 
                    periode_id,
                    DATE_FORMAT(created_at, '%Y-%m') as bulan
                FROM trx_rkat_pagu
                WHERE unit_type = 'Prodi' AND unit_id = :prodi_id
                ORDER BY created_at DESC
                LIMIT 12
            """
            rows = db.execute(text(sql), {"prodi_id": prodi_id}).fetchall()
            
            if len(rows) < 3:  # Need minimum data for statistical analysis
                return []
            
            # Calculate absorption percentages
            absorptions = []
            for row in rows:
                if row.pagu_total > 0:
                    absorption = (float(row.terpakai) / float(row.pagu_total)) * 100
                    absorptions.append(absorption)
            
            if len(absorptions) < 3:
                return []
            
            # Convert to numpy array for statistical analysis
            absorptions_array = np.array(absorptions)
            
            # Calculate basic statistics
            mean_absorption = np.mean(absorptions_array)
            std_absorption = np.std(absorptions_array, ddof=1)
            
            # Current absorption (most recent)
            current_absorption = absorptions_array[0] if len(absorptions_array) > 0 else 0
            
            warnings = []
            
            # Anomaly detection using Z-score (if we have enough data and variation)
            if std_absorption > 1.0 and len(absorptions_array) >= 5:
                z_score = abs((current_absorption - mean_absorption) / std_absorption)
                
                # If current absorption is unusually low (more than 2 standard deviations below mean)
                if current_absorption < mean_absorption and z_score > 2.0:
                    # Calculate probability of this being an anomaly
                    anomaly_prob = stats.norm.cdf(-z_score)  # One-tailed test
                    
                    if anomaly_prob < 0.05:  # Significant at p<0.05
                        warnings.append({
                            "level": "warning" if anomaly_prob > 0.01 else "critical",
                            "kategori": "rkat_anomaly",
                            "judul": "Anomali Penyerapan RKAT Terdeteksi",
                            "deskripsi": f"Penyerapan RKAT ({current_absorption:.1f}%) secara signifikan lebih rendah dari rata-rata historis ({mean_absorption:.1f}% ± {std_absorption:.1f}%). Probabilitas anomali: {anomaly_prob:.1%}",
                            "dosen_id": None,
                        })
            
            # Trend analysis: Check for declining trend
            if len(absorptions) >= 4:
                # Simple linear regression to detect trend
                x = np.arange(len(absorptions))
                slope, intercept, r_value, p_value, std_err = stats.linregress(x, absorptions)
                
                # If there's a significant negative trend (declining absorption)
                if slope < -0.5 and p_value < 0.1:  # Declining by more than 0.5% per period with 90% confidence
                    warnings.append({
                        "level": "warning",
                        "kategori": "rkat_trend",
                        "judul": "Trend Penyerapan RKAT Menurun",
                        "deskripsi": f"Penyerapan RKAT menunjukkan trend menurun signifikan ({slope:.2f}% per periode). Dari {absorptions[-1]:.1f}% ke {absorptions[0]:.1f}% dalam {len(absorptions)} periode terakhir.",
                        "dosen_id": None,
                    })
            
            return warnings
            
        except Exception as e:
            logger.warning(f"Smart RKAT anomaly detection failed: {e}")
            return []  # Fall back to original method if smart detection fails

    def _check_bkd(self, db, prodi_id: int | None) -> list:
        sql = """
            SELECT d.id as dosen_id, d.nama_depan, d.nidn, COALESCE(b.total_sks, 0) AS total_sks
            FROM m_dosen d
            LEFT JOIN trx_bkd b ON b.dosen_id = d.id
                AND b.status = 'disetujui'
            WHERE d.is_active = TRUE AND d.deleted_at IS NULL
        """
        params = {}
        if prodi_id:
            sql += " AND d.prodi_id = :prodi_id"
            params["prodi_id"] = prodi_id
        sql += " GROUP BY d.id, d.nama_depan, d.nidn, b.total_sks HAVING COALESCE(b.total_sks, 0) < 12"

        rows = db.execute(text(sql), params).fetchall()
        warnings = []
        for r in rows:
            warnings.append({
                "level": "critical" if r.total_sks == 0 else "warning",
                "kategori": "bkd",
                "judul": "BKD di bawah minimal 12 SKS",
                "deskripsi": f"{r.nama_depan} ({r.nidn}) hanya {r.total_sks} SKS",
                "dosen_id": r.dosen_id,
            })
        return warnings

    def _check_smart_bkd_anomaly(self, db, prodi_id: int | None) -> list:
        """Smart BKD analysis using anomaly detection to find unusual workload patterns."""
        if not prodi_id:
            return []
        
        try:
            # Get historical BKD data for dosen in this prodi
            sql = """
                SELECT 
                    d.id as dosen_id,
                    d.nama_depan,
                    d.nidn,
                    COALESCE(b.total_sks, 0) AS total_sks,
                    b.created_at
                FROM m_dosen d
                LEFT JOIN trx_bkd b ON b.dosen_id = d.id AND b.status = 'disetujui'
                WHERE d.is_active = TRUE AND d.deleted_at IS NULL
            """
            params = {}
            if prodi_id:
                sql += " AND d.prodi_id = :prodi_id"
                params["prodi_id"] = prodi_id
            sql += " ORDER BY b.created_at DESC LIMIT 50"  # Get recent records
            
            rows = db.execute(text(sql), params).fetchall()
            
            if len(rows) < 5:  # Need minimum data for analysis
                return []
            
            # Extract SKS values
            sks_values = [float(row.total_sks) for row in rows if row.total_sks is not None]
            
            if len(sks_values) < 5:
                return []
            
            # Convert to numpy array
            sks_array = np.array(sks_values)
            
            # Calculate statistics
            mean_sks = np.mean(sks_array)
            std_sks = np.std(sks_array, ddof=1)
            
            warnings = []
            
            # Detect dosen with unusually low or high SKS using modified Z-score
            # Using median and MAD (Median Absolute Deviation) for robustness against outliers
            median_sks = np.median(sks_array)
            mad = np.median(np.abs(sks_array - median_sks))
            
            # Modified Z-score threshold (typically 3.5 for outlier detection)
            modified_z_threshold = 3.5
            
            for row in rows:
                if row.total_sks is None:
                    continue
                    
                sks = float(row.total_sks)
                
                # Avoid division by zero
                if mad == 0:
                    continue
                    
                # Modified Z-score
                modified_z = 0.6745 * (sks - median_sks) / mad
                
                # Check for unusually low SKS (potential underutilization)
                if modified_z < -modified_z_threshold and sks < 8:  # Significantly below normal
                    warnings.append({
                        "level": "info",  # Info level for anomalies that aren't critical
                        "kategori": "bkd_anomaly",
                        "judul": f"BKD {row.nama_depan} Tidak Typical",
                        "deskripsi": f"BKD {row.nama_depan} ({row.nidn}) hanya {sks} SKS, yang sangat berbeda dari dosen lain (median: {median_sks:.1f} SKS).",
                        "dosen_id": row.dosen_id,
                    })
                # Check for unusually high SKS (potential overload)
                elif modified_z > modified_z_threshold and sks > 25:  # Significantly above normal
                    warnings.append({
                        "level": "warning",
                        "kategori": "bkd_anomaly",
                        "judul": f"BKD {row.nama_depan} Potensi Overload",
                        "deskripsi": f"BKD {row.nama_depan} ({row.nidn}) mencapai {sks} SKS, jauh di atas norma ({median_sks:.1f} SKS). Periksa distribusi beban kerja.",
                        "dosen_id": row.dosen_id,
                    })
            
            return warnings
            
        except Exception as e:
            logger.warning(f"Smart BKD anomaly detection failed: {e}")
            return []  # Fall back to original method if smart detection fails

    def _check_kalibrasi(self, db, prodi_id: int | None) -> list:
        today = date.today()
        sql = "SELECT id, prodi_id, nama_sarana, tanggal_kalibrasi, tanggal_kalibrasi_berikut FROM m_sarana WHERE deleted_at IS NULL"
        params = {}
        if prodi_id:
            sql += " AND prodi_id = :prodi_id"
            params["prodi_id"] = prodi_id

        rows = db.execute(text(sql), params).fetchall()
        warnings = []
        for r in rows:
            if r.tanggal_kalibrasi_berikut is None:
                continue
            expiry = r.tanggal_kalibrasi_berikut
            if isinstance(expiry, datetime):
                expiry = expiry.date()
            days_left = (expiry - today).days

            if days_left <= 0:
                level = "critical"
            elif days_left <= 30:
                level = "warning"
            else:
                continue

            warnings.append({
                "level": level,
                "kategori": "kalibrasi",
                "judul": f"Kalibrasi {r.nama_sarana}",
                "deskripsi": f"{r.nama_sarana} {'kadaluarsa' if days_left <=0 else f'tersisa {days_left} hari'} (jatuh tempo: {r.tanggal_kalibrasi_berikut})",
                "dosen_id": None,
            })
        return warnings

    def _check_iku_cascading(self, db, prodi_id: int | None) -> list:
        """Check IKU cascade target achievement for a prodi."""
        if not prodi_id:
            return []

        sql = """
            SELECT ci.id, ci.target, ci.capaian, ci.unit_id, i.kode_iku, i.nama_iku, i.satuan, p.nama_periode
            FROM trx_cascading_iku ci
            JOIN m_indikator_iku i ON i.id = ci.iku_id
            LEFT JOIN m_periode_akademik p ON p.id = ci.periode_id
            WHERE ci.unit_type = 'Prodi' AND ci.unit_id = :prodi_id
              AND ci.target > 0
            ORDER BY p.created_at DESC, i.kode_iku
        """
        rows = db.execute(text(sql), {"prodi_id": prodi_id}).fetchall()

        warnings = []
        for r in rows:
            pct = (float(r.capaian) / float(r.target)) * 100
            level = "info"
            if pct < 50:
                level = "critical"
            elif pct < 75:
                level = "warning"

            if level != "info":
                warnings.append({
                    "level": level,
                    "kategori": "iku_cascading",
                    "judul": f"IKU {r.kode_iku} Tidak On Track",
                    "deskripsi": f"{r.kode_iku} ({r.nama_iku}) baru tercapai {pct:.1f}% dari target {r.target} {r.satuan}. Segera lakukan aksi korektif.",
                    "dosen_id": None,
                })
        return warnings

    def _check_akreditasi(self, db, prodi_id: int | None) -> list:
        today = date.today()
        sql = "SELECT id, nama_prodi, akreditasi, tanggal_kadaluarsa FROM m_prodi WHERE deleted_at IS NULL"
        params = {}
        if prodi_id:
            sql += " AND id = :prodi_id"
            params["prodi_id"] = prodi_id

        rows = db.execute(text(sql), params).fetchall()
        warnings = []
        for r in rows:
            if r.tanggal_kadaluarsa is None:
                continue
            expiry = r.tanggal_kadaluarsa
            if isinstance(expiry, datetime):
                expiry = expiry.date()
            days_left = (expiry - today).days

            if days_left <= 0:
                level = "critical"
                msg = f"Akreditasi {r.nama_prodi} sudah kadaluarsa"
            elif days_left <= 180:
                level = "warning"
                msg = f"Akreditasi {r.nama_prodi} akan kadaluarsa dalam {days_left} hari"
            elif days_left <= 365:
                level = "info"
                msg = f"Akreditasi {r.nama_prodi} tersisa {days_left} hari"
            else:
                continue

            warnings.append({
                "level": level,
                "kategori": "akreditasi",
                "judul": msg,
                "deskripsi": f"Status: {r.akreditasi or 'N/A'}, Kadaluarsa: {r.tanggal_kadaluarsa}",
                "dosen_id": None,
            })
        return warnings
