# Script para verificar e fazer push
Set-Location "C:\Users\Johan 7K\Documents\GitHub\site-produtos"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "   VERIFICAÇÃO E PUSH DO REPOSITÓRIO" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Verifica se é repositório git
if (-not (Test-Path .git)) {
    Write-Host "⚠️  Repositório não inicializado. Inicializando..." -ForegroundColor Yellow
    git init
    Write-Host "✅ Repositório inicializado`n" -ForegroundColor Green
}

# Status
Write-Host "📊 Status do repositório:" -ForegroundColor Cyan
$status = git status --porcelain
if ($status) {
    Write-Host $status -ForegroundColor Yellow
    Write-Host "`n📦 Adicionando arquivos..." -ForegroundColor Cyan
    git add .
    
    Write-Host "💾 Criando commit..." -ForegroundColor Cyan
    git commit -m "feat: sistema sem banco de dados e PIX único - Removida dependência MySQL - Sistema FileStorage JSON - Admin gerenciar PIX - Checkout PIX com QR Code"
    Write-Host "✅ Commit criado`n" -ForegroundColor Green
} else {
    Write-Host "✅ Nenhuma alteração pendente`n" -ForegroundColor Green
}

# Branch
Write-Host "🌿 Configurando branch main..." -ForegroundColor Cyan
git branch -M main 2>$null

# Remote
Write-Host "🔗 Configurando remote..." -ForegroundColor Cyan
git remote remove origin 2>$null
git remote add origin https://github.com/DexsDevelopers/site-produtos.git 2>$null

Write-Host "`n📍 Remote configurado:" -ForegroundColor Green
git remote -v

# Últimos commits
Write-Host "`n📝 Últimos 3 commits:" -ForegroundColor Cyan
git log --oneline -3

# Push
Write-Host "`n🚀 Fazendo push para origin/main..." -ForegroundColor Yellow
Write-Host "   (Isso pode solicitar credenciais do GitHub)`n" -ForegroundColor Gray

$pushOutput = git push -u origin main 2>&1 | Out-String

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅✅✅ PUSH REALIZADO COM SUCESSO! ✅✅✅" -ForegroundColor Green
    Write-Host "`n🔗 Repositório: https://github.com/DexsDevelopers/site-produtos" -ForegroundColor Cyan
} else {
    Write-Host "❌ Erro ao fazer push:" -ForegroundColor Red
    Write-Host $pushOutput -ForegroundColor Red
    
    Write-Host "`n💡 Possíveis soluções:" -ForegroundColor Yellow
    Write-Host "1. Configure credenciais do GitHub:" -ForegroundColor White
    Write-Host "   git config --global user.name 'Seu Nome'" -ForegroundColor Gray
    Write-Host "   git config --global user.email 'seu@email.com'" -ForegroundColor Gray
    Write-Host "`n2. Use Personal Access Token:" -ForegroundColor White
    Write-Host "   - Acesse: https://github.com/settings/tokens" -ForegroundColor Gray
    Write-Host "   - Crie um token com permissão 'repo'" -ForegroundColor Gray
    Write-Host "   - Use o token como senha" -ForegroundColor Gray
}

Write-Host "`n========================================`n" -ForegroundColor Cyan

