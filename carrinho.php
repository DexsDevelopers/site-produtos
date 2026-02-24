<?php
// carrinho.php — MACARIO BRAZIL
session_start();
require_once 'config.php';

// Process Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cart_key = $_POST['cart_key'] ?? '';

    if (!isset($_SESSION['carrinho']))
        $_SESSION['carrinho'] = [];

    switch ($action) {
        case 'remove':
            if (!empty($cart_key) && isset($_SESSION['carrinho'][$cart_key])) {
                unset($_SESSION['carrinho'][$cart_key]);
            }
            break;

        case 'update':
            $quantidade = max(1, (int)($_POST['quantidade'] ?? 1));
            if (!empty($cart_key) && isset($_SESSION['carrinho'][$cart_key])) {
                $_SESSION['carrinho'][$cart_key]['quantidade'] = $quantidade;
            }
            break;

        case 'clear':
            $_SESSION['carrinho'] = [];
            break;
    }

    // Sincroniza Carrinho Abandonado
    if (function_exists('salvarCarrinho')) {
        salvarCarrinho($pdo);
    }

    if ($action === 'update' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    header('Location: carrinho.php');
    exit;
}

$page_title = 'Carrinho';
require_once 'templates/header.php';

$carrinho_itens = $_SESSION['carrinho'] ?? [];
$total_itens = 0;
$total_preco = 0;
foreach ($carrinho_itens as $item) {
    $total_itens += $item['quantidade'];
    $total_preco += $item['preco'] * $item['quantidade'];
}
?>

<div class="container" style="padding-top: 40px; min-height: 80vh;">
    <h1 style="font-size:clamp(2rem, 5vw, 3rem); margin-bottom: 32px;">Seu Carrinho</h1>

    <?php if (empty($carrinho_itens)): ?>
    <div
        style="text-align: center; padding: 100px 0; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
        <i class="fas fa-shopping-bag" style="font-size: 4rem; color: var(--border-color); margin-bottom: 24px;"></i>
        <h3 style="margin-bottom: 16px;">Seu carrinho está vazio</h3>
        <p style="color: var(--text-muted); margin-bottom: 32px;">Adicione itens exclusivos à sua coleção.</p>
        <a href="index.php" class="btn-primary"
            style="display: inline-block; padding: 12px 32px; text-decoration: none;">Explorar Catálogo</a>
    </div>
    <?php else: ?>
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 40px; align-items: start;">

        <!-- Lista de Itens -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <?php foreach ($carrinho_itens as $cart_key => $item): ?>
            <div
                style="display: flex; gap: 20px; background: var(--bg-card); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); align-items: center;">
                <!-- Imagem -->
                <div
                    style="width: 100px; height: 100px; flex-shrink: 0; background: var(--bg-tertiary); border-radius: var(--radius-sm); overflow: hidden;">
                    <?php if (!empty($item['imagem']) && file_exists($item['imagem'])): ?>
                    <img src="<?= htmlspecialchars($item['imagem'])?>" alt="<?= htmlspecialchars($item['nome'])?>"
                        style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                    <div
                        style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); font-size: 2rem;">
                        <i class="fas fa-image"></i>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div style="flex: 1;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 4px;">
                        <?= htmlspecialchars($item['nome'])?>
                    </h3>
                    
                    <?php if (!empty($item['tamanho_valor'])): ?>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                        <span style="text-transform: uppercase; font-weight: 600; font-size: 0.75rem; background: var(--bg-tertiary); padding: 2px 8px; border-radius: 4px; border: 1px solid var(--border-color);">
                            Tamanho: <?= htmlspecialchars($item['tamanho_valor']) ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--text-primary);">
                        <?= formatarPreco($item['preco'])?>
                    </div>
                </div>

                <!-- Quantidade -->
                <form method="POST" class="form-qtd" style="display: flex; align-items: center; gap: 8px;">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="cart_key" value="<?= $cart_key ?>">
                    <input type="number" name="quantidade" value="<?= $item['quantidade']?>" min="1" max="99"
                        style="width: 60px; padding: 8px; background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm); text-align: center;"
                        onchange="this.form.submit()">
                </form>

                <!-- Remover -->
                <form method="POST" onsubmit="return confirm('Remover este item?');">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="cart_key" value="<?= $cart_key ?>">
                    <button type="submit"
                        style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 8px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>

            <!-- Limpar Carrinho -->
            <form method="POST" onsubmit="return confirm('Limpar todo o carrinho?');" style="align-self: flex-start;">
                <input type="hidden" name="action" value="clear">
                <button type="submit"
                    style="background: none; border: none; color: var(--text-muted); text-decoration: underline; cursor: pointer; font-size: 0.9rem;">
                    Esvaziar Carrinho
                </button>
            </form>
        </div>

        <!-- Resumo -->
        <div
            style="background: var(--bg-card); padding: 32px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); position: sticky; top: 120px;">
            <h3 style="font-size: 1.2rem; margin-bottom: 24px; text-transform: uppercase; letter-spacing: 0.05em;">
                Resumo</h3>

            <div
                style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem; color: var(--text-muted);">
                <span>Subtotal (
                    <?= $total_itens?> itens)
                </span>
                <span>
                    <?= formatarPreco($total_preco)?>
                </span>
            </div>

            <div
                style="display: flex; justify-content: space-between; margin-bottom: 24px; font-size: 0.9rem; color: var(--text-muted);">
                <span>Frete</span>
                <span style="color: var(--text-primary);">Grátis</span>
            </div>

            <div style="border-top: 1px solid var(--border-color); margin-bottom: 24px;"></div>

            <div
                style="display: flex; justify-content: space-between; margin-bottom: 32px; font-size: 1.3rem; font-weight: 800;">
                <span>Total</span>
                <span>
                    <?= formatarPreco($total_preco)?>
                </span>
            </div>

            <a href="<?=(isset($_SESSION['user_id']) ? 'checkout.php' : 'login.php?redirect=checkout.php')?>"
                class="btn-primary"
                style="display: block; width: 100%; padding: 16px; text-align: center; text-decoration: none; border-radius: var(--radius-md); font-weight: 700; text-transform: uppercase;">
                Finalizar Compra
            </a>

            <div
                style="margin-top: 24px; display: flex; gap: 12px; justify-content: center; color: var(--text-muted); font-size: 1.5rem;">
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-visa"></i>
                <i class="fas fa-barcode"></i>
                <i class="fas fa-qrcode"></i>
            </div>
        </div>

        <style>
            @media (max-width: 900px) {
                div[style*="grid-template-columns"] {
                    grid-template-columns: 1fr !important;
                }

                .carrinho-item {
                    flex-direction: column;
                    align-items: flex-start;
                }
            }
        </style>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>
