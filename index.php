<?php
// index.php - O Mercado é dos Tubarões (Modernized)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// --- RASTREAMENTO AUTOMÁTICO DE AFILIAÇÃO ---
if (isset($_GET['ref'])) {
    require_once 'includes/affiliate_system.php';
    $affiliateSystem = new AffiliateSystem($pdo);

    $affiliate_code = $_GET['ref'];
    $product_id = null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $result = $affiliateSystem->registerClick($affiliate_code, $product_id, $ip_address);

    if ($result['success']) {
        $_SESSION['affiliate_tracking'] = [
            'affiliate_code' => $affiliate_code,
            'click_id' => $result['click_id'],
            'timestamp' => time()
        ];
    }

    $params = $_GET;
    unset($params['ref']);
    $redirect_url = 'index.php';
    if (!empty($params)) {
        $redirect_url .= '?' . http_build_query($params);
    }
    header('Location: ' . $redirect_url);
    exit();
}

// --- BUSCA DADOS ---
try {
    $banners_principais = $pdo->query("SELECT * FROM banners WHERE tipo = 'principal' AND ativo = 1 ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    $banners_categorias = $pdo->query("SELECT * FROM banners WHERE tipo = 'categoria' AND ativo = 1 ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    $categorias = $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);

    $produtos_por_categoria = [];
    foreach ($categorias as $categoria) {
        $stmt = $pdo->prepare("SELECT id, nome, preco, imagem, descricao_curta FROM produtos WHERE categoria_id = ? ORDER BY id DESC");
        $stmt->execute([$categoria['id']]);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($produtos)) {
            $produtos_por_categoria[$categoria['id']] = $produtos;
        }
    }
} catch (Exception $e) {
    error_log("Erro ao buscar dados: " . $e->getMessage());
    $produtos_por_categoria = [];
}

$page_title = 'Início';
require_once 'templates/header.php';
?>

<link rel="stylesheet" href="assets/css/modern.css">

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-bg-effect"></div>
    <div class="hero-content reveal-load">
        <h1 class="hero-title">O Mercado é dos <span>Tubarões</span></h1>
        <p class="hero-subtitle">Produtos premium para quem não aceita o segundo lugar. Qualidade, velocidade e
            segurança.</p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#produtos" class="btn-modern">
                <i class="fas fa-shopping-bag"></i> Ver Produtos
            </a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn-modern" style="background: transparent; border: 1px solid var(--brand-red);">
                    <i class="fas fa-user"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Banners Principais -->
<?php if (!empty($banners_principais)): ?>
    <section class="py-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($banners_principais as $banner): ?>
                    <a href="<?= htmlspecialchars($banner['link']) ?>"
                        class="group relative overflow-hidden rounded-2xl block aspect-[21/9]">
                        <img src="<?= htmlspecialchars($banner['imagem']) ?>" alt="<?= htmlspecialchars($banner['titulo']) ?>"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex items-end p-6">
                            <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($banner['titulo']) ?></h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Banners Categorias -->
<?php if (!empty($banners_categorias)): ?>
    <section class="py-10">
        <div class="container mx-auto px-4">
            <div class="section-header">
                <span class="section-label">Navegue</span>
                <h2 class="section-title-large">Categorias</h2>
            </div>
            <div class="flex flex-wrap justify-center gap-6">
                <?php foreach ($banners_categorias as $banner): ?>
                    <a href="<?= htmlspecialchars($banner['link']) ?>" class="flex flex-col items-center gap-3 group">
                        <div
                            class="w-24 h-24 rounded-full overflow-hidden border-2 border-white/10 group-hover:border-[#ff0000] transition-colors p-1">
                            <img src="<?= htmlspecialchars($banner['imagem']) ?>"
                                class="w-full h-full object-cover rounded-full" alt="Categoria">
                        </div>
                        <span
                            class="font-bold text-sm tracking-wide uppercase group-hover:text-[#ff0000] transition-colors"><?= htmlspecialchars($banner['titulo']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Produtos -->
<?php if (!empty($produtos_por_categoria)): ?>
    <section class="py-20" id="produtos">
        <div class="container mx-auto px-4">
            <?php foreach ($produtos_por_categoria as $categoria_id => $produtos): ?>
                <?php
                $cat_name = "Categoria";
                foreach ($categorias as $c) {
                    if ($c['id'] == $categoria_id)
                        $cat_name = $c['nome'];
                }
                ?>
                <div class="mb-20">
                    <div class="flex items-center justify-between mb-8 border-b border-white/10 pb-4">
                        <h3 class="text-3xl font-bold text-white uppercase"><?= htmlspecialchars($cat_name) ?></h3>
                        <a href="categoria.php?id=<?= $categoria_id ?>"
                            class="text-[#ff0000] font-bold text-sm tracking-widest hover:text-white transition-colors">VER
                            TODOS</a>
                    </div>

                    <div class="swiper produtos-swiper produtos-swiper-<?= $categoria_id ?> overflow-visible">
                        <div class="swiper-wrapper">
                            <?php foreach ($produtos as $produto): ?>
                                <div class="swiper-slide h-auto">
                                    <div class="product-card-modern h-full">
                                        <div class="card-image-wrapper">
                                            <?php if (!empty($produto['imagem']) && file_exists($produto['imagem'])): ?>
                                                <img src="<?= htmlspecialchars($produto['imagem']) ?>"
                                                    alt="<?= htmlspecialchars($produto['nome']) ?>">
                                            <?php else: ?>
                                                <div class="w-full h-full bg-[#111] flex items-center justify-center text-white/20">
                                                    <i class="fas fa-image text-4xl"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-content">
                                            <h4 class="product-title leading-tight"><?= htmlspecialchars($produto['nome']) ?></h4>
                                            <p class="text-xs text-gray-400 mb-4 line-clamp-2">
                                                <?= htmlspecialchars($produto['descricao_curta']) ?></p>
                                            <div class="flex items-center justify-between mt-auto pt-4 border-t border-white/5">
                                                <span class="product-price"><?= formatarPreco($produto['preco']) ?></span>
                                                <a href="produto.php?id=<?= $produto['id'] ?>"
                                                    class="w-10 h-10 rounded-full bg-[#ff0000] flex items-center justify-center text-white hover:bg-white hover:text-black transition-colors">
                                                    <i class="fas fa-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- Features -->
<section class="py-20 bg-[#080808]">
    <div class="container mx-auto px-4">
        <div class="features-grid">
            <div class="glass-panel text-center">
                <i class="fas fa-bolt text-4xl text-[#ff0000] mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Entrega Rápida</h3>
                <p class="text-gray-400 text-sm">Receba seu produto minutos após a confirmação do pagamento.</p>
            </div>
            <div class="glass-panel text-center">
                <i class="fas fa-shield-alt text-4xl text-[#ff0000] mb-4"></i>
                <h3 class="text-xl font-bold mb-2">100% Seguro</h3>
                <p class="text-gray-400 text-sm">Transações criptografadas e garantia de funcionamento.</p>
            </div>
            <div class="glass-panel text-center">
                <i class="fas fa-headset text-4xl text-[#ff0000] mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Suporte 24/7</h3>
                <p class="text-gray-400 text-sm">Equipe pronta para ajudar você a qualquer momento.</p>
            </div>
        </div>
    </div>
</section>

<!-- Swiper Init -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php foreach ($produtos_por_categoria as $categoria_id => $produtos): ?>
            new Swiper('.produtos-swiper-<?= $categoria_id ?>', {
                slidesPerView: 1,
                spaceBetween: 24,
                breakpoints: {
                    640: { slidesPerView: 2 },
                    768: { slidesPerView: 3 },
                    1024: { slidesPerView: 4 }
                }
            });
        <?php endforeach; ?>
    });
</script>

<?php require_once 'templates/footer.php'; ?>