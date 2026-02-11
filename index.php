não e nos desta
<?php
// index.php — MACARIO BRAZIL E-commerce
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// --- RASTREAMENTO DE AFILIAÇÃO ---
if (isset($_GET['ref'])) {
    require_once 'includes/affiliate_system.php';
    $affiliateSystem = new AffiliateSystem($pdo);
    $affiliate_code = $_GET['ref'];
    $result = $affiliateSystem->registerClick($affiliate_code, null, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    if ($result['success']) {
        $_SESSION['affiliate_tracking'] = [
            'affiliate_code' => $affiliate_code,
            'click_id' => $result['click_id'],
            'timestamp' => time()
        ];
    }
    $params = $_GET;
    unset($params['ref']);
    header('Location: index.php' . (!empty($params) ? '?' . http_build_query($params) : ''));
    exit();
}

// --- BUSCAR DADOS ---
try {
    $banners_principais = $pdo->query("SELECT * FROM banners WHERE tipo = 'principal' AND ativo = 1 ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // Filtra apenas categorias que devem aparecer na home
    $categorias = $pdo->query("SELECT * FROM categorias WHERE exibir_home = 1 ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);

    $produtos_por_categoria = [];
    foreach ($categorias as $categoria) {
        $stmt = $pdo->prepare("SELECT id, nome, preco, imagem, descricao_curta FROM produtos WHERE categoria_id = ? ORDER BY id DESC");
        $stmt->execute([$categoria['id']]);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($produtos)) {
            $produtos_por_categoria[$categoria['id']] = $produtos;
        }
    }

    // Busca produtos marcados como destaque
    $destaques = $pdo->query("SELECT id, nome, preco, imagem, descricao_curta FROM produtos WHERE destaque = 1 ORDER BY id DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);

    // Se não houver nenhum marcado, respeitamos o controle manual e não mostramos nada automaticamente
    if (!$destaques) {
        $destaques = [];
    }

}
catch (Exception $e) {
    error_log("Erro ao buscar dados: " . $e->getMessage());
    $produtos_por_categoria = [];
    $destaques = [];
    $banners_principais = [];
}

$page_title = 'Início';
$page_description = 'MACARIO BRAZIL — Roupas, tênis, eletrônicos e produtos digitais premium. Estilo e cultura na sua casa.';
require_once 'templates/header.php';
?>

<!-- ══════════════════════════════════════════
     HERO SECTION
     ══════════════════════════════════════════ -->
<section class="hero" id="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-eyebrow">
            <span>Nova Coleção Disponível</span>
        </div>
        <h1 class="hero-title">Estilo &<br>Cultura</h1>
        <p class="hero-subtitle">Roupas, tênis, eletrônicos e produtos digitais com qualidade premium. Levando o melhor
            até a sua casa.</p>
        <div class="hero-actions">
            <a href="busca.php?todos=1" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Ver Catálogo
            </a>
            <a href="#produtos" class="btn btn-outline">
                Explorar
                <i class="fas fa-arrow-down"></i>
            </a>
        </div>
    </div>
    <div class="scroll-indicator">
        <span>Scroll</span>
        <div class="scroll-line"></div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     CATEGORY BAR
     ══════════════════════════════════════════ -->
<?php if (!empty($categorias)): ?>
<section class="category-bar">
    <div class="container">
        <div class="category-scroll">
            <a href="busca.php?todos=1" class="category-item">
                <div class="category-icon">
                    <i class="fas fa-fire"></i>
                </div>
                <span class="category-name">Em Alta</span>
            </a>
            <?php
    $cat_icons = [
        'roupas' => 'fa-tshirt',
        'roupa' => 'fa-tshirt',
        'camiseta' => 'fa-tshirt',
        'camisa' => 'fa-tshirt',
        'tenis' => 'fa-shoe-prints',
        'tênis' => 'fa-shoe-prints',
        'sneakers' => 'fa-shoe-prints',
        'calçado' => 'fa-shoe-prints',
        'eletronico' => 'fa-laptop',
        'eletrônico' => 'fa-laptop',
        'digital' => 'fa-code',
        'acessorio' => 'fa-gem',
        'acessório' => 'fa-gem',
        'bone' => 'fa-hat-cowboy',
        'boné' => 'fa-hat-cowboy',
        'conjunto' => 'fa-layer-group',
        'relogio' => 'fa-clock',
        'relógio' => 'fa-clock',
        'bolsa' => 'fa-shopping-bag',
        'joia' => 'fa-ring',
        'fone' => 'fa-headphones',
        'celular' => 'fa-mobile-alt',
        'game' => 'fa-gamepad',
        'jogo' => 'fa-gamepad',
    ];
    foreach ($categorias as $cat):
        $icon = 'fa-tag';
        $nome_lower = mb_strtolower($cat['nome']);
        foreach ($cat_icons as $key => $val) {
            if (strpos($nome_lower, $key) !== false) {
                $icon = $val;
                break;
            }
        }
?>
            <a href="categoria.php?id=<?= $cat['id']?>" class="category-item">
                <div class="category-icon">
                    <i class="fas <?= $icon?>"></i>
                </div>
                <span class="category-name">
                    <?= htmlspecialchars($cat['nome'])?>
                </span>
            </a>
            <?php
    endforeach; ?>
        </div>
    </div>
</section>
<?php
endif; ?>

<!-- ══════════════════════════════════════════
     FEATURED PRODUCTS (Destaques)
     ══════════════════════════════════════════ -->
<?php if (!empty($destaques)): ?>
<section class="section" id="produtos">
    <div class="container">
        <div class="section-header">
            <div>
                <div class="section-label">Novidades</div>
                <h2 class="section-title">Destaques</h2>
            </div>
            <a href="busca.php?todos=1" class="section-more">
                Ver tudo <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="swiper destaques-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($destaques as $idx => $produto): ?>
                <div class="swiper-slide" style="height:auto;">
                    <a href="produto.php?id=<?= $produto['id']?>" class="product-card" style="height:100%;">
                        <div class="product-image">
                            <?php if (!empty($produto['imagem']) && file_exists($produto['imagem'])): ?>
                            <img src="<?= htmlspecialchars($produto['imagem'])?>"
                                alt="<?= htmlspecialchars($produto['nome'])?>" loading="lazy" />
                            <?php
        else: ?>
                            <div class="product-image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                            <?php
        endif; ?>
                            <?php if ($idx < 3): ?>
                            <span class="product-badge">Novo</span>
                            <?php
        endif; ?>
                        </div>
                        <div class="product-info">
                            <span class="product-category-tag">MACARIO BRAZIL</span>
                            <h3 class="product-name">
                                <?= htmlspecialchars($produto['nome'])?>
                            </h3>
                            <div class="product-price-row">
                                <span class="product-price">
                                    <?= formatarPreco($produto['preco'])?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
    endforeach; ?>
            </div>
            <!-- Pagination/Navigation if needed -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>
<?php
endif; ?>

<!-- ══════════════════════════════════════════
     FEATURED BANNER
     ══════════════════════════════════════════ -->
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div class="featured-banner">
            <div class="featured-banner-bg"
                style="background-image: linear-gradient(135deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);">
            </div>
            <div class="featured-banner-content">
                <div class="section-label">Exclusivo</div>
                <h2>Coleção Premium</h2>
                <p>Descubra nossos produtos selecionados com qualidade e estilo incomparáveis. Entrega rápida para todo
                    o Brasil.</p>
                <a href="busca.php?todos=1" class="btn btn-primary">
                    Comprar Agora <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     PRODUCTS BY CATEGORY
     ══════════════════════════════════════════ -->
<?php if (!empty($produtos_por_categoria)): ?>
<?php foreach ($produtos_por_categoria as $categoria_id => $produtos): ?>
<?php
        $cat_name = "Categoria";
        foreach ($categorias as $c) {
            if ($c['id'] == $categoria_id)
                $cat_name = $c['nome'];
        }
?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <div class="section-label">Coleção</div>
                <h2 class="section-title">
                    <?= htmlspecialchars($cat_name)?>
                </h2>
            </div>
            <a href="categoria.php?id=<?= $categoria_id?>" class="section-more">
                Ver tudo <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="swiper produtos-swiper produtos-swiper-<?= $categoria_id?>">
            <div class="swiper-wrapper">
                <?php foreach ($produtos as $produto): ?>
                <div class="swiper-slide" style="height:auto;">
                    <a href="produto.php?id=<?= $produto['id']?>" class="product-card" style="height:100%;">
                        <div class="product-image">
                            <?php if (!empty($produto['imagem']) && file_exists($produto['imagem'])): ?>
                            <img src="<?= htmlspecialchars($produto['imagem'])?>"
                                alt="<?= htmlspecialchars($produto['nome'])?>" loading="lazy" />
                            <?php
            else: ?>
                            <div class="product-image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                            <?php
            endif; ?>
                        </div>
                        <div class="product-info">
                            <span class="product-category-tag">
                                <?= htmlspecialchars($cat_name)?>
                            </span>
                            <h3 class="product-name">
                                <?= htmlspecialchars($produto['nome'])?>
                            </h3>
                            <div class="product-price-row">
                                <span class="product-price">
                                    <?= formatarPreco($produto['preco'])?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
        endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php
    endforeach; ?>
<?php
endif; ?>

<!-- ══════════════════════════════════════════
     TRUST BAR
     ══════════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <div class="trust-bar">
            <div class="trust-item">
                <div class="trust-icon"><i class="fas fa-truck"></i></div>
                <div class="trust-title">Frete Grátis</div>
                <div class="trust-desc">Entrega grátis para todo o Brasil em todos os pedidos.</div>
            </div>
            <div class="trust-item">
                <div class="trust-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="trust-title">Compra Segura</div>
                <div class="trust-desc">Seus dados protegidos com criptografia de ponta a ponta.</div>
            </div>
            <div class="trust-item">
                <div class="trust-icon"><i class="fas fa-sync-alt"></i></div>
                <div class="trust-title">Trocas Fáceis</div>
                <div class="trust-desc">Primeira troca grátis em até 30 dias após a compra.</div>
            </div>
            <div class="trust-item">
                <div class="trust-icon"><i class="fas fa-headset"></i></div>
                <div class="trust-title">Suporte 24/7</div>
                <div class="trust-desc">Atendimento rápido via WhatsApp e chat a qualquer momento.</div>
            </div>
        </div>
    </div>
</section>

<!-- Swiper Init -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swiper !== 'undefined') {
            // Init Destaques Swiper
            new Swiper('.destaques-swiper', {
                slidesPerView: 1.2,
                spaceBetween: 16,
                grabCursor: true,
                breakpoints: {
                    480: { slidesPerView: 2.2, spaceBetween: 16 },
                    768: { slidesPerView: 3, spaceBetween: 20 },
                    1024: { slidesPerView: 4, spaceBetween: 20 }
                }
            });

        // Init Categories Swipers
        <?php if (!empty($produtos_por_categoria)): ?>
        <?php foreach($produtos_por_categoria as $categoria_id => $produtos_cat): ?>
                new Swiper('.produtos-swiper-<?= $categoria_id?>', {
                    slidesPerView: 1.2,
                    spaceBetween: 16,
                    grabCursor: true,
                    breakpoints: {
                        480: { slidesPerView: 2.2, spaceBetween: 16 },
                        768: { slidesPerView: 3, spaceBetween: 20 },
                        1024: { slidesPerView: 4, spaceBetween: 20 }
                    }
                });
        <?php
    endforeach; ?>
        <?php
endif; ?>
    } else {
            console.error('Swiper não carregado');
        }
    });
</script>

<?php require_once 'templates/footer.php'; ?>