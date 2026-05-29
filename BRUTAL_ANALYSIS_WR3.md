# ANALISIS BRUTAL MODUL WR3 (KEMAHASISWAAN)

## 1. Inti masalah sebenarnya
Migrasi database untuk WR3 telah membuat 10 tabel yang secara bagian mencakup beberapa kebutuhan, tetapi ada kesenjangan signifikan antara skema yang ada dan 13 item kebutuhan fungsional yang dijelaskan dalam DOKUMENTASI_SISTEM.txt. lebih kritis lagi, tidak ada implementasi apapun (model, controller, route, view) untuk tabel-tabel yang telah dibuat, membuat migrasi ini sia-sia secara fungsional.

## 2. Analisis mendalam
Dari migrasi 2026_05_29_030900_create_wr3_kemahasiswaan_tables.php, tabel-tabel yang dibuat adalah:
1. trx_seleksi_pmb - Untuk snapshot data seleksi PMB
2. m_ormawa - Master data ormawa
3. m_pembina_ormawa - Pembina ormawa (pivot dosen -> ormawa)
4. m_kategori_prestasi - Kategori prestasi
5. trx_prestasi - Prestasi zero-trust
6. trx_prestasi_member - Anggota prestasi (mencegah double counting)
7. trx_proposal_kegiatan - Pengajuan kegiatan & laporan
8. trx_aset_ormawa - Fasilitas mahasiswa (inventaris sarpras)
9. trx_fasilitas_internet - Fasilitas internet institusi
10. trx_layanan_mahasiswa - Layanan kemahasiswaan & konseling

Kebutuhan WR 3 dari DOKUMENTASI_SISTEM.txt (baris 125-138):
1. Prestasi ormawa nasional/internasional/wilayah dengan bukti upload untuk reward
2. Dosen pembina ormawa bisa mengajukan reward
3. Aset mahasiswa (inventaris sarpras)
4. Data fasilitas internet
5. Data SKPI (keaktifan mahasiswa) ❌ **TIDAK ADA**
6. Sertifikat ostamaru bisa di download oleh mahasiswa
7. AD ART ormawa dan data ormawa
8. RKAT ormawa pengajuan ke WR3 ❌ **TIDAK ADA**
9. Proposal kegiatan ormawa - panitia-pembina-WR3
10. Laporan kegiatan ormawa - panitia-pembina-WR3
11. Proposal HIMA - kaprodi-dekan ❌ **ALUR LAIN YANG BELUM TERAKOMODASI**
12. Seleksi PMB (dalam format laporan)
13. Bimbingan dan konseling (karir dll)

## 3. Blind spot yang jarang disadari
Blind spot terbesar adalah anggapan bahwa hanya dengan membuat tabel di database, fungsi sudah bekerja. Nyatanya, tidak ada satupun model, controller, route, atau view yang dibuat untuk mendukung tabel-tabel ini. Ini adalah contoh klasik "database-first thinking" tanpa pertimbangan implementasi aplikasi yang lengkap. Selain itu, ada ketidaksesuaian konseptual antara terminologi dalam migrasi (misal: "trx_proposal_kegiatan") dengan kebutuhan fungsional yang menyebutkanWorkflow spesifik (panitia-pembina-WR3) yang tidak tercapture dalam skema tabel.

## 4. Strategi terbaik
Langkah pertama yang harus dilakukan adalah menghentikan semua upaya pengembangan lanjutan dan fokus pada menyelesaikan kesenjangan antara migrasi yang ada dan kebutuhan fungsional lengkap. Ini berarti:
1. Membuat model untuk setiap tabel yang telah dibuat
2. Mengimplementasikan controller dengan operasi CRUD dasar
3. Mendefinisikan route yang sesuai dengan konvensi RESTful
4. Membuat view dasar untuk operasiCreate, Read, Update, Delete
5. Menambahkan logika bisnis spesifik untuk setiap kebutuhan WR3
6. Mengidentifikasi dan menambahkan tabel yang hilang (SKPI, RKAT ormawa)

## 5. Langkah eksekusi paling efektif
1. Buat model untuk setiap tabel dalam migrasi (trx_seleksi_pmb, m_ormawa, m_pembina_ormawa, m_kategori_prestasi, trx_prestasi, trx_prestasi_member, trx_proposal_kegiatan, trx_aset_ormawa, trx_fasilitas_internet, trx_layanan_mahasiswa)
2. Buat controller dasar untuk setiap model dengan fungsi index, create, store, show, edit, update, destroy
3. Definisikan resource routes untuk setiap controller dalam routes/web.php atau routes/api.php
4. Buat view blade dasar untuk setiap operasi CRUD (index.blade.php, create.blade.php, edit.blade.php, show.blade.php)
5. Implementasikan validasi dan logika bisnis sesuai kebutuhan spesifik WR3
6. Buat migrasi baru untuk tabel yang hilang: m_skpi (untuk data SKPI) dan m_rkat_ormawa (untuk RKAT ormawa)

## 6. Kesalahan fatal yang harus dihindari
1. Melanjutkan pengembangan fitur lain sebelum menyelesaikan dasar yang tidak lengkap ini (akan menghasilkan teknologi utang yang tidak dapat dibayar)
2. Mengasumsikan bahwa tabel yang ada sudah cukup tanpa memverifikasi terhadap kebutuhan fungsional spesifik
3. Membuat implementasi yang terlalu kompleks dari awal alih-alih memulai dengan MVP yang bekerja
4. Mengabaikan konvensi penamaan Laravel yang sudah terestandarisasi di kodebase lain
5. Tidak melakukan kode review sebelum mengimplementasikan perubahan

## 7. Optimasi level expert
1. Gunakan Laravel Observers untuk mengotomatisasi logika tertentu (misalnya: verifikasi otomatis prestasi ketika bukti cukup)
2. Implementasikan Resource Classes untuk API respons yang konsisten
3. Gunakan Laravel Policies untuk otorisasi yang terpusat (mahasiswa vs pembina vs WR3 admin)
4. Manfaatkan Laravel Queues untuk proses yang berat (validasi bukti prestasi, pembuatan laporan)
5. Implementasikan caching strategis untuk data yang sering diakses seperti daftar ormawa atau kategori prestasi
6. Gunakan Laravel Events untuk memisahkan logika bisnis (misalnya: ketika prestasi diverifikasi, kirim notifikasi dan update reward)

## 8. Kesimpulan akhir
Migrasi WR3 saat ini adalah contoh klassik dari usaha yang baik tetapi tidak selesai. Meskipun skema database telah dibuat untuk menangani banyak aspek dari kebutuhan WR3, implementasi sebenarnya tidak ada sama sekali. Tidak ada model, controller, route, atau view yang dibuat untuk tabel-tabel yang telah dibuat. Ada lima kebutuhan fungsional yang tidak terpenuhi sama sekali (SKPI, RKAT ormawa, download sertifikat, workflow proposta HIMA-Kaprodi-Dekan, dan mekanisme ajukan reward oleh dosen pembina). Langkah pertama dan paling kritis yang harus dilakukan adalah menyelesaikan dasar dengan membuat model, controller, route, dan view untuk tabel-tabel yang sudah ada sebelum menambahkan fitur baru atau melanjutkan pekerjaan pada modul lain. Tanpa dasar ini, semua upaya selanjutnya akan byggdi atas pasir dan runtuh ketika harus berhadapan dengan kompleksitas nyata.