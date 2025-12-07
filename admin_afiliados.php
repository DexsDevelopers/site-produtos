<?php
// admin_afiliados.php - Gerenciamento de Afiliados (Admin)
session_start();
require_once 'config.php';
require_once 'includes/affiliate_system.php';

// Verificar se é admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar se é admin (ajustar conforme sua lógica)
$is_admin = ($_SESSION['user_id'] == 1); // Assumindo que user_id = 1 é admin

if (!$is_admin) {
    header('Location: index.php?msg=access_denied');
    exit();
}

$affiliateSystem = new AffiliateSystem($pdo);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'approve_commission':
            $venda_id = $_POST['venda_id'];
            $result = $affiliateSystem->approveCommission($venda_id);
            $message = $result['success'] ? 'Comissão aprovada!' : 'Erro: ' . $result['message'];
            break;
            
        case 'reject_commission':
            $venda_id = $_POST['venda_id'];
            $motivo = $_POST['motivo'] ?? '';
            $result = $affiliateSystem->rejectCommission($venda_id, $motivo);
            $message = $result['success'] ? 'Comissão rejeitada!' : 'Erro: ' . $result['message'];
            break;
            
        case 'update_commission_rate':
            $afiliado_id = $_POST['afiliado_id'];
            $nova_taxa = $_POST['nova_taxa'];
            
            try {
                $stmt = $pdo->prepare("UPDATE afiliados SET comissao_percentual = ? WHERE id = ?");
                $stmt->execute([$nova_taxa, $afiliado_id]);
                $message = 'Taxa de comissão atualizada!';
            } catch (Exception $e) {
                $message = 'Erro ao atualizar taxa: ' . $e->getMessage();
            }
            break;
    }
}

// Buscar dados
try {
    // Estatísticas gerais
    $stats = $pdo->query("
        SELECT 
            COUNT(DISTINCT a.id) as total_afiliados,
            COUNT(DISTINCT va.id) as total_vendas,
            SUM(va.valor_venda) as total_vendas_valor,
            SUM(va.comissao_valor) as total_comissoes,
            AVG(a.taxa_conversao) as media_conversao
        FROM afiliados a
        LEFT JOIN vendas_afiliados va ON a.id = va.afiliado_id
    ")->fetch(PDO::FETCH_ASSOC);
    
    // Afiliados
    $afiliados = $pdo->query("
        SELECT a.*, u.nome, u.email,
               COUNT(DISTINCT va.id) as vendas_aprovadas,
               COUNT(DISTINCT ca.id) as cliques_totais
        FROM afiliados a
        JOIN usuarios u ON a.usuario_id = u.id
        LEFT JOIN vendas_afiliados va ON a.id = va.afiliado_id AND va.status = 'aprovada'
        LEFT JOIN cliques_afiliados ca ON a.id = ca.afiliado_id
        GROUP BY a.id
        ORDER BY a.total_comissoes DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Vendas pendentes
    $vendas_pendentes = $pdo->query("
        SELECT va.*, a.codigo_afiliado, u.nome as afiliado_nome, p.nome as produto_nome
        FROM vendas_afiliados va
        JOIN afiliados a ON va.afiliado_id = a.id
        JOIN usuarios u ON a.usuario_id = u.id
        LEFT JOIN produtos p ON va.produto_id = p.id
        WHERE va.status = 'pendente'
        ORDER BY va.data_venda DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $message = 'Erro ao carregar dados: ' . $e->getMessage();
    $stats = [];
    $afiliados = [];
    $vendas_pendentes = [];
}

$page_title = 'Gerenciamento de Afiliados';
require_once 'templates/header.php';
?>

<style>
.admin-card {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(139, 92, 246, 0.2));
    border: 1px solid rgba(59, 130, 246, 0.3);
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #3B82F6;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #94A3B8;
    font-size: 0.875rem;
}

.table-container {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    overflow: hidden;
}

.table-header {
    background: rgba(59, 130, 246, 0.1);
    padding: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.table-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
    padding: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    align-items: center;
}

.table-row:last-child {
    border-bottom: none;
}

.table-row:hover {
    background: rgba(255, 255, 255, 0.05);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-ativo {
    background: rgba(16, 185, 129, 0.2);
    color: #10B981;
}

.status-inativo {
    background: rgba(107, 114, 128, 0.2);
    color: #6B7280;
}

.status-suspenso {
    background: rgba(239, 68, 68, 0.2);
    color: #EF4444;
}

.action-btn {
    background: rgba(59, 130, 246, 0.2);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #60A5FA;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
    margin: 0.25rem;
}

.action-btn:hover {
    background: rgba(59, 130, 246, 0.4);
    color: white;
}

.action-btn.approve {
    background: rgba(16, 185, 129, 0.2);
    border-color: #10B981;
    color: #10B981;
}

.action-btn.approve:hover {
    background: rgba(16, 185, 129, 0.4);
}

.action-btn.reject {
    background: rgba(239, 68, 68, 0.2);
    border-color: #EF4444;
    color: #EF4444;
}

.action-btn.reject:hover {
    background: rgba(239, 68, 68, 0.4);
}

.modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 50;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-content {
    background: #1F2937;
    border-radius: 0.75rem;
    padding: 1.5rem;
    max-width: 500px;
    width: 100%;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    color: white;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.75rem;
    border-radius: 0.5rem;
}

.form-input:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}
</style>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <h1 class="text-3xl font-bold text-white mb-8">Gerenciamento de Afiliados</h1>
        
        <?php if (isset($message)): ?>
            <div class="bg-green-500/20 text-green-300 p-4 rounded-lg mb-6">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Estatísticas Gerais -->
        <div class="admin-card">
            <h2 class="text-xl font-bold text-white mb-6">Estatísticas Gerais</h2>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_afiliados'] ?? 0 ?></div>
                    <div class="stat-label">Total de Afiliados</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_vendas'] ?? 0 ?></div>
                    <div class="stat-label">Vendas Totais</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">R$ <?= number_format($stats['total_vendas_valor'] ?? 0, 0, ',', '.') ?></div>
                    <div class="stat-label">Valor das Vendas</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">R$ <?= number_format($stats['total_comissoes'] ?? 0, 0, ',', '.') ?></div>
                    <div class="stat-label">Comissões Pagas</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($stats['media_conversao'] ?? 0, 1) ?>%</div>
                    <div class="stat-label">Conversão Média</div>
                </div>
            </div>
        </div>
        
        <!-- Vendas Pendentes -->
        <?php if (!empty($vendas_pendentes)): ?>
        <div class="admin-card">
            <h2 class="text-xl font-bold text-white mb-6">Vendas Pendentes de Aprovação</h2>
            <div class="table-container">
                <div class="table-header">
                    <div class="grid grid-cols-6 gap-4 text-sm font-semibold text-white">
                        <div>Afiliado</div>
                        <div>Produto</div>
                        <div>Valor Venda</div>
                        <div>Comissão</div>
                        <div>Data</div>
                        <div>Ações</div>
                    </div>
                </div>
                
                <?php foreach ($vendas_pendentes as $venda): ?>
                    <div class="table-row">
                        <div>
                            <div class="text-white font-semibold"><?= htmlspecialchars($venda['afiliado_nome']) ?></div>
                            <div class="text-gray-400 text-sm"><?= htmlspecialchars($venda['codigo_afiliado']) ?></div>
                        </div>
                        <div class="text-gray-300"><?= htmlspecialchars($venda['produto_nome'] ?? 'Produto Geral') ?></div>
                        <div class="text-white">R$ <?= number_format($venda['valor_venda'], 2, ',', '.') ?></div>
                        <div class="text-green-400 font-semibold">R$ <?= number_format($venda['comissao_valor'], 2, ',', '.') ?></div>
                        <div class="text-gray-400"><?= date('d/m/Y', strtotime($venda['data_venda'])) ?></div>
                        <div class="flex gap-2">
                            <button onclick="approveCommission(<?= $venda['id'] ?>)" 
                                    class="action-btn approve">
                                <i class="fas fa-check mr-1"></i>
                                Aprovar
                            </button>
                            <button onclick="rejectCommission(<?= $venda['id'] ?>)" 
                                    class="action-btn reject">
                                <i class="fas fa-times mr-1"></i>
                                Rejeitar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Lista de Afiliados -->
        <div class="admin-card">
            <h2 class="text-xl font-bold text-white mb-6">Todos os Afiliados</h2>
            <div class="table-container">
                <div class="table-header">
                    <div class="grid grid-cols-6 gap-4 text-sm font-semibold text-white">
                        <div>Afiliado</div>
                        <div>Status</div>
                        <div>Vendas</div>
                        <div>Comissões</div>
                        <div>Taxa</div>
                        <div>Ações</div>
                    </div>
                </div>
                
                <?php foreach ($afiliados as $afiliado): ?>
                    <div class="table-row">
                        <div>
                            <div class="text-white font-semibold"><?= htmlspecialchars($afiliado['nome']) ?></div>
                            <div class="text-gray-400 text-sm"><?= htmlspecialchars($afiliado['email']) ?></div>
                            <div class="text-gray-500 text-xs"><?= htmlspecialchars($afiliado['codigo_afiliado']) ?></div>
                        </div>
                        <div>
                            <span class="status-badge status-<?= $afiliado['status'] ?>">
                                <?= ucfirst($afiliado['status']) ?>
                            </span>
                        </div>
                        <div class="text-white"><?= $afiliado['vendas_aprovadas'] ?></div>
                        <div class="text-green-400 font-semibold">R$ <?= number_format($afiliado['total_comissoes'], 2, ',', '.') ?></div>
                        <div class="text-blue-400"><?= $afiliado['comissao_percentual'] ?>%</div>
                        <div>
                            <button onclick="updateCommissionRate(<?= $afiliado['id'] ?>, <?= $afiliado['comissao_percentual'] ?>)" 
                                    class="action-btn">
                                <i class="fas fa-edit mr-1"></i>
                                Taxa
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para rejeitar comissão -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <h3 class="text-xl font-bold text-white mb-4">Rejeitar Comissão</h3>
        <form method="POST" id="rejectForm">
            <input type="hidden" name="action" value="reject_commission">
            <input type="hidden" name="venda_id" id="rejectVendaId">
            
            <div class="form-group">
                <label class="form-label">Motivo da Rejeição</label>
                <textarea name="motivo" class="form-input" rows="3" 
                          placeholder="Explique o motivo da rejeição..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="action-btn reject">
                    <i class="fas fa-times mr-2"></i>
                    Rejeitar
                </button>
                <button type="button" onclick="closeRejectModal()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para atualizar taxa -->
<div id="rateModal" class="modal">
    <div class="modal-content">
        <h3 class="text-xl font-bold text-white mb-4">Atualizar Taxa de Comissão</h3>
        <form method="POST" id="rateForm">
            <input type="hidden" name="action" value="update_commission_rate">
            <input type="hidden" name="afiliado_id" id="rateAfiliadoId">
            
            <div class="form-group">
                <label class="form-label">Nova Taxa de Comissão (%)</label>
                <input type="number" name="nova_taxa" id="rateValue" 
                       class="form-input" min="0" max="100" step="0.01" required>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="action-btn">
                    <i class="fas fa-save mr-2"></i>
                    Atualizar
                </button>
                <button type="button" onclick="closeRateModal()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Funções para aprovar comissão
function approveCommission(vendaId) {
    if (confirm('Aprovar esta comissão?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="approve_commission">
            <input type="hidden" name="venda_id" value="${vendaId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Funções para rejeitar comissão
function rejectCommission(vendaId) {
    document.getElementById('rejectVendaId').value = vendaId;
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

// Funções para atualizar taxa
function updateCommissionRate(afiliadoId, currentRate) {
    document.getElementById('rateAfiliadoId').value = afiliadoId;
    document.getElementById('rateValue').value = currentRate;
    document.getElementById('rateModal').style.display = 'flex';
}

function closeRateModal() {
    document.getElementById('rateModal').style.display = 'none';
}

// Fechar modais com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRejectModal();
        closeRateModal();
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
