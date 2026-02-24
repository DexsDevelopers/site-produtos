<?php
// admin/pedidos.php - Gerenciamento de Pedidos Premium
require_once 'secure.php';
$page_title = 'Gerenciar Pedidos';
require_once 'templates/header_admin.php';

// Busca todos os pedidos
try {
    $stmt = $pdo->query(
        "SELECT pedidos.*, usuarios.nome AS nome_cliente, usuarios.email AS email_cliente
         FROM pedidos 
         LEFT JOIN usuarios ON pedidos.usuario_id = usuarios.id 
         ORDER BY pedidos.data_pedido DESC"
    );
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $pedidos = [];
    $erro = "Erro ao buscar pedidos: " . $e->getMessage();
}

$msg = $_GET['msg'] ?? '';
$erro_msg = $_GET['erro'] ?? '';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Pedidos</h1>
            <p class="text-admin-gray-400">Acompanhe e gerencie as vendas da loja</p>
        </div>
        
        <div class="flex gap-3">
            <button type="button" onclick="confirmarExclusaoEmMassa()" class="bg-red-500/10 text-red-500 border border-red-500/20 px-4 py-2 rounded-lg hover:bg-red-500/20 transition-all font-bold text-sm flex items-center gap-2">
                <i class="fas fa-trash-alt"></i> Excluir Selecionados
            </button>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($erro_msg): ?>
        <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro_msg) ?>
        </div>
    <?php endif; ?>

    <form id="bulk-delete-form" action="processa_pedido.php" method="POST">
        <input type="hidden" name="action" value="excluir_em_massa">
        
        <div class="admin-card overflow-hidden">
            <!-- Visualização Desktop (Tabela) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-admin-gray-800/50">
                            <th class="px-6 py-4 text-left">
                                <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-white/10 bg-black/20 text-admin-primary">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Cliente</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Data</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Total</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-admin-gray-400 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($pedidos)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-admin-gray-500">
                                <i class="fas fa-shopping-bag text-4xl mb-3 opacity-50"></i>
                                <p>Nenhum pedido encontrado.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($pedidos as $pedido): ?>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <input type="checkbox" name="pedido_ids[]" value="<?= $pedido['id'] ?>" class="pedido-checkbox w-4 h-4 rounded border-white/10 bg-black/20 text-admin-primary">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">#<?= $pedido['id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-white"><?= htmlspecialchars($pedido['nome_cliente'] ?? 'Cliente Removido') ?></div>
                                <div class="text-xs text-admin-gray-500"><?= htmlspecialchars($pedido['email_cliente'] ?? '-') ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-gray-400">
                                <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-white">
                                <?= formatarPreco($pedido['valor_total']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                    $statusClass = match(strtolower($pedido['status'])) {
                                        'pago', 'concluido', 'entregue' => 'status-success',
                                        'cancelado', 'recusado' => 'status-danger',
                                        'pendente', 'aguardando' => 'status-warning',
                                        default => 'bg-gray-500/10 text-gray-400 border border-gray-500/20 px-2.5 py-0.5 rounded-full text-xs font-medium'
                                    };
                                ?>
                                <span class="<?= $statusClass ?>"><?= htmlspecialchars($pedido['status']) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                <a href="pedido_detalhes_admin.php?id=<?= $pedido['id'] ?>" class="text-admin-primary hover:text-white transition-colors">
                                    <i class="fas fa-eye"></i> Detalhes
                                </a>
                                <button type="button" onclick="confirmarExclusaoUnica(<?= $pedido['id'] ?>)" class="text-red-500 hover:text-red-400 transition-colors">
                                    <i class="fas fa-trash-alt"></i> Excluir
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Visualização Mobile (Cards) -->
            <div class="md:hidden space-y-4 p-4">
                <?php if (empty($pedidos)): ?>
                    <p class="text-center text-admin-gray-500 text-sm py-4">Nenhum pedido encontrado.</p>
                <?php else: ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <div class="bg-white/5 p-4 rounded-xl border border-white/10 space-y-3 relative">
                            <div class="absolute top-4 right-4">
                                <input type="checkbox" name="pedido_ids[]" value="<?= $pedido['id'] ?>" class="pedido-checkbox w-5 h-5 rounded border-white/10 bg-black/20 text-admin-primary">
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-white font-bold">#<?= $pedido['id'] ?></span>
                            </div>
                            <div class="text-sm text-white font-medium"><?= htmlspecialchars($pedido['nome_cliente'] ?? 'Cliente Removido') ?></div>
                            <div class="flex justify-between items-center pt-3 border-t border-white/10">
                                <span class="text-white font-bold"><?= formatarPreco($pedido['valor_total']) ?></span>
                                <div class="flex gap-2">
                                    <a href="pedido_detalhes_admin.php?id=<?= $pedido['id'] ?>" class="bg-white/10 p-2 rounded-lg text-white"><i class="fas fa-eye"></i></a>
                                    <button type="button" onclick="confirmarExclusaoUnica(<?= $pedido['id'] ?>)" class="bg-red-500/10 p-2 rounded-lg text-red-500"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<form id="single-delete-form" action="processa_pedido.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="excluir_unico">
    <input type="hidden" name="pedido_id" id="delete-pedido-id">
</form>

<script>
const selectAll = document.getElementById('select-all');
if(selectAll) {
    selectAll.addEventListener('change', function() {
        const boxes = document.querySelectorAll('.pedido-checkbox');
        boxes.forEach(box => box.checked = this.checked);
    });
}

function confirmarExclusaoUnica(id) {
    if (confirm('Deseja excluir o pedido #' + id + '?')) {
        document.getElementById('delete-pedido-id').value = id;
        document.getElementById('single-delete-form').submit();
    }
}

function confirmarExclusaoEmMassa() {
    const selecionados = document.querySelectorAll('input[name="pedido_ids[]"]:checked').length;
    if (selecionados === 0) return alert('Selecione ao menos um pedido.');
    if (confirm('Excluir ' + selecionados + ' pedidos?')) {
        document.getElementById('bulk-delete-form').submit();
    }
}
</script>

<?php require_once 'templates/footer_admin.php'; ?>
