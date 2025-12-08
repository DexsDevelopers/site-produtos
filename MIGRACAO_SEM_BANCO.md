# 🔄 Migração para Sistema sem Banco de Dados

## ✅ Mudanças Implementadas

### 1. Sistema de Armazenamento em Arquivo JSON

- **Arquivo**: `includes/file_storage.php`
- **Localização dos dados**: `data/produtos.json` e `data/config.json`
- **Funcionalidades**:
  - Gerenciamento de produtos
  - Configuração de chave PIX
  - Busca e filtros
  - Categorias

### 2. Gerenciamento de Chave PIX

- **Página Admin**: `admin/gerenciar_pix.php`
- **Acesso**: Menu Admin > Gerenciar PIX
- **Funcionalidades**:
  - Configurar chave PIX única para todos os produtos
  - Alterar chave PIX a qualquer momento
  - Validação de formato de chave PIX
  - Informações do recebedor (nome e cidade)

### 3. Checkout PIX

- **Página**: `checkout_pix.php`
- **Funcionalidades**:
  - Geração automática de QR Code PIX
  - Código PIX copiável
  - Informações do recebedor
  - Instruções de pagamento

### 4. Arquivos Atualizados

- ✅ `config.php` - Removida conexão com banco de dados
- ✅ `index.php` - Usa FileStorage para buscar produtos
- ✅ `produto.php` - Usa FileStorage para buscar produto
- ✅ `busca.php` - Usa FileStorage para busca e filtros
- ✅ `checkout.php` - Redireciona para checkout_pix.php
- ✅ `admin/templates/header_admin.php` - Adicionado link para Gerenciar PIX

## 📁 Estrutura de Arquivos

```
data/
├── produtos.json      # Armazena todos os produtos
└── config.json        # Armazena configurações (chave PIX, etc.)
```

## 🚀 Como Usar

### Configurar Chave PIX

1. Acesse o painel administrativo: `/admin/`
2. Clique em "Gerenciar PIX" no menu lateral
3. Preencha:
   - **Chave PIX**: CPF, CNPJ, email, telefone (+5511999999999) ou chave aleatória
   - **Nome do Recebedor**: Nome completo ou razão social
   - **Cidade**: Cidade do recebedor
4. Clique em "Salvar Chave PIX"

### Adicionar Produtos

Os produtos agora são gerenciados através do sistema de arquivos. Use a página de administração para adicionar/editar produtos.

## ⚠️ Importante

- A chave PIX configurada será aplicada a **TODOS os produtos**
- Ao alterar a chave PIX, todos os produtos automaticamente usarão a nova chave
- Os arquivos JSON são criados automaticamente na primeira execução
- Faça backup regular dos arquivos em `data/`

## 🔒 Segurança

- Os arquivos JSON em `data/` estão no `.gitignore` para não serem commitados
- Mantenha backups seguros dos arquivos de dados
- Configure permissões adequadas no servidor (755 para diretórios, 644 para arquivos)

## 📝 Notas Técnicas

- O sistema não depende mais de banco de dados MySQL
- Todos os dados são armazenados em arquivos JSON
- O sistema é mais simples e fácil de fazer backup (apenas copiar a pasta `data/`)
- Performance adequada para lojas pequenas/médias

