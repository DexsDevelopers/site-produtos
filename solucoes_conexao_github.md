# 🔧 Soluções para Erro de Conexão com GitHub

## ❌ Erro Atual
```
fatal: unable to access 'https://github.com/...': 
Failed to connect to github.com port 443 after 21114 ms: 
Could not connect to server
```

## ✅ Soluções

### 1. Aumentar Timeout do Git

```bash
git config --global http.postBuffer 524288000
git config --global http.lowSpeedLimit 0
git config --global http.lowSpeedTime 999999
```

### 2. Verificar Firewall/Antivírus

- Desative temporariamente o firewall/antivírus
- Adicione exceção para Git/GitHub
- Verifique se não há VPN bloqueando

### 3. Usar SSH ao invés de HTTPS

```bash
# Verificar se já tem chave SSH
ls ~/.ssh

# Se não tiver, gerar chave
ssh-keygen -t ed25519 -C "162776282+DexsDevelopers@users.noreply.github.com"

# Adicionar chave ao GitHub (copiar conteúdo de ~/.ssh/id_ed25519.pub)
# Depois mudar remote para SSH
git remote set-url origin git@github.com:DexsDevelopers/site-produtos.git
```

### 4. Configurar Proxy (se estiver em rede corporativa)

```bash
# Se tiver proxy
git config --global http.proxy http://proxy:porta
git config --global https.proxy https://proxy:porta

# Se não tiver proxy, desabilitar
git config --global --unset http.proxy
git config --global --unset https.proxy
```

### 5. Tentar Push com Mais Tempo

```bash
# Aumentar timeout
git config --global http.timeout 300
git push origin main
```

### 6. Usar GitHub CLI (gh)

```bash
# Instalar GitHub CLI
# Depois fazer push via CLI
gh repo sync
```

### 7. Verificar DNS

```bash
# Testar DNS
nslookup github.com

# Se não resolver, usar DNS do Google
# 8.8.8.8 ou 1.1.1.1
```

## 🚀 Solução Rápida Recomendada

Execute estes comandos:

```bash
# 1. Aumentar timeouts
git config --global http.timeout 300
git config --global http.postBuffer 524288000

# 2. Tentar push novamente
git push origin main

# 3. Se ainda falhar, tentar com SSH
git remote set-url origin git@github.com:DexsDevelopers/site-produtos.git
git push origin main
```

