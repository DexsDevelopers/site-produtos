<?php
// admin/pedidos.php
require_once 'secure.php';
require_once 'templates/header_admin.php';

// Busca todos os pedidos, juntando com a tabela de usuários para pegar o nome do cliente
$stmt = $pdo->query(
    "SELECT pedidos.*, usuarios.nome AS nome_cliente 
     FROM pedidos 
     JOIN usuarios ON pedidos.usuario_id = usuarios.id 
     ORDER BY pedidos.data_pedido DESC"
);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto">
    <h2 class="text-2xl font-semibold text-white mb-6">Gerenciar Pedidos</h2>

    <div class="bg-brand-gray-light p-6 rounded-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-brand-black text-xs text-gray-400 uppercase">
                    <tr>
                        <th class="px-4 py-3">Pedido ID</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3">Valor Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pedidos)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-10 text-brand-gray-text">Nenhum pedido encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pedidos as $pedido): ?>
                        <tr class="border-b border-brand-gray">
                            <td class="px-4 py-3 font-medium text-white">#<?= $pedido['id'] ?></td>
                            <td class="px-4 py-3 text-white"><?= htmlspecialchars($pedido['nome_cliente']) ?></td>
                            <td class="px-4 py-3 text-white"><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></td>
                            <td class="px-4 py-3 text-brand-red font-semibold"><?= formatarPreco($pedido['valor_total']) ?></td>
                            <td class="px-4 py-3">
                                <span class="bg-yellow-500/20 text-yellow-300 text-xs font-semibold px-2.5 py-1 rounded-full"><?= htmlspecialchars($pedido['status']) ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="pedido_detalhes_admin.php?id=<?= $pedido['id'] ?>" class="font-medium text-blue-500 hover:underline">Ver Detalhes</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer_admin.php';
?>