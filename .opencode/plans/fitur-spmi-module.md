# FITUR YANG DIBUTUHKAN — MODUL SPMI AUDIT
## Referensi: PTS dengan SPMI Terbaik di Indonesia

---

## REFERENSI PTS

| PTS | Sistem/Aplikasi | Keunggulan |
|-----|-----------------|------------|
| **BINUS University** | BIANQA, SYSQA, RISMA, ISRA | Tipologi 1 SPMI LLDIKTI, integrasi audit + risk + survey |
| **Telkom University** | SPM-DMS, APT Onboard, Tel-U Onboard | ISO 21001:2018 + SPMI, 36 standar mutu, SAI independen |
| **eSPMI (eCampuz)** — dipakai STPN, dll | eSPMI Cloud | PPEPP digital end-to-end, integrasi SIAKAD, early warning |
| **UMY** | — | OBE-based, benchmarking dengan UGM |
| **Univ. Katolik Musi Charitas** | SPMI Digital (Laravel 11) | Dashboard, dokumen standar, evaluasi data — tech stack sama |

---

## FITUR YANG HARUS ADA (Urutan Prioritas)

### 🔴 PRIORITAS 1 — Fondasi SPMI

#### 1. Manajemen Standar Mutu
- Tabel referensi standar mutu (mengacu 24-36 standar SNDIKTI + SPMI institusi)
- Kategorisasi: Pendidikan, Penelitian, PKM, Standar Tambahan
- Setiap temuan audit WAJIB terikat ke standar mutu tertentu (standar_mutu_id)
- Target nilai per standar per periode
- **Referensi**: Tel-U punya 36 standar (8+8+8+12), BINUS punya quality standard terintegrasi

#### 2. Siklus PPEPP End-to-End
- **P**enetapan: Buat/edit standar mutu + target + IKU
- **P**elaksanaan: Upload bukti dukung by unit/prodi
- **E**valuasi: AMI (Audit Mutu Internal) — modul yang sudah ada, tapi harus di-upgrade
- **P**engendalian: Rencana Tindak Korektif (RTL) — PIC, deadline, bukti perbaikan
- **P**eningkatan: Review RTM → naikkan standar untuk siklus berikutnya
- **Referensi**: eSPMI siklus PPEPP digital, BINUS SYSQA

#### 3. Severity & Scoring System
- Severity: Ringan / Sedang / Berat / Kritis
- Scoring otomatis: severity × jumlah temuan → skor mutu prodi
- Risk-based: mapping ke risk register
- **Referensi**: BINUS RISMA, UMBP AMI scoring 80 butir pertanyaan

---

### 🟡 PRIORITAS 2 — Workflow & Otomatisasi

#### 4. Workflow Audit State Machine
Status flow:
```
Draft → Submitted → Assigned → In Progress → 
Awaiting Verification → Verified → Closed → Archived
```
Setiap transisi:
- Wajib ada PIC assignment
- Notifikasi otomatis ke PIC
- Upload bukti tindak lanjut
- Review oleh auditor/LPM
- **Referensi**: BINUS SYSQA workflow, Tel-U SAI audit process

#### 5. Audit Trail & Immutability
- Setiap perubahan tercatat (siapa + kapan + apa yang diubah)
- Temuan yang sudah "Verified" jadi immutable (tidak bisa diedit/dihapus)
- Hapus soft delete → ganti dengan `is_archived`
- **Referensi**: best practice audit internal — data audit wajib retained forever

#### 6. CAPA (Corrective Action Preventive Action)
- Setiap temuan berat WAJIB punya Root Cause Analysis
- Corrective action: what will be done to fix it?
- Preventive action: what will be done to prevent recurrence?
- Deadline + PIC + bukti penyelesaian
- **Referensi**: UGM CAPA system

---

### 🟢 PRIORITAS 3 — AI & Integrasi

#### 7. AI asli (bukan str_contains)
- Klasifikasi otomatis temuan ke standar mutu menggunakan NLP/RAG
- Rekomendasi tindak lanjut dari Knowledge Base yang sudah ada
- Deteksi similaritas dengan temuan sebelumnya
- Auto-assign PIC berdasarkan jenis temuan
- **Referensi**: Tel-U AI-assisted audit, BINUS automated QA

#### 8. Dashboard & Analytics
- Grafik tren temuan per prodi/periode/standar
- Close rate, average resolution time
- Risk heatmap (severity × likelihood)
- Skor mutu per prodi real-time
- Early warning: threshold temuan kritis → alert pimpinan
- **Referensi**: BINUS ISRA survey analytics, eSPMI dashboard, ITS early warning system

