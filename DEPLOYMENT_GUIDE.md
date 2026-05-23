# Panduan Deployment: Sistem Multi-Agent AI Akreditasi (Bare Metal)

Panduan ini ditujukan untuk administrator server yang akan melakukan deployment sistem ke VPS/Server berbasis Ubuntu 22.04 atau 24.04 tanpa menggunakan Docker.

## Prasyarat
- VPS dengan minimal RAM 8GB (karena menjalankan model AI lokal).
- OS Ubuntu 22.04 LTS atau 24.04 LTS.
- Akses Root/Sudo.

## Langkah 1: Setup Lingkungan Awal
Jalankan skrip otomasi yang telah disediakan untuk menginstal PHP, Python, PostgreSQL (pgvector), Redis, dan RabbitMQ.

```bash
chmod +x scripts/production-setup.sh
./scripts/production-setup.sh
```

## Langkah 2: Persiapan Kode & Library
Pindahkan kode sumber ke `/var/www/akreditasi`, lalu instal dependensi untuk masing-masing modul.

### A. Laravel (Frontend & Web API)
```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build
php artisan migrate --force
php artisan storage:link
```

### B. AI Service RAG (Python)
```bash
cd ai-service
source venv/bin/activate
pip install -r requirements.txt
```

### C. AI Agents (Python)
```bash
cd ai-agents
source venv/bin/activate
pip install -r requirements.txt
```

## Langkah 3: Konfigurasi Supervisor
Gunakan Supervisor untuk memastikan semua servis berjalan di latar belakang dan auto-restart jika terjadi crash. Contoh konfigurasi tersedia di folder `scripts/supervisor/`.

Servis yang harus didaftarkan:
1. `laravel-worker` (Queue)
2. `laravel-reverb` (WebSocket)
3. `ai-service-rag` (FastAPI Port 5001)
4. `ai-agents` (FastAPI Port 8001)

## Langkah 4: Konfigurasi Nginx
Atur Nginx sebagai reverse proxy untuk mengarahkan domain Anda ke aplikasi Laravel dan API Python.

---
*Vetted by AI - Manual Review Required by Senior Engineer/Manager*
