#!/bin/bash
# ============================================================
# Satu Baris: Sistem Multi-Agent AI Akreditasi — Full Stack
# ============================================================
#
# ⚠️ WARNING: This script uses development servers (php artisan serve + npm run dev).
# For production, use Docker/Supervisor stack with PHP-FPM + Nginx.
# Run: vendor/bin/phpunit && npx vitest run  # test before deploy
#
set -e

echo "=== 1/6 Install PHP dependencies ==="
composer install --no-interaction --prefer-dist

echo "=== 2/6 Install Node dependencies ==="
npm install --legacy-peer-deps

echo "=== 3/6 Install Python dependencies ==="
pip install -r ai-service/requirements.txt -q

echo "=== 4/6 Database migration ==="
php artisan migrate --force

echo "=== 5/6 Build frontend ==="
npm run build

echo "=== 6/6 Start all services ==="
# Laravel:8000 + Vite:5173 + AI Service:5001
php artisan serve --port=8000 &
npm run dev &
(cd ai-service && python main.py) &

echo "======================================"
echo "✅ ALL SYSTEMS READY"
echo "   Laravel       → http://localhost:8000"
echo "   Vite          → http://localhost:5173"
echo "   AI Service    → http://localhost:5001"
echo "   Health Check  → curl localhost:5001/health"
echo "======================================"
wait
