-- admin/update_database.sql - Atualização do Banco de Dados para Funcionalidades Avançadas

-- Adicionar colunas à tabela categorias
ALTER TABLE categorias 
ADD COLUMN IF NOT EXISTS descricao TEXT,
ADD COLUMN IF NOT EXISTS icone VARCHAR(50) DEFAULT 'fas fa-tag',
ADD COLUMN IF NOT EXISTS cor VARCHAR(7) DEFAULT '#FF3B5C',
ADD COLUMN IF NOT EXISTS ativa TINYINT(1) DEFAULT 1,
ADD COLUMN IF NOT EXISTS destaque TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS meta_title VARCHAR(60),
ADD COLUMN IF NOT EXISTS meta_description VARCHAR(160),
ADD COLUMN IF NOT EXISTS data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS data_atualizacao TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;

-- Adicionar colunas à tabela banners
ALTER TABLE banners 
ADD COLUMN IF NOT EXISTS subtitulo VARCHAR(255),
ADD COLUMN IF NOT EXISTS texto_botao VARCHAR(50) DEFAULT 'Saiba Mais',
ADD COLUMN IF NOT EXISTS posicao INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS nova_aba TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS data_atualizacao TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;

-- Atualizar ordem das categorias existentes
UPDATE categorias SET ordem = id WHERE ordem IS NULL OR ordem = 0;

-- Atualizar posição dos banners existentes
UPDATE banners SET posicao = id WHERE posicao IS NULL OR posicao = 0;

-- Criar índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_categorias_ativa ON categorias(ativa);
CREATE INDEX IF NOT EXISTS idx_categorias_ordem ON categorias(ordem);
CREATE INDEX IF NOT EXISTS idx_categorias_destaque ON categorias(destaque);

CREATE INDEX IF NOT EXISTS idx_banners_ativo ON banners(ativo);
CREATE INDEX IF NOT EXISTS idx_banners_tipo ON banners(tipo);
CREATE INDEX IF NOT EXISTS idx_banners_posicao ON banners(posicao);

-- Inserir categorias padrão se não existirem
INSERT IGNORE INTO categorias (nome, descricao, icone, cor, ativa, destaque, ordem) VALUES
('Eletrônicos', 'Produtos eletrônicos e tecnológicos', 'fas fa-laptop', '#3B82F6', 1, 1, 1),
('Roupas', 'Vestuário e acessórios de moda', 'fas fa-tshirt', '#EF4444', 1, 1, 2),
('Casa', 'Produtos para casa e decoração', 'fas fa-home', '#10B981', 1, 0, 3),
('Esportes', 'Artigos esportivos e fitness', 'fas fa-dumbbell', '#F59E0B', 1, 0, 4),
('Livros', 'Livros e materiais educacionais', 'fas fa-book', '#8B5CF6', 1, 0, 5);

-- Inserir banners padrão se não existirem
INSERT IGNORE INTO banners (titulo, subtitulo, link, texto_botao, tipo, posicao, ativo, imagem) VALUES
('Bem-vindo à Nossa Loja', 'Descubra produtos incríveis', '#', 'Explorar', 'principal', 1, 1, 'assets/uploads/banner_padrao.jpg'),
('Promoções Especiais', 'Ofertas imperdíveis', '#', 'Ver Ofertas', 'promocao', 2, 1, 'assets/uploads/banner_promocao.jpg');

-- Comentários para documentação
-- Este script adiciona funcionalidades avançadas às tabelas existentes
-- Execute este script no seu banco de dados para habilitar as novas funcionalidades

