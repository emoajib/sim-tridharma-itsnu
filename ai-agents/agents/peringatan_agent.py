import logging
from datetime import date, datetime

from sqlalchemy import text

from agents.base_agent import BaseAgent
from database import SessionLocal

logger = logging.getLogger("agent.peringatan")


class PeringatanAgent(BaseAgent):
    name = "peringatan"
    version = "1.0.0"

    def execute(self, data: dict) -> dict:
        prodi_id = data.get("prodi_id")
        db = SessionLocal()
        try:
            warnings = []

            bkd_warnings = self._check_bkd(db, prodi_id)
            warnings.extend(bkd_warnings)

            kalibrasi_warnings = self._check_kalibrasi(db, prodi_id)
            warnings.extend(kalibrasi_warnings)

            akreditasi_warnings = self._check_akreditasi(db, prodi_id)
            warnings.extend(akreditasi_warnings)

            result = {
                "warnings": warnings,
                "total": len(warnings),
                "prodi_id": prodi_id,
            }

            self.log_execution(self.name, None, data, result)
            return result

        except Exception as e:
            logger.error(f"Peringatan error: {e}", exc_info=True)
            result = {"status": "error", "message": str(e)}
            self.log_execution(self.name, None, data, result, status="error", error_message=str(e))
            return result
        finally:
            db.close()

    def _check_bkd(self, db, prodi_id: int | None) -> list:
        if prodi_id:
            rows = db.execute(
                text("""
                    SELECT d.nama, d.nidn, COALESCE(SUM(b.sks_diampu), 0) AS total_sks
                    FROM dosen d
                    LEFT JOIN bkd b ON b.dosen_id = d.id
                        AND b.tahun = EXTRACT(YEAR FROM CURRENT_DATE)
                    WHERE d.prodi_id = :prodi_id AND d.aktif = TRUE
                    GROUP BY d.id, d.nama, d.nidn
                    HAVING COALESCE(SUM(b.sks_diampu), 0) < 12
                """),
                {"prodi_id": prodi_id},
            ).fetchall()
        else:
            rows = db.execute(
                text("""
                    SELECT d.nama, d.nidn, COALESCE(SUM(b.sks_diampu), 0) AS total_sks
                    FROM dosen d
                    LEFT JOIN bkd b ON b.dosen_id = d.id
                        AND b.tahun = EXTRACT(YEAR FROM CURRENT_DATE)
                    WHERE d.aktif = TRUE
                    GROUP BY d.id, d.nama, d.nidn
                    HAVING COALESCE(SUM(b.sks_diampu), 0) < 12
                """)
            ).fetchall()

        warnings = []
        for r in rows:
            warnings.append({
                "level": "warning",
                "kategori": "bkd",
                "judul": f"BKD di bawah minimal",
                "deskripsi": f"Dosen {r.nama} ({r.nidn}) hanya memiliki {r.total_sks} SKS dari minimal 12 SKS",
                "entity": {"nidn": r.nidn, "total_sks": r.total_sks},
            })
        return warnings

    def _check_kalibrasi(self, db, prodi_id: int | None) -> list:
        today = date.today()

        if prodi_id:
            rows = db.execute(
                text("""
                    SELECT s.nama_sarana, s.kode_sarana, s.tgl_kalibrasi_terakhir, s.periode_kalibrasi_hari
                    FROM sarana s
                    WHERE s.prodi_id = :prodi_id
                """),
                {"prodi_id": prodi_id},
            ).fetchall()
        else:
            rows = db.execute(
                text("""
                    SELECT s.nama_sarana, s.kode_sarana, s.tgl_kalibrasi_terakhir, s.periode_kalibrasi_hari
                    FROM sarana s
                """)
            ).fetchall()

        warnings = []
        for r in rows:
            if r.tgl_kalibrasi_terakhir is None or r.periode_kalibrasi_hari is None:
                continue
            last_cal = r.tgl_kalibrasi_terakhir
            if isinstance(last_cal, datetime):
                last_cal = last_cal.date()
            days_since = (today - last_cal).days

            if days_since > r.periode_kalibrasi_hari:
                level = "critical"
            elif days_since > r.periode_kalibrasi_hari * 0.8:
                level = "warning"
            else:
                continue

            warnings.append({
                "level": level,
                "kategori": "kalibrasi",
                "judul": f"Kalibrasi {r.nama_sarana} perlu diperbarui",
                "deskripsi": f"Sarana '{r.nama_sarana}' ({r.kode_sarana}) — {days_since} hari sejak kalibrasi terakhir (periode: {r.periode_kalibrasi_hari} hari)",
                "entity": {"kode_sarana": r.kode_sarana, "hari_terlewat": days_since},
            })
        return warnings

    def _check_akreditasi(self, db, prodi_id: int | None) -> list:
        if prodi_id:
            rows = db.execute(
                text("""
                    SELECT nama_prodi, status_akreditasi, tgl_kadaluarsa
                    FROM prodi
                    WHERE id = :prodi_id
                """),
                {"prodi_id": prodi_id},
            ).fetchall()
        else:
            rows = db.execute(
                text("""
                    SELECT nama_prodi, status_akreditasi, tgl_kadaluarsa
                    FROM prodi
                """)
            ).fetchall()

        today = date.today()
        warnings = []
        for r in rows:
            if r.tgl_kadaluarsa is None:
                continue
            expiry = r.tgl_kadaluarsa
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
                "deskripsi": f"Status: {r.status_akreditasi}, Kadaluarsa: {r.tgl_kadaluarsa}",
                "entity": {"prodi": r.nama_prodi, "hari_tersisa": days_left},
            })
        return warnings
