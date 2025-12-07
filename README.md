# 🛒 Minha Loja - E-commerce Moderno

Uma loja online moderna e responsiva desenvolvida em PHP com foco em performance, segurança e experiência do usuário.

## ✨ Características Principais

### 🔒 Segurança

- Sanitização de dados de entrada
- Proteção contra SQL Injection
- Validação de tokens CSRF
- Headers de segurança
- Tratamento robusto de erros

### 📱 Design Responsivo

- Interface otimizada para mobile e desktop
- Carrosséis responsivos
- Menu lateral para dispositivos móveis
- Lazy loading de imagens

### ⚡ Performance

- Sistema de cache inteligente
- Otimização automática de imagens
- Lazy loading de conteúdo
- Compressão de assets

### 🛍️ Funcionalidades de E-commerce

- Catálogo de produtos com categorias
- Sistema de busca avançada com filtros
- Carrinho de compras funcional
- Sistema de avaliações
- Painel administrativo

### 🔍 SEO Otimizado

- Meta tags dinâmicas
- Structured data (Schema.org)
- Sitemap XML automático
- URLs amigáveis
- Open Graph e Twitter Cards

## 🚀 Instalação

### Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)

### Passos

1. Clone o repositório
2. Configure o banco de dados em `config.php`
3. Importe a estrutura do banco (se necessário)
4. Configure as permissões de upload
5. Acesse o site

## 📁 Estrutura do Projeto

```
public_html/
├── admin/                  # Painel administrativo
├── assets/                 # Assets estáticos
│   ├── images/            # Imagens do site
│   ├── js/                # JavaScript
│   └── uploads/           # Uploads de usuários
├── cache/                 # Cache do sistema
├── config/                # Configurações
├── includes/              # Classes e funções auxiliares
├── logs/                  # Logs do sistema
├── templates/             # Templates HTML
├── 404.php               # Página de erro 404
├── busca.php             # Sistema de busca
├── carrinho.php          # Carrinho de compras
├── index.php             # Página inicial
├── produto.php           # Página do produto
└── README.md             # Este arquivo
```

## 🛠️ Funcionalidades Técnicas

### Sistema de Cache

- Cache de consultas de banco de dados
- Limpeza automática de cache expirado
- Cache por tempo configurável

### Otimização de Imagens

- Redimensionamento automático
- Compressão inteligente
- Suporte a múltiplos formatos

### Tratamento de Erros

- Logs detalhados de erros
- Páginas de erro personalizadas
- Tratamento diferenciado para produção/desenvolvimento

### Sistema de Notificações

- Toast notifications
- Mensagens de sucesso/erro
- Feedback visual para ações do usuário

## 🎨 Personalização

### Cores e Tema

As cores principais podem ser alteradas no arquivo `templates/header.php`:

```css
colors: {
    'brand-red': '#E53E3E',
    'brand-black': '#000000',
    'brand-gray': '#1A202C'
}
```

### Configurações

Principais configurações em `config.php`:

- Dados de conexão com banco
- Configurações de segurança
- Timeouts e limites

## 📊 Painel Administrativo

Acesse `/admin/` para:

- Gerenciar produtos
- Gerenciar categorias
- Gerenciar banners
- Visualizar estatísticas
- Gerenciar pedidos

## 🔧 Manutenção

### Logs

- Erros: `logs/error.log`
- Atividades: `logs/atividades.log`

### Cache

- Limpeza manual: Delete arquivos em `cache/`
- Limpeza automática: Configurada para 1 hora

### Backup

- Faça backup regular do banco de dados
- Mantenha backup dos uploads em `assets/uploads/`

## 🚀 Melhorias Futuras

- [ ] Sistema de pagamento integrado
- [ ] Notificações por email
- [ ] Sistema de cupons de desconto
- [ ] Relatórios avançados
- [ ] API REST
- [ ] PWA (Progressive Web App)

## 📝 Licença

Este projeto é de uso livre para fins educacionais e comerciais.

## 🤝 Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para:

- Reportar bugs
- Sugerir melhorias
- Enviar pull requests

## 📞 Suporte

Para suporte técnico ou dúvidas:

- Email: suporte@minhaloja.com
- Documentação: Consulte este README

---

**Desenvolvido com ❤️ para proporcionar a melhor experiência de compra online.**
