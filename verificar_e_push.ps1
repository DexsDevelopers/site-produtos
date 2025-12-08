# Script para verificar status e fazer push
cd "C:\Users\Johan 7K\Documents\GitHub\site-produtos"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "   VERIFICAÇÃO E PUSH" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# 1. Status
Write-Host "[1] Status do repositório:" -ForegroundColor Yellow
git status
Write-Host ""

# 2. Últimos commits
Write-Host "[2] Últimos 3 commits:" -ForegroundColor Yellow
git log --oneline -3
Write-Host ""

# 3. Commits pendentes
Write-Host "[3] Commits pendentes para push:" -ForegroundColor Yellow
$pending = git log origin/main..HEAD --oneline 2>&1
if ($pending -match 'fatal' -or [string]::IsNullOrWhiteSpace($pending)) {
    Write-Host "✅ Nenhum commit pendente (já está sincronizado)" -ForegroundColor Green
} else {
    Write-Host $pending -ForegroundColor Cyan
    Write-Host "⚠️  Há commits pendentes para push" -ForegroundColor Yellow
}

Write-Host ""

# 4. Adicionar e commitar se houver mudanças
$status = git status --porcelain
if ($status) {
    Write-Host "[4] Há arquivos modificados. Adicionando..." -ForegroundColor Yellow
    git add .
    
    Write-Host "[5] Criando commit..." -ForegroundColor Yellow
    git commit -m "feat: substituição PagBank por PIX único - Todos produtos usam chave PIX única - Botões redirecionam para checkout_pix.php"
    Write-Host "✅ Commit criado" -ForegroundColor Green
} else {
    Write-Host "[4] Nenhuma alteração para commitar" -ForegroundColor Gray
}

Write-Host ""

# 5. Tentar push
Write-Host "[6] Tentando fazer push..." -ForegroundColor Yellow
Write-Host "   (Se falhar, pode ser problema de conexão)`n" -ForegroundColor Gray

$pushOutput = git push origin main 2>&1 | Out-String

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅✅✅ PUSH REALIZADO COM SUCESSO! ✅✅✅" -ForegroundColor Green
    Write-Host "`n🔗 Verifique em: https://github.com/DexsDevelopers/site-produtos" -ForegroundColor Cyan
} else {
    Write-Host "❌ Erro no push:" -ForegroundColor Red
    Write-Host $pushOutput -ForegroundColor Red
    
    Write-Host "`n💡 Se o erro for de conexão (porta 443):" -ForegroundColor Yellow
    Write-Host "   - Verifique firewall/antivírus" -ForegroundColor White
    Write-Host "   - Tente fazer push pelo GitHub Desktop" -ForegroundColor White
    Write-Host "   - Ou faça upload manual pelo site do GitHub" -ForegroundColor White
}

Write-Host "`n========================================`n" -ForegroundColor Cyan

