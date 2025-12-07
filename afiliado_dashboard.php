<?php
// afiliado_dashboard.php - Dashboard do Afiliado
session_start();
require_once 'config.php';
require_once 'includes/affiliate_system.php';

// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$affiliateSystem = new AffiliateSystem($pdo);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_link':
            $product_id = $_POST['product_id'] ?? null;
            $custom_code = $_POST['custom_code'] ?? null;
            
            $result = $affiliateSystem->createAffiliateLink($_SESSION['user_id'], $product_id, $custom_code);
            $message = $result['success'] ? 'Link criado com sucesso!' : 'Erro: ' . $result['message'];
            break;
            
        case 'request_payment':
            $valor = $_POST['valor'] ?? 0;
            if ($valor >= 50) { // Valor mínimo
                // Implementar solicitação de pagamento
                $message = 'Solicitação de pagamento enviada!';
            } else {
                $message = 'Valor mínimo para pagamento é R$ 50,00';
            }
            break;
    }
}

// Obter dados do dashboard
$dashboard = $affiliateSystem->getAffiliateDashboard($_SESSION['user_id']);

if (!$dashboard) {
    // Criar afiliado se não existir
    $result = $affiliateSystem->createAffiliateLink($_SESSION['user_id']);
    $dashboard = $affiliateSystem->getAffiliateDashboard($_SESSION['user_id']);
}

$page_title = 'Dashboard do Afiliado';
require_once 'templates/header.php';
?>

<style>
/* Estilos para dashboard do afiliado */
.dashboard-card {
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
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #3B82F6;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #94A3B8;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.stat-change {
    font-size: 0.75rem;
    color: #10B981;
}

.affiliate-link {
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
    font-family: monospace;
    word-break: break-all;
    color: #60A5FA;
    margin: 1rem 0;
}

.copy-btn {
    background: rgba(59, 130, 246, 0.2);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #60A5FA;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.copy-btn:hover {
    background: rgba(59, 130, 246, 0.4);
    color: white;
}

.action-btn {
    background: linear-gradient(135deg, #3B82F6, #2563EB);
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.action-btn:hover {
    background: linear-gradient(135deg, #2563EB, #1D4ED8);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
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
    grid-template-columns: 1fr 1fr 1fr 1fr;
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

.status-pendente {
    background: rgba(245, 158, 11, 0.2);
    color: #F59E0B;
}

.status-aprovada {
    background: rgba(16, 185, 129, 0.2);
    color: #10B981;
}

.status-rejeitada {
    background: rgba(239, 68, 68, 0.2);
    color: #EF4444;
}

.status-paga {
    background: rgba(59, 130, 246, 0.2);
    color: #3B82F6;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #94A3B8;
}

.empty-state .icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
</style>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Dashboard do Afiliado</h1>
                <p class="text-gray-400">Gerencie seus links de afiliação e acompanhe suas comissões</p>
            </div>
            <div class="flex gap-3">
                <button onclick="showCreateLinkModal()" class="action-btn">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Link
                </button>
                <button onclick="showPaymentModal()" class="action-btn">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Solicitar Pagamento
                </button>
            </div>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="bg-green-500/20 text-green-300 p-4 rounded-lg mb-6">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card">
                <div class="stat-number">R$ <?= number_format($dashboard['stats']['total_comissoes'], 2, ',', '.') ?></div>
                <div class="stat-label">Total de Comissões</div>
                <div class="stat-change">+<?= $dashboard['stats']['taxa_conversao'] ?>% conversão</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= $dashboard['stats']['vendas_aprovadas'] ?></div>
                <div class="stat-label">Vendas Aprovadas</div>
                <div class="stat-change">R$ <?= number_format($dashboard['stats']['total_vendas_valor'], 2, ',', '.') ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= $dashboard['stats']['cliques_totais'] ?></div>
                <div class="stat-label">Total de Cliques</div>
                <div class="stat-change"><?= $dashboard['stats']['taxa_conversao'] ?>% conversão</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= $dashboard['stats']['comissao_percentual'] ?>%</div>
                <div class="stat-label">Taxa de Comissão</div>
                <div class="stat-change">Por venda</div>
            </div>
        </div>
        
        <!-- Links de Afiliação -->
        <div class="dashboard-card">
            <h2 class="text-xl font-bold text-white mb-6">Meus Links de Afiliação</h2>
            
            <div class="space-y-4">
                <!-- Link geral -->
                <div>
                    <h3 class="font-semibold text-white mb-2">Link Geral</h3>
                    <div class="affiliate-link">
                        <?= 'https://' . $_SERVER['HTTP_HOST'] . '/index.php?ref=' . $dashboard['stats']['codigo_afiliado'] ?>
                    </div>
                    <button onclick="copyToClipboard('<?= 'https://' . $_SERVER['HTTP_HOST'] . '/index.php?ref=' . $dashboard['stats']['codigo_afiliado'] ?>')" 
                            class="copy-btn">
                        <i class="fas fa-copy mr-1"></i>
                        Copiar Link
                    </button>
                </div>
                
                <!-- Links por produto -->
                <div>
                    <h3 class="font-semibold text-white mb-2">Links por Produto</h3>
                    <p class="text-gray-400 text-sm mb-4">Adicione ?ref=<?= $dashboard['stats']['codigo_afiliado'] ?> a qualquer URL de produto</p>
                    <div class="text-sm text-gray-400">
                        Exemplo: produto.php?id=123&ref=<?= $dashboard['stats']['codigo_afiliado'] ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Vendas Recentes -->
        <div class="dashboard-card">
            <h2 class="text-xl font-bold text-white mb-6">Vendas Recentes</h2>
            
            <?php if (empty($dashboard['vendas_recentes'])): ?>
                <div class="empty-state">
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Nenhuma venda ainda</h3>
                    <p class="text-gray-400">Compartilhe seus links para começar a ganhar comissões!</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="table-header">
                        <div class="grid grid-cols-4 gap-4 text-sm font-semibold text-white">
                            <div>Data</div>
                            <div>Produto</div>
                            <div>Valor</div>
                            <div>Status</div>
                        </div>
                    </div>
                    
                    <?php foreach ($dashboard['vendas_recentes'] as $venda): ?>
                        <div class="table-row">
                            <div class="text-white"><?= date('d/m/Y', strtotime($venda['data_venda'])) ?></div>
                            <div class="text-gray-300"><?= htmlspecialchars($venda['produto_nome'] ?? 'Produto Geral') ?></div>
                            <div class="text-green-400 font-semibold">R$ <?= number_format($venda['comissao_valor'], 2, ',', '.') ?></div>
                            <div>
                                <span class="status-badge status-<?= $venda['status'] ?>">
                                    <?= ucfirst($venda['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Cliques Recentes -->
        <div class="dashboard-card">
            <h2 class="text-xl font-bold text-white mb-6">Cliques Recentes</h2>
            
            <?php if (empty($dashboard['cliques_recentes'])): ?>
                <div class="empty-state">
                    <div class="icon">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Nenhum clique ainda</h3>
                    <p class="text-gray-400">Compartilhe seus links para começar a receber cliques!</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="table-header">
                        <div class="grid grid-cols-3 gap-4 text-sm font-semibold text-white">
                            <div>Data</div>
                            <div>Produto</div>
                            <div>IP</div>
                        </div>
                    </div>
                    
                    <?php foreach ($dashboard['cliques_recentes'] as $clique): ?>
                        <div class="table-row">
                            <div class="text-white"><?= date('d/m/Y H:i', strtotime($clique['data_clique'])) ?></div>
                            <div class="text-gray-300"><?= htmlspecialchars($clique['produto_nome'] ?? 'Página Inicial') ?></div>
                            <div class="text-gray-400"><?= htmlspecialchars($clique['ip_address']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para criar link -->
<div id="createLinkModal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
    <div class="bg-gray-800 rounded-xl p-6 max-w-md w-full">
        <h3 class="text-xl font-bold text-white mb-4">Criar Link de Afiliação</h3>
        
        <form method="POST">
            <input type="hidden" name="action" value="create_link">
            
            <div class="mb-4">
                <label class="block text-white font-semibold mb-2">Produto (opcional)</label>
                <select name="product_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                    <option value="">Link Geral</option>
                    <?php
                    $produtos = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($produtos as $produto):
                    ?>
                        <option value="<?= $produto['id'] ?>"><?= htmlspecialchars($produto['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-white font-semibold mb-2">Código Personalizado (opcional)</label>
                <input type="text" name="custom_code" 
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2"
                       placeholder="Deixe vazio para gerar automaticamente">
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="action-btn flex-1">
                    <i class="fas fa-link mr-2"></i>
                    Criar Link
                </button>
                <button type="button" onclick="closeCreateLinkModal()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para solicitar pagamento -->
<div id="paymentModal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
    <div class="bg-gray-800 rounded-xl p-6 max-w-md w-full">
        <h3 class="text-xl font-bold text-white mb-4">Solicitar Pagamento</h3>
        
        <div class="mb-4">
            <div class="text-white font-semibold mb-2">Saldo Disponível</div>
            <div class="text-2xl font-bold text-green-400">R$ <?= number_format($dashboard['stats']['total_comissoes'], 2, ',', '.') ?></div>
        </div>
        
        <div class="mb-6">
            <div class="text-gray-400 text-sm">
                Valor mínimo para pagamento: R$ 50,00
            </div>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="request_payment">
            <input type="hidden" name="valor" value="<?= $dashboard['stats']['total_comissoes'] ?>">
            
            <div class="flex gap-3">
                <button type="submit" class="action-btn flex-1" 
                        <?= $dashboard['stats']['total_comissoes'] < 50 ? 'disabled' : '' ?>>
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Solicitar Pagamento
                </button>
                <button type="button" onclick="closePaymentModal()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Funções para modais
function showCreateLinkModal() {
    document.getElementById('createLinkModal').classList.remove('hidden');
    document.getElementById('createLinkModal').classList.add('flex');
}

function closeCreateLinkModal() {
    document.getElementById('createLinkModal').classList.add('hidden');
    document.getElementById('createLinkModal').classList.remove('flex');
}

function showPaymentModal() {
    document.getElementById('paymentModal').classList.remove('hidden');
    document.getElementById('paymentModal').classList.add('flex');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentModal').classList.remove('flex');
}

// Função para copiar link
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Link copiado para a área de transferência!');
    }).catch(() => {
        // Fallback para navegadores mais antigos
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Link copiado para a área de transferência!');
    });
}

// Fechar modais com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateLinkModal();
        closePaymentModal();
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
