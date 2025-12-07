<?php
// admin/pedido_detalhes_admin.php
require_once 'secure.php';
require_once 'templates/header_admin.php';

$pedido_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Busca os dados do pedido e do cliente
$stmt = $pdo->prepare(
    "SELECT p.*, u.nome, u.email 
     FROM pedidos p 
     JOIN usuarios u ON p.usuario_id = u.id 
     WHERE p.id = ?"
);
$stmt->execute([$pedido_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o pedido não for encontrado, redireciona
if (!$pedido) {
    header('Location: pedidos.php');
    exit();
}

// Busca os itens do pedido
$stmt_itens = $pdo->prepare("SELECT * FROM pedido_itens WHERE pedido_id = ?");
$stmt_itens->execute([$pedido_id]);
$itens_pedido = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto">
    <a href="pedidos.php" class="text-brand-red hover:underline mb-6 inline-block">&larr; Voltar para a lista de pedidos</a>
    <h2 class="text-2xl font-semibold text-white">Detalhes do Pedido #<?= $pedido['id'] ?></h2>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
        <div class="lg:col-span-2">
            <div class="bg-brand-gray-light p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-white mb-4">Itens do Pedido</h3>
                <div class="space-y-4">
                    <?php foreach ($itens_pedido as $item): ?>
                        <div class="flex justify-between items-center border-b border-brand-gray pb-2">
                            <div>
                                <p class="font-semibold text-white"><?= htmlspecialchars($item['nome_produto']) ?></p>
                                <p class="text-sm text-brand-gray-text">Qtd: <?= $item['quantidade'] ?> | Preço Unit.: <?= formatarPreco($item['preco_unitario']) ?></p>
                            </div>
                            <p class="font-semibold text-white"><?= formatarPreco($item['quantidade'] * $item['preco_unitario']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-right mt-4">
                    <p class="text-brand-gray-text">Subtotal: <?= formatarPreco($pedido['valor_total']) ?></p>
                    <p class="text-lg font-bold text-white">Total: <?= formatarPreco($pedido['valor_total']) ?></p>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-brand-gray-light p-6 rounded-lg">
                <h3 class="text-xl font-semibold text-white mb-4">Informações do Cliente</h3>
                <div class="space-y-2 text-sm">
                    <p><strong class="text-brand-gray-text">Nome:</strong> <span class="text-white"><?= htmlspecialchars($pedido['nome']) ?></span></p>
                    <p><strong class="text-brand-gray-text">E-mail:</strong> <span class="text-white"><?= htmlspecialchars($pedido['email']) ?></span></p>
                </div>
            </div>

            <div class="bg-brand-gray-light p-6 rounded-lg mt-6">
                <h3 class="text-xl font-semibold text-white mb-4">Status do Pedido</h3>
                <form action="processa_pedido_admin.php" method="POST">
                    <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                    <select name="status" class="w-full p-2 bg-brand-gray rounded-lg border border-brand-gray text-white">
                        <option value="Processando" <?= $pedido['status'] == 'Processando' ? 'selected' : '' ?>>Processando</option>
                        <option value="Enviado" <?= $pedido['status'] == 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                        <option value="Concluído" <?= $pedido['status'] == 'Concluído' ? 'selected' : '' ?>>Concluído</option>
                        <option value="Cancelado" <?= $pedido['status'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                    <button type="submit" class="w-full mt-4 bg-brand-red hover:bg-brand-red-dark text-white font-bold py-2 rounded-lg">
                        Atualizar Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer_admin.php';
?>