# ====================================================================
# AI Agents — Python MCP Microservice
# Serves: MCP tools for accreditation data processing & predictions
# ====================================================================

FROM python:3.12-slim

WORKDIR /app

# ── System Dependencies ──────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc \
    libpq-dev \
    curl \
    && rm -rf /var/lib/apt/lists/*

# ── Python Dependencies ──────────────────────────────────────────────
COPY requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt

# ── Application ───────────────────────────────────────────────────────
COPY . .

# ── Healthcheck ───────────────────────────────────────────────────────
HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
    CMD curl -f http://localhost:8001/health || exit 1

# ── Expose Port ───────────────────────────────────────────────────────
EXPOSE 8001

# ── Run ───────────────────────────────────────────────────────────────
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8001", "--workers", "1"]
