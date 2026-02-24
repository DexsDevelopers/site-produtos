<?php
// admin/carrinhos_abandonados.php - Gestão de Carrinhos Abandonados
require_once 'secure.php';
$page_title = 'Carrinhos Abandonados';
require_once 'templates/header_admin.php';

try {
    $stmt = $pdo->query("
        SELECT c.*, u.nome, u.email, u.whatsapp
        FROM carrinhos_abandonados c
        LEFT JOIN usuarios u ON c.usuario_id = u.id
        ORDER BY c.data_atualizacao DESC
    ");
    $carrinhos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $carrinhos = [];
    $erro = "Erro ao buscar carrinhos: " . $e->getMessage();
}

if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $stmt = $pdo->prepare("DELETE FROM carrinhos_abandonados WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: carrinhos_abandonados.php?msg=Carrinho removido");
    exit;
}
?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-white mb-2">Carrinhos Abandonados</h1>
        <p class="text-admin-gray-400">Veja quem iniciou uma compra e não finalizou</p>
    </div>

    <?php if (isset($erro)): ?>
        <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg"><?= $erro ?></div>
    <?php endif; ?>

    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-admin-gray-800/50">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Cliente / Sessão</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Conteúdo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Total</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase">Última Ativ.</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-admin-gray-400 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($carrinhos)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-admin-gray-500">Nenhum carrinho abandonado no momento.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($carrinhos as $c): 
                            $itens = json_decode($c['dados_carrinho'], true) ?: [];
                        ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4">
                                    <?php if ($c['nome']): ?>
                                        <div class="text-sm font-medium text-white"><?= htmlspecialchars($c['nome']) ?></div>
                                        <div class="text-xs text-admin-gray-500"><?= htmlspecialchars($c['email']) ?></div>
                                    <?php else: ?>
                                        <div class="text-sm font-medium text-admin-gray-400">Visitante Anônimo</div>
                                        <div class="text-[10px] text-admin-gray-600"><?= substr($c['sessao_id'], 0, 15) ?>...</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-white space-y-1">
                                        <?php foreach ($itens as $item): ?>
                                            <div><?= $item['quantidade'] ?>x <?= htmlspecialchars($item['nome']) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-admin-primary">
                                    <?= formatarPreco($c['valor_total']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-admin-gray-400">
                                    <?= date('d/m/Y H:i', strtotime($c['data_atualizacao'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <?php if (!empty($c['whatsapp'])): ?>
                                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $c['whatsapp']) ?>?text=Olá <?= urlencode($c['nome']) ?>, vimos que você deixou alguns itens no carrinho. Gostaria de ajuda para finalizar sua compra?" 
                                               target="_blank" class="p-2 bg-green-500/10 text-green-400 rounded-lg hover:bg-green-500/20" title="Recuperar via WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?excluir=<?= $c['id'] ?>" onclick="return confirm('Excluir este registro?')" class="p-2 bg-red-500/10 text-red-500 rounded-lg hover:bg-red-500/20">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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
