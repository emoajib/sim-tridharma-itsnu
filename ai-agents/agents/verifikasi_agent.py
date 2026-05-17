import logging
import os

from sqlalchemy import text

from agents.base_agent import BaseAgent
from database import SessionLocal

logger = logging.getLogger("agent.verifikasi")


class VerifikasiAgent(BaseAgent):
    name = "verifikasi"
    version = "1.0.0"

    def execute(self, data: dict) -> dict:
        if isinstance(data, list):
            data = {}
        results = []
        db = SessionLocal()
        try:
            prodi_id = data.get("prodi_id")
            dosen_id = data.get("dosen_id")

            query = "SELECT id, dosen_id, prodi_id, nama_dokumen, file_path, file_type, file_size, hash, documentable_type, documentable_id, is_verified FROM doc_bukti WHERE deleted_at IS NULL"
            params = {}
            if prodi_id:
                query += " AND prodi_id = :prodi_id"
                params["prodi_id"] = prodi_id
            if dosen_id:
                query += " AND dosen_id = :dosen_id"
                params["dosen_id"] = dosen_id

            docs = db.execute(text(query), params).fetchall()

            for doc in docs:
                issues = []
                doc_id, d_dosen_id, d_prodi_id, nama, file_path, file_type, file_size, file_hash, doc_type, doc_id_ref, is_verified = doc

                if not file_path or not os.path.isfile(file_path) if file_path else True:
                    issues.append("File fisik tidak ditemukan di penyimpanan")
                elif file_size is None or file_size <= 0:
                    issues.append("Ukuran file tidak valid (0 byte)")

                if file_hash and not issues:
                    dup = db.execute(
                        text("SELECT COUNT(*) FROM doc_bukti WHERE hash = :hash AND id != :id AND deleted_at IS NULL"),
                        {"hash": file_hash, "id": doc_id},
                    ).scalar()
                    if dup and dup > 0:
                        issues.append(f"File duplikat ditemukan ({dup} file lain dengan hash sama)")

                if issues:
                    status = "need_review"
                    confidence = 0.7
                else:
                    status = "valid"
                    confidence = 0.95 if file_path else 0.5

                results.append({
                    "doc_id": doc_id,
                    "nama_dokumen": nama,
                    "status": status,
                    "catatan": "; ".join(issues) if issues else "File terverifikasi",
                    "confidence": confidence,
                    "is_verified": is_verified,
                })

                db.execute(
                    text("""
                        INSERT INTO agent_verifikasi_hasil (prodi_id, dosen_id, doc_bukti_id, status, catatan, tingkat_kepercayaan, created_at, updated_at)
                        VALUES (:prodi_id, :dosen_id, :doc_bukti_id, :status, :catatan, :confidence, NOW(), NOW())
                        ON CONFLICT DO NOTHING
                    """),
                    {
                        "prodi_id": d_prodi_id,
                        "dosen_id": d_dosen_id,
                        "doc_bukti_id": doc_id,
                        "status": status,
                        "catatan": "; ".join(issues) if issues else "File terverifikasi",
                        "confidence": confidence,
                    },
                )
            db.commit()

            result = {
                "status": "success",
                "total_docs": len(docs),
                "valid": sum(1 for r in results if r["status"] == "valid"),
                "need_review": sum(1 for r in results if r["status"] == "need_review"),
                "results": results,
            }
            self.log_execution(self.name, None, data, result)
            return result

        except Exception as e:
            logger.error(f"Verifikasi error: {e}", exc_info=True)
            result = {"status": "error", "message": str(e)}
            self.log_execution(self.name, None, data, result, status="error", error_message=str(e))
            return result
        finally:
            db.close()
