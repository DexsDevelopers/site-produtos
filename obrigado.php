<?php
// obrigado.php — MACARIO BRAZIL
session_start();
require_once 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$pedido_id = $_GET['pedido_id'] ?? null;
$pedido = null;

if ($pedido_id) {
    $my_orders = $_SESSION['my_orders'] ?? [];
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? AND (usuario_id = ? OR id IN (" . implode(',', array_map('intval', array_merge([0], $my_orders))) . "))");
        $stmt->execute([$pedido_id, $user_id]);
    } else {
        if (in_array((int)$pedido_id, $my_orders)) {
            $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
            $stmt->execute([$pedido_id]);
        } else {
            header('Location: index.php');
            exit;
        }
    }
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

// --- RASTREAMENTO DE CONVERSÃO (Meta Ads / Conversions API) ---
$user_info = [];
if ($user_id) {
    try {
        $stmt_u = $pdo->prepare("SELECT nome, email, whatsapp, cep, cidade, estado FROM usuarios WHERE id = ?");
        $stmt_u->execute([$user_id]);
        $user_info = $stmt_u->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {}
} else if ($pedido) {
    $user_info = [
        'nome' => $pedido['nome_cliente'] ?? '',
        'email' => $pedido['email_cliente'] ?? '',
        'whatsapp' => $pedido['whatsapp'] ?? '',
        'cep' => $pedido['cep'] ?? '',
        'cidade' => $pedido['cidade'] ?? '',
        'estado' => $pedido['estado'] ?? ''
    ];
}

$itens_pedido = [];
$contents = [];
try {
    $stmt_it = $pdo->prepare("SELECT produto_id, nome_produto, quantidade, preco_unitario FROM pedido_itens WHERE pedido_id = ?");
    $stmt_it->execute([$pedido['id']]);
    $itens_pedido = $stmt_it->fetchAll(PDO::FETCH_ASSOC);
    foreach ($itens_pedido as $it) {
        $contents[] = [
            'id' => $it['produto_id'],
            'quantity' => (int)$it['quantidade'],
            'item_price' => (float)$it['preco_unitario']
        ];
    }
} catch (Exception $e) {}

$customData = [
    'value' => (float)$pedido['valor_total'],
    'currency' => 'BRL',
    'content_type' => 'product',
    'contents' => $contents
];

if (!isset($_SESSION['tracked_orders'])) {
    $_SESSION['tracked_orders'] = [];
}
$should_track = !in_array($pedido['id'], $_SESSION['tracked_orders']);
if ($should_track) {
    $_SESSION['tracked_orders'][] = $pedido['id'];
}

$purchase_event_id = 'pur_' . $pedido['id'];

if ($should_track) {
    try {
        require_once 'includes/meta_capi.php';
        $capi = new MetaCAPI($pdo);
        if ($capi->isConfigured()) {
            $nome_parts = explode(' ', trim($user_info['nome'] ?? ''));
            $fn = $nome_parts[0] ?? '';
            $ln = count($nome_parts) > 1 ? end($nome_parts) : '';
            
            $userData = [
                'em' => $user_info['email'] ?? '',
                'ph' => $user_info['whatsapp'] ?? $pedido['whatsapp'] ?? '',
                'fn' => $fn,
                'ln' => $ln,
                'ct' => $user_info['cidade'] ?? $pedido['cidade'] ?? '',
                'st' => $user_info['estado'] ?? $pedido['estado'] ?? '',
                'zp' => $user_info['cep'] ?? $pedido['cep'] ?? '',
                'country' => 'BR'
            ];
            $capi->sendEvent('Purchase', $purchase_event_id, $customData, $userData);
        }
    } catch (Exception $e) {
        error_log("Erro Meta CAPI Purchase: " . $e->getMessage());
    }
}

$page_title = 'Pedido Confirmado';
require_once 'templates/header.php';
?>

<?php if ($should_track && !empty($meta_pixel_id)): ?>
<!-- Meta Pixel Purchase Event -->
<script>
fbq('track', 'Purchase', {
    value: <?= (float)$pedido['valor_total'] ?>,
    currency: 'BRL',
    content_type: 'product',
    contents: <?= json_encode($contents) ?>
}, {eventID: '<?= $purchase_event_id ?>'});
</script>
<?php endif; ?>

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