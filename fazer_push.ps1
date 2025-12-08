# Script para fazer push completo
Write-Host "`n=== Adicionando arquivos ===" -ForegroundColor Cyan
git add .

Write-Host "`n=== Status após adicionar ===" -ForegroundColor Cyan
git status --short

Write-Host "`n=== Criando commit ===" -ForegroundColor Cyan
git commit -m "feat: migração para sistema sem banco de dados e integração PIX única

- Removida dependência do banco de dados MySQL
- Implementado sistema de armazenamento em arquivo JSON (FileStorage)
- Criada página admin para gerenciar chave PIX única
- Implementado checkout PIX com QR Code
- Atualizados index.php, produto.php, busca.php para usar FileStorage
- Adicionado checkout_pix.php com geração de código PIX
- Adicionado .gitignore para proteger arquivos sensíveis"

Write-Host "`n=== Fazendo push para origin/main ===" -ForegroundColor Yellow
git push origin main

Write-Host "`n=== Verificando status final ===" -ForegroundColor Cyan
git status

Write-Host "`n=== Últimos commits ===" -ForegroundColor Cyan
git log --oneline -3

Write-Host "`n✅ Concluído! Verifique em: https://github.com/DexsDevelopers/site-produtos" -ForegroundColor Green

