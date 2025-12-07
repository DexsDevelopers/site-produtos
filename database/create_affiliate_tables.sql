-- SQL para criar tabelas do sistema de afiliação

-- Tabela de afiliados
CREATE TABLE IF NOT EXISTS afiliados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    codigo_afiliado VARCHAR(20) NOT NULL UNIQUE,
    status ENUM('ativo', 'inativo', 'suspenso') DEFAULT 'ativo',
    comissao_percentual DECIMAL(5,2) DEFAULT 10.00,
    total_vendas INT DEFAULT 0,
    total_vendas_valor DECIMAL(10,2) DEFAULT 0.00,
    total_comissoes DECIMAL(10,2) DEFAULT 0.00,
    total_cliques INT DEFAULT 0,
    taxa_conversao DECIMAL(5,2) DEFAULT 0.00,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_afiliados_codigo (codigo_afiliado),
    INDEX idx_afiliados_usuario (usuario_id),
    INDEX idx_afiliados_status (status)
);

-- Tabela de cliques de afiliados
CREATE TABLE IF NOT EXISTS cliques_afiliados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    afiliado_id INT NOT NULL,
    produto_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referer VARCHAR(500),
    data_clique TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (afiliado_id) REFERENCES afiliados(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL,
    INDEX idx_cliques_afiliado (afiliado_id),
    INDEX idx_cliques_data (data_clique),
    INDEX idx_cliques_produto (produto_id)
);

-- Tabela de vendas de afiliados
CREATE TABLE IF NOT EXISTS vendas_afiliados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    afiliado_id INT NOT NULL,
    order_id VARCHAR(50) NOT NULL,
    produto_id INT NULL,
    valor_venda DECIMAL(10,2) NOT NULL,
    comissao_percentual DECIMAL(5,2) NOT NULL,
    comissao_valor DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'aprovada', 'rejeitada', 'paga') DEFAULT 'pendente',
    motivo_rejeicao TEXT NULL,
    data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP NULL,
    data_rejeicao TIMESTAMP NULL,
    data_pagamento TIMESTAMP NULL,
    FOREIGN KEY (afiliado_id) REFERENCES afiliados(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL,
    INDEX idx_vendas_afiliado (afiliado_id),
    INDEX idx_vendas_order (order_id),
    INDEX idx_vendas_status (status),
    INDEX idx_vendas_data (data_venda)
);

-- Tabela de pagamentos de comissões
CREATE TABLE IF NOT EXISTS pagamentos_comissoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    afiliado_id INT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    vendas_ids TEXT NOT NULL, -- JSON com IDs das vendas
    status ENUM('pendente', 'processando', 'pago', 'falhou') DEFAULT 'pendente',
    metodo_pagamento ENUM('pix', 'transferencia', 'paypal') DEFAULT 'pix',
    dados_pagamento TEXT NULL, -- JSON com dados do pagamento
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_processamento TIMESTAMP NULL,
    data_pagamento TIMESTAMP NULL,
    observacoes TEXT NULL,
    FOREIGN KEY (afiliado_id) REFERENCES afiliados(id) ON DELETE CASCADE,
    INDEX idx_pagamentos_afiliado (afiliado_id),
    INDEX idx_pagamentos_status (status),
    INDEX idx_pagamentos_data (data_solicitacao)
);

-- Tabela de configurações de afiliação
CREATE TABLE IF NOT EXISTS config_afiliacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(50) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descricao TEXT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir configurações padrão
INSERT INTO config_afiliacao (chave, valor, descricao) VALUES
('comissao_padrao', '10.00', 'Comissão padrão em percentual'),
('comissao_minima_pagamento', '50.00', 'Valor mínimo para solicitar pagamento'),
('dias_aprovacao', '7', 'Dias para aprovação automática de comissões'),
('status_afiliacao', 'ativo', 'Status padrão para novos afiliados'),
('email_notificacao', '1', 'Enviar emails de notificação'),
('pixel_tracking', '1', 'Ativar pixel de tracking')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);

-- Adicionar coluna de afiliado na tabela de pedidos (se existir)
-- ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS afiliado_id INT NULL;
-- ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS comissao_valor DECIMAL(10,2) DEFAULT 0.00;
-- ALTER TABLE pedidos ADD FOREIGN KEY (afiliado_id) REFERENCES afiliados(id) ON DELETE SET NULL;

-- Índices adicionais para performance
CREATE INDEX IF NOT EXISTS idx_afiliados_stats ON afiliados(total_vendas, total_comissoes);
CREATE INDEX IF NOT EXISTS idx_vendas_periodo ON vendas_afiliados(data_venda, status);
CREATE INDEX IF NOT EXISTS idx_cliques_periodo ON cliques_afiliados(data_clique);
