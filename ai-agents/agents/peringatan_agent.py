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
        if isinstance(data, list):
            data = {}
        prodi_id = data.get("prodi_id")
        db = SessionLocal()
        try:
            warnings = []
            warnings.extend(self._check_bkd(db, prodi_id))
            warnings.extend(self._check_kalibrasi(db, prodi_id))
            warnings.extend(self._check_akreditasi(db, prodi_id))

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
