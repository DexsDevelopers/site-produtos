-- SQL para criar tabelas do sistema de avaliações avançado

-- Tabela de fotos das avaliações
CREATE TABLE IF NOT EXISTS fotos_avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    avaliacao_id INT NOT NULL,
    foto_url VARCHAR(500) NOT NULL,
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (avaliacao_id) REFERENCES avaliacoes(id) ON DELETE CASCADE
);

-- Adicionar colunas à tabela de avaliações existente
ALTER TABLE avaliacoes 
ADD COLUMN IF NOT EXISTS resposta TEXT,
ADD COLUMN IF NOT EXISTS resposta_data TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS vendedor_id INT NULL,
ADD COLUMN IF NOT EXISTS status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'aprovada',
ADD COLUMN IF NOT EXISTS util_nao_util INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS util_sim INT DEFAULT 0;

-- Adicionar colunas à tabela de produtos para cache de avaliações
ALTER TABLE produtos 
ADD COLUMN IF NOT EXISTS media_avaliacoes DECIMAL(2,1) DEFAULT 0.0,
ADD COLUMN IF NOT EXISTS total_avaliacoes INT DEFAULT 0;

-- Índices para performance
CREATE INDEX IF NOT EXISTS idx_avaliacoes_produto_status ON avaliacoes(produto_id, status);
CREATE INDEX IF NOT EXISTS idx_avaliacoes_nota ON avaliacoes(nota);
CREATE INDEX IF NOT EXISTS idx_fotos_avaliacao ON fotos_avaliacoes(avaliacao_id);
