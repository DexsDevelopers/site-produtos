# Script para fazer push com output completo
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "   PUSH MANUAL PARA GITHUB" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Navega para o diretório
Set-Location "C:\Users\Johan 7K\Documents\GitHub\site-produtos"

# 1. Status inicial
Write-Host "1. Verificando status inicial..." -ForegroundColor Yellow
$status = git status
Write-Host $status

# 2. Adicionar arquivos
Write-Host "`n2. Adicionando arquivos ao staging..." -ForegroundColor Yellow
git add .
$addResult = $?
if ($addResult) {
    Write-Host "✅ Arquivos adicionados" -ForegroundColor Green
} else {
    Write-Host "❌ Erro ao adicionar arquivos" -ForegroundColor Red
}

# 3. Verificar o que será commitado
Write-Host "`n3. Arquivos que serão commitados:" -ForegroundColor Yellow
$staged = git diff --cached --name-only
if ($staged) {
    Write-Host $staged -ForegroundColor Cyan
} else {
    Write-Host "⚠️  Nenhum arquivo para commitar" -ForegroundColor Yellow
}

# 4. Criar commit
Write-Host "`n4. Criando commit..." -ForegroundColor Yellow
$commitMsg = "feat: atualização do projeto - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
git commit -m $commitMsg
$commitResult = $?
if ($commitResult) {
    Write-Host "✅ Commit criado" -ForegroundColor Green
} else {
    Write-Host "⚠️  Nenhuma alteração para commitar ou commit já existe" -ForegroundColor Yellow
}

# 5. Verificar branch
Write-Host "`n5. Verificando branch..." -ForegroundColor Yellow
$branch = git branch --show-current
Write-Host "Branch atual: $branch" -ForegroundColor Cyan

# 6. Verificar remote
Write-Host "`n6. Verificando remote..." -ForegroundColor Yellow
$remote = git remote -v
Write-Host $remote

# 7. Verificar se há commits para push
Write-Host "`n7. Verificando commits pendentes..." -ForegroundColor Yellow
$commitsAhead = git rev-list --count origin/main..HEAD 2>&1
if ($commitsAhead -match '^\d+$') {
    Write-Host "Commits à frente: $commitsAhead" -ForegroundColor Cyan
} else {
    Write-Host "Sem commits pendentes ou branch não configurada" -ForegroundColor Yellow
}

# 8. Fazer push
Write-Host "`n8. Fazendo push para origin/main..." -ForegroundColor Yellow
Write-Host "   (Isso pode solicitar credenciais)`n" -ForegroundColor Gray

$pushOutput = git push origin main 2>&1 | Out-String

Write-Host "Output do push:" -ForegroundColor Cyan
Write-Host $pushOutput

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅✅✅ PUSH REALIZADO COM SUCESSO! ✅✅✅" -ForegroundColor Green
    Write-Host "`n🔗 Verifique em: https://github.com/DexsDevelopers/site-produtos" -ForegroundColor Cyan
} else {
    Write-Host "`n❌ Erro no push (código: $LASTEXITCODE)" -ForegroundColor Red
    Write-Host "`n💡 Possíveis soluções:" -ForegroundColor Yellow
    Write-Host "1. Execute manualmente: git push origin main" -ForegroundColor White
    Write-Host "2. Configure credenciais do GitHub" -ForegroundColor White
    Write-Host "3. Use Personal Access Token" -ForegroundColor White
}

# 9. Status final
Write-Host "`n9. Status final:" -ForegroundColor Yellow
git status

Write-Host "`n========================================`n" -ForegroundColor Cyan

