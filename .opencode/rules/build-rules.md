# ATURAN BAKU PENGEMBANGAN APLIKASI WEB
# Sistem Multi-Agent AI untuk Manajemen Tridharma Akreditasi ITSNU Pekalongan

## Workflow Wajib

PLAN -> BUILD BY STEP -> TEST KODE -> GIT COMMIT -> PUSH GITHUB -> TES KODE DI GITHUB

## 1. PLAN
- Baca blueprint & aturan ini sebelum mulai
- Jabarkan tugas menjadi sub-task kecil (maksimal 1 file per sub-task)
- Konfirmasi rencana ke user sebelum eksekusi

## 2. BUILD BY STEP
- Kerjakan 1 sub-task dalam 1 kali respons
- Selesaikan 1 file dulu, baru lanjut file berikutnya
- JANGAN menulis banyak file sekaligus dalam 1 respons
- Setiap file selesai -> informasikan progress

## 3. TEST KODE
- Laravel: `php artisan test`
- Frontend: `npm run lint`
- Pastikan tidak ada error sebelum lanjut

## 4. GIT COMMIT
git add .
git commit -m "feat: <modul> - <fitur> [Sprint <n>]"

## 5. PUSH GITHUB
git push origin <branch>

## 6. TES KODE DI GITHUB
- Tunggu GitHub Actions selesai (jika ada)
- Pastikan semua test lulus

## Larangan
- Jangan skip testing
- Jangan commit tanpa test
- Jangan edit banyak file sekaligus
- Jangan tanya ulang aturan ini (sudah baku)
- Kerjakan urut sesuai sprint plan

## Referensi
- Blueprint: BLUPRINT SISTEM INFORMASI MANAJEMEN TRIDHARMA DOSEN ITSNU PEKALONGAN.txt
- Project path: /Volumes/WORK/PROJECT PROTOTYPE/Sistem Multi-Agent AI AKREDITASI/
