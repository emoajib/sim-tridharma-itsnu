#!/bin/bash
set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
PASS=0; FAIL=0
check() { local n="$1"; local c="$2"; if $c; then echo -e "  ${GREEN}✅ PASS${NC} $n"; ((PASS++)); else echo -e "  ${RED}❌ FAIL${NC} $n"; ((FAIL++)); fi; }

echo "🔍 MCP Health Check — $(date)"
echo ""

# 1. Direct health endpoint
health=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8001/health 2>/dev/null || echo "000")
check "GET /health → HTTP $health" [ "$health" = "200" ]

# 2. MCP list tools
tools_json=$(curl -s http://localhost:8001/api/mcp/tools 2>/dev/null)
tools_count=$(echo "$tools_json" | python3 -c "import sys,json; d=json.load(sys.stdin); print(len(d.get('tools',[])))" 2>/dev/null || echo "0")
check "GET /api/mcp/tools → $tools_count tools" [ "$tools_count" -gt 0 ]

# 3. MCP tool call — peringatan_check
result=$(curl -s -X POST http://localhost:8001/api/mcp/tools/call \
  -H "Content-Type: application/json" \
  -H "X-API-Key: ${AGENT_API_KEY:-}" \
  -d '{"name":"peringatan_check","arguments":{"prodi_id":1}}' 2>/dev/null)
has_result=$(echo "$result" | python3 -c "import sys,json; d=json.load(sys.stdin); print('result' in d)" 2>/dev/null || echo "False")
check "POST /api/mcp/tools/call → peringatan_check" [ "$has_result" = "True" ]

# 4. MCP tool call — prediksi_skor
result2=$(curl -s -X POST http://localhost:8001/api/mcp/tools/call \
  -H "Content-Type: application/json" \
  -H "X-API-Key: ${AGENT_API_KEY:-}" \
  -d '{"name":"prediksi_skor","arguments":{"prodi_id":1}}' 2>/dev/null)
has_result2=$(echo "$result2" | python3 -c "import sys,json; d=json.load(sys.stdin); print('result' in d)" 2>/dev/null || echo "False")
check "POST /api/mcp/tools/call → prediksi_skor" [ "$has_result2" = "True" ]

# 5. PHP health check via artisan
php_result=$(php artisan tinker --execute="echo app(App\Services\MCP\MCPClientService::class)->healthCheck()['agents'] ? 'OK' : 'FAIL';" 2>/dev/null)
check "php artisan mcp health → agents" [ "$php_result" = "OK" ]

# 6. RAG service
rag_health=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:5001/health 2>/dev/null || echo "000")
check "RAG health → HTTP $rag_health" [ "$rag_health" = "200" -o "$rag_health" = "000" ]  # optional

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "Results: ${GREEN}$PASS passed${NC}, ${RED}$FAIL failed${NC}"
exit $FAIL
