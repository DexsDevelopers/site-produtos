# 📝 Como Editar Categorias e Banners

## 🎯 **Acesso Rápido aos Editores**

### **1. Para Editar Categorias:**

#### **Opção A: Pela Lista de Categorias**

1. Acesse: `admin/gerenciar_categorias.php`
2. Na tabela de categorias, clique em **"Editar"** (link azul)
3. Você será direcionado para o editor completo

#### **Opção B: Direto no Editor**

1. Acesse: `admin/editar_categoria.php`
2. Para editar uma existente, adicione `?id=ID_DA_CATEGORIA` na URL
3. Exemplo: `admin/editar_categoria.php?id=1`

### **2. Para Editar Banners:**

#### **Opção A: Pela Lista de Banners**

1. Acesse: `admin/gerenciar_banners.php`
2. Na tabela de banners, clique em **"Editar"** (link azul)
3. Você será direcionado para o editor completo

#### **Opção B: Direto no Editor**

1. Acesse: `admin/editar_banner.php`
2. Para editar um existente, adicione `?id=ID_DO_BANNER` na URL
3. Exemplo: `admin/editar_banner.php?id=1`

## 🎛️ **Funcionalidades dos Editores**

### **Editor de Categorias:**

- ✅ **Nome da Categoria** (obrigatório)
- ✅ **Descrição** (opcional)
- ✅ **Ordem de Exibição** (numérico)
- ✅ **Ícone** (seletor FontAwesome)
- ✅ **Cor da Categoria** (seletor de cores)
- ✅ **Status** (ativa/inativa)
- ✅ **Destaque** (aparece em posição destacada)
- ✅ **SEO** (meta title e description)

### **Editor de Banners:**

- ✅ **Título** (opcional)
- ✅ **Subtítulo** (opcional)
- ✅ **Link do Botão** (URL)
- ✅ **Texto do Botão** (ex: "Saiba Mais")
- ✅ **Tipo de Banner** (principal, categoria, promoção, destaque)
- ✅ **Posição** (ordem de exibição)
- ✅ **Status** (ativo/inativo)
- ✅ **Nova Aba** (abrir link em nova aba)
- ✅ **Imagem** (upload com preview)

## 🚀 **Links Diretos**

### **Categorias:**

- **Gerenciar**: `admin/gerenciar_categorias.php`
- **Nova Categoria**: `admin/editar_categoria.php`
- **Editar Categoria ID 1**: `admin/editar_categoria.php?id=1`

### **Banners:**

- **Gerenciar**: `admin/gerenciar_banners.php`
- **Novo Banner**: `admin/editar_banner.php`
- **Editar Banner ID 1**: `admin/editar_banner.php?id=1`

## 🎨 **Interface dos Editores**

### **Características:**

- **Preview em Tempo Real**: Veja as mudanças instantaneamente
- **Validação Automática**: Campos obrigatórios são validados
- **Seletores Visuais**: Ícones e cores com interface amigável
- **Responsivo**: Funciona em desktop e mobile
- **Salvamento Inteligente**: Dados são salvos automaticamente

### **Navegação:**

- **Botão "Voltar"**: Retorna à lista de gerenciamento
- **Botão "Cancelar"**: Descarta as alterações
- **Botão "Salvar"**: Salva as alterações
- **Preview**: Visualização em tempo real

## 🔧 **Dicas de Uso**

### **Para Categorias:**

1. **Ícones**: Use classes FontAwesome (ex: `fas fa-tag`)
2. **Cores**: Use códigos hexadecimais (ex: `#FF3B5C`)
3. **Ordem**: Números menores aparecem primeiro
4. **SEO**: Títulos até 60 caracteres, descrições até 160

### **Para Banners:**

1. **Imagens**: Use formatos JPG, PNG, GIF ou WebP
2. **Tamanho**: Máximo 5MB por imagem
3. **Tipos**: Escolha o tipo adequado para cada banner
4. **Links**: Use URLs completas (ex: `https://exemplo.com`)

## 🎯 **Fluxo de Trabalho Recomendado**

### **1. Criar Nova Categoria:**

1. Acesse `admin/gerenciar_categorias.php`
2. Clique em **"Nova Categoria"**
3. Preencha os campos obrigatórios
4. Configure ícone e cor
5. Salve

### **2. Editar Categoria Existente:**

1. Acesse `admin/gerenciar_categorias.php`
2. Clique em **"Editar"** na categoria desejada
3. Faça as alterações necessárias
4. Salve

### **3. Criar Novo Banner:**

1. Acesse `admin/gerenciar_banners.php`
2. Clique em **"Novo Banner"**
3. Faça upload da imagem
4. Configure título e link
5. Salve

### **4. Editar Banner Existente:**

1. Acesse `admin/gerenciar_banners.php`
2. Clique em **"Editar"** no banner desejado
3. Faça as alterações necessárias
4. Salve

## ⚡ **Atalhos Rápidos**

### **URLs Diretas:**

```
# Categorias
admin/gerenciar_categorias.php          # Lista de categorias
admin/editar_categoria.php              # Nova categoria
admin/editar_categoria.php?id=1         # Editar categoria ID 1

# Banners
admin/gerenciar_banners.php             # Lista de banners
admin/editar_banner.php                 # Novo banner
admin/editar_banner.php?id=1            # Editar banner ID 1
```

### **Navegação pelo Admin:**

1. **Dashboard**: `admin/index.php`
2. **Categorias**: Clique em "Gerenciar Categorias"
3. **Banners**: Clique em "Gerenciar Banners"

---

**✅ Agora você tem acesso completo aos editores!**
**🎛️ Use os links "Editar" nas listas ou acesse diretamente os editores**
**🚀 Todas as funcionalidades estão disponíveis e funcionando**

