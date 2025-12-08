# Script para fazer push das correções
Write-Host "`n=== Adicionando arquivos ===" -ForegroundColor Cyan
git add .

Write-Host "`n=== Status ===" -ForegroundColor Cyan
git status --short

Write-Host "`n=== Criando commit ===" -ForegroundColor Cyan
git commit -m "fix: correção erro 500 no admin

- Removido uso de PDO em admin/secure.php
- Atualizado admin/index.php para usar FileStorage
- Melhorado tratamento de erros no FileStorage
- Adicionado teste de FileStorage (admin/test_file_storage.php)"

Write-Host "`n=== Fazendo push ===" -ForegroundColor Yellow
git push origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Push realizado com sucesso!" -ForegroundColor Green
    Write-Host "Verifique em: https://github.com/DexsDevelopers/site-produtos" -ForegroundColor Cyan
} else {
    Write-Host "`n❌ Erro no push. Verifique as credenciais." -ForegroundColor Red
}

Write-Host "`n=== Status final ===" -ForegroundColor Cyan
git status

