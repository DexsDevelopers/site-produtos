# Script para verificar status completo do repositório
cd "C:\Users\Johan 7K\Documents\GitHub\site-produtos"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "   STATUS DO REPOSITÓRIO" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Remote
Write-Host "[1] Remote configurado:" -ForegroundColor Yellow
git remote -v
Write-Host ""

# Status
Write-Host "[2] Status do repositório:" -ForegroundColor Yellow
git status
Write-Host ""

# Últimos commits locais
Write-Host "[3] Últimos 5 commits locais:" -ForegroundColor Yellow
git log --oneline -5
Write-Host ""

# Commits pendentes
Write-Host "[4] Commits pendentes para push:" -ForegroundColor Yellow
$pending = git log origin/main..HEAD --oneline 2>&1
if ($pending -match 'fatal') {
    Write-Host "⚠️  Erro ao verificar commits pendentes" -ForegroundColor Red
    Write-Host "   Pode ser problema de conexão" -ForegroundColor Gray
} elseif ([string]::IsNullOrWhiteSpace($pending)) {
    Write-Host "✅ Nenhum commit pendente - repositório sincronizado" -ForegroundColor Green
} else {
    Write-Host "⚠️  Há commits pendentes:" -ForegroundColor Yellow
    Write-Host $pending -ForegroundColor Cyan
}

Write-Host ""

# Branch atual
Write-Host "[5] Branch atual:" -ForegroundColor Yellow
git branch --show-current
Write-Host ""

# Último commit no remote (se conseguir acessar)
Write-Host "[6] Tentando verificar último commit no GitHub..." -ForegroundColor Yellow
try {
    $remoteCommit = git ls-remote origin main 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Conexão com GitHub OK" -ForegroundColor Green
        $commitHash = ($remoteCommit -split '\s+')[0]
        Write-Host "Último commit no GitHub: $commitHash" -ForegroundColor Cyan
    } else {
        Write-Host "⚠️  Não foi possível conectar ao GitHub" -ForegroundColor Yellow
        Write-Host "   Pode ser problema de conexão ou autenticação" -ForegroundColor Gray
    }
} catch {
    Write-Host "⚠️  Erro ao verificar GitHub" -ForegroundColor Yellow
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "🔗 Repositório: https://github.com/DexsDevelopers/site-produtos" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan





