<?php
// admin/pedidos.php - Gerenciamento de Pedidos Premium
require_once 'secure.php';
$page_title = 'Gerenciar Pedidos';
require_once 'templates/header_admin.php';

// Busca todos os pedidos
try {
    // LEFT JOIN para garantir que pedidos de usuários excluídos ainda apareçam
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
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Pedidos</h1>
            <p class="text-admin-gray-400">Acompanhe e gerencie as vendas da loja</p>
        </div>
        <!-- Filtros futuros poderiam entrar aqui -->
    </div>

    <div class="admin-card overflow-hidden">
        <!-- Visualização Mobile (Cards) -->
        <div class="md:hidden space-y-4 p-4">
            <?php if (empty($pedidos)): ?>
            <div class="text-center text-admin-gray-500 py-8">
                <i class="fas fa-shopping-bag text-4xl mb-3 opacity-50"></i>
                <p>Nenhum pedido encontrado.</p>
            </div>
            <?php else: ?>
            <?php foreach ($pedidos as $pedido): 
                     $statusClass = match(strtolower($pedido['status'])) {
                        'pago', 'concluido', 'entregue' => 'status-success',
                        'cancelado', 'recusado' => 'status-danger',
                        'pendente', 'aguardando' => 'status-warning',
                        default => 'bg-gray-500/10 text-gray-400 border border-gray-500/20 px-2.5 py-0.5 rounded-full text-xs font-medium'
                    };
                ?>
            <div class="bg-white/5 p-4 rounded-xl border border-white/10 space-y-3">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-white font-bold block">#
                            <?= $pedido['id'] ?>
                        </span>
                        <span class="text-xs text-admin-gray-400">
                            <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?>
                        </span>
                    </div>
                    <span class="<?= $statusClass ?>">
                        <?= htmlspecialchars($pedido['status']) ?>
                    </span>
                </div>

                <div>
                    <div class="text-sm text-white font-medium">
                        <?= htmlspecialchars($pedido['nome_cliente'] ?? 'Cliente Removido') ?>
                    </div>
                    <div class="text-xs text-admin-gray-500">
                        <?= htmlspecialchars($pedido['email_cliente'] ?? '-') ?>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-3 border-t border-white/10">
                    <span class="text-white font-bold">
                        <?= formatarPreco($pedido['valor_total']) ?>
                    </span>
                    <a href="pedido_detalhes_admin.php?id=<?= $pedido['id'] ?>"
                        class="text-xs font-bold text-admin-primary bg-admin-primary/10 px-3 py-2 rounded-lg hover:bg-admin-primary/20 transition-all">
                        DETALHES <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Visualização Desktop (Tabela) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-admin-gray-800/50">
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            ID</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Cliente</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Data</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Total</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-4 text-right text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-admin-gray-500">
                            <i class="fas fa-shopping-bag text-4xl mb-3 opacity-50"></i>
                            <p>Nenhum pedido encontrado.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($pedidos as $pedido): ?>
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                            #
                            <?= $pedido['id'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-white">
                                <?= htmlspecialchars($pedido['nome_cliente'] ?? 'Cliente Removido') ?>
                            </div>
                            <div class="text-xs text-admin-gray-500">
                                <?= htmlspecialchars($pedido['email_cliente'] ?? '-') ?>
                            </div>
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
                            <span class="<?= $statusClass ?>">
                                <?= htmlspecialchars($pedido['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="pedido_detalhes_admin.php?id=<?= $pedido['id'] ?>"
                                class="text-admin-primary hover:text-white transition-colors">
                                Detalhes <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>