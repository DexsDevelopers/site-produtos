<?php
// admin/pedido_detalhes_admin.php
require_once 'secure.php';
$page_title = 'Detalhes do Pedido';
require_once 'templates/header_admin.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    header('Location: pedidos.php');
    exit;
}

// Processar atualização de Rastreio
if (isset($_POST['atualizar_rastreio'])) {
    $codigo = $_POST['codigo_rastreio'];
    $transportadora = $_POST['transportadora'];
    $url = $_POST['url_rastreio'];

    $stmt = $pdo->prepare("UPDATE pedidos SET codigo_rastreio = ?, transportadora = ?, url_rastreio = ?, status = 'enviado' WHERE id = ?");
    $stmt->execute([$codigo, $transportadora, $url, $id]);
    $msg = "Rastreio atualizado com sucesso!";
}

// Processar mudança de status simples
if (isset($_POST['mudar_status'])) {
    $novo_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
    $stmt->execute([$novo_status, $id]);
    $msg = "Status atualizado para $novo_status.";
}

// Buscar Pedido com LEFT JOIN para prevenir erros se usuário for deletado
$stmt = $pdo->prepare("SELECT p.*, u.nome, u.email, u.telefone 
                       FROM pedidos p 
                       LEFT JOIN usuarios u ON p.usuario_id = u.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "<div class='p-8 text-center text-white'>Pedido não encontrado.</div>";
    require_once 'templates/footer_admin.php';
    exit;
}

// Buscar Itens com LEFT JOIN para prevenir erros se produto for deletado
$itens = $pdo->prepare("SELECT pi.*, p.imagem, p.nome as nome_original 
                        FROM pedido_itens pi 
                        LEFT JOIN produtos p ON pi.produto_id = p.id 
                        WHERE pi.pedido_id = ?");
$itens->execute([$id]);
$lista_itens = $itens->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center gap-4 mb-6">
        <a href="pedidos.php" class="text-admin-gray-400 hover:text-white transition-colors">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <h1 class="text-2xl font-bold text-white">Pedido #
            <?= $pedido['id']?>
        </h1>
        <span class="bg-admin-primary/20 text-white px-3 py-1 rounded-full text-sm">
            <?= $pedido['status']?>
        </span>
    </div>

    <?php if (isset($msg)): ?>
    <div class="p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg">
        <?= $msg?>
    </div>
    <?php
endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detalhes do Pedido -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Produtos -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Itens do Pedido</h3>
                <div class="space-y-4">
                    <?php foreach ($lista_itens as $item): 
                        $img_src = !empty($item['imagem']) ? "../" . $item['imagem'] : "https://placehold.co/100x100?text=Removido";
                    ?>
                    <div class="flex gap-4 items-center p-3 bg-white/5 rounded-lg">
                        <img src="<?= $img_src ?>" class="w-16 h-16 object-cover rounded bg-admin-gray-800">
                        <div class="flex-1">
                            <div class="text-white font-medium">
                                <?= $item['nome_produto'] ?>
                            </div>
                            <?php if (!empty($item['valor_tamanho'])): ?>
                            <div class="text-[10px] uppercase font-bold text-admin-primary/70 tracking-tight">
                                TAMANHO:
                                <?= htmlspecialchars($item['valor_tamanho']) ?>
                            </div>
                            <?php endif; ?>
                            <div class="text-sm text-admin-gray-400">
                                <?= $item['quantidade'] ?>x
                                <?= number_format($item['preco_unitario'], 2, ',', '.') ?>
                            </div>
                        </div>
                        <div class="text-white font-bold">
                            R$
                            <?= number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.') ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 pt-4 border-t border-white/10 flex justify-between items-center">
                    <span class="text-admin-gray-400">Total do Pedido</span>
                    <span class="text-2xl font-bold text-white">R$
                        <?= number_format($pedido['valor_total'], 2, ',', '.') ?>
                    </span>
                </div>
            </div>

            <!-- Dados de Entrega e Rastreio -->
            <div class="admin-card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Entrega e Rastreio</h3>

                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Transportadora</label>
                            <input type="text" name="transportadora"
                                value="<?= htmlspecialchars($pedido['transportadora'] ?? '') ?>"
                                placeholder="Ex: Correios" class="w-full bg-admin-gray-900 border border-admin-gray-700 rounded p-2 text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Código de Rastreio</label>
                            <input type="text" name="codigo_rastreio"
                                value="<?= htmlspecialchars($pedido['codigo_rastreio'] ?? '') ?>"
                                placeholder="Ex: AB123456789BR" class="w-full bg-admin-gray-900 border border-admin-gray-700 rounded p-2 text-white uppercase">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">URL de Rastreamento</label>
                        <input type="text" name="url_rastreio"
                            value="<?= htmlspecialchars($pedido['url_rastreio'] ?? '') ?>" 
                            placeholder="https://..."
                            class="w-full bg-admin-gray-900 border border-admin-gray-700 rounded p-2 text-white">
                    </div>

                    <button type="submit" name="atualizar_rastreio"
                        class="w-full bg-white text-black font-bold py-3 rounded hover:bg-gray-200 transition-colors">
                        Salvar Informações de Envio
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar Cliente -->
        <div class="space-y-6">
            <div class="admin-card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Cliente</h3>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-admin-gray-700 flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($pedido['nome'] ?? '?', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="text-white font-medium">
                            <?= htmlspecialchars($pedido['nome'] ?? 'Usuário Removido') ?>
                        </div>
                        <div class="text-xs text-admin-gray-400">Desde
                            <?= date('Y') ?>
                        </div>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-admin-gray-300">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-envelope w-5 opacity-50"></i>
                        <?= htmlspecialchars($pedido['email'] ?? 'E-mail não disponível') ?>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-phone w-5 opacity-50"></i>
                        <?= htmlspecialchars($pedido['telefone'] ?? 'Não informado') ?>
                    </div>
                </div>
            </div>

            <div class="admin-card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Atualizar Status</h3>
                <form method="POST" class="space-y-3">
                    <select name="status" class="w-full bg-admin-gray-900 text-white border border-admin-gray-700 rounded p-2">
                        <option value="pendente" <?= $pedido['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="pago" <?= $pedido['status'] == 'pago' ? 'selected' : '' ?>>Pago</option>
                        <option value="enviado" <?= $pedido['status'] == 'enviado' ? 'selected' : '' ?>>Enviado</option>
                        <option value="entregue" <?= $pedido['status'] == 'entregue' ? 'selected' : '' ?>>Entregue</option>
                        <option value="cancelado" <?= $pedido['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                    <button type="submit" name="mudar_status"
                        class="w-full border border-white/20 text-white py-2 rounded hover:bg-white/10 transition-colors">
                        Atualizar Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>