-- admin/fix_banner_columns.sql - Adicionar colunas faltantes na tabela banners

-- Adicionar colunas que podem estar faltando
ALTER TABLE banners 
ADD COLUMN IF NOT EXISTS subtitulo VARCHAR(255),
ADD COLUMN IF NOT EXISTS texto_botao VARCHAR(50) DEFAULT 'Saiba Mais',
ADD COLUMN IF NOT EXISTS posicao INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS nova_aba TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS data_atualizacao TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;

-- Atualizar posição dos banners existentes se for NULL ou 0
UPDATE banners SET posicao = id WHERE posicao IS NULL OR posicao = 0;

-- Criar índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_banners_ativo ON banners(ativo);
CREATE INDEX IF NOT EXISTS idx_banners_tipo ON banners(tipo);
CREATE INDEX IF NOT EXISTS idx_banners_posicao ON banners(posicao);

-- Verificar se as colunas foram adicionadas
SELECT 'Colunas adicionadas com sucesso!' as status;

