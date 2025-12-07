# 🔧 Solução para Acesso ao Painel Administrativo

## ❌ Problema Identificado

O painel administrativo não está acessível porque:

1. Não existe usuário administrador no banco de dados
2. A tabela `usuarios` pode não existir
3. O sistema de autenticação precisa ser configurado

## ✅ Solução Passo a Passo

### 1. **Criar Usuário Administrador**

Acesse: `http://seudominio.com/criar_admin.php`

Este script irá:

- ✅ Verificar se a tabela `usuarios` existe
- ✅ Criar a tabela se necessário
- ✅ Criar um usuário administrador padrão
- ✅ Mostrar todos os usuários do sistema

**Credenciais padrão criadas:**

- 📧 **Email:** admin@loja.com
- 🔑 **Senha:** admin123
- 👑 **Role:** admin

### 2. **Fazer Login como Administrador**

Acesse: `http://seudominio.com/admin_login.php`

Use as credenciais:

- Email: `admin@loja.com`
- Senha: `admin123`

### 3. **Acessar o Painel Administrativo**

Após o login, você será redirecionado para:
`http://seudominio.com/admin/index.php`

## 🛠️ Arquivos Criados para Resolver o Problema

### 1. **`criar_admin.php`**

- Script para criar usuário administrador
- Verifica e cria a tabela `usuarios`
- Lista todos os usuários do sistema

### 2. **`admin_login.php`**

- Login específico para administradores
- Interface moderna e responsiva
- Validação de permissões

### 3. **`teste_admin.php`**

- Script de teste para verificar status do usuário
- Mostra informações de debug
- Útil para diagnosticar problemas

### 4. **`admin/secure.php` (Atualizado)**

- Redirecionamento corrigido para `admin_login.php`
- Verificação de permissões melhorada

## 🔍 Como Verificar se Está Funcionando

### Teste 1: Verificar Usuário Admin

```
1. Acesse: http://seudominio.com/criar_admin.php
2. Verifique se aparece: "✅ Usuário administrador criado com sucesso!"
3. Anote as credenciais mostradas
```

### Teste 2: Fazer Login

```
1. Acesse: http://seudominio.com/admin_login.php
2. Use: admin@loja.com / admin123
3. Deve redirecionar para o painel admin
```

### Teste 3: Acessar Painel Admin

```
1. Acesse: http://seudominio.com/admin/index.php
2. Deve mostrar o dashboard administrativo
3. Verifique se as estatísticas aparecem
```

## 🚨 Possíveis Problemas e Soluções

### Problema: "Tabela usuarios não existe"

**Solução:** Execute `criar_admin.php` primeiro

### Problema: "Acesso negado"

**Solução:** Verifique se o usuário tem role = 'admin'

### Problema: "Erro de conexão com banco"

**Solução:** Verifique as credenciais em `config.php`

### Problema: "Página em branco"

**Solução:** Ative o debug em `config.php`:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📋 Estrutura da Tabela Usuarios

```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🎯 Próximos Passos

1. **Execute `criar_admin.php`** para criar o usuário admin
2. **Acesse `admin_login.php`** para fazer login
3. **Teste o painel admin** em `admin/index.php`
4. **Configure produtos e categorias** através do painel

## 📞 Suporte

Se ainda houver problemas:

1. Verifique os logs de erro do PHP
2. Confirme se o banco de dados está funcionando
3. Teste com `teste_admin.php` para debug

---

**Desenvolvido para resolver o problema de acesso ao painel administrativo!** 🚀
