# Script para testar conexão com GitHub
Write-Host "`n=== Testando Conexão com GitHub ===" -ForegroundColor Cyan

# Teste 1: Ping
Write-Host "`n[1] Testando ping para github.com..." -ForegroundColor Yellow
$ping = Test-Connection -ComputerName github.com -Count 2 -ErrorAction SilentlyContinue
if ($ping) {
    Write-Host "✅ Ping OK" -ForegroundColor Green
} else {
    Write-Host "❌ Ping falhou" -ForegroundColor Red
}

# Teste 2: HTTPS
Write-Host "`n[2] Testando conexão HTTPS..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "https://github.com" -TimeoutSec 10 -UseBasicParsing -ErrorAction Stop
    Write-Host "✅ HTTPS OK (Status: $($response.StatusCode))" -ForegroundColor Green
} catch {
    Write-Host "❌ HTTPS falhou: $($_.Exception.Message)" -ForegroundColor Red
}

# Teste 3: Verificar proxy
Write-Host "`n[3] Verificando configurações de proxy..." -ForegroundColor Yellow
$proxy = [System.Net.WebRequest]::GetSystemWebProxy()
$proxyUrl = $proxy.GetProxy("https://github.com")
Write-Host "Proxy configurado: $proxyUrl" -ForegroundColor Cyan

# Teste 4: Configurações Git
Write-Host "`n[4] Configurações Git atuais:" -ForegroundColor Yellow
Write-Host "http.sslVerify: $(git config --global http.sslVerify)" -ForegroundColor Cyan
Write-Host "http.postBuffer: $(git config --global http.postBuffer)" -ForegroundColor Cyan
Write-Host "http.lowSpeedLimit: $(git config --global http.lowSpeedLimit)" -ForegroundColor Cyan
Write-Host "http.lowSpeedTime: $(git config --global http.lowSpeedTime)" -ForegroundColor Cyan

# Teste 5: Tentar conectar via Git
Write-Host "`n[5] Testando conexão Git..." -ForegroundColor Yellow
$gitTest = git ls-remote https://github.com/DexsDevelopers/site-produtos.git 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Conexão Git OK" -ForegroundColor Green
} else {
    Write-Host "❌ Conexão Git falhou" -ForegroundColor Red
    Write-Host $gitTest -ForegroundColor Red
}

Write-Host "`n=== Fim dos Testes ===" -ForegroundColor Cyan

