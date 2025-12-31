# Script para fazer push para main
cd "C:\Users\Johan 7K\Documents\GitHub\site-produtos"

Write-Host "`n=== PUSH PARA MAIN ===" -ForegroundColor Cyan
Write-Host ""

# Status
Write-Host "[1] Status:" -ForegroundColor Yellow
git status
Write-Host ""

# Últimos commits
Write-Host "[2] Últimos commits:" -ForegroundColor Yellow
git log --oneline -5
Write-Host ""

# Verificar se há commits pendentes
Write-Host "[3] Verificando commits pendentes..." -ForegroundColor Yellow
$pending = git log origin/main..HEAD --oneline 2>&1
if ($LASTEXITCODE -eq 0 -and $pending -notmatch 'fatal' -and $pending.Trim()) {
    Write-Host "Commits pendentes:" -ForegroundColor Cyan
    Write-Host $pending
    Write-Host ""
    
    # Fazer push
    Write-Host "[4] Fazendo push..." -ForegroundColor Yellow
    Write-Host "(Aguarde, pode demorar se houver problema de conexão)`n" -ForegroundColor Gray
    
    git push origin main 2>&1 | ForEach-Object {
        Write-Host $_ -ForegroundColor White
    }
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "`n✅ PUSH REALIZADO COM SUCESSO!" -ForegroundColor Green
    } else {
        Write-Host "`n❌ Erro no push (código: $LASTEXITCODE)" -ForegroundColor Red
        Write-Host "`n💡 Se o erro for de conexão, tente:" -ForegroundColor Yellow
        Write-Host "   - Verificar firewall/antivírus" -ForegroundColor White
        Write-Host "   - Usar GitHub Desktop" -ForegroundColor White
        Write-Host "   - Fazer push manualmente pelo terminal" -ForegroundColor White
    }
} else {
    Write-Host "✅ Nenhum commit pendente - já está sincronizado" -ForegroundColor Green
}

Write-Host "`n=== CONCLUÍDO ===" -ForegroundColor Cyan
Write-Host ""





