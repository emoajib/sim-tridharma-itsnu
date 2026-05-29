# Agent Guidelines for Sistem Multi-Agent AI AKREDITASI

## Essential Commands

### Development Workflow
- **Start all services**: `composer dev` (Laravel, Reverb, Queue, Pail, Vite, AI Agents, RAG)
- **PHP dependencies**: `composer install`
- **Node dependencies**: `npm install`
- **Database migrations**: `php artisan migrate`
- **Frontend build**: `npm run build`
- **Frontend dev**: `npm run dev`
- **Laravel server**: `php artisan serve`
- **WebSocket server**: `php artisan reverb:start`
- **Queue worker**: `php artisan queue:listen --tries=1 --timeout=0`
- **Log viewer**: `php artisan pail --timeout=0`
- **AI Agents**: `cd ai-agents && source venv/bin/activate && python main.py`
- **AI RAG Service**: `cd ai-service && source venv/bin/activate && python main.py`

### Testing & Verification
- **PHP syntax**: `php -l app/`
- **TypeScript check**: `npx tsc --noEmit`
- **Route listing**: `php artisan route:list`
- **Run tests**: `@php artisan test` (via composer test)
- **Manual testing**: Verify via browser after changes

## Critical Constraints

### Database Safety
- **NEVER** run `php artisan migrate:fresh` on production (PostgreSQL) - deletes all data
- Use only `php artisan migrate` for incremental schema changes
- SINTA synchronization MUST use upsert (update or create) - never delete old data
- pgAdmin 4 must connect to `sim_tridharma_itsnu` on port 5433
- Weekly SQL backups required from pgAdmin

### Git Workflow
- Commit format: `feat: [deskripsi] #[sprint]`
- Required sequence: PLAN → BUILD → TES KODE → GIT COMMIT → PUSH GITHUB → TES KODE DI GITHUB
- Never commit if build/test errors exist
- Never push if local build fails
- Always test after changes

## Architecture Notes

### Service Boundaries
- **Backend**: Laravel 11 (PHP 8.3+) in `/app`
- **Frontend**: React 18 + Inertia + Tailwind in `/resources/js`
- **AI Microservices**: Python FastAPI in `/ai-agents` and `/ai-service`
- **Queue System**: Legacy RabbitMQ/Celery deprecated - replaced by MCP direct HTTP calls
- **Cache**: Redis
- **Database**: SQLite (dev), PostgreSQL (prod)

### Entry Points
- Web: Laravel routes (`/dashboard`, `/peringatan`, `/verifikasi`, `/generator`)
- API: `/api/agents/{agent}/run` (POST with `{"prodi_id": 1, "periode_id": 1}`)
- Scheduler: `php artisan schedule:run` or `php artisan agents:run-all`
- AI Services: Direct HTTP calls via MCP (ports 8001 for agents, 5001 for RAG)

## Environment Setup
- Copy `.env.example` to `.env` and configure
- Create SQLite: `touch database/database.sqlite`
- Python venvs: `ai-agents/venv` and `ai-service/venv` with `fastapi` installed
- Redis server required for caching

### Production Deployment
- **Setup script**: `scripts/production-setup.sh` (Ubuntu 22.04/24.04)
- **Deployment directory**: `/var/www/akreditasi`
- **Environment files**: Configure `.env` for Laravel, ai-service, and ai-agents
- **Python dependencies**: `pip install -r requirements.txt` in each venv
- **Services to supervise**: 
  - `laravel-worker` (Queue)
  - `laravel-reverb` (WebSocket)
  - `ai-service-rag` (FastAPI Port 5001)
  - `ai-agents` (FastAPI Port 8001)
- **Firewall ports**: Open 22 (SSH), 5001 (RAG), 8001 (Agents), Nginx Full
- **Database**: PostgreSQL with pgvector extension required