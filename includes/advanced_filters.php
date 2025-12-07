<?php
// includes/advanced_filters.php - Sistema de Filtros Avançados

class AdvancedFilters {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Aplicar filtros na busca de produtos
    public function buscarProdutosComFiltros($filtros = []) {
        $sql = "SELECT p.*, c.nome as categoria_nome, 
                       AVG(a.nota) as media_avaliacoes,
                       COUNT(a.id) as total_avaliacoes
                FROM produtos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN avaliacoes a ON p.id = a.produto_id AND a.status = 'aprovada'
                WHERE 1=1";
        
        $params = [];
        
        // Filtro por termo de busca
        if (!empty($filtros['termo'])) {
            $sql .= " AND (p.nome LIKE ? OR p.descricao LIKE ? OR p.descricao_curta LIKE ?)";
            $termo = '%' . $filtros['termo'] . '%';
            $params[] = $termo;
            $params[] = $termo;
            $params[] = $termo;
        }
        
        // Filtro por categoria
        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.categoria_id = ?";
            $params[] = $filtros['categoria_id'];
        }
        
        // Filtro por faixa de preço
        if (!empty($filtros['preco_min'])) {
            $sql .= " AND p.preco >= ?";
            $params[] = $filtros['preco_min'];
        }
        if (!empty($filtros['preco_max'])) {
            $sql .= " AND p.preco <= ?";
            $params[] = $filtros['preco_max'];
        }
        
        // Filtro por avaliação mínima
        if (!empty($filtros['avaliacao_min'])) {
            $sql .= " AND (SELECT AVG(nota) FROM avaliacoes WHERE produto_id = p.id AND status = 'aprovada') >= ?";
            $params[] = $filtros['avaliacao_min'];
        }
        
        // Filtro por disponibilidade
        if (isset($filtros['disponivel']) && $filtros['disponivel'] !== '') {
            if ($filtros['disponivel']) {
                $sql .= " AND p.estoque > 0";
            } else {
                $sql .= " AND p.estoque = 0";
            }
        }
        
        // Filtro por marca (se existir coluna marca)
        if (!empty($filtros['marca'])) {
            $sql .= " AND p.marca LIKE ?";
            $params[] = '%' . $filtros['marca'] . '%';
        }
        
        // Filtro por tags (se existir tabela de tags)
        if (!empty($filtros['tags'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM produto_tags pt 
                JOIN tags t ON pt.tag_id = t.id 
                WHERE pt.produto_id = p.id 
                AND t.nome IN (" . str_repeat('?,', count($filtros['tags']) - 1) . "?)
            )";
            $params = array_merge($params, $filtros['tags']);
        }
        
        // Filtro por data de lançamento
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND p.data_cadastro >= ?";
            $params[] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND p.data_cadastro <= ?";
            $params[] = $filtros['data_fim'];
        }
        
        // Agrupar por produto
        $sql .= " GROUP BY p.id";
        
        // Ordenação
        $ordenacao = $this->getOrdenacao($filtros['ordenacao'] ?? 'relevancia');
        $sql .= " ORDER BY " . $ordenacao;
        
        // Paginação
        $limite = $filtros['limite'] ?? 20;
        $offset = ($filtros['pagina'] ?? 0) * $limite;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Contar total de produtos com filtros
    public function contarProdutosComFiltros($filtros = []) {
        $sql = "SELECT COUNT(DISTINCT p.id) as total
                FROM produtos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN avaliacoes a ON p.id = a.produto_id AND a.status = 'aprovada'
                WHERE 1=1";
        
        $params = [];
        
        // Aplicar mesmos filtros da busca
        if (!empty($filtros['termo'])) {
            $sql .= " AND (p.nome LIKE ? OR p.descricao LIKE ? OR p.descricao_curta LIKE ?)";
            $termo = '%' . $filtros['termo'] . '%';
            $params[] = $termo;
            $params[] = $termo;
            $params[] = $termo;
        }
        
        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND p.categoria_id = ?";
            $params[] = $filtros['categoria_id'];
        }
        
        if (!empty($filtros['preco_min'])) {
            $sql .= " AND p.preco >= ?";
            $params[] = $filtros['preco_min'];
        }
        if (!empty($filtros['preco_max'])) {
            $sql .= " AND p.preco <= ?";
            $params[] = $filtros['preco_max'];
        }
        
        if (!empty($filtros['avaliacao_min'])) {
            $sql .= " AND (SELECT AVG(nota) FROM avaliacoes WHERE produto_id = p.id AND status = 'aprovada') >= ?";
            $params[] = $filtros['avaliacao_min'];
        }
        
        if (isset($filtros['disponivel']) && $filtros['disponivel'] !== '') {
            if ($filtros['disponivel']) {
                $sql .= " AND p.estoque > 0";
            } else {
                $sql .= " AND p.estoque = 0";
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    // Obter opções de filtros
    public function getOpcoesFiltros($filtros = []) {
        $opcoes = [];
        
        // Categorias
        $stmt = $this->pdo->query("SELECT id, nome FROM categorias ORDER BY nome");
        $opcoes['categorias'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Faixa de preços
        $stmt = $this->pdo->query("SELECT MIN(preco) as min_preco, MAX(preco) as max_preco FROM produtos");
        $precos = $stmt->fetch(PDO::FETCH_ASSOC);
        $opcoes['faixa_precos'] = [
            'min' => floor($precos['min_preco']),
            'max' => ceil($precos['max_preco'])
        ];
        
        // Marcas (se existir)
        $stmt = $this->pdo->query("SELECT DISTINCT marca FROM produtos WHERE marca IS NOT NULL AND marca != '' ORDER BY marca");
        $opcoes['marcas'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Tags (se existir)
        $stmt = $this->pdo->query("SELECT DISTINCT nome FROM tags ORDER BY nome");
        $opcoes['tags'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $opcoes;
    }
    
    // Obter ordenação
    private function getOrdenacao($tipo) {
        switch ($tipo) {
            case 'preco_asc':
                return 'p.preco ASC';
            case 'preco_desc':
                return 'p.preco DESC';
            case 'nome_asc':
                return 'p.nome ASC';
            case 'nome_desc':
                return 'p.nome DESC';
            case 'avaliacao':
                return 'media_avaliacoes DESC, total_avaliacoes DESC';
            case 'mais_vendidos':
                return 'p.vendas DESC';
            case 'mais_recentes':
                return 'p.data_cadastro DESC';
            case 'relevancia':
            default:
                return 'p.id DESC';
        }
    }
    
    // Salvar filtros do usuário
    public function salvarFiltrosUsuario($usuario_id, $filtros) {
        $stmt = $this->pdo->prepare("
            INSERT INTO filtros_usuario (usuario_id, filtros, data_salvamento) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE filtros = VALUES(filtros), data_salvamento = NOW()
        ");
        return $stmt->execute([$usuario_id, json_encode($filtros)]);
    }
    
    // Carregar filtros salvos do usuário
    public function carregarFiltrosUsuario($usuario_id) {
        $stmt = $this->pdo->prepare("SELECT filtros FROM filtros_usuario WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? json_decode($resultado['filtros'], true) : [];
    }
    
    // Obter sugestões de busca
    public function getSugestoesBusca($termo, $limite = 10) {
        $sql = "SELECT DISTINCT nome FROM produtos 
                WHERE nome LIKE ? 
                ORDER BY nome 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['%' . $termo . '%', $limite]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
