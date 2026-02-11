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

<style>
    /* ── Tailwind Overrides & Custom Animations ── */
    .product-card-new {
        background: #111;
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .product-card-new:hover {
        transform: translateY(-6px);
        border-color: rgba(255, 255, 255, 0.15);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), 0 0 30px rgba(255, 255, 255, 0.03);
    }

    .product-card-new .card-img {
        position: relative;
        aspect-ratio: 1/1;
        overflow: hidden;
        background: #0a0a0a;
    }

    .product-card-new .card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .product-card-new:hover .card-img img {
        transform: scale(1.08);
    }

    .product-card-new .card-body {
        padding: 14px 16px 18px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .product-card-new .card-tag {
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.4);
        font-weight: 600;
        margin-bottom: 6px;
    }

    .product-card-new .card-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 0.85rem;
        font-weight: 600;
        color: #fff;
        line-height: 1.3;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-card-new .card-price {
        font-family: 'Syne', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        margin-top: auto;
    }

    .product-card-new .card-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #fff;
        color: #000;
        font-size: 0.55rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 4px 10px;
        border-radius: 20px;
        z-index: 2;
    }

    .product-card-new .card-action {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.75rem;
        opacity: 0;
        transform: translateY(8px);
        transition: all 0.3s ease;
        z-index: 2;
    }

    .product-card-new:hover .card-action {
        opacity: 1;
        transform: translateY(0);
    }

    .hero-gradient {
        background: linear-gradient(135deg, #000 0%, #111 40%, #0a0a0a 100%);
    }

    .section-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08), transparent);
    }

    .glow-text {
        text-shadow: 0 0 40px rgba(255, 255, 255, 0.1);
    }

    .swiper-slide {
        height: auto !important;
    }

    /* Category pills */
    .cat-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .cat-pill:hover .cat-icon-circle {
        background: #fff;
        color: #000;
        transform: translateY(-2px);
    }

    .cat-icon-circle {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .cat-pill-name {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.6);
        font-weight: 500;
        text-align: center;
        letter-spacing: 0.5px;
    }

    /* Trust bar */
    .trust-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 14px;
        padding: 24px 20px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .trust-card:hover {
        border-color: rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.05);
    }

    /* Featured Banner */
    .cta-banner {
        background: linear-gradient(135deg, #111 0%, #1a1a1a 50%, #111 100%);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 20px;
        overflow: hidden;
        position: relative;
    }

    .cta-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.03) 0%, transparent 70%);
        pointer-events: none;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .float-anim {
        animation: float 6s ease-in-out infinite;
    }

    /* Placeholder for no image */
    .img-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0a0a0a, #151515);
        color: rgba(255, 255, 255, 0.15);
        font-size: 2rem;
    }
</style>

<!-- ══════════════════════════════════════════
     HERO SECTION
     ══════════════════════════════════════════ -->
<section class="hero-gradient relative overflow-hidden" style="min-height: 85vh; display:flex; align-items:center;">
    <div class="absolute inset-0 opacity-10"
        style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.05) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(255,255,255,0.03) 0%, transparent 50%);">
    </div>

    <div class="container mx-auto px-6 relative z-10" data-aos="fade-up" data-aos-duration="1000">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 bg-white/5 backdrop-blur-sm mb-8"
                data-aos="fade-up" data-aos-delay="200">
                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                <span class="text-white/70 text-xs font-medium tracking-wider uppercase font-body">Nova Coleção
                    Disponível</span>
            </div>

            <h1 class="font-display text-5xl md:text-7xl font-extrabold text-white leading-tight mb-6 glow-text"
                data-aos="fade-up" data-aos-delay="300">
                Estilo &<br>
                <span class="text-white/80">Cultura</span>
            </h1>

            <p class="text-white/50 text-base md:text-lg font-body max-w-md mb-10 leading-relaxed" data-aos="fade-up"
                data-aos-delay="400">
                Roupas, tênis, eletrônicos e produtos digitais com qualidade premium. Levando o melhor até a sua casa.
            </p>

            <div class="flex flex-wrap gap-4" data-aos="fade-up" data-aos-delay="500">
                <a href="busca.php?todos=1"
                    class="inline-flex items-center gap-3 bg-white text-black px-7 py-3.5 rounded-full font-semibold text-sm tracking-wide hover:bg-white/90 transition-all duration-300 hover:scale-105 font-body">
                    <i class="fas fa-shopping-bag text-xs"></i>
                    Ver Catálogo
                </a>
                <a href="#produtos"
                    class="inline-flex items-center gap-3 border border-white/20 text-white px-7 py-3.5 rounded-full font-medium text-sm tracking-wide hover:bg-white/5 transition-all duration-300 font-body">
                    Explorar
                    <i class="fas fa-arrow-down text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-40">
        <span class="text-white text-[10px] uppercase tracking-[3px] font-body">Scroll</span>
        <div class="w-px h-8 bg-gradient-to-b from-white/50 to-transparent"></div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     CATEGORY BAR
     ══════════════════════════════════════════ -->
<?php if (!empty($categorias)): ?>
<section class="py-8 border-b border-white/5" data-aos="fade-up" data-aos-duration="800">
    <div class="container mx-auto px-6">
        <div class="flex gap-6 overflow-x-auto pb-2" style="scrollbar-width: none; -ms-overflow-style: none;">
            <a href="busca.php?todos=1" class="cat-pill">
                <div class="cat-icon-circle">
                    <i class="fas fa-fire"></i>
                </div>
                <span class="cat-pill-name">Em Alta</span>
            </a>
            <?php
    $cat_icons = [
        'roupas' => 'fa-tshirt', 'roupa' => 'fa-tshirt', 'camiseta' => 'fa-tshirt', 'camisa' => 'fa-tshirt',
        'tenis' => 'fa-shoe-prints', 'tênis' => 'fa-shoe-prints', 'sneakers' => 'fa-shoe-prints', 'calçado' => 'fa-shoe-prints',
        'eletronico' => 'fa-laptop', 'eletrônico' => 'fa-laptop', 'digital' => 'fa-code',
        'acessorio' => 'fa-gem', 'acessório' => 'fa-gem', 'bone' => 'fa-hat-cowboy', 'boné' => 'fa-hat-cowboy',
        'conjunto' => 'fa-layer-group', 'relogio' => 'fa-clock', 'relógio' => 'fa-clock',
        'bolsa' => 'fa-shopping-bag', 'joia' => 'fa-ring', 'fone' => 'fa-headphones',
        'celular' => 'fa-mobile-alt', 'game' => 'fa-gamepad', 'jogo' => 'fa-gamepad',
        'streaming' => 'fa-play', 'marketing' => 'fa-bullhorn', 'air' => 'fa-shoe-prints',
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
            <a href="categoria.php?id=<?= $cat['id']?>" class="cat-pill">
                <div class="cat-icon-circle">
                    <i class="fas <?= $icon?>"></i>
                </div>
                <span class="cat-pill-name">
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
<section class="py-16" id="produtos">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between mb-10" data-aos="fade-up">
            <div>
                <span class="text-[10px] uppercase tracking-[3px] text-white/40 font-body block mb-2">Novidades</span>
                <h2 class="font-display text-2xl md:text-3xl font-bold text-white">Destaques</h2>
            </div>
            <a href="busca.php?todos=1"
                class="text-white/50 hover:text-white text-xs font-medium tracking-wider uppercase transition-colors duration-300 font-body flex items-center gap-2">
                Ver tudo <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="swiper destaques-swiper" data-aos="fade-up" data-aos-delay="200">
            <div class="swiper-wrapper">
                <?php foreach ($destaques as $idx => $produto): ?>
                <div class="swiper-slide">
                    <a href="produto.php?id=<?= $produto['id']?>" class="product-card-new block">
                        <div class="card-img">
                            <?php if (!empty($produto['imagem']) && file_exists($produto['imagem'])): ?>
                            <img src="<?= htmlspecialchars($produto['imagem'])?>"
                                alt="<?= htmlspecialchars($produto['nome'])?>" loading="lazy" />
                            <?php
        else: ?>
                            <div class="img-placeholder"><i class="fas fa-image"></i></div>
                            <?php
        endif; ?>
                            <?php if ($idx < 3): ?>
                            <div class="card-badge">Novo</div>
                            <?php
        endif; ?>
                            <div class="card-action"><i class="fas fa-arrow-right"></i></div>
                        </div>
                        <div class="card-body">
                            <span class="card-tag">MACARIO BRAZIL</span>
                            <h3 class="card-title">
                                <?= htmlspecialchars($produto['nome'])?>
                            </h3>
                            <span class="card-price">
                                <?= formatarPreco($produto['preco'])?>
                            </span>
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
endif; ?>

<!-- ══════════════════════════════════════════
     DIVIDER
     ══════════════════════════════════════════ -->
<div class="section-divider mx-auto" style="max-width: 80%;"></div>

<!-- ══════════════════════════════════════════
     FEATURED BANNER
     ══════════════════════════════════════════ -->
<section class="py-16">
    <div class="container mx-auto px-6">
        <div class="cta-banner p-10 md:p-16" data-aos="fade-up" data-aos-duration="1000">
            <div class="relative z-10 max-w-lg">
                <span class="text-[10px] uppercase tracking-[3px] text-white/40 font-body block mb-4">Exclusivo</span>
                <h2 class="font-display text-3xl md:text-4xl font-bold text-white mb-4">Coleção Premium</h2>
                <p class="text-white/45 font-body text-sm leading-relaxed mb-8">
                    Descubra nossos produtos selecionados com qualidade e estilo incomparáveis. Entrega rápida para todo
                    o Brasil.
                </p>
                <a href="busca.php?todos=1"
                    class="inline-flex items-center gap-3 bg-white text-black px-7 py-3 rounded-full font-semibold text-sm tracking-wide hover:bg-white/90 transition-all duration-300 hover:scale-105 font-body">
                    Comprar Agora <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     DIVIDER
     ══════════════════════════════════════════ -->
<div class="section-divider mx-auto" style="max-width: 80%;"></div>

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
<section class="py-16">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between mb-10" data-aos="fade-up">
            <div>
                <span class="text-[10px] uppercase tracking-[3px] text-white/40 font-body block mb-2">Coleção</span>
                <h2 class="font-display text-2xl md:text-3xl font-bold text-white">
                    <?= htmlspecialchars($cat_name)?>
                </h2>
            </div>
            <a href="categoria.php?id=<?= $categoria_id?>"
                class="text-white/50 hover:text-white text-xs font-medium tracking-wider uppercase transition-colors duration-300 font-body flex items-center gap-2">
                Ver tudo <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="swiper produtos-swiper" data-aos="fade-up" data-aos-delay="100">
            <div class="swiper-wrapper">
                <?php foreach ($produtos as $produto): ?>
                <div class="swiper-slide">
                    <a href="produto.php?id=<?= $produto['id']?>" class="product-card-new block">
                        <div class="card-img">
                            <?php if (!empty($produto['imagem']) && file_exists($produto['imagem'])): ?>
                            <img src="<?= htmlspecialchars($produto['imagem'])?>"
                                alt="<?= htmlspecialchars($produto['nome'])?>" loading="lazy" />
                            <?php
            else: ?>
                            <div class="img-placeholder"><i class="fas fa-image"></i></div>
                            <?php
            endif; ?>
                            <div class="card-action"><i class="fas fa-arrow-right"></i></div>
                        </div>
                        <div class="card-body">
                            <span class="card-tag">
                                <?= htmlspecialchars($cat_name)?>
                            </span>
                            <h3 class="card-title">
                                <?= htmlspecialchars($produto['nome'])?>
                            </h3>
                            <span class="card-price">
                                <?= formatarPreco($produto['preco'])?>
                            </span>
                        </div>
                    </a>
                </div>
                <?php
        endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Divider between categories -->
    <div class="section-divider mx-auto mt-16" style="max-width: 60%;"></div>
</section>
<?php
    endforeach; ?>
<?php
endif; ?>

<!-- ══════════════════════════════════════════
     TRUST BAR
     ══════════════════════════════════════════ -->
<section class="py-16">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" data-aos="fade-up" data-aos-duration="800">
            <div class="trust-card">
                <div class="text-white/80 text-xl mb-3"><i class="fas fa-truck"></i></div>
                <div class="text-white font-semibold text-sm font-body mb-1">Frete Grátis</div>
                <div class="text-white/35 text-xs font-body leading-relaxed">Entrega grátis para todo o Brasil.</div>
            </div>
            <div class="trust-card">
                <div class="text-white/80 text-xl mb-3"><i class="fas fa-shield-alt"></i></div>
                <div class="text-white font-semibold text-sm font-body mb-1">Compra Segura</div>
                <div class="text-white/35 text-xs font-body leading-relaxed">Dados protegidos com criptografia.</div>
            </div>
            <div class="trust-card">
                <div class="text-white/80 text-xl mb-3"><i class="fas fa-sync-alt"></i></div>
                <div class="text-white font-semibold text-sm font-body mb-1">Trocas Fáceis</div>
                <div class="text-white/35 text-xs font-body leading-relaxed">Primeira troca grátis em 30 dias.</div>
            </div>
            <div class="trust-card">
                <div class="text-white/80 text-xl mb-3"><i class="fas fa-headset"></i></div>
                <div class="text-white font-semibold text-sm font-body mb-1">Suporte 24/7</div>
                <div class="text-white/35 text-xs font-body leading-relaxed">Atendimento via WhatsApp e chat.</div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     SWIPER + AOS INIT
     ══════════════════════════════════════════ -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Init AOS
        AOS.init({
            once: true,
            duration: 700,
            easing: 'ease-out-cubic',
            offset: 50
        });

        // Init Swipers
        if (typeof Swiper !== 'undefined') {
            const swiperConfig = {
                slidesPerView: 1.4,
                spaceBetween: 14,
                grabCursor: true,
                breakpoints: {
                    480: { slidesPerView: 2.3, spaceBetween: 14 },
                    768: { slidesPerView: 3.2, spaceBetween: 18 },
                    1024: { slidesPerView: 4.2, spaceBetween: 20 },
                    1280: { slidesPerView: 5, spaceBetween: 22 }
                }
            };

            // Destaques
            if (document.querySelector('.destaques-swiper')) {
                new Swiper('.destaques-swiper', swiperConfig);
            }

            // Categories
            document.querySelectorAll('.produtos-swiper').forEach(function (el) {
                new Swiper(el, swiperConfig);
            });
        }
    });
</script>

<?php require_once 'templates/footer.php'; ?>