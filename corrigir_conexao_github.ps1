# Script para corrigir conexão com GitHub
Write-Host "`n=== Corrigindo Conexão com GitHub ===" -ForegroundColor Cyan

# 1. Aumentar timeouts
Write-Host "`n[1] Configurando timeouts..." -ForegroundColor Yellow
git config --global http.timeout 300
git config --global http.postBuffer 524288000
git config --global http.lowSpeedLimit 0
git config --global http.lowSpeedTime 999999
Write-Host "✅ Timeouts configurados" -ForegroundColor Green

# 2. Verificar proxy
Write-Host "`n[2] Verificando proxy..." -ForegroundColor Yellow
$proxy = git config --global http.proxy
if ($proxy) {
    Write-Host "Proxy configurado: $proxy" -ForegroundColor Cyan
    Write-Host "Se estiver em rede sem proxy, remova com:" -ForegroundColor Yellow
    Write-Host "  git config --global --unset http.proxy" -ForegroundColor Gray
} else {
    Write-Host "✅ Nenhum proxy configurado" -ForegroundColor Green
}

# 3. Testar conexão
Write-Host "`n[3] Testando conexão com GitHub..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "https://github.com" -TimeoutSec 10 -UseBasicParsing -ErrorAction Stop
    Write-Host "✅ Conexão HTTPS OK" -ForegroundColor Green
} catch {
    Write-Host "❌ Conexão HTTPS falhou" -ForegroundColor Red
    Write-Host "   Erro: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "`n💡 Possíveis causas:" -ForegroundColor Yellow
    Write-Host "   - Firewall bloqueando" -ForegroundColor Gray
    Write-Host "   - VPN ativa" -ForegroundColor Gray
    Write-Host "   - Proxy necessário" -ForegroundColor Gray
    Write-Host "   - Problema de rede" -ForegroundColor Gray
}

# 4. Tentar push
Write-Host "`n[4] Tentando fazer push..." -ForegroundColor Yellow
Write-Host "   (Aguarde, pode demorar devido ao timeout aumentado)`n" -ForegroundColor Gray

$pushResult = git push origin main 2>&1 | Out-String

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅✅✅ PUSH REALIZADO COM SUCESSO! ✅✅✅" -ForegroundColor Green
} else {
    Write-Host "❌ Push ainda falhou" -ForegroundColor Red
    Write-Host $pushResult -ForegroundColor Red
    Write-Host "`n💡 Próximos passos:" -ForegroundColor Yellow
    Write-Host "1. Verifique firewall/antivírus" -ForegroundColor White
    Write-Host "2. Tente usar SSH (veja solucoes_conexao_github.md)" -ForegroundColor White
    Write-Host "3. Tente fazer push pelo GitHub Desktop ou web" -ForegroundColor White
}

Write-Host "`n=== Concluído ===" -ForegroundColor Cyan

