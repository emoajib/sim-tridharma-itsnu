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

Versi: 1.0
Tanggal: 15 Mei 2026