# Script para verificar status e salvar em arquivo
cd "C:\Users\Johan 7K\Documents\GitHub\site-produtos"

$outputFile = "status_repositorio.txt"

Write-Host "Verificando repositório..." -ForegroundColor Yellow
Write-Host "Resultado será salvo em: $outputFile`n" -ForegroundColor Gray

# Limpar arquivo anterior
if (Test-Path $outputFile) {
    Remove-Item $outputFile
}

# Função para executar e salvar
function ExecutarESalvar {
    param($comando, $titulo)
    
    Add-Content -Path $outputFile -Value "`n=== $titulo ==="
    Add-Content -Path $outputFile -Value ""
    
    $resultado = Invoke-Expression $comando 2>&1 | Out-String
    Add-Content -Path $outputFile -Value $resultado
    
    Write-Host "$titulo..." -ForegroundColor Cyan
}

# 1. Remote
ExecutarESalvar "git remote -v" "REMOTE CONFIGURADO"

# 2. Branch
ExecutarESalvar "git branch --show-current" "BRANCH ATUAL"

# 3. Status
ExecutarESalvar "git status" "STATUS DO REPOSITÓRIO"

# 4. Últimos commits
ExecutarESalvar "git log --oneline -5" "ÚLTIMOS 5 COMMITS"

# 5. Commits pendentes
Add-Content -Path $outputFile -Value "`n=== COMMITS PENDENTES ==="
Add-Content -Path $outputFile -Value ""
$pending = git log origin/main..HEAD --oneline 2>&1 | Out-String
if ($pending.Trim()) {
    Add-Content -Path $outputFile -Value $pending
    Write-Host "⚠️  Há commits pendentes" -ForegroundColor Yellow
} else {
    Add-Content -Path $outputFile -Value "Nenhum commit pendente - repositório sincronizado"
    Write-Host "✅ Repositório sincronizado" -ForegroundColor Green
}

# 6. Informações do repositório
Add-Content -Path $outputFile -Value "`n=== INFORMAÇÕES ==="
Add-Content -Path $outputFile -Value ""
Add-Content -Path $outputFile -Value "Repositório: https://github.com/DexsDevelopers/site-produtos"
Add-Content -Path $outputFile -Value "Data/Hora: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"

Write-Host "`n✅ Verificação concluída!" -ForegroundColor Green
Write-Host "📄 Resultado salvo em: $outputFile" -ForegroundColor Cyan
Write-Host "`nAbra o arquivo para ver os detalhes.`n" -ForegroundColor Gray

# Abrir o arquivo
if (Test-Path $outputFile) {
    notepad $outputFile
}