#### 9. Integrasi Modul
| Modul | Integrasi |
|-------|-----------|
| Risk Register | Temuan kritis → trigger risk register |
| RKAT | Temuan sarana → ajukan pengadaan |
| IKU | Temuan terkait capaian IKU → update scorecard |
| Knowledge Base | Rekomendasi evidence-based dari KB |
| Peringatan AI Agent | Auto-reminder ke PIC via sistem notifikasi |
| Dosen/Prodi | Dashboard mutu per dosen/prodi |

---

### 🔵 PRIORITAS 4 — Pelaporan & Regulasi

#### 10. Pelaporan SPMI ke LLDIKTI
- Generate laporan SPMI sesuai format LLDIKTI
- Sinkronisasi data mutu dengan PD Dikti
- Siap untuk verifikasi & validasi SPMI
- **Regulasi**: Permendiktisaintek No. 39/2025

#### 11. Manajemen Dokumen Mutu
- Repositori dokumen standar (Kebijakan SPMI, Manual Mutu, Standar, SOP, Form)
- Version control untuk setiap dokumen
- Approval workflow (draft → review → approve → publish)
- Masa berlaku dokumen + auto-reminder revisi
- **Referensi**: Tel-U SPM-DMS, eSPMI repository

#### 12. Survey & Kepuasan Stakeholder
- Survey kepuasan: mahasiswa, dosen, alumni, pengguna lulusan
- Terintegrasi dengan temuan audit
- Otomatis jadi input untuk RTM (Rapat Tinjauan Manajemen)
- **Referensi**: BINUS ISRA (Integrated Survey Reporting Application)

---

### 📋 PRIORITAS 5 — Evaluasi Diri & Akreditasi

#### 13. Evaluasi Diri Program Studi (EDPS)
- Template self-assessment berdasarkan standar BAN-PT / LAM
- Upload bukti dukung mandiri per standar
- Scoring otomatis dari bukti yang diupload
- Gap analysis: target vs realisasi
- **Referensi**: UGM SI-EDPS, eSPMI evaluasi diri

#### 14. Rapat Tinjauan Manajemen (RTM) Digital
- Agenda RTM dari temuan audit + survey + evaluasi diri
- Notulen digital dengan action items
- Tracking tindak lanjut hasil RTM
- **Referensi**: Tel-U management review, BINUS SYSQA Reports

#### 15. Persiapan Akreditasi
- Kesiapan data untuk APT (Akreditasi Perguruan Tinggi)
- Kesiapan data untuk APS (Akreditasi Program Studi)
- Instrumen akreditasi terintegrasi dengan data SPMI
- **Referensi**: Tel-U APT Onboard, BINUS APT 3.0 / APS 4.0

---

## ROADMAP IMPLEMENTASI

### Phase 1 (Minggu 1-2) — Fondasi
- ✅ Standar Mutu + Severity
- ✅ PPEPP lengkap (Pengendalian & Peningkatan)
- ✅ Workflow state machine

### Phase 2 (Minggu 3-4) — Penguatan
- ✅ CAPA + Audit Trail
- ✅ Dashboard & Analytics
- ✅ AI klasifikasi temuan (pakai RAG yang ada)

### Phase 3 (Minggu 5-6) — Integrasi
- ✅ Integrasi modul (Risk, RKAT, IKU, KB, Peringatan)
- ✅ Pelaporan LLDIKTI
- ✅ Survey stakeholder

### Phase 4 (Minggu 7-8) — Advanced
- ✅ Evaluasi Diri (EDPS)
- ✅ RTM Digital
- ✅ Persiapan Akreditasi (APT/APS)

---

## PERBANDINGAN: CURRENT VS TARGET

| Aspek | Current (Skor 3.5/10) | Target (Skor 9/10) |
|-------|----------------------|-------------------|
| PPEPP | Hanya E (Evaluasi), 2 tahap hilang | 5 tahap lengkap + terintegrasi |
| Standar Mutu | Tidak ada referensi | 24-36 standar terdefinisi |
| Workflow | Manual, 3 status | State machine 8 status, auto-notifikasi |
| AI | str_contains() | RAG/NLP klasifikasi + rekomendasi |
| Dashboard | Tabel biasa | Real-time analytics + early warning |
| Audit Trail | Soft delete bisa dihapus | Immutable, versioned |
| Integrasi | Silo sendiri | Terkoneksi semua modul |
| Pelaporan | Tidak ada | Siap LLDIKTI + Akreditasi |
