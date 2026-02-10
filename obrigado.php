<?php
// obrigado.php — MACARIO BRAZIL
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pedido_id = $_GET['pedido_id'] ?? null;
$pedido = null;

if ($pedido_id) {
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$pedido_id, $user_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Se não achou pedido via GET, tenta processar o carrinho (legado/fallback)
if (!$pedido && !empty($_SESSION['carrinho'])) {
    try {
        $pdo->beginTransaction();

        $carrinho_itens = $_SESSION['carrinho'];
        $valor_total = 0;
        foreach ($carrinho_itens as $item) {
            $valor_total += $item['preco'] * $item['quantidade'];
        }

        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, valor_total, status, data_pedido) VALUES (?, ?, 'Pendente', NOW())");
        $stmt->execute([$user_id, $valor_total]);
        $pedido_id = $pdo->lastInsertId();

        $stmt_item = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, nome_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)");
        foreach ($carrinho_itens as $item) {
            $stmt_item->execute([
                $pedido_id,
                $item['id'],
                $item['nome'],
                $item['quantidade'],
                $item['preco']
            ]);
        }

        $pdo->commit();
        unset($_SESSION['carrinho']);

        // Recarrega o pedido criado
        $pedido = [
            'id' => $pedido_id,
            'valor_total' => $valor_total,
            'status' => 'Pendente'
        ];

    }
    catch (PDOException $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        error_log("Erro ao criar pedido: " . $e->getMessage());
        header('Location: carrinho.php?error=erro_pedido');
        exit;
    }
}

if (!$pedido) {
    header('Location: index.php');
    exit;
}

$page_title = 'Pedido Confirmado';
require_once 'templates/header.php';
?>

<div class="container" style="padding-top: 80px; min-height: 80vh; text-align: center;">

    <div
        style="background: var(--bg-card); padding: 60px 20px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); max-width: 600px; margin: 0 auto;">

        <div
            style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 32px; color: #10B981; font-size: 2.5rem;">
            <i class="fas fa-check"></i>
        </div>

        <h1 style="font-size: 2.5rem; margin-bottom: 16px;">Obrigado!</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 32px;">Seu pedido foi recebido com
            sucesso.</p>

        <div
            style="background: var(--bg-tertiary); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 32px; text-align: left;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="color: var(--text-muted);">Número do Pedido:</span>
                <span style="font-weight: 700; color: var(--text-primary);">#
                    <?= $pedido['id']?>
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="color: var(--text-muted);">Total:</span>
                <span style="font-weight: 700; color: var(--text-primary);">
                    <?= formatarPreco($pedido['valor_total'])?>
                </span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Status:</span>
                <span style="color: #FBBF24;">
                    <?= htmlspecialchars($pedido['status'])?>
                </span>
            </div>
        </div>

        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="pedido_detalhes.php?id=<?= $pedido['id']?>" class="btn-secondary"
                style="padding: 12px 24px; border: 1px solid var(--border-color); border-radius: var(--radius-md); text-decoration: none; color: var(--text-primary);">Acompanhar
                Pedido</a>
            <a href="index.php" class="btn-primary"
                style="padding: 12px 24px; border-radius: var(--radius-md); text-decoration: none;">Voltar para Loja</a>
        </div>

    </div>

</div>

<?php require_once 'templates/footer.php'; ?>