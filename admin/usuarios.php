<?php
// admin/usuarios.php - Gerenciamento de Clientes
require_once 'secure.php';
$page_title = 'Gerenciar Clientes';
require_once 'templates/header_admin.php';

try {
    $stmt = $pdo->query("
        SELECT u.*, 
        (SELECT COUNT(*) FROM pedidos WHERE usuario_id = u.id) as total_pedidos,
        (SELECT SUM(valor_total) FROM pedidos WHERE usuario_id = u.id AND status IN ('pago', 'entregue', 'concluido')) as total_gasto
        FROM usuarios u 
        ORDER BY u.id DESC
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $usuarios = [];
    $erro = "Erro ao buscar clientes: " . $e->getMessage();
}
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Clientes</h1>
            <p class="text-admin-gray-400">Visualize e gerencie as contas dos seus clientes</p>
        </div>
    </div>

    <?php if (isset($erro)): ?>
        <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg">
            <?= $erro ?>
        </div>
    <?php endif; ?>

    <div class="admin-card overflow-hidden">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-admin-gray-800/50">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Cliente</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Contato</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Localização</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Pedidos</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Total Gasto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-admin-gray-500">Nenhum cliente registrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $user): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-admin-primary/10 flex items-center justify-center text-admin-primary font-bold">
                                            <?= strtoupper(substr($user['nome'] ?? '?', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-white"><?= htmlspecialchars($user['nome']) ?></div>
                                            <div class="text-xs text-admin-gray-500">ID #<?= $user['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-white"><?= htmlspecialchars($user['email']) ?></div>
                                    <?php if (!empty($user['whatsapp'])): ?>
                                        <a href="https://wa.me/<?= preg_replace('/\D/', '', $user['whatsapp']) ?>" target="_blank" class="text-xs text-green-400 flex items-center gap-1">
                                            <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($user['whatsapp']) ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-gray-400">
                                    <?= htmlspecialchars($user['cidade'] ?? '---') ?> / <?= htmlspecialchars($user['estado'] ?? '--') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    <?= (int)$user['total_pedidos'] ?> pedido(s)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-admin-primary">
                                    <?= formatarPreco($user['total_gasto'] ?? 0) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="md:hidden divide-y divide-white/5">
            <?php foreach ($usuarios as $user): ?>
                <div class="p-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-admin-primary/10 flex items-center justify-center text-admin-primary font-bold">
                            <?= strtoupper(substr($user['nome'] ?? '?', 0, 1)) ?>
                        </div>
                        <div>
                            <div class="text-white font-bold"><?= htmlspecialchars($user['nome']) ?></div>
                            <div class="text-xs text-admin-gray-500"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-black/20 p-2 rounded">
                            <span class="text-admin-gray-500 block">Pedidos</span>
                            <span class="text-white font-bold"><?= (int)$user['total_pedidos'] ?></span>
                        </div>
                        <div class="bg-black/20 p-2 rounded">
                            <span class="text-admin-gray-500 block">Total Gasto</span>
                            <span class="text-admin-primary font-bold"><?= formatarPreco($user['total_gasto'] ?? 0) ?></span>
                        </div>
                    </div>
                    <?php if (!empty($user['whatsapp'])): ?>
                    <a href="https://wa.me/<?= preg_replace('/\D/', '', $user['whatsapp']) ?>" target="_blank" class="block text-center py-2 bg-green-500/10 text-green-400 rounded-lg text-sm font-bold">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>
