import logging
import os
from datetime import datetime

from sqlalchemy import text
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH

from agents.base_agent import BaseAgent
from database import SessionLocal

logger = logging.getLogger("agent.generator")


class GeneratorAgent(BaseAgent):
    name = "generator"
    version = "1.0.0"

    def execute(self, data: dict) -> dict:
        missing = self.validate_input(data, ["prodi_id", "periode_id", "jenis_dokumen"])
        if missing:
            return {"status": "error", "message": f"Missing fields: {missing}"}

        prodi_id = data["prodi_id"]
        periode_id = data["periode_id"]
        jenis_dokumen = data["jenis_dokumen"]

        db = SessionLocal()
        try:
            prodi = db.execute(
                text("SELECT nama_prodi FROM prodi WHERE id = :prodi_id"),
                {"prodi_id": prodi_id},
            ).fetchone()

            periode = db.execute(
                text("SELECT nama_periode FROM periode WHERE id = :periode_id"),
                {"periode_id": periode_id},
            ).fetchone()

            nama_prodi = prodi.nama_prodi if prodi else f"Prodi #{prodi_id}"
            nama_periode = periode.nama_periode if periode else f"Periode #{periode_id}"

            indikator_data = db.execute(
                text("""
                    SELECT i.kode_indikator, i.nama_indikator, i.target, pi.skor_tercapai, pi.status
                    FROM trx_pemenuhan_indikator pi
                    JOIN indikator i ON i.id = pi.indikator_id
                    WHERE pi.prodi_id = :prodi_id AND pi.periode_id = :periode_id
                    ORDER BY i.kode_indikator
                """),
                {"prodi_id": prodi_id, "periode_id": periode_id},
            ).fetchall()

            sections = self._build_sections(nama_prodi, nama_periode, indikator_data, jenis_dokumen)
            narasi = "\n\n".join(s["isi"] for s in sections)

            docx_path = self._create_docx(nama_prodi, nama_periode, sections, jenis_dokumen)

            result = {
                "narasi": narasi,
                "sections": sections,
                "jenis_dokumen": jenis_dokumen,
                "prodi": nama_prodi,
                "periode": nama_periode,
                "file_path": docx_path,
            }

            db.execute(
                text("""
                    INSERT INTO agent_generator_history 
                        (prodi_id, periode_id, jenis_dokumen, judul, file_path, status, hasil_text, generated_by, created_at, updated_at)
                    VALUES (:prodi_id, :periode_id, :jenis, :judul, :file_path, :status, :hasil, :generated, NOW(), NOW())
                """),
                {
                    "prodi_id": prodi_id,
                    "periode_id": periode_id,
                    "jenis": jenis_dokumen,
                    "judul": f"{jenis_dokumen.upper()} - {nama_prodi} - {nama_periode}",
                    "file_path": docx_path,
                    "status": "selesai",
                    "hasil": result,
                    "generated": "agent",
                },
            )
            db.commit()

            self.log_execution(self.name, None, data, result)
            return result

        except Exception as e:
            logger.error(f"Generator error: {e}", exc_info=True)
            result = {"status": "error", "message": str(e)}
            self.log_execution(self.name, None, data, result, status="error", error_message=str(e))
            return result
        finally:
            db.close()

    def _build_sections(self, prodi: str, periode: str, indikator_data: list, jenis: str) -> list:
        today = datetime.now().strftime("%d %B %Y")
        sections = []

        if jenis in ("led", "lkpt"):
            sections.append({
                "judul": "Cover",
                "isi": f"DOKUMEN {jenis.upper()}\nProgram Studi {prodi}\nPeriode {periode}\nDibuat: {today}",
            })

            sections.append({
                "judul": "Pendahuluan",
                "isi": (
                    f"Dokumen {jenis.upper()} ini disusun untuk Program Studi {prodi} "
                    f"pada periode {periode}. Dokumen ini berisi ringkasan capaian indikator "
                    f"kinerja yang telah dicapai dalam periode tersebut."
                ),
            })

            sections.append({
                "judul": "Capaian Indikator",
                "isi": self._format_indikator_table(indikator_data),
            })

            merah = [r for r in indikator_data if r.status == "merah"]
            kuning = [r for r in indikator_data if r.status == "kuning"]
            hijau = [r for r in indikator_data if r.status == "hijau"]

            analisa = []
            if merah:
                analisa.append(f"Terdapat {len(merah)} indikator berstatus merah yang memerlukan tindakan segera.")
            if kuning:
                analisa.append(f"Terdapat {len(kuning)} indikator berstatus kuning yang perlu ditingkatkan.")
            if hijau:
                analisa.append(f"Terdapat {len(hijau)} indikator berstatus hijau yang sudah tercapai.")
            sections.append({"judul": "Analisis", "isi": " ".join(analisa) if analisa else "Belum ada data capaian."})

            sections.append({
                "judul": "Penutup",
                "isi": f"Dokumen ini dihasilkan secara otomatis oleh sistem AI Akreditasi pada {today}.",
            })

        return sections

    def _format_indikator_table(self, rows: list) -> str:
        if not rows:
            return "Tidak ada data indikator."
        lines = ["| Kode | Indikator | Target | Tercapai | Status |", "|------|-----------|--------|----------|--------|"]
        for r in rows:
            target = r.target if r.target else "-"
            skor = r.skor_tercapai if r.skor_tercapai else "-"
            lines.append(f"| {r.kode_indikator} | {r.nama_indikator} | {target} | {skor} | {r.status} |")
        return "\n".join(lines)

    def _create_docx(self, prodi: str, periode: str, sections: list, jenis: str) -> str:
        doc = Document()
        
        title = doc.add_heading(f"DOKUMEN {jenis.upper()}", 0)
        title.alignment = WD_ALIGN_PARAGRAPH.CENTER
        
        doc.add_paragraph(f"Program Studi: {prodi}")
        doc.add_paragraph(f"Periode: {periode}")
        doc.add_paragraph(f"Tanggal: {datetime.now().strftime('%d %B %Y')}")
        doc.add_paragraph()
        
        for section in sections:
            heading = doc.add_heading(section.get("judul", ""), level=1)
            content = section.get("isi", "")
            
            paragraphs = content.split("\n")
            for para in paragraphs:
                if para.strip():
                    p = doc.add_paragraph(para)
            
            doc.add_paragraph()
        
        output_dir = "/tmp/akreditasi_docs"
        os.makedirs(output_dir, exist_ok=True)
        
        filename = f"{jenis}_{prodi.replace(' ', '_')}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.docx"
        filepath = os.path.join(output_dir, filename)
        
        doc.save(filepath)
        logger.info(f"DOCX saved to: {filepath}")
        
        return filepath
