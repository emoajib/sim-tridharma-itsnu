# Aturan Pengembangan Aplikasi
# Workflow: PLAN - BUILD - TES KODE - GIT COMMIT - PUSH GITHUB - TES KODE DI GITHUB

## 📋 Alur Kerja Wajib

Setiap pengembangan fitur HARUS mengikuti urutan ini:

```
PLAN → BUILD → TES KODE → GIT COMMIT → PUSH GITHUB → TES KODE DI GITHUB
```

### 1. PLAN (Perencanaan)
- Analisis kebutuhan
- Buat plan detail dengan task breakdown
- Estimasi waktu
- Tanya clarifikasi jika ada yang tidak jelas

### 2. BUILD (Pengembangan)
- Eksekusi kode sesuai plan
- Ikuti coding standards yang ada
- Jangan skip step sebelumnya

### 3. TES KODE (Testing Lokal)
- Cek PHP syntax: `php -l app/`
- Cek TypeScript: `npx tsc --noEmit`
- Build frontend: `npm run build`
- Cek Routes: `php artisan route:list`
- Test manually via browser




Let me do a comprehensive audit of the codebase to find:
1. Code errors and bugs
2. Missing logic
3. Data/process synchronization issues
4. Inconsistencies across modules
5. Non-uniform code patterns

### 4. GIT COMMIT
- Commit dengan pesan yang jelas:
  - Format: `feat: [deskripsi] #[sprint]`
  - Contoh: `feat: Add AI Peringatan widget #Sprint10`
- Jangan commit jika ada error

### 5. PUSH GITHUB
- Push ke branch yang sesuai
- Pastikan tidak ada conflict

### 6. TES KODE DI GITHUB
- Cek CI/CD pipeline
- Cek automated tests
- Verifikasi deployment berhasil

---

## 🛡️ Protokol Keamanan Data & Database (PENTING)

Untuk mencegah kehilangan data (Data Loss) di masa depan, aturan berikut bersifat **MUTLAK**:

1.  **DILARANG KERAS** menjalankan perintah `php artisan migrate:fresh` pada database utama (PostgreSQL). Perintah ini akan menghapus seluruh data permanen.
2.  **Gunakan Migrasi Inkremental**: Hanya gunakan `php artisan migrate` untuk menambah struktur baru.
3.  **Prosedur Sinkronisasi SINTA**:
    *   Sistem sinkronisasi harus menggunakan metode **Upsert** (Update or Create).
    *   Dosen dicocokkan berdasarkan **NIDN/NUPTK**.
    *   Publikasi dicocokkan berdasarkan **Judul + ID Dosen**.
    *   JANGAN pernah menghapus data lama untuk memasukkan data baru dari Excel.
4.  **Verifikasi pgAdmin 4**:
    *   Pastikan pgAdmin 4 terhubung ke database `sim_tridharma_itsnu` di port **5433**.
    *   Data yang ada di aplikasi adalah cerminan langsung dari tabel di pgAdmin.
5.  **Backup Rutin**: Admin IT wajib melakukan export backup `.sql` dari pgAdmin minimal sekali dalam seminggu.

---

## ⚠️ Penting

- **JANGAN** skip step TES KODE
- **JANGAN** push jika build error
- **SELALU** test setelah setiap perubahan
- **DOKUMENTASIKAN** setiap fitur baru di README

---

## 📊 Checkpoint List

| Step | Checklist |
|------|-----------|
| PLAN | Plan sudah disetujui user |
| BUILD | Semua task sudah selesai |
| TES KODE | No errors (PHP, TS, Build) |
| GIT COMMIT | Commit message jelas |
| PUSH GITHUB | Branch up-to-date |
| TES KODE DI GITHUB | CI/CD pass |

---

Versi: 1.1 (Security Update)
Tanggal: 17 Mei 2026
Update: Penambahan Protokol Keamanan Data & Database
_