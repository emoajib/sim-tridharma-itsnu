# Sistem Multi-Agent AI untuk Manajemen Tridharma Dosen

Sistem Informasi Manajemen Tridharma Dosen berbasis Laravel 11 + React + Inertia dengan integrasi **AI Agent Microservice** untuk optimalisasi akreditasi.

## Features

### 🔹 Core Features
- **Master Data**: Fakultas, Prodi, Dosen, Mahasiswa, Alumni, MK, Kurikulum, CPL, Periode Akademik
- **Portofolio Tridharma**: Pendidikan, Penelitian, Publikasi, PKM, Penunjang
- **BKD (Beban Kerja Dosen)**: Input dan validasi BKD dosen
- **Dokumen Bukti**: Upload dan management dokumen akreditasi
- **SPMI**: Audit Mutu dan Risk Register
- **Kurikulum**: CPL-MK Mapping Matrix dan RPS
- **E-RKAT & IKU (M13)**: Perencanaan anggaran (RKAT) dan Cascading IKU Kemdiktisaintek

### 🤖 AI Agent Features (6 Agents)
1. **Verifikasi Agent** - Validasi dokumen, deteksi duplikasi
2. **Prediksi Agent** - Prediksi skor akreditasi (Monte Carlo simulation)
3. **Rekomendasi Agent** - Saran perbaikan prioritas
4. **Peringatan Agent** - Alert BKD, kalibrasi, kadaluarsa akreditasi
5. **Generator Agent** - Generate otomatis narasi LED/LKPT
6. **Integrasi Agent** - Sinkronisasi PDDIKTI, SINTA, Sister

### 🔧 Technology Stack
- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: React 18 + Inertia + Tailwind CSS
- **AI Microservice**: Python FastAPI + MCP (replaces legacy Celery/RabbitMQ)
- **Database**: SQLite (dev) / PostgreSQL (prod)
- **Queue**: (legacy RabbitMQ deprecated, replaced by MCP direct calls)
- **Cache**: Redis

## Installation

```bash
# Clone repository
git clone <repository-url>
cd "Sistem Multi-Agent AI AKREDITASI"

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Setup environment
cp .env.example .env
# Edit .env for database settings (RabbitMQ no longer required)

# Run migrations
php artisan migrate

# Build frontend
npm run build

# Start Laravel
php artisan serve

# Start AI Agent Microservice (separate terminal)
cd ai-agents
source venv/bin/activate
python main.py
```

## Quick Start (Development)

```bash
# Satu baris perintah untuk menjalankan seluruh aplikasi dengan hot-reload otomatis
composer dev

# Output: semua layanan (Laravel, Reverb, Queue, Pail, Vite, AI Agents, RAG Service)
# muncul dalam satu terminal dengan label berwarna. Tekan Ctrl+C untuk berhenti.
# Perubahan kode pada React, PHP, atau Python akan langsung terdeteksi secara otomatis.
```

## AI Agent Usage

### Via Dashboard
1. Buka halaman Dashboard
2. Widget AI Agent menampilkan:
   - PeringatanBadge (jumlah alert)
   - PrediksiWidget (skor gauge chart)
   - RadarChart (capaian kriteria)

### Via Menu AI Agent
- **Peringatan** (`/peringatan`) - Lihat dan kelola alert
- **Verifikasi** (`/verifikasi`) - Hasil verifikasi dokumen
- **Generator** (`/generator`) - Generate dokumen LED/LKPT

### Via API
```bash
# Trigger agent via API
POST /api/agents/{agent}/run
Body: {"prodi_id": 1, "periode_id": 1}
```

### Via Scheduler
Agents dijalankan otomatis setiap jam:
```bash
php artisan schedule:run
# atau
php artisan agents:run-all
```

## Project Structure

```
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Http/Controllers/    # API & Web controllers
│   ├── Jobs/                 # Queue jobs
│   ├── Models/               # Eloquent models
│   └── Services/             # Business logic
├── ai-agents/
│   ├── agents/               # Python AI agents
│   ├── main.py               # FastAPI entry point
│   └── worker.py             # Legacy Celery tasks (deprecated, replaced by MCP tools in agents_mcp/)
├── resources/js/
│   ├── Components/           # React components
│   │   └── Agent/            # AI widgets
│   └── Pages/                # Inertia pages
│       ├── Peringatan/       # Peringatan page
│       ├── Verifikasi/       # Verifikasi page
│       └── Generator/        # Generator page
└── database/
    ├── migrations/           # DB migrations
    └── seeders/              # Data seeders
```

## Available Routes

| Route | Description |
|-------|-------------|
| `/dashboard` | Main dashboard |
| `/peringatan` | AI Peringatan Dini |
| `/verifikasi` | AI Verifikasi Dokumen |
| `/generator` | AI Generator Dokumen |
| `/portofolio` | Portofolio Tridharma |
| `/bkd` | BKD Dosen |
| `/kurikulum/mapping` | CPL-MK Mapping |
| `/spmi/audit` | Audit Mutu |
| `/spmi/risk` | Risk Register |

## License

MIT License - see LICENSE file for details.