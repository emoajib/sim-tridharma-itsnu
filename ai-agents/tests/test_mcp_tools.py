import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), ".."))

from unittest.mock import patch, AsyncMock
import pytest

from agents_mcp.tools import (
    rekomendasi_generate,
    prediksi_skor,
    verifikasi_dokumen,
    peringatan_check,
    _NoOpContext,
    _ctx,
)


class TestNoOpContext:
    async def test_report_progress_does_not_raise(self):
        ctx = _NoOpContext()
        await ctx.report_progress(1, 2, "test")

    async def test_info_does_not_raise(self):
        ctx = _NoOpContext()
        await ctx.info("test")

    async def test_error_does_not_raise(self):
        ctx = _NoOpContext()
        await ctx.error("test")


class TestCtx:
    def test_returns_noop_when_none(self):
        result = _ctx(None)
        assert isinstance(result, _NoOpContext)

    def test_returns_context_when_provided(self):
        class FakeCtx:
            pass
        ctx = FakeCtx()
        result = _ctx(ctx)
        assert result is ctx


class TestRekomendasiGenerate:
    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_returns_empty_when_no_data(self, mock_query):
        mock_query.return_value = []

        result = await rekomendasi_generate(prodi_id=1, top_n=10)

        assert result["prodi_id"] == 1
        assert result["recommendation_count"] == 0
        assert result["recommendations"] == []

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_assigns_tinggi_priority_for_bobot_ge_4(self, mock_query):
        mock_query.return_value = [
            {"id": 1, "kode_indikator": "I1", "nama_indikator": "Test 1", "bobot": 5, "status": "merah", "nilai": None},
        ]

        result = await rekomendasi_generate(prodi_id=1)

        assert result["recommendation_count"] == 1
        assert result["recommendations"][0]["prioritas"] == "tinggi"
        assert result["recommendations"][0]["bobot"] == 5

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_assigns_sedang_priority_for_bobot_2_to_3(self, mock_query):
        mock_query.return_value = [
            {"id": 2, "kode_indikator": "I2", "nama_indikator": "Test 2", "bobot": 3, "status": "kuning", "nilai": None},
        ]

        result = await rekomendasi_generate(prodi_id=1)

        assert result["recommendations"][0]["prioritas"] == "sedang"

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_assigns_rendah_priority_for_bobot_lt_2(self, mock_query):
        mock_query.return_value = [
            {"id": 3, "kode_indikator": "I3", "nama_indikator": "Test 3", "bobot": 1, "status": "merah", "nilai": None},
        ]

        result = await rekomendasi_generate(prodi_id=1)

        assert result["recommendations"][0]["prioritas"] == "rendah"

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_sorts_by_bobot_descending(self, mock_query):
        mock_query.return_value = [
            {"id": 2, "kode_indikator": "I2", "nama_indikator": "High", "bobot": 5, "status": "merah", "nilai": None},
            {"id": 1, "kode_indikator": "I1", "nama_indikator": "Low", "bobot": 2, "status": "merah", "nilai": None},
        ]

        result = await rekomendasi_generate(prodi_id=1, top_n=10)

        bobots = [r["bobot"] for r in result["recommendations"]]
        assert bobots == [5, 2]
        assert result["recommendations"][0]["nama"] == "High"


class TestPrediksiSkor:
    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_returns_error_when_no_data(self, mock_query):
        mock_query.return_value = []

        result = await prediksi_skor(prodi_id=1)

        assert result["error"] is not None
        assert result["predicted_score"] is None

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_computes_prediction_from_data(self, mock_query):
        # Mock historical data and budget data
        mock_query.side_effect = [
            [
                {"periode_id": 1, "nilai": 3.0, "bobot": 4},
                {"periode_id": 1, "nilai": 2.5, "bobot": 3},
                {"periode_id": 2, "nilai": 3.5, "bobot": 4},
                {"periode_id": 2, "nilai": 3.0, "bobot": 3},
            ],
            [
                {"total_biaya": 10000000, "periode_id": 1},
                {"total_biaya": 12000000, "periode_id": 2},
            ]
        ]

        result = await prediksi_skor(prodi_id=1)

        assert result["prodi_id"] == 1
        assert result["predicted_score"] is not None
        assert result["predicted_category"] in ["Unggul", "Baik Sekali", "Baik"]
        assert "probabilities" in result
        assert "trend_analysis" in result
        assert "budget_impact" in result


class TestVerifikasiDokumen:
    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_returns_empty_when_no_documents(self, mock_query):
        mock_query.return_value = []

        result = await verifikasi_dokumen(prodi_id=1)

        assert result["total_documents"] == 0
        assert result["valid_count"] == 0
        assert result["need_review_count"] == 0

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    @patch("agents_mcp.tools.os.path.exists", return_value=True)
    async def test_marks_valid_when_file_exists(self, mock_exists, mock_query):
        mock_query.return_value = [
            {"id": 1, "nama_dokumen": "Dokumen 1", "file_path": "/path/to/file.pdf",
             "file_size": 1024, "hash": "abc123", "keterangan": None,
             "nama_depan": "John", "nama_belakang": "Doe"},
        ]

        result = await verifikasi_dokumen(prodi_id=1)

        assert result["total_documents"] == 1
        assert result["valid_count"] == 1
        assert result["results"][0]["status"] == "valid"

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    @patch("agents_mcp.tools.os.path.exists", return_value=False)
    async def test_marks_need_review_when_file_missing(self, mock_exists, mock_query):
        mock_query.return_value = [
            {"id": 1, "nama_dokumen": "Missing File", "file_path": "/gone/file.pdf",
             "file_size": 1024, "hash": "abc123", "keterangan": None,
             "nama_depan": "John", "nama_belakang": "Doe"},
        ]

        result = await verifikasi_dokumen(prodi_id=1)

        assert result["need_review_count"] == 1
        assert result["results"][0]["status"] == "need_review"
        assert "tidak ditemukan" in result["results"][0]["catatan"]

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    @patch("agents_mcp.tools.os.path.exists", return_value=True)
    async def test_marks_need_review_when_file_empty(self, mock_exists, mock_query):
        mock_query.return_value = [
            {"id": 1, "nama_dokumen": "Empty File", "file_path": "/empty/file.pdf",
             "file_size": 0, "hash": "def456", "keterangan": None,
             "nama_depan": "Jane", "nama_belakang": "Smith"},
        ]

        result = await verifikasi_dokumen(prodi_id=1)

        assert result["results"][0]["status"] == "need_review"
        assert "File kosong" in result["results"][0]["catatan"]

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    @patch("agents_mcp.tools.os.path.exists", return_value=True)
    async def test_detects_duplicate_hash(self, mock_exists, mock_query):
        mock_query.return_value = [
            {"id": 1, "nama_dokumen": "Doc 1", "file_path": "/path/1.pdf",
             "file_size": 1024, "hash": "dup123", "keterangan": None,
             "nama_depan": "A", "nama_belakang": "B"},
            {"id": 2, "nama_dokumen": "Doc 2", "file_path": "/path/2.pdf",
             "file_size": 2048, "hash": "dup123", "keterangan": None,
             "nama_depan": "C", "nama_belakang": "D"},
        ]

        result = await verifikasi_dokumen(prodi_id=1)

        assert result["results"][0]["status"] == "valid"
        assert result["results"][1]["status"] == "need_review"
        assert "duplikat" in result["results"][1]["catatan"]


class TestPeringatanCheck:
    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_returns_zero_warnings_when_no_issues(self, mock_query):
        mock_query.side_effect = [[], [], [], []]

        result = await peringatan_check(prodi_id=1)

        assert result["warning_count"] == 0
        assert result["warnings"] == []

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_returns_bkd_warnings(self, mock_query):
        mock_query.side_effect = [
            [{"id": 1, "nama_depan": "John", "nama_belakang": "Doe",
              "nidn": "123456", "total_sks": 8}],
            [],
            [],
            [],
        ]

        result = await peringatan_check(prodi_id=1)

        assert result["warning_count"] == 1
        assert result["warnings"][0]["jenis"] == "bkd"
        assert result["warnings"][0]["tingkat"] == "warning"

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_bkd_critical_when_sks_below_6(self, mock_query):
        mock_query.side_effect = [
            [{"id": 1, "nama_depan": "John", "nama_belakang": "Doe",
              "nidn": "123456", "total_sks": 4}],
            [],
            [],
            [],
        ]

        result = await peringatan_check(prodi_id=1)

        assert result["warnings"][0]["tingkat"] == "critical"

    @patch("agents_mcp.tools.execute_query", new_callable=AsyncMock)
    async def test_returns_kalibrasi_warnings(self, mock_query):
        mock_query.side_effect = [
            [],
            [{"nama_sarana": "Mikroskop", "tanggal_kalibrasi": "2024-01-01",
              "tanggal_kalibrasi_berikut": "2025-01-01"}],
            [],
            [],
        ]

        result = await peringatan_check(prodi_id=1)

        assert result["warning_count"] == 1
        assert result["warnings"][0]["jenis"] == "kalibrasi"
