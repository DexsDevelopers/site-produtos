# Script para fazer push do repositório para o GitHub
Set-Location "C:\Users\Johan 7K\Documents\GitHub\site-produtos"

Write-Host "=== Configurando repositório Git ===" -ForegroundColor Cyan

# Verifica se é um repositório git
if (-not (Test-Path .git)) {
    Write-Host "Inicializando repositório Git..." -ForegroundColor Yellow
    git init
}

# Verifica se há arquivos para commitar
$status = git status --porcelain
if ($status) {
    Write-Host "Adicionando arquivos ao staging..." -ForegroundColor Yellow
    git add .
    
    Write-Host "Criando commit..." -ForegroundColor Yellow
    git commit -m "feat: atualização do projeto site-produtos - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
}

# Configura branch main
Write-Host "Configurando branch main..." -ForegroundColor Yellow
git branch -M main 2>$null

# Configura remote
Write-Host "Configurando remote origin..." -ForegroundColor Yellow
git remote remove origin 2>$null
git remote add origin https://github.com/DexsDevelopers/site-produtos.git

# Verifica remote
Write-Host "`nRemote configurado:" -ForegroundColor Green
git remote -v

# Faz push
Write-Host "`nFazendo push para origin/main..." -ForegroundColor Yellow
git push -u origin main --force

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Push realizado com sucesso!" -ForegroundColor Green
} else {
    Write-Host "`n❌ Erro ao fazer push. Verifique as credenciais do GitHub." -ForegroundColor Red
    Write-Host "Você pode precisar configurar autenticação:" -ForegroundColor Yellow
    Write-Host "  git config --global user.name 'Seu Nome'" -ForegroundColor Gray
    Write-Host "  git config --global user.email 'seu@email.com'" -ForegroundColor Gray
    Write-Host "  Ou usar Personal Access Token do GitHub" -ForegroundColor Gray
}

