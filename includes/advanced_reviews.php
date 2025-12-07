<?php
// includes/advanced_reviews.php - Sistema de Avaliações Avançado

class AdvancedReviews {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Adicionar avaliação com foto
    public function adicionarAvaliacao($produto_id, $usuario_id, $nota, $comentario, $fotos = []) {
        try {
            $this->pdo->beginTransaction();
            
            // Inserir avaliação
            $stmt = $this->pdo->prepare("
                INSERT INTO avaliacoes (produto_id, usuario_id, nota, comentario, data_avaliacao, status) 
                VALUES (?, ?, ?, ?, NOW(), 'aprovada')
            ");
            $stmt->execute([$produto_id, $usuario_id, $nota, $comentario]);
            $avaliacao_id = $this->pdo->lastInsertId();
            
            // Salvar fotos se houver
            if (!empty($fotos)) {
                foreach ($fotos as $foto) {
                    $this->salvarFotoAvaliacao($avaliacao_id, $foto);
                }
            }
            
            // Atualizar média do produto
            $this->atualizarMediaProduto($produto_id);
            
            $this->pdo->commit();
            return ['success' => true, 'avaliacao_id' => $avaliacao_id];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // Salvar foto da avaliação
    private function salvarFotoAvaliacao($avaliacao_id, $foto) {
        $upload_dir = 'assets/uploads/avaliacoes/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = uniqid() . '_' . time() . '.jpg';
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($foto['tmp_name'], $filepath)) {
            $stmt = $this->pdo->prepare("
                INSERT INTO fotos_avaliacoes (avaliacao_id, foto_url) VALUES (?, ?)
            ");
            $stmt->execute([$avaliacao_id, $filepath]);
        }
    }
    
    // Buscar avaliações com filtros
    public function buscarAvaliacoes($produto_id, $filtros = []) {
        $sql = "
            SELECT a.*, u.nome as nome_usuario, u.avatar,
                   GROUP_CONCAT(f.foto_url) as fotos
            FROM avaliacoes a
            JOIN usuarios u ON a.usuario_id = u.id
            LEFT JOIN fotos_avaliacoes f ON a.id = f.avaliacao_id
            WHERE a.produto_id = ? AND a.status = 'aprovada'
        ";
        
        $params = [$produto_id];
        
        // Filtro por nota
        if (isset($filtros['nota']) && $filtros['nota'] > 0) {
            $sql .= " AND a.nota = ?";
            $params[] = $filtros['nota'];
        }
        
        // Filtro por fotos
        if (isset($filtros['com_fotos']) && $filtros['com_fotos']) {
            $sql .= " AND f.foto_url IS NOT NULL";
        }
        
        // Ordenação
        $ordenacao = $filtros['ordenacao'] ?? 'data_avaliacao DESC';
        $sql .= " ORDER BY " . $ordenacao;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Responder avaliação (vendedor)
    public function responderAvaliacao($avaliacao_id, $resposta, $vendedor_id) {
        $stmt = $this->pdo->prepare("
            UPDATE avaliacoes 
            SET resposta = ?, resposta_data = NOW(), vendedor_id = ?
            WHERE id = ?
        ");
        return $stmt->execute([$resposta, $vendedor_id, $avaliacao_id]);
    }
    
    // Atualizar média do produto
    private function atualizarMediaProduto($produto_id) {
        $stmt = $this->pdo->prepare("
            SELECT AVG(nota) as media, COUNT(*) as total
            FROM avaliacoes 
            WHERE produto_id = ? AND status = 'aprovada'
        ");
        $stmt->execute([$produto_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $this->pdo->prepare("
            UPDATE produtos 
            SET media_avaliacoes = ?, total_avaliacoes = ?
            WHERE id = ?
        ");
        $stmt->execute([
            round($resultado['media'], 1),
            $resultado['total'],
            $produto_id
        ]);
    }
    
    // Buscar estatísticas de avaliações
    public function getEstatisticasAvaliacoes($produto_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total,
                AVG(nota) as media,
                SUM(CASE WHEN nota = 5 THEN 1 ELSE 0 END) as cinco_estrelas,
                SUM(CASE WHEN nota = 4 THEN 1 ELSE 0 END) as quatro_estrelas,
                SUM(CASE WHEN nota = 3 THEN 1 ELSE 0 END) as tres_estrelas,
                SUM(CASE WHEN nota = 2 THEN 1 ELSE 0 END) as duas_estrelas,
                SUM(CASE WHEN nota = 1 THEN 1 ELSE 0 END) as uma_estrela
            FROM avaliacoes 
            WHERE produto_id = ? AND status = 'aprovada'
        ");
        $stmt->execute([$produto_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
