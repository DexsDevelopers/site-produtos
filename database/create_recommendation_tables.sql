-- SQL para criar tabelas do sistema de recomendações

-- Tabela de visualizações de produtos
CREATE TABLE IF NOT EXISTS produto_visualizacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    usuario_id INT NULL,
    data_visualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_produto_visualizacoes_produto (produto_id),
    INDEX idx_produto_visualizacoes_usuario (usuario_id),
    INDEX idx_produto_visualizacoes_data (data_visualizacao)
);

-- Tabela de tags (se não existir)
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    cor VARCHAR(7) DEFAULT '#3B82F6',
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de relacionamento produto-tag
CREATE TABLE IF NOT EXISTS produto_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    tag_id INT NOT NULL,
    data_associacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    UNIQUE KEY unique_produto_tag (produto_id, tag_id)
);

-- Tabela de carrinhos (se não existir)
CREATE TABLE IF NOT EXISTS carrinhos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    session_id VARCHAR(100),
    status ENUM('ativo', 'finalizado', 'abandonado') DEFAULT 'ativo',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_finalizacao TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_carrinhos_usuario (usuario_id),
    INDEX idx_carrinhos_status (status)
);

-- Tabela de itens do carrinho (se não existir)
CREATE TABLE IF NOT EXISTS carrinho_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    carrinho_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT DEFAULT 1,
    preco_unitario DECIMAL(10,2),
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (carrinho_id) REFERENCES carrinhos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    INDEX idx_carrinho_itens_carrinho (carrinho_id),
    INDEX idx_carrinho_itens_produto (produto_id)
);

-- Tabela de filtros salvos do usuário
CREATE TABLE IF NOT EXISTS filtros_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    filtros JSON,
    data_salvamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_filtros (usuario_id)
);

-- Adicionar colunas à tabela de produtos se não existirem
ALTER TABLE produtos 
ADD COLUMN IF NOT EXISTS ativo BOOLEAN DEFAULT TRUE,
ADD COLUMN IF NOT EXISTS data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS vendas INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS visualizacoes INT DEFAULT 0;

-- Índices para performance
CREATE INDEX IF NOT EXISTS idx_produtos_categoria_ativo ON produtos(categoria_id, ativo);
CREATE INDEX IF NOT EXISTS idx_produtos_data_cadastro ON produtos(data_cadastro);
CREATE INDEX IF NOT EXISTS idx_produtos_vendas ON produtos(vendas);
CREATE INDEX IF NOT EXISTS idx_produtos_visualizacoes ON produtos(visualizacoes);
