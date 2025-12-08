# Script para fazer push e salvar output
Set-Location "C:\Users\Johan 7K\Documents\GitHub\site-produtos"

$logFile = "git_push_log.txt"

Write-Host "`n=== Iniciando Push ===" -ForegroundColor Cyan
Write-Host "Log será salvo em: $logFile`n" -ForegroundColor Gray

# Configurar credential helper se não estiver
$credHelper = git config --global credential.helper
if (-not $credHelper) {
    Write-Host "Configurando credential helper..." -ForegroundColor Yellow
    git config --global credential.helper store | Out-Null
}

# Função para executar e logar
function Execute-GitCommand {
    param($command, $description)
    
    Write-Host "$description..." -ForegroundColor Yellow
    $output = Invoke-Expression $command 2>&1 | Out-String
    Add-Content -Path $logFile -Value "`n=== $description ==="
    Add-Content -Path $logFile -Value $output
    Write-Host $output
    return $output
}

# Limpar log anterior
if (Test-Path $logFile) {
    Remove-Item $logFile
}

# 1. Status
Execute-GitCommand "git status" "1. Verificando status"

# 2. Adicionar
Execute-GitCommand "git add ." "2. Adicionando arquivos"

# 3. Status após add
Execute-GitCommand "git status --short" "3. Arquivos no staging"

# 4. Commit
$commitMsg = "feat: atualização automática - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
Execute-GitCommand "git commit -m '$commitMsg'" "4. Criando commit"

# 5. Verificar commits pendentes
Execute-GitCommand "git log origin/main..HEAD --oneline" "5. Commits pendentes"

# 6. Push
Write-Host "`n6. Fazendo push..." -ForegroundColor Yellow
Write-Host "   (Pode solicitar credenciais)`n" -ForegroundColor Gray

$pushOutput = git push origin main 2>&1 | Out-String
Add-Content -Path $logFile -Value "`n=== Push Output ==="
Add-Content -Path $logFile -Value $pushOutput
Write-Host $pushOutput

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ PUSH REALIZADO COM SUCESSO!" -ForegroundColor Green
} else {
    Write-Host "`n❌ Erro no push (código: $LASTEXITCODE)" -ForegroundColor Red
    Write-Host "Verifique o arquivo $logFile para mais detalhes" -ForegroundColor Yellow
}

# 7. Status final
Execute-GitCommand "git status" "7. Status final"

Write-Host "`n=== Concluído ===" -ForegroundColor Cyan
Write-Host "Log completo salvo em: $logFile" -ForegroundColor Gray

