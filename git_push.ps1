# Script para fazer push do repositório
Set-Location "C:\Users\Johan 7K\Documents\GitHub\site-produtos"

Write-Host "=== Git Push para GitHub ===" -ForegroundColor Cyan
Write-Host ""

# Verifica se é um repositório git
if (-not (Test-Path .git)) {
    Write-Host "Inicializando repositório Git..." -ForegroundColor Yellow
    git init
}

# Verifica status
Write-Host "Verificando status do repositório..." -ForegroundColor Yellow
$status = git status --porcelain
if ($status) {
    Write-Host "Arquivos modificados encontrados:" -ForegroundColor Green
    Write-Host $status
    
    Write-Host "`nAdicionando arquivos ao staging..." -ForegroundColor Yellow
    git add .
    
    Write-Host "Criando commit..." -ForegroundColor Yellow
    git commit -m "feat: migração para sistema sem banco de dados e integração PIX única - Removida dependência do banco de dados MySQL - Implementado sistema de armazenamento em arquivo JSON - Criada página admin para gerenciar chave PIX única - Implementado checkout PIX com QR Code"
} else {
    Write-Host "Nenhuma alteração para commitar." -ForegroundColor Gray
}

# Configura branch
Write-Host "`nConfigurando branch main..." -ForegroundColor Yellow
git branch -M main 2>$null

# Configura remote
Write-Host "Configurando remote origin..." -ForegroundColor Yellow
git remote remove origin 2>$null
git remote add origin https://github.com/DexsDevelopers/site-produtos.git

Write-Host "`nRemote configurado:" -ForegroundColor Green
git remote -v

# Faz push
Write-Host "`nFazendo push para origin/main..." -ForegroundColor Yellow
$pushResult = git push -u origin main 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Push realizado com sucesso!" -ForegroundColor Green
    Write-Host "Repositório: https://github.com/DexsDevelopers/site-produtos" -ForegroundColor Cyan
} else {
    Write-Host "`n❌ Erro ao fazer push:" -ForegroundColor Red
    Write-Host $pushResult -ForegroundColor Red
    Write-Host "`nPossíveis causas:" -ForegroundColor Yellow
    Write-Host "1. Credenciais do GitHub não configuradas" -ForegroundColor Gray
    Write-Host "2. Token de acesso necessário" -ForegroundColor Gray
    Write-Host "3. Repositório remoto não existe ou sem permissão" -ForegroundColor Gray
    Write-Host "`nSoluções:" -ForegroundColor Yellow
    Write-Host "- Configure um Personal Access Token do GitHub" -ForegroundColor Gray
    Write-Host "- Ou use: git config --global credential.helper store" -ForegroundColor Gray
}

Write-Host "`n=== Concluído ===" -ForegroundColor Cyan

