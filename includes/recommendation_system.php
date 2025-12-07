<?php
// includes/recommendation_system.php - Sistema de Recomendações Inteligente

class RecommendationSystem {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Recomendações baseadas em produtos similares
    public function getProdutosSimilares($produto_id, $limite = 8) {
        // Buscar categoria do produto
        $stmt = $this->pdo->prepare("SELECT categoria_id FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        $categoria_id = $stmt->fetchColumn();
        
        if (!$categoria_id) {
            return [];
        }
        
        // Buscar produtos da mesma categoria, excluindo o produto atual
        $sql = "SELECT p.*, 
                       AVG(a.nota) as media_avaliacoes,
                       COUNT(a.id) as total_avaliacoes,
                       COUNT(pv.id) as total_visualizacoes
                FROM produtos p
                LEFT JOIN avaliacoes a ON p.id = a.produto_id AND a.status = 'aprovada'
                LEFT JOIN produto_visualizacoes pv ON p.id = pv.produto_id
                WHERE p.categoria_id = ? AND p.id != ?
                GROUP BY p.id
                ORDER BY media_avaliacoes DESC, total_avaliacoes DESC, total_visualizacoes DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoria_id, $produto_id, $limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Recomendações baseadas no histórico do usuário
    public function getRecomendacoesUsuario($usuario_id, $limite = 8) {
        if (!$usuario_id) {
            return $this->getProdutosPopulares($limite);
        }
        
        // Buscar categorias que o usuário mais visualizou
        $stmt = $this->pdo->prepare("
            SELECT p.categoria_id, COUNT(*) as total_views
            FROM produto_visualizacoes pv
            JOIN produtos p ON pv.produto_id = p.id
            WHERE pv.usuario_id = ?
            GROUP BY p.categoria_id
            ORDER BY total_views DESC
            LIMIT 3
        ");
        $stmt->execute([$usuario_id]);
        $categorias_preferidas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($categorias_preferidas)) {
            return $this->getProdutosPopulares($limite);
        }
        
        // Buscar produtos das categorias preferidas
        $placeholders = str_repeat('?,', count($categorias_preferidas) - 1) . '?';
        $sql = "SELECT p.*, 
                       AVG(a.nota) as media_avaliacoes,
                       COUNT(a.id) as total_avaliacoes
                FROM produtos p
                LEFT JOIN avaliacoes a ON p.id = a.produto_id AND a.status = 'aprovada'
                WHERE p.categoria_id IN ($placeholders)
                GROUP BY p.id
                ORDER BY media_avaliacoes DESC, total_avaliacoes DESC
                LIMIT ?";
        
        $params = array_merge($categorias_preferidas, [$limite]);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // "Clientes que compraram isso também compraram"
    public function getProdutosRelacionados($produto_id, $limite = 6) {
        // Buscar produtos que foram comprados junto com este produto
        $sql = "SELECT p.*, 
                       COUNT(*) as frequencia,
                       AVG(a.nota) as media_avaliacoes
                FROM produtos p
                JOIN carrinho_itens ci1 ON p.id = ci1.produto_id
                JOIN carrinho_itens ci2 ON ci1.carrinho_id = ci2.carrinho_id
                LEFT JOIN avaliacoes a ON p.id = a.produto_id AND a.status = 'aprovada'
                WHERE ci2.produto_id = ? AND p.id != ?
                GROUP BY p.id
                ORDER BY frequencia DESC, media_avaliacoes DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$produto_id, $produto_id, $limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Produtos em alta
    public function getProdutosPopulares($limite = 8) {
        $sql = "SELECT p.*, 
                       AVG(a.nota) as media_avaliacoes,
                       COUNT(a.id) as total_avaliacoes,
                       COUNT(pv.id) as total_visualizacoes,
                       COUNT(ci.id) as total_compras
                FROM produtos p
                LEFT JOIN avaliacoes a ON p.id = a.produto_id AND a.status = 'aprovada'
                LEFT JOIN produto_visualizacoes pv ON p.id = pv.produto_id
                LEFT JOIN carrinho_itens ci ON p.id = ci.produto_id
                WHERE p.ativo = 1
                GROUP BY p.id
                ORDER BY (total_visualizacoes * 0.3 + total_compras * 0.7 + media_avaliacoes * 0.5) DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Produtos recém-adicionados
    public function getProdutosRecentes($limite = 8) {
        $sql = "SELECT p.*, 
                       AVG(a.nota) as media_avaliacoes,
                       COUNT(a.id) as total_avaliacoes
                FROM produtos p
                LEFT JOIN avaliacoes a ON p.id = a.produto_id AND a.status = 'aprovada'
                WHERE p.ativo = 1
                GROUP BY p.id
                ORDER BY p.data_cadastro DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Produtos com desconto
    public function getProdutosEmOferta($limite = 8) {
        $sql = "SELECT p.*, 
                       AVG(a.nota) as media_avaliacoes,
                       COUNT(a.id) as total_avaliacoes,
                       ((p.preco_antigo - p.preco) / p.preco_antigo * 100) as desconto_percentual
                FROM produtos p
                LEFT JOIN avaliacoes a ON p.id = a.produto_id AND a.status = 'aprovada'
                WHERE p.ativo = 1 AND p.preco_antigo > p.preco
                GROUP BY p.id
                ORDER BY desconto_percentual DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Recomendações personalizadas baseadas em comportamento
    public function getRecomendacoesPersonalizadas($usuario_id, $limite = 12) {
        if (!$usuario_id) {
            return $this->getProdutosPopulares($limite);
        }
        
        // Buscar produtos que o usuário visualizou mas não comprou
        $stmt = $this->pdo->prepare("
            SELECT p.categoria_id, COUNT(*) as views
            FROM produto_visualizacoes pv
            JOIN produtos p ON pv.produto_id = p.id
            WHERE pv.usuario_id = ? 
            AND pv.produto_id NOT IN (
                SELECT DISTINCT ci.produto_id 
                FROM carrinho_itens ci 
                JOIN carrinhos c ON ci.carrinho_id = c.id 
                WHERE c.usuario_id = ? AND c.status = 'finalizado'
            )
            GROUP BY p.categoria_id
            ORDER BY views DESC
            LIMIT 3
        ");
        $stmt->execute([$usuario_id, $usuario_id]);
        $categorias_interesse = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($categorias_interesse)) {
            return $this->getProdutosPopulares($limite);
        }
        
        $categoria_ids = array_column($categorias_interesse, 'categoria_id');
        $placeholders = str_repeat('?,', count($categoria_ids) - 1) . '?';
        
        $sql = "SELECT p.*, 
                       AVG(a.nota) as media_avaliacoes,
                       COUNT(a.id) as total_avaliacoes,
                       COUNT(pv.id) as total_views
                FROM produtos p
                LEFT JOIN avaliacoes a ON p.id = a.produto_id AND a.status = 'aprovada'
                LEFT JOIN produto_visualizacoes pv ON p.id = pv.produto_id
                WHERE p.categoria_id IN ($placeholders)
                AND p.id NOT IN (
                    SELECT DISTINCT ci.produto_id 
                    FROM carrinho_itens ci 
                    JOIN carrinhos c ON ci.carrinho_id = c.id 
                    WHERE c.usuario_id = ? AND c.status = 'finalizado'
                )
                GROUP BY p.id
                ORDER BY total_views DESC, media_avaliacoes DESC
                LIMIT ?";
        
        $params = array_merge($categoria_ids, [$usuario_id, $limite]);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Registrar visualização de produto
    public function registrarVisualizacao($produto_id, $usuario_id = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO produto_visualizacoes (produto_id, usuario_id, data_visualizacao, ip_address) 
            VALUES (?, ?, NOW(), ?)
        ");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return $stmt->execute([$produto_id, $usuario_id, $ip]);
    }
    
    // Buscar produtos por tags similares
    public function getProdutosPorTagsSimilares($produto_id, $limite = 6) {
        // Buscar tags do produto atual
        $stmt = $this->pdo->prepare("
            SELECT t.id, t.nome 
            FROM produto_tags pt 
            JOIN tags t ON pt.tag_id = t.id 
            WHERE pt.produto_id = ?
        ");
        $stmt->execute([$produto_id]);
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($tags)) {
            return [];
        }
        
        $tag_ids = array_column($tags, 'id');
        $placeholders = str_repeat('?,', count($tag_ids) - 1) . '?';
        
        // Buscar produtos com tags similares
        $sql = "SELECT p.*, 
                       COUNT(pt.tag_id) as tags_comuns,
                       AVG(a.nota) as media_avaliacoes
                FROM produtos p
                JOIN produto_tags pt ON p.id = pt.produto_id
                LEFT JOIN avaliacoes a ON p.id = a.produto_id AND a.status = 'aprovada'
                WHERE pt.tag_id IN ($placeholders) AND p.id != ?
                GROUP BY p.id
                ORDER BY tags_comuns DESC, media_avaliacoes DESC
                LIMIT ?";
        
        $params = array_merge($tag_ids, [$produto_id, $limite]);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obter estatísticas de recomendações
    public function getEstatisticasRecomendacoes($usuario_id = null) {
        $sql = "SELECT 
                    COUNT(DISTINCT pv.produto_id) as produtos_visualizados,
                    COUNT(DISTINCT pv.usuario_id) as usuarios_ativos,
                    AVG(a.nota) as media_avaliacoes_geral
                FROM produto_visualizacoes pv
                LEFT JOIN avaliacoes a ON pv.produto_id = a.produto_id AND a.status = 'aprovada'";
        
        $params = [];
        if ($usuario_id) {
            $sql .= " WHERE pv.usuario_id = ?";
            $params[] = $usuario_id;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
