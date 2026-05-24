# SISTER Dosen Import - Plan & Progress

**Tanggal Update**: 24 Mei 2026  
**Status**: Dalam tahap analisis dan perbaikan bertahap (Mode Aman)  
**Tujuan Utama**: Memperbaiki proses import Data_dosen.xlsx dari SISTER secara aman, tanpa membuat duplikat data, dan tetap menjaga integritas data.

---

## Prinsip Kerja (Mode Aman)

- Hanya mengekspos data SISTER di mode **Preview/Simulasi**.
- **Tidak** menyentuh record di tabel `m_dosen` (kecuali linking `dosen_id`).
- Tidak menghapus atau mengubah data existing tanpa persetujuan eksplisit.
- Setiap perubahan dilakukan secara bertahap dan terdokumentasi.

---

## Status Saat Ini (24 Mei 2026)

### Kemajuan Terbaru
- Importer sudah stabil (50 success, 0 error pada import LIVE terakhir).
- Semua field penting dari Excel sudah diekspos di hasil simulasi.
- Domain email sudah diubah ke `@itsnupekalongan.ac.id`.
- Tabel preview di UI sudah diperbarui dengan banyak field baru.
- Ditemukan masalah serius: **40 dosen** memiliki lebih dari 1 user yang tertaut (multiple link).

### Masalah Utama yang Ditemukan
- 40 dosen memiliki 4+ user (campuran domain lama + baru + suffix `.1`, `.2`, `.3`).
- Ini merupakan akumulasi dari import sebelumnya yang belum bersih.

---

## File yang Perlu di-Commit Hari Ini (24 Mei 2026)

| File | Alasan Perubahan | Prioritas |
|------|------------------|---------|
| `app/Models/Dosen.php` | Menambahkan relationship `users()` sementara untuk keperluan analisis (A1) | Tinggi |
| `resources/js/Pages/Admin/Users/Index.tsx` | Update `PreviewResult` interface + tabel preview (menambahkan kolom Kepangkatan, Rumpun Ilmu, Status Pegawai, Ikatan Kerja, Penempatan) | Tinggi |

**Rekomendasi Commit Message**:
```
feat: A1+A2 - Temporary users() relationship + enhance SISTER preview table

- Add temporary read-only `users()` relationship in Dosen model (for duplication analysis)
- Update PreviewResult interface and preview table with additional SISTER fields
- Maintain strict Mode Aman (no changes to m_dosen data)
```

---

## Rencana Kerja Besok (25 Mei 2026)

### Fase 1: Analisis Mendalam (A1) — Prioritas Tertinggi

**Tujuan**: Memahami secara detail pola duplikasi pada 40 dosen sebelum merencanakan pembersihan.

**Aktivitas yang Akan Dilakukan**:

1. **Setup Analisis**
   - Gunakan relationship `users()` yang sudah ditambahkan.
   - Buat folder dokumentasi di `storage/app/analysis/`.

2. **Analisis Pola Duplikasi**
   - Kelompokkan 40 dosen berdasarkan pola email:
     - Berapa yang memiliki domain lama (`@itsnu.ac.id`)
     - Berapa yang memiliki domain baru
     - Berapa yang memiliki suffix `.1`, `.2`, `.3`
   - Identifikasi user "utama" vs user duplikat per dosen.

3. **Analisis Risiko & Dampak**
   - Periksa apakah user-user duplikat memiliki role yang berbeda.
   - Cek aktivitas user (created_at, updated_at).
   - Evaluasi dampak terhadap fitur lain (dashboard, portofolio, scoping, dll).

4. **Dokumentasi**
   - Buat laporan singkat berisi:
     - Ringkasan temuan
     - Daftar 10–15 dosen paling bermasalah
     - Rekomendasi strategi pembersihan (read-only dulu)

**Output yang Diharapkan**:
- Laporan analisis (bisa dalam bentuk Markdown di `storage/app/analysis/`)

---

### Fase 2: Penyempurnaan UI Preview (A2)

**Tujuan**: Membuat tabel Simulasi Import lebih informatif dan nyaman digunakan.

**Aktivitas**:

- Perbaiki layout tabel preview (karena sudah banyak kolom).
- Tambahkan horizontal scroll yang lebih baik jika diperlukan.
- Pertimbangkan penambahan filter/search sederhana di dalam modal simulasi (opsional).
- Pastikan semua field yang sudah diekspos di backend tampil dengan rapi.

---

### Fase 3: Persiapan A3 (Logging yang Lebih Baik)

**Tujuan**: Meningkatkan traceability proses import di masa depan.

**Aktivitas**:
- Tambahkan logging yang lebih detail di `SisterDosenUserImport.php`:
  - Catat secara eksplisit apakah baris tersebut **CREATE** atau **UPDATE**.
  - Catat apakah linking ke dosen berhasil.
  - Catat alasan skip (jika ada).

---

### Fase 4: Persiapan Strategi Pembersihan Data (Jangka Menengah)

**Catatan**: Fase ini **belum akan dieksekusi** besok. Hanya persiapan.

- Rancang pendekatan pembersihan yang sangat aman.
- Buat skrip yang bersifat **read-only** terlebih dahulu (hanya membuat laporan rekomendasi).
- Tentukan aturan prioritas user yang akan dipertahankan.

---

## Ringkasan Prioritas Besok (25 Mei 2026)

| Waktu          | Fokus     | Aktivitas Utama                          | Output Utama                  |
|----------------|-----------|------------------------------------------|-------------------------------|
| Pagi – Siang   | **A1**    | Analisis mendalam 40 dosen multiple link | Laporan temuan + rekomendasi  |
| Siang – Sore   | **A2**    | Penyempurnaan tabel preview              | UI Preview lebih baik         |
| Sore           | **A3**    | Persiapan logging yang lebih detail      | Rencana perubahan logging     |

---

## Catatan Keamanan

- Semua pekerjaan tetap mengikuti **Mode Aman**.
- Tidak ada penghapusan data yang akan dilakukan tanpa persetujuan eksplisit.
- Setiap langkah analisis bersifat read-only.
- Semua perubahan didokumentasikan.

---

**File ini dibuat untuk keperluan tracking dan perencanaan.**  
Silakan update file ini setiap hari sesuai progress.