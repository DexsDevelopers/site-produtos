<?php
// produto.php — MACARIO BRAZIL — Página de Produto
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once 'config.php';

// --- RASTREAMENTO AUTOMÃTICO DE AFILIAÇÃƒO ---
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

    // Buscar tamanhos disponíveis para produto físico
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
        // Tabela pode não existir
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

// Busca galeria de imagens
$galeria_produto = [];
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS produto_imagens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            produto_id INT NOT NULL,
            imagem VARCHAR(500) NOT NULL,
            ordem INT DEFAULT 0,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $stmt_gi = $pdo->prepare("SELECT imagem FROM produto_imagens WHERE produto_id = ? ORDER BY ordem ASC, id ASC");
    $stmt_gi->execute([$produto_id]);
    $galeria_produto = $stmt_gi->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

// ── Fake reviews for social proof ──
$fake_nomes = [
    'Ana Carolina S.','Pedro Henrique M.','Juliana Costa','Rafael Oliveira','Mariana Santos',
    'Lucas Ferreira','Camila Rodrigues','Gabriel Almeida','Isabela Souza','Thiago Pereira',
    'Larissa Lima','Matheus Cardoso','Fernanda Alves','Bruno Souza','Letícia Nunes',
    'Diego Martins','Priscila Fonseca','Rodrigo Castelo','Natália Vieira','Felipe Ramos',
    'Amanda Gomes','Henrique Barros','Tatiane Moura','Carlos Eduardo P.','Bianca Freitas',
    'Vinícius Lopes','Jéssica Andrade','Eduardo Cunha','Samantha Borges','Leonardo Dias',
];
$fake_cidades = [
    'São Paulo, SP','Rio de Janeiro, RJ','Belo Horizonte, MG','Curitiba, PR','Porto Alegre, RS',
    'Salvador, BA','Brasília, DF','Recife, PE','Fortaleza, CE','Florianópolis, SC',
    'Goiânia, GO','Manaus, AM','Natal, RN','Maceió, AL','Vitória, ES',
];
$fake_textos = [
    'Produto incrível! Superou todas as minhas expectativas. Qualidade excelente e chegou bem antes do prazo.',
    'Amei demais! Material de primeira qualidade, acabamento impecável. Já indiquei para todos os amigos.',
    'Melhor compra que fiz esse ano! Chegou em 3 dias, embalagem perfeita e produto idêntico às fotos.',
    'Comprei com receio mas me surpreendi. Qualidade muito boa pelo preço. Com certeza vou comprar mais.',
    'Chegou super rápido! O produto é exatamente como descrito, muito bem feito. Nota 10!',
    'Fiz o pedido e chegou rapidinho. Produto com ótima qualidade, estou muito satisfeita com a compra!',
    'Excelente custo-benefício! Produto bonito, bem feito e chegou antes do prazo. Recomendo a todos!',
    'Produto original, qualidade top. Já é minha terceira compra aqui e nunca me decepcionou.',
    'Vim pela indicação de uma amiga e não me arrependo. Produto perfeito, entrega rápida!',
    'Qualidade absurda pelo preço. Parece bem mais caro do que é. Fiquei impressionado com o acabamento.',
    'Atendimento nota 10, produto chegou embalado com muito cuidado. Recomendo a loja de olhos fechados!',
    'Produto de alta qualidade, exatamente como nas fotos. A entrega foi mais rápida do que esperava.',
    'Simplesmente perfeito! Não tenho nenhuma reclamação. Chegou no prazo e produto top demais.',
    'Valeu cada centavo! Muito bem embalado, produto lindo e de qualidade. Vou comprar mais vezes.',
    'Comprei de presente e a pessoa amou! A qualidade é visível, parece produto de loja de grife.',
    'Já fiz várias compras aqui e todas foram ótimas. Loja séria, produto sempre no prazo e perfeito.',
    'Não esperava tanta qualidade por esse preço. Ficou ainda melhor do que parecia nas fotos.',
    'Primeira compra e já virei fã! Chegou bem embalado, produto impecável. Nota máxima!',
    'Produto top! Comprei pra mim e comprei mais um de presente. Qualidade que surpreende.',
    'Entrega mais rápida do que prometido, produto com acabamento incrível. Superou tudo!',
    'Nunca fui muito de comprar online mas esse produto me conquistou. Qualidade real, sem enganação.',
    'Perfeito em todos os sentidos. Embalagem caprichada, produto original e entrega pontual.',
    'Usei e adorei desde o primeiro dia. Acabamento de qualidade, parece produto de boutique.',
    'Chegou antes do prazo com nota fiscal e tudo certinho. Produto de qualidade real. Recomendo!',
    'Comprei depois de ver muitos comentários positivos e não me decepcionei. Produto excelente!',
    'Loja confiável e produto que supera as expectativas. Já recomendei para toda a família.',
    'Produto com acabamento impecável, entrega super rápida. 10/10 em tudo, sem dúvidas!',
    'Qualidade que se sente na hora que abre a embalagem. Produto premium por um preço justo.',
    'Recebeu elogios de todo mundo que viu. Qualidade visível, loja que entrega o que promete.',
    'Fiquei chocada com a qualidade! Muito melhor do que eu imaginava. Com certeza vou comprar mais.',
];
$fake_notas_pool = [5,5,5,5,4,5,5,5,4,5,5,4,5,5,5,5,4,5,5,5];

// Strong per-product hash to guarantee unique review sets
function _fhash($a, $b) { return abs(($a * 2654435761 ^ $b * 40503) & 0x7FFFFFFF); }

$fake_count = 8 + (_fhash($produto_id, 0) % 6); // 8 to 13 reviews
$fake_reviews = [];
for ($i = 0; $i < $fake_count; $i++) {
    $fake_reviews[] = [
        'nome'   => $fake_nomes[_fhash($produto_id * 31, $i * 7)   % count($fake_nomes)],
        'cidade' => $fake_cidades[_fhash($produto_id * 53, $i * 11) % count($fake_cidades)],
        'nota'   => $fake_notas_pool[_fhash($produto_id * 17, $i * 3) % count($fake_notas_pool)],
        'texto'  => $fake_textos[_fhash($produto_id * 97, $i * 13)  % count($fake_textos)],
        'data'   => date('d/m/Y', strtotime('-' . (2 + _fhash($produto_id * 43, $i * 19) % 88) . ' days')),
    ];
}
$fake_rating = [4.7,4.8,4.9,5.0,4.8,4.9,5.0,4.7,4.8,4.9,4.6,4.9,4.8,5.0,4.7][$produto_id % 15];
$fake_total  = 47 + ((_fhash($produto_id, 99)) % 453);
$display_total = $fake_total + $total_avaliacoes;
$pct5 = 70 + (_fhash($produto_id, 1) % 20);
$pct4 = 10 + (_fhash($produto_id, 2) % 10);
$pct3 = max(2, 100 - $pct5 - $pct4 - 3);
$pct2 = 2; $pct1 = 1;

$page_title = htmlspecialchars($produto_selecionado['nome']);
$page_description = htmlspecialchars($produto_selecionado['descricao_curta']);
$page_keywords = 'produto, ' . strtolower(str_replace(' ', ', ', $produto_selecionado['nome'])) . ', comprar, macario brazil';
$page_image = htmlspecialchars($produto_selecionado['imagem']);

// Métodos de pagamento — lê do banco
$metodos_pagamento = [];
try {
    $stmt_cfg = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('infinite_tag','infinite_status','pixghost_api_key','pix_status')");
    $cfg = $stmt_cfg->fetchAll(PDO::FETCH_KEY_PAIR);

    if (($cfg['infinite_status'] ?? 'off') === 'on' && !empty($cfg['infinite_tag'] ?? '')) {
        $metodos_pagamento['infinitepay'] = [
            'url'      => 'checkout_infinitepay.php',
            'btn_text' => 'Comprar Agora — Cartão',
            'icon'     => 'fas fa-credit-card'
        ];
    }
    if (($cfg['pix_status'] ?? 'off') === 'on' && !empty($cfg['pixghost_api_key'] ?? '')) {
        $metodos_pagamento['pix'] = [
            'url'      => 'checkout_pix.php',
            'btn_text' => 'Comprar Agora — PIX',
            'icon'     => 'fas fa-qrcode'
        ];
    }
} catch (Exception $e) {
    error_log("Erro métodos pagamento: " . $e->getMessage());
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
            <div class="product-main-image" id="main-image-wrap">
                <?php if (!empty($produto_selecionado['imagem'])): ?>
                <img id="main-product-img"
                     src="<?= htmlspecialchars($produto_selecionado['imagem'])?>"
                     alt="<?= htmlspecialchars($produto_selecionado['nome'])?>" />
                <?php else: ?>
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--bg-tertiary);">
                    <i class="fas fa-image" style="font-size:4rem;color:var(--text-muted);opacity:0.3;"></i>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($galeria_produto)): ?>
            <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
                <?php if (!empty($produto_selecionado['imagem'])): ?>
                <img src="<?= htmlspecialchars($produto_selecionado['imagem']) ?>"
                     onclick="trocarImagem(this,'<?= htmlspecialchars($produto_selecionado['imagem']) ?>')"
                     style="width:64px;height:64px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid var(--text-primary);opacity:1;"
                     class="thumb-img">
                <?php endif; ?>
                <?php foreach ($galeria_produto as $gi): ?>
                <img src="<?= htmlspecialchars($gi) ?>"
                     onclick="trocarImagem(this,'<?= htmlspecialchars($gi) ?>')"
                     style="width:64px;height:64px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid transparent;opacity:0.6;"
                     class="thumb-img">
                <?php endforeach; ?>
            </div>
            <script>
            function trocarImagem(el, src) {
                document.getElementById('main-product-img').src = src;
                document.querySelectorAll('.thumb-img').forEach(t => {
                    t.style.border = '2px solid transparent';
                    t.style.opacity = '0.6';
                });
                el.style.border = '2px solid var(--text-primary)';
                el.style.opacity = '1';
            }
            </script>
            <?php endif; ?>

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
                    <?php
                    $rs_full = floor($fake_rating);
                    $rs_half = ($fake_rating - $rs_full) >= 0.5 ? 1 : 0;
                    for ($i = 0; $i < $rs_full; $i++) echo '<i class="fas fa-star"></i>';
                    if ($rs_half) echo '<i class="fas fa-star-half-alt"></i>';
                    for ($i = $rs_full + $rs_half; $i < 5; $i++) echo '<i class="far fa-star" style="opacity:0.4;"></i>';
                    ?>
                </div>
                <span class="rating-count" style="cursor:pointer;" onclick="switchTab('reviews', document.querySelectorAll('.product-tab-btn')[1])">
                    <?= number_format($fake_rating, 1) ?> &nbsp;·&nbsp; <?= $display_total ?> avaliações
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
                <a href="checkout_produto.php?produto_id=<?= $produto_selecionado['id']?>&quantidade=1"
                    class="product-action-btn product-action-primary checkout-link"
                    onclick="return handleCheckoutClick(this, event, <?= !empty($produto_tamanhos) ? 'true' : 'false' ?>)">
                    <i class="fas fa-lock"></i>
                    <span class="btn-text">Comprar Agora</span>
                </a>
                <?php endif; ?>

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
            <button class="product-tab-btn" onclick="switchTab('reviews', this)">Avaliações (<?= $display_total ?>)</button>
        </div>

        <div id="tab-desc" class="product-tab-content active">
            <div style="color:var(--text-secondary);line-height:1.8;">
                <?= nl2br(htmlspecialchars($produto_selecionado['descricao'] ?? ''))?>
            </div>
        </div>

        <div id="tab-reviews" class="product-tab-content">

            <!-- Rating Summary -->
            <div style="display:flex;gap:32px;align-items:flex-start;flex-wrap:wrap;padding:24px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-md);margin-bottom:24px;">
                <div style="text-align:center;min-width:100px;">
                    <div style="font-family:var(--font-display);font-size:3rem;font-weight:800;color:var(--text-primary);line-height:1;"><?= number_format($fake_rating, 1) ?></div>
                    <div class="review-stars" style="font-size:0.9rem;margin:6px 0;">
                        <?php
                        $rs2_full = floor($fake_rating); $rs2_half = ($fake_rating - $rs2_full) >= 0.5 ? 1 : 0;
                        for ($i = 0; $i < $rs2_full; $i++) echo '<i class="fas fa-star"></i>';
                        if ($rs2_half) echo '<i class="fas fa-star-half-alt"></i>';
                        for ($i = $rs2_full + $rs2_half; $i < 5; $i++) echo '<i class="far fa-star" style="opacity:0.4;"></i>';
                        ?>
                    </div>
                    <div style="font-size:0.75rem;color:var(--text-muted);"><?= $display_total ?> avaliações</div>
                </div>
                <div style="flex:1;min-width:200px;display:flex;flex-direction:column;gap:6px;">
                    <?php foreach ([5=>$pct5, 4=>$pct4, 3=>$pct3, 2=>$pct2, 1=>$pct1] as $star => $pct): ?>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:0.75rem;color:var(--text-muted);min-width:14px;text-align:right;"><?= $star ?></span>
                        <i class="fas fa-star" style="color:#d4a017;font-size:0.65rem;"></i>
                        <div style="flex:1;height:6px;background:var(--bg-tertiary);border-radius:4px;overflow:hidden;">
                            <div style="height:100%;width:<?= $pct ?>%;background:<?= $star >= 4 ? '#d4a017' : ($star === 3 ? '#888' : '#555') ?>;border-radius:4px;"></div>
                        </div>
                        <span style="font-size:0.7rem;color:var(--text-muted);min-width:32px;"><?= $pct ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Fake reviews -->
            <?php foreach ($fake_reviews as $fr):
                $inicial = mb_strtoupper(mb_substr($fr['nome'], 0, 1));
            ?>
            <div class="review-card" style="margin-bottom:12px;">
                <div class="review-header">
                    <div class="review-user">
                        <div class="review-avatar"><?= $inicial ?></div>
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($fr['nome']) ?></div>
                            <div style="font-size:0.65rem;color:var(--text-muted);"><?= htmlspecialchars($fr['cidade']) ?></div>
                            <div class="review-stars" style="margin-top:2px;">
                                <?php for ($i = 0; $i < $fr['nota']; $i++) echo '<i class="fas fa-star"></i>'; ?>
                            </div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:0.75rem;color:var(--text-muted);display:block;"><?= $fr['data'] ?></span>
                        <span style="font-size:0.6rem;color:#22c55e;display:flex;align-items:center;gap:3px;margin-top:4px;justify-content:flex-end;"><i class="fas fa-check-circle"></i> Compra verificada</span>
                    </div>
                </div>
                <p style="color:var(--text-secondary);font-size:0.875rem;line-height:1.6;"><?= htmlspecialchars($fr['texto']) ?></p>
            </div>
            <?php endforeach; ?>

            <!-- Real reviews -->
            <?php foreach ($avaliacoes as $avaliacao): ?>
            <div class="review-card" style="margin-bottom:12px;">
                <div class="review-header">
                    <div class="review-user">
                        <div class="review-avatar"><?= strtoupper(substr($avaliacao['nome_usuario'], 0, 1)) ?></div>
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($avaliacao['nome_usuario']) ?></div>
                            <div class="review-stars" style="margin-top:2px;">
                                <?php for ($i = 0; $i < $avaliacao['nota']; $i++) echo '<i class="fas fa-star"></i>'; ?>
                            </div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:0.75rem;color:var(--text-muted);display:block;"><?= date('d/m/Y', strtotime($avaliacao['data_avaliacao'])) ?></span>
                        <span style="font-size:0.6rem;color:#22c55e;display:flex;align-items:center;gap:3px;margin-top:4px;justify-content:flex-end;"><i class="fas fa-check-circle"></i> Compra verificada</span>
                    </div>
                </div>
                <p style="color:var(--text-secondary);font-size:0.875rem;line-height:1.6;"><?= htmlspecialchars($avaliacao['comentario']) ?></p>
            </div>
            <?php endforeach; ?>

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

    function handleCheckoutClick(link, e, requiresSize) {
        if (requiresSize && !validateSize(link)) return false;
        link.style.pointerEvents = 'none';
        link.style.opacity = '0.7';
        link.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i> Processando...';
        return true;
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
            document.getElementById('selected-size-label').textContent = '⚠️ Selecione um tamanho';
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
                alert('Erro de conexão.');
                btn.innerHTML = original;
                btn.disabled = false;
            });
    });
</script>

<?php require_once 'templates/footer.php'; ?>
