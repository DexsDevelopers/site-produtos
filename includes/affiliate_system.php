<?php
// includes/affiliate_system.php - Sistema de Afiliação

class AffiliateSystem {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Criar link de afiliado
    public function createAffiliateLink($user_id, $product_id = null, $custom_code = null) {
        try {
            // Verificar se usuário já é afiliado
            $stmt = $this->pdo->prepare("SELECT id FROM afiliados WHERE usuario_id = ?");
            $stmt->execute([$user_id]);
            $afiliado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$afiliado) {
                // Criar novo afiliado
                $stmt = $this->pdo->prepare("
                    INSERT INTO afiliados (usuario_id, codigo_afiliado, status, data_cadastro, comissao_percentual) 
                    VALUES (?, ?, 'ativo', NOW(), 10.0)
                ");
                $codigo = $custom_code ?: $this->generateAffiliateCode($user_id);
                $stmt->execute([$user_id, $codigo]);
                $afiliado_id = $this->pdo->lastInsertId();
            } else {
                $afiliado_id = $afiliado['id'];
            }
            
            // Gerar link de afiliado
            $base_url = 'https://' . $_SERVER['HTTP_HOST'];
            $affiliate_code = $this->getAffiliateCode($user_id);
            
            if ($product_id) {
                $link = $base_url . "/produto.php?id=$product_id&ref=$affiliate_code";
            } else {
                $link = $base_url . "/index.php?ref=$affiliate_code";
            }
            
            return [
                'success' => true,
                'affiliate_id' => $afiliado_id,
                'affiliate_code' => $affiliate_code,
                'affiliate_link' => $link
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Obter código de afiliado
    private function getAffiliateCode($user_id) {
        $stmt = $this->pdo->prepare("SELECT codigo_afiliado FROM afiliados WHERE usuario_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['codigo_afiliado'] : null;
    }
    
    // Gerar código único de afiliado
    private function generateAffiliateCode($user_id) {
        $prefix = 'AFF';
        $suffix = str_pad($user_id, 6, '0', STR_PAD_LEFT);
        return $prefix . $suffix;
    }
    
    // Registrar clique de afiliado
    public function registerClick($affiliate_code, $product_id = null, $ip_address = null) {
        try {
            // Buscar afiliado
            $stmt = $this->pdo->prepare("SELECT id FROM afiliados WHERE codigo_afiliado = ? AND status = 'ativo'");
            $stmt->execute([$affiliate_code]);
            $afiliado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$afiliado) {
                return ['success' => false, 'message' => 'Código de afiliado inválido'];
            }
            
            // Registrar clique
            $stmt = $this->pdo->prepare("
                INSERT INTO cliques_afiliados (afiliado_id, produto_id, ip_address, data_clique) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$afiliado['id'], $product_id, $ip_address]);
            
            // Salvar na sessão para tracking
            $_SESSION['affiliate_code'] = $affiliate_code;
            $_SESSION['affiliate_click_id'] = $this->pdo->lastInsertId();
            
            return ['success' => true, 'click_id' => $this->pdo->lastInsertId()];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Registrar venda de afiliado
    public function registerSale($order_id, $total_amount) {
        try {
            // Verificar se há afiliado na sessão
            if (!isset($_SESSION['affiliate_code'])) {
                return ['success' => false, 'message' => 'Nenhum afiliado associado'];
            }
            
            $affiliate_code = $_SESSION['affiliate_code'];
            
            // Buscar afiliado
            $stmt = $this->pdo->prepare("
                SELECT a.id, a.comissao_percentual, u.nome 
                FROM afiliados a 
                JOIN usuarios u ON a.usuario_id = u.id 
                WHERE a.codigo_afiliado = ? AND a.status = 'ativo'
            ");
            $stmt->execute([$affiliate_code]);
            $afiliado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$afiliado) {
                return ['success' => false, 'message' => 'Afiliado não encontrado'];
            }
            
            // Calcular comissão
            $comissao_valor = ($total_amount * $afiliado['comissao_percentual']) / 100;
            
            // Registrar venda
            $stmt = $this->pdo->prepare("
                INSERT INTO vendas_afiliados (afiliado_id, order_id, valor_venda, comissao_percentual, comissao_valor, status, data_venda) 
                VALUES (?, ?, ?, ?, ?, 'pendente', NOW())
            ");
            $stmt->execute([
                $afiliado['id'], 
                $order_id, 
                $total_amount, 
                $afiliado['comissao_percentual'], 
                $comissao_valor
            ]);
            
            $venda_id = $this->pdo->lastInsertId();
            
            // Atualizar estatísticas do afiliado
            $this->updateAffiliateStats($afiliado['id']);
            
            // Limpar sessão de afiliado
            unset($_SESSION['affiliate_code']);
            unset($_SESSION['affiliate_click_id']);
            
            return [
                'success' => true, 
                'venda_id' => $venda_id,
                'comissao_valor' => $comissao_valor,
                'afiliado_nome' => $afiliado['nome']
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Atualizar estatísticas do afiliado
    private function updateAffiliateStats($afiliado_id) {
        try {
            // Contar vendas
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total_vendas, SUM(valor_venda) as total_vendas_valor, SUM(comissao_valor) as total_comissoes
                FROM vendas_afiliados 
                WHERE afiliado_id = ? AND status = 'aprovada'
            ");
            $stmt->execute([$afiliado_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Contar cliques
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total_cliques 
                FROM cliques_afiliados 
                WHERE afiliado_id = ?
            ");
            $stmt->execute([$afiliado_id]);
            $cliques = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calcular taxa de conversão
            $taxa_conversao = $cliques['total_cliques'] > 0 ? 
                ($stats['total_vendas'] / $cliques['total_cliques']) * 100 : 0;
            
            // Atualizar estatísticas
            $stmt = $this->pdo->prepare("
                UPDATE afiliados SET 
                    total_vendas = ?, 
                    total_vendas_valor = ?, 
                    total_comissoes = ?, 
                    total_cliques = ?, 
                    taxa_conversao = ?,
                    data_ultima_atualizacao = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $stats['total_vendas'],
                $stats['total_vendas_valor'],
                $stats['total_comissoes'],
                $cliques['total_cliques'],
                $taxa_conversao,
                $afiliado_id
            ]);
            
        } catch (Exception $e) {
            error_log('Erro ao atualizar estatísticas do afiliado: ' . $e->getMessage());
        }
    }
    
    // Obter estatísticas do afiliado
    public function getAffiliateStats($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, u.nome, u.email,
                       COUNT(DISTINCT va.id) as vendas_aprovadas,
                       COUNT(DISTINCT ca.id) as cliques_totais
                FROM afiliados a
                JOIN usuarios u ON a.usuario_id = u.id
                LEFT JOIN vendas_afiliados va ON a.id = va.afiliado_id AND va.status = 'aprovada'
                LEFT JOIN cliques_afiliados ca ON a.id = ca.afiliado_id
                WHERE a.usuario_id = ?
                GROUP BY a.id
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    // Obter dashboard do afiliado
    public function getAffiliateDashboard($user_id) {
        try {
            $stats = $this->getAffiliateStats($user_id);
            if (!$stats) return null;
            
            // Vendas recentes
            $stmt = $this->pdo->prepare("
                SELECT va.*, p.nome as produto_nome
                FROM vendas_afiliados va
                LEFT JOIN produtos p ON va.produto_id = p.id
                WHERE va.afiliado_id = ?
                ORDER BY va.data_venda DESC
                LIMIT 10
            ");
            $stmt->execute([$stats['id']]);
            $vendas_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Cliques recentes
            $stmt = $this->pdo->prepare("
                SELECT ca.*, p.nome as produto_nome
                FROM cliques_afiliados ca
                LEFT JOIN produtos p ON ca.produto_id = p.id
                WHERE ca.afiliado_id = ?
                ORDER BY ca.data_clique DESC
                LIMIT 10
            ");
            $stmt->execute([$stats['id']]);
            $cliques_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'stats' => $stats,
                'vendas_recentes' => $vendas_recentes,
                'cliques_recentes' => $cliques_recentes
            ];
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    // Gerar relatório de comissões
    public function generateCommissionReport($afiliado_id, $data_inicio = null, $data_fim = null) {
        try {
            $sql = "
                SELECT va.*, u.nome as afiliado_nome, p.nome as produto_nome
                FROM vendas_afiliados va
                JOIN afiliados a ON va.afiliado_id = a.id
                JOIN usuarios u ON a.usuario_id = u.id
                LEFT JOIN produtos p ON va.produto_id = p.id
                WHERE va.afiliado_id = ?
            ";
            
            $params = [$afiliado_id];
            
            if ($data_inicio) {
                $sql .= " AND va.data_venda >= ?";
                $params[] = $data_inicio;
            }
            
            if ($data_fim) {
                $sql .= " AND va.data_venda <= ?";
                $params[] = $data_fim;
            }
            
            $sql .= " ORDER BY va.data_venda DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Aprovar comissão
    public function approveCommission($venda_id) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE vendas_afiliados 
                SET status = 'aprovada', data_aprovacao = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$venda_id]);
            
            // Atualizar estatísticas
            $stmt = $this->pdo->prepare("SELECT afiliado_id FROM vendas_afiliados WHERE id = ?");
            $stmt->execute([$venda_id]);
            $venda = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($venda) {
                $this->updateAffiliateStats($venda['afiliado_id']);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Rejeitar comissão
    public function rejectCommission($venda_id, $motivo = '') {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE vendas_afiliados 
                SET status = 'rejeitada', motivo_rejeicao = ?, data_rejeicao = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$motivo, $venda_id]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
