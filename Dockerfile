# ====================================================================
# MULTI-STAGE DOCKER BUILD — Production
# Stage 1: Frontend Assets (npm build)
# Stage 2: PHP Dependencies (composer install)
# Stage 3: Production Runtime (PHP-FPM + Nginx + Supervisor)
# ====================================================================

# ── Stage 1: Frontend Assets ───────────────────────────────────────
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --legacy-peer-deps --no-audit --no-fund

COPY resources/ resources/
COPY vite.config.js tsconfig.json ./
RUN npm run build

# ── Stage 2: PHP Dependencies ──────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# ── Stage 3: Production Runtime ─────────────────────────────────────
FROM php:8.4-fpm-alpine

# ── System Dependencies ──────────────────────────────────────────────
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql16-client \
    curl \
    bash \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pcntl

# ── Application Files ─────────────────────────────────────────────────
COPY --from=frontend /app/public/build /var/www/public/build
COPY --from=vendor  /app/vendor        /var/www/vendor
COPY . /var/www

# ── Configuration Files ───────────────────────────────────────────────
COPY docker/nginx.conf      /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# ── Storage & Cache Permissions ───────────────────────────────────────
RUN mkdir -p /var/www/storage/logs \
    /var/www/storage/framework/cache/data \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# ── Laravel Optimization (Production) ─────────────────────────────────
RUN php /var/www/artisan storage:link --force 2>/dev/null || true

# ── Cleanup ───────────────────────────────────────────────────────────
RUN rm -rf /var/www/.env.example \
    /var/www/.env \
    /var/www/.git \
    /var/www/node_modules \
    /var/www/tests \
    /var/www/docker

# ── Expose Ports ──────────────────────────────────────────────────────
EXPOSE 80 443 8080

# ── Healthcheck ───────────────────────────────────────────────────────
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

# ── Entrypoint ────────────────────────────────────────────────────────
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
