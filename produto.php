<?php
// produto.php â€” MACARIO BRAZIL â€” PÃ¡gina de Produto
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once 'config.php';

// --- RASTREAMENTO AUTOMÃTICO DE AFILIAÃ‡ÃƒO ---
if (isset($_GET['ref'])) {
    require_once 'includes/affiliate_system.php';
    $affiliateSystem = new AffiliateSystem($pdo);
    $affiliate_code = $_GET['ref'];
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $result = $affiliateSystem->registerClick($affiliate_code, $product_id, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    if ($result['success']) {
        $_SESSION['affiliate_tracking'] = [
            'affiliate_code' => $affiliate_code,
            'click_id' => $result['click_id'],
            'timestamp' => time()
        ];
    }
    $params = $_GET;
    unset($params['ref']);
    header('Location: produto.php' . (!empty($params) ? '?' . http_build_query($params) : ''));
    exit();
}

$produto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produto_selecionado = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto_selecionado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto_selecionado) {
        $stmt_avaliacoes = $pdo->prepare(
            "SELECT a.*, u.nome as nome_usuario 
             FROM avaliacoes a
             JOIN usuarios u ON a.usuario_id = u.id
             WHERE a.produto_id = ? 
             ORDER BY a.data_avaliacao DESC"
        );
        $stmt_avaliacoes->execute([$produto_id]);
        $avaliacoes = $stmt_avaliacoes->fetchAll(PDO::FETCH_ASSOC);

        $total_avaliacoes = count($avaliacoes);
        $soma_notas = 0;
        foreach ($avaliacoes as $avaliacao) {
            $soma_notas += $avaliacao['nota'];
        }
        $media_notas = ($total_avaliacoes > 0) ? round($soma_notas / $total_avaliacoes, 1) : 0;
    }

    // Buscar tamanhos disponÃ­veis para produto fÃ­sico
    $produto_tamanhos = [];
    if ($produto_selecionado && ($produto_selecionado['tipo'] ?? 'digital') === 'fisico') {
        try {
            $stmt_tam = $pdo->prepare(
                "SELECT t.id, t.valor, pt.estoque 
                 FROM produto_tamanhos pt 
                 JOIN tamanhos t ON pt.tamanho_id = t.id 
                 WHERE pt.produto_id = ?
                 ORDER BY t.ordem ASC"
            );
            $stmt_tam->execute([$produto_id]);
            $produto_tamanhos = $stmt_tam->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (Exception $e) {
        // Tabela pode nÃ£o existir
        }
    }
}
catch (Exception $e) {
    error_log("Erro em produto.php: " . $e->getMessage());
    die("Erro ao carregar o produto.");
}

if (!$produto_selecionado) {
    header('Location: index.php');
    exit();
}

$page_title = htmlspecialchars($produto_selecionado['nome']);
$page_description = htmlspecialchars($produto_selecionado['descricao_curta']);
$page_keywords = 'produto, ' . strtolower(str_replace(' ', ', ', $produto_selecionado['nome'])) . ', comprar, macario brazil';
$page_image = htmlspecialchars($produto_selecionado['imagem']);

// MÃ©todos de pagamento
$metodos_pagamento = [];
try {
    if (isset($fileStorage) && is_object($fileStorage)) {
        $config = $fileStorage->getConfig();
        $infinite_tag = $config['infinite_tag'] ?? '';
        $infinite_status = $config['infinite_status'] ?? 'off';
        if ($infinite_status === 'on' && !empty($infinite_tag)) {
            $metodos_pagamento['infinitepay'] = [
                'url' => 'buy_now.php',
                'btn_text' => 'Pagar com Cartão / PIX',
                'sub_text' => 'Via InfinitePay',
                'icon' => 'fas fa-credit-card'
            ];
        }
        $chave_pix = $config['chave_pix'] ?? '';
        $pix_status = $config['pix_status'] ?? 'off';
        if ($pix_status === 'on' && !empty($chave_pix)) {
            $metodos_pagamento['pix'] = [
                'url' => 'buy_now.php',
                'btn_text' => 'Pagar com PIX',
                'sub_text' => 'Transferência Direta',
                'icon' => 'fas fa-qrcode'
            ];
        }
    }
}
catch (Exception $e) {
    error_log("Erro mÃ©todos pagamento: " . $e->getMessage());
}

require_once 'templates/header.php';
?>

<style>
    .product-page-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
    }

    @media (min-width: 1024px) {
        .product-page-grid {
            grid-template-columns: 1.2fr 1fr;
            gap: 3rem;
            align-items: start;
        }

        .product-info-sticky {
            position: sticky;
            top: 140px;
        }
    }

    .product-main-image {
        position: relative;
        border-radius: var(--radius-lg);
        overflow: hidden;
        background: var(--bg-tertiary);
        border: 1px solid var(--border-color);
        aspect-ratio: 1/1;
    }

    .product-main-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .product-main-image:hover img {
        transform: scale(1.05);
    }

    .product-detail-title {
        font-family: var(--font-display);
        font-size: clamp(1.8rem, 4vw, 2.8rem);
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 12px;
    }

    .product-detail-price {
        font-family: var(--font-display);
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 24px;
    }

    .product-detail-desc {
        color: var(--text-secondary);
        line-height: 1.7;
        margin-bottom: 32px;
        padding-left: 16px;
        border-left: 2px solid rgba(255, 255, 255, 0.15);
    }

    .product-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 16px 24px;
        font-family: var(--font-body);
        font-weight: 700;
        font-size: 0.9rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all var(--transition-base);
        border: none;
    }

    .product-action-primary {
        background: var(--text-primary);
        color: var(--bg-primary);
    }

    .product-action-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(255, 255, 255, 0.15);
    }

    .product-action-secondary {
        background: var(--glass-bg);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .product-action-secondary:hover {
        background: var(--glass-hover);
        border-color: var(--border-hover);
    }

    .product-features-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        margin-top: 24px;
    }

    .product-feature-item {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px;
        text-align: center;
        transition: border-color var(--transition-fast);
    }

    .product-feature-item:hover {
        border-color: var(--border-hover);
    }

    .product-feature-item i {
        font-size: 1.3rem;
        color: var(--text-secondary);
        margin-bottom: 8px;
        display: block;
    }

    .product-feature-item span {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-secondary);
    }

    /* Tabs */
    .product-tabs {
        display: flex;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 24px;
        gap: 0;
    }

    .product-tab-btn {
        background: none;
        border: none;
        color: var(--text-muted);
        padding: 12px 24px;
        font-family: var(--font-body);
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        cursor: pointer;
        position: relative;
        transition: color var(--transition-fast);
    }

    .product-tab-btn.active {
        color: var(--text-primary);
    }

    .product-tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--text-primary);
    }

    .product-tab-content {
        display: none;
    }

    .product-tab-content.active {
        display: block;
    }

    .review-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 20px;
        margin-bottom: 12px;
    }

    .review-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .review-user {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .review-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--glass-hover);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .review-stars {
        color: #d4a017;
        font-size: 0.7rem;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 32px;
        padding: 0 24px;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }

    .breadcrumb a:hover {
        color: var(--text-primary);
    }

    .breadcrumb i {
        font-size: 0.6rem;
    }

    .rating-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

    .rating-stars {
        color: #d4a017;
        font-size: 0.85rem;
    }

    .rating-count {
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    /* Size selector */
    .size-option:hover {
        border-color: var(--text-primary) !important;
        color: var(--text-primary) !important;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-6px); }
        40% { transform: translateX(6px); }
        60% { transform: translateX(-4px); }
        80% { transform: translateX(4px); }
    }
</style>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="index.php">Início</a>
    <i class="fas fa-chevron-right"></i>
    <a href="busca.php?todos=1">Catálogo</a>
    <i class="fas fa-chevron-right"></i>
    <span style="color: var(--text-primary);">
        <?= htmlspecialchars($produto_selecionado['nome'])?>
    </span>
</nav>

<section style="padding: 0 0 80px;">
    <div class="product-page-grid">
        <!-- Left: Image -->
        <div>
            <div class="product-main-image">
                <?php if (!empty($produto_selecionado['imagem']) && file_exists($produto_selecionado['imagem'])): ?>
                <img src="<?= htmlspecialchars($produto_selecionado['imagem'])?>"
                    alt="<?= htmlspecialchars($produto_selecionado['nome'])?>" />
                <?php
else: ?>
                <div
                    style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--bg-tertiary);">
                    <i class="fas fa-image" style="font-size:4rem;color:var(--text-muted);opacity:0.3;"></i>
                </div>
                <?php
endif; ?>
            </div>

            <div class="product-features-row">
                <div class="product-feature-item">
                    <i class="fas fa-truck"></i>
                    <span>Frete Grátis</span>
                </div>
                <div class="product-feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Garantia Total</span>
                </div>
                <div class="product-feature-item">
                    <i class="fas fa-bolt"></i>
                    <span>Entrega Rápida</span>
                </div>
            </div>
        </div>

        <!-- Right: Product Info -->
        <div class="product-info-sticky">
            <h1 class="product-detail-title">
                <?= htmlspecialchars($produto_selecionado['nome'])?>
            </h1>

            <!-- Rating -->
            <div class="rating-row">
                <div class="rating-stars">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <i class="fas fa-star<?=($i < round($media_notas)) ? '' : ' '?>"></i>
                    <?php
endfor; ?>
                </div>
                <span class="rating-count">(
                    <?= $total_avaliacoes?> avaliações)
                </span>
            </div>

            <!-- Price -->
            <div class="product-detail-price">
                <?= formatarPreco($produto_selecionado['preco'])?>
                <?php if (isset($produto_selecionado['preco_antigo']) && $produto_selecionado['preco_antigo'] > 0): ?>
                <span style="font-size:1rem;color:var(--text-muted);text-decoration:line-through;margin-left:8px;">
                    <?= formatarPreco($produto_selecionado['preco_antigo'])?>
                </span>
                <?php
endif; ?>
            </div>

            <!-- Short Description -->
            <?php if (!empty($produto_selecionado['descricao_curta'])): ?>
            <p class="product-detail-desc">
                <?= htmlspecialchars($produto_selecionado['descricao_curta'])?>
            </p>
            <?php
endif; ?>

            <!-- Size Selector (Physical products only) -->
            <?php if (!empty($produto_tamanhos)): ?>
            <div class="size-selector" style="margin-bottom:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <span style="font-size:0.85rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-secondary);">Tamanho</span>
                    <span id="selected-size-label" style="font-size:0.8rem;color:var(--text-muted);">Selecione</span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <?php foreach ($produto_tamanhos as $tam): 
                        $disponivel = $tam['estoque'] > 0;
                    ?>
                    <button type="button" 
                            class="size-option <?= $disponivel ? '' : 'out-of-stock' ?>" 
                            data-size-id="<?= $tam['id'] ?>" 
                            data-size-value="<?= htmlspecialchars($tam['valor']) ?>"
                            onclick="<?= $disponivel ? 'selectSize(this)' : 'void(0)' ?>"
                            <?= $disponivel ? '' : 'disabled' ?>
                            style="min-width:52px;padding:10px 16px;border:1px solid var(--border-color);border-radius:var(--radius-md);background:var(--bg-card);color:var(--text-secondary);font-family:var(--font-body);font-weight:600;font-size:0.9rem;cursor:<?= $disponivel ? 'pointer' : 'not-allowed' ?>;text-align:center;transition:all 0.2s ease; <?= $disponivel ? '' : 'opacity:0.4;position:relative;' ?>">
                        <?= htmlspecialchars($tam['valor']) ?>
                        <?php if (!$disponivel): ?>
                        <span style="position: absolute; top: -8px; left: 50%; transform: translateX(-50%); font-size: 7px; background: #ff4444; color: white; padding: 1px 4px; border-radius: 3px; font-weight: 900; text-transform: uppercase; white-space: nowrap;">Esgotado</span>
                        <?php endif; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selected-size-id" value="">
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php if (!empty($metodos_pagamento)): ?>
                <?php foreach ($metodos_pagamento as $metodo): ?>
                <a href="<?= $metodo['url']?>?produto_id=<?= $produto_selecionado['id']?>&quantidade=1"
                    class="product-action-btn product-action-primary checkout-link"
                    <?= !empty($produto_tamanhos) ? 'onclick="return validateSize(this)"' : '' ?>>
                    <i class="<?= $metodo['icon']?>"></i>
                    <?= htmlspecialchars($metodo['btn_text'])?>
                </a>
                <?php
    endforeach; ?>
                <?php
 endif; ?>

                <form id="add-to-cart-form" style="margin:0;">
                    <input type="hidden" name="produto_id" value="<?= $produto_selecionado['id']?>">
                    <input type="hidden" name="tamanho_id" id="cart-tamanho-id" value="">
                    <button type="submit" class="product-action-btn product-action-secondary"
                        <?= !empty($produto_tamanhos) ? 'onclick="return validateSize(this)"' : '' ?>>
                        <i class="fas fa-shopping-bag"></i>
                        Adicionar ao Carrinho
                    </button>
                </form>
            </div>

            <!-- Trust signals -->
            <div
                style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border-color);display:flex;gap:16px;flex-wrap:wrap;">
                <span style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:var(--text-muted);">
                    <i class="fas fa-lock"></i> Pagamento Seguro
                </span>
                <span style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:var(--text-muted);">
                    <i class="fas fa-headset"></i> Suporte 24/7
                </span>
                <span style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:var(--text-muted);">
                    <i class="fas fa-sync-alt"></i> Trocas Grátis
                </span>
            </div>
        </div>
    </div>

    <!-- Tabs: Description & Reviews -->
    <div style="max-width:1200px;margin:48px auto 0;padding:0 24px;">
        <div class="product-tabs">
            <button class="product-tab-btn active" onclick="switchTab('desc', this)">Descrição</button>
            <button class="product-tab-btn" onclick="switchTab('reviews', this)">Avaliações (
                <?= $total_avaliacoes?>)
            </button>
        </div>

        <div id="tab-desc" class="product-tab-content active">
            <div style="color:var(--text-secondary);line-height:1.8;">
                <?= nl2br(htmlspecialchars($produto_selecionado['descricao'] ?? ''))?>
            </div>
        </div>

        <div id="tab-reviews" class="product-tab-content">
            <?php if (!empty($avaliacoes)): ?>
            <?php foreach ($avaliacoes as $avaliacao): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="review-user">
                        <div class="review-avatar">
                            <?= strtoupper(substr($avaliacao['nome_usuario'], 0, 1))?>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;">
                                <?= htmlspecialchars($avaliacao['nome_usuario'])?>
                            </div>
                            <div class="review-stars">
                                <?php for ($i = 0; $i < $avaliacao['nota']; $i++)
            echo '<i class="fas fa-star"></i>'; ?>
                            </div>
                        </div>
                    </div>
                    <span style="font-size:0.75rem;color:var(--text-muted);">
                        <?= date('d/m/Y', strtotime($avaliacao['data_avaliacao']))?>
                    </span>
                </div>
                <p style="color:var(--text-secondary);font-size:0.9rem;">
                    <?= htmlspecialchars($avaliacao['comentario'])?>
                </p>
            </div>
            <?php
    endforeach; ?>
            <?php
else: ?>
            <p style="text-align:center;color:var(--text-muted);padding:40px 0;">Nenhuma avaliaÃ§Ã£o ainda. Seja o
                primeiro a avaliar!</p>
            <?php
endif; ?>
        </div>
    </div>
</section>

<script>
    function switchTab(tabName, btn) {
        document.querySelectorAll('.product-tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.product-tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');
        btn.classList.add('active');
    }

    // Size Selection
    function selectSize(btn) {
        // Remove active from all
        document.querySelectorAll('.size-option').forEach(el => {
            el.style.borderColor = 'var(--border-color)';
            el.style.background = 'var(--bg-card)';
            el.style.color = 'var(--text-secondary)';
        });
        // Activate selected
        btn.style.borderColor = 'var(--text-primary)';
        btn.style.background = 'var(--text-primary)';
        btn.style.color = 'var(--bg-primary)';
        // Update hidden inputs
        const sizeId = btn.dataset.sizeId;
        const sizeValue = btn.dataset.sizeValue;
        document.getElementById('selected-size-id').value = sizeId;
        document.getElementById('cart-tamanho-id').value = sizeId;
        document.getElementById('selected-size-label').textContent = sizeValue;
        // Update checkout links with size
        document.querySelectorAll('.checkout-link').forEach(link => {
            const url = new URL(link.href, window.location.origin);
            url.searchParams.set('tamanho_id', sizeId);
            link.href = url.toString();
        });
    }

    function validateSize(link) {
        const sizeId = document.getElementById('selected-size-id');
        if (sizeId && !sizeId.value) {
            // Visual shake effect
            const selector = document.querySelector('.size-selector');
            selector.style.animation = 'none';
            selector.offsetHeight; // trigger reflow
            selector.style.animation = 'shake 0.5s ease';
            // Highlight
            document.getElementById('selected-size-label').textContent = 'âš ï¸ Selecione um tamanho';
            document.getElementById('selected-size-label').style.color = '#f87171';
            setTimeout(() => {
                document.getElementById('selected-size-label').style.color = '';
            }, 2000);
            return false;
        }
        return true;
    }

    document.getElementById('add-to-cart-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('button');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';

        fetch('adicionar_carrinho.php', {
            method: 'POST',
            body: new FormData(this)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '<i class="fas fa-check"></i> Adicionado!';
                    if (typeof showToast === 'function') showToast('Produto adicionado ao carrinho!', '🛒');
                    const badge = document.getElementById('cart-count');
                    if (badge) badge.textContent = data.cart_count || (parseInt(badge.textContent || 0) + 1);
                    setTimeout(() => { btn.innerHTML = original; btn.disabled = false; }, 2000);
                } else {
                    alert(data.message || 'Erro ao adicionar.');
                    btn.innerHTML = original;
                    btn.disabled = false;
                }
            })
            .catch(() => {
                alert('Erro de conexÃ£o.');
                btn.innerHTML = original;
                btn.disabled = false;
            });
    });
</script>

<?php require_once 'templates/footer.php'; ?>
