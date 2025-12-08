# Script simples para fazer push
cd "C:\Users\Johan 7K\Documents\GitHub\site-produtos"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "   PUSH PARA GITHUB" -ForegroundColor Cyan  
Write-Host "========================================`n" -ForegroundColor Cyan

# Configurar credential helper
Write-Host "[1/6] Configurando credential helper..." -ForegroundColor Yellow
git config --global credential.helper store

# Status
Write-Host "`n[2/6] Status do repositório:" -ForegroundColor Yellow
git status

# Adicionar
Write-Host "`n[3/6] Adicionando arquivos..." -ForegroundColor Yellow
git add .
Write-Host "✅ Arquivos adicionados" -ForegroundColor Green

# Commit
Write-Host "`n[4/6] Criando commit..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
git commit -m "feat: atualização - $timestamp"
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Commit criado" -ForegroundColor Green
} else {
    Write-Host "⚠️  Nenhuma alteração para commitar" -ForegroundColor Yellow
}

# Verificar remote
Write-Host "`n[5/6] Verificando remote..." -ForegroundColor Yellow
git remote -v

# Push
Write-Host "`n[6/6] Fazendo push para origin/main..." -ForegroundColor Yellow
Write-Host "   (Se pedir credenciais, use seu token do GitHub)`n" -ForegroundColor Gray

git push origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅✅✅ PUSH REALIZADO COM SUCESSO! ✅✅✅" -ForegroundColor Green
    Write-Host "`n🔗 Verifique: https://github.com/DexsDevelopers/site-produtos" -ForegroundColor Cyan
} else {
    Write-Host "`n❌ Erro no push" -ForegroundColor Red
    Write-Host "`n💡 Dica: Execute manualmente: git push origin main" -ForegroundColor Yellow
}

Write-Host "`n========================================`n" -ForegroundColor Cyan

