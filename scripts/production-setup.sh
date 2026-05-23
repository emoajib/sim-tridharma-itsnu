#!/bin/bash

# ==============================================================================
# MASTER SETUP SCRIPT - SISTEM MULTI-AGENT AI AKREDITASI
# TARGET OS: Ubuntu 22.04 / 24.04 (Bare Metal / VPS)
# ==============================================================================
# Vetted by AI - Manual Review Required by Senior Engineer/Manager

set -e

echo "🚀 Memulai Setup Server untuk Sistem Multi-Agent AI Akreditasi..."

# 1. Update System
echo "🔄 Updating system packages..."
sudo apt-get update && sudo apt-get upgrade -y

# 2. Install Common Dependencies
echo "📦 Installing common dependencies..."
sudo apt-get install -y curl git unzip zip software-properties-common supervisor nginx ufw python3-pip python3-venv

# 3. Install PHP 8.3
echo "🐘 Installing PHP 8.3 & Extensions..."
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt-get install -y php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-pgsql php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath php8.3-redis php8.3-intl

# 4. Install Composer
echo "🎼 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 5. Install PostgreSQL & pgvector
echo "🐘 Installing PostgreSQL & pgvector..."
sudo apt-get install -y postgresql postgresql-contrib
# Install pgvector from source (since apt version might be old)
sudo apt-get install -y postgresql-server-dev-all make gcc
cd /tmp
git clone --branch v0.7.0 https://github.com/pgvector/pgvector.git
cd pgvector
make
sudo make install
cd ~

# 6. Install Redis & RabbitMQ
echo "🔋 Installing Redis & RabbitMQ..."
sudo apt-get install -y redis-server rabbitmq-server
sudo systemctl enable redis-server
sudo systemctl enable rabbitmq-server

# 7. Setup Application Directories (Skeleton)
echo "📁 Setting up application directories..."
sudo mkdir -p /var/www/akreditasi
sudo chown -R $USER:$USER /var/www/akreditasi

# 8. Setup Python Virtual Environments
echo "🐍 Setting up Python Virtual Environments..."

# AI Service RAG
mkdir -p /var/www/akreditasi/ai-service
python3 -m venv /var/www/akreditasi/ai-service/venv

# AI Agents
mkdir -p /var/www/akreditasi/ai-agents
python3 -m venv /var/www/akreditasi/ai-agents/venv

# 9. Firewall Configuration
echo "🛡️ Configuring Firewall..."
sudo ufw allow 'Nginx Full'
sudo ufw allow 22
sudo ufw allow 5001
sudo ufw allow 8001
# sudo ufw --force enable

# 10. Summary
echo "=============================================================================="
echo "✅ SETUP AWAL BERHASIL!"
echo "=============================================================================="
echo "Langkah selanjutnya yang harus Anda lakukan secara manual:"
echo "1. Clone repository ke /var/www/akreditasi"
echo "2. Konfigurasi .env untuk Laravel, ai-service, dan ai-agents."
echo "3. Jalankan 'composer install' dan 'npm install && npm run build' di Laravel."
echo "4. Jalankan 'pip install -r requirements.txt' di masing-masing venv Python."
echo "5. Gunakan Supervisor untuk menjalankan Laravel Queue, Reverb, dan FastAPI."
echo "=============================================================================="
