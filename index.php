<?php
// index.php - Página Principal no Estilo Adsly
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

// --- BUSCA DADOS OTIMIZADA ---
try {
    $banners_principais = $pdo->query("SELECT * FROM banners WHERE tipo = 'principal' AND ativo = 1 ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    $banners_categorias = $pdo->query("SELECT * FROM banners WHERE tipo = 'categoria' AND ativo = 1 ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    // Busca TODAS as categorias (sem limite)
    $categorias = $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Busca produtos em destaque (todos os produtos)
    $produtos_destaque = $pdo->query("SELECT id, nome, preco, imagem, descricao_curta FROM produtos ORDER BY id DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);

    // Busca produtos por categoria (todos os produtos de cada categoria)
    $produtos_por_categoria = [];
    foreach ($categorias as $categoria) {
        // Usa prepared statement para segurança e busca TODOS os produtos da categoria
        $stmt = $pdo->prepare("SELECT id, nome, preco, imagem, descricao_curta FROM produtos WHERE categoria_id = ? ORDER BY id DESC");
        $stmt->execute([$categoria['id']]);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($produtos)) {
            $produtos_por_categoria[$categoria['id']] = $produtos;
        }
    }
} catch (Exception $e) {
    $banners_principais = $banners_categorias = $categorias = $produtos_destaque = [];
    $produtos_por_categoria = [];
    error_log("Erro ao buscar produtos na página principal: " . $e->getMessage());
}

// Define meta tags
$page_title = 'Início';
$page_description = 'Descubra produtos incríveis na nossa loja online. Qualidade, preços competitivos e entrega rápida.';
$page_keywords = 'loja online, produtos, compras, e-commerce, qualidade, preços baixos';

require_once 'templates/header.php';
?>

<!-- CSS Específico da Homepage no Estilo Adsly -->
<!-- Thunder Store CSS -->
<style>
    /* Thunder Store Custom Styles */
    html {
        scroll-behavior: smooth;
    }

    html,
    body {
        touch-action: pan-x pan-y;
    }

    /* body background handled in header.php or main tag */

    .reveal {
        opacity: 0;
        transform: translateY(18px);
        filter: blur(6px);
        transition: opacity 700ms cubic-bezier(.2, .8, .2, 1), transform 700ms cubic-bezier(.2, .8, .2, 1), filter 700ms cubic-bezier(.2, .8, .2, 1);
    }

    .reveal.is-in {
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
    }

    .shine {
        background: radial-gradient(900px 280px at var(--mx, 50%) var(--my, 50%), rgba(220, 38, 38, 0.18), transparent 55%),
            radial-gradient(900px 280px at calc(var(--mx, 50%) + 120px) calc(var(--my, 50%) + 80px), rgba(255, 255, 255, 0.08), transparent 60%);
    }

    @media (prefers-reduced-motion: reduce) {
        .reveal {
            opacity: 1;
            transform: none;
            filter: none;
            transition: none;
        }
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    .animate-float-delayed {
        animation: float 6s ease-in-out 3s infinite;
    }

    @keyframes shine-sweep {
        0% {
            transform: translateX(-100%) skewX(-15deg);
        }

        100% {
            transform: translateX(200%) skewX(-15deg);
        }
    }

    .btn-shine {
        position: relative;
        overflow: hidden;
        background: linear-gradient(45deg, #FF9900, #ff5e00);
    }

    .btn-shine::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        height: 100%;
        background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.4), transparent);
        transform: translateX(-100%) skewX(-15deg);
    }

    .btn-shine:hover::after {
        animation: shine-sweep 0.75s;
    }
</style>

<!-- Hero Section -->
<div class="relative min-h-[90vh] bg-black flex items-center overflow-hidden">
    <!-- Background Effect -->
    <div class="absolute inset-0 z-0">
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-gray-900/40 via-black to-black opacity-80">
        </div>
        <!-- Cross Pattern Background -->
        <div class="absolute top-0 left-0 w-full h-full opacity-20"
            style="background-image: url('data:image/svg+xml,%3Csvg width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M11 11H9v2h2v2h2v-2h2v-2h-2V9h-2v2z\' fill=\'%23ffffff\' fill-rule=\'evenodd\'/%3E%3C/svg%3E'); background-size: 40px 40px;">
        </div>
        <!-- White/Gray glow spots for Thunder theme -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-white/5 rounded-full blur-[128px] animate-float"></div>
        <div
            class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-red-600/5 rounded-full blur-[128px] animate-float-delayed">
        </div>
    </div>

    <div
        class="relative z-10 max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 w-full flex flex-col items-center text-center justify-center pt-20 pb-10">
        <!-- Content -->
        <div class="space-y-8 flex flex-col items-center max-w-4xl mx-auto">
            <!-- Image as Title -->
            <div class="relative flex justify-center w-full reveal is-in">
                <img src="assets/img/logo-thunder.png" alt="THUNDER STORE"
                    class="w-full max-w-3xl drop-shadow-[0_0_30px_rgba(255,165,0,0.2)] transform hover:scale-105 transition-transform duration-500"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                <!-- Fallback Text if image not found -->
                <h1 class="text-6xl md:text-8xl font-black tracking-tighter leading-none hidden">
                    <span class="block text-white">THUNDER</span>
                    <span class="block text-gray-400 drop-shadow-[0_0_15px_rgba(255,165,0,0.5)]">STORE</span>
                </h1>
            </div>

            <p class="text-gray-400 text-lg md:text-2xl max-w-2xl font-medium leading-relaxed reveal is-in"
                style="transition-delay: 100ms;">
                A melhor loja para seus produtos digitais.
                <br />
                Qualidade premium, entrega rápida e segurança total via PIX.
            </p>

            <div class="flex flex-col sm:flex-row gap-6 justify-center w-full reveal is-in"
                style="transition-delay: 200ms;">
                <a href="#produtos"
                    class="btn-shine bg-white text-black hover:bg-gray-200 font-bold py-4 px-10 rounded-full flex items-center justify-center gap-2 transition-all uppercase tracking-wide text-sm sm:text-base hover:-translate-y-1 shadow-lg group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:scale-110 transition-transform"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    Ver Produtos
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/painel"
                        class="btn-shine bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-10 rounded-full flex items-center justify-center gap-2 shadow-[0_0_20px_rgba(255,0,51,0.4)] hover:shadow-[0_0_30px_rgba(255,0,51,0.6)] transition-all uppercase tracking-wide text-sm sm:text-base hover:-translate-y-1">
                        Acessar Painel
                    </a>
                <?php else: ?>
                    <a href="/login.php"
                        class="btn-shine bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-10 rounded-full flex items-center justify-center gap-2 shadow-[0_0_20px_rgba(255,0,51,0.4)] hover:shadow-[0_0_30px_rgba(255,0,51,0.6)] transition-all uppercase tracking-wide text-sm sm:text-base hover:-translate-y-1">
                        Acessar Painel
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Produtos por Categoria no Estilo Adsly -->
<?php if (!empty($produtos_por_categoria)): ?>
    <section class="adsly-products" id="produtos">
        <div class="container">
            <h2>Nossos Produtos</h2>
            <p class="subtitle">Organizados por categoria para facilitar sua busca</p>

            <?php foreach ($produtos_por_categoria as $categoria_id => $produtos): ?>
                <?php
                // Busca informações da categoria
                $categoria_info = null;
                foreach ($categorias as $cat) {
                    if ($cat['id'] == $categoria_id) {
                        $categoria_info = $cat;
                        break;
                    }
                }
                ?>

                <?php if ($categoria_info): ?>
                    <div class="categoria-section" style="margin-bottom: 4rem;">
                        <h3 class="categoria-titulo"
                            style="font-size: 2rem; font-weight: 700; margin-bottom: 2rem; color: white; text-align: center; text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);">
                            <?= htmlspecialchars($categoria_info['nome']) ?>
                        </h3>

                        <!-- Carrossel Swiper para Produtos -->
                        <div class="swiper produtos-swiper produtos-swiper-<?= $categoria_id ?>" style="position: relative;">
                            <div class="swiper-wrapper">
                                <?php foreach ($produtos as $produto): ?>
                                    <div class="swiper-slide">
                                        <div class="product-card reveal group rounded-2xl overflow-hidden border border-white/10 bg-white/5 backdrop-blur-md transition-all duration-300 hover:-translate-y-2 hover:border-red-600/40 hover:shadow-[0_0_45px_rgba(220,38,38,0.18)] h-full flex flex-col"
                                            data-reveal>
                                            <div class="relative h-52 overflow-hidden shine flex-shrink-0">
                                                <?php
                                                $imagem_produto = $produto['imagem'];
                                                $imagem_existe = !empty($imagem_produto) && file_exists(__DIR__ . '/' . $imagem_produto);
                                                ?>

                                                <?php if ($imagem_existe): ?>
                                                    <img src="<?= htmlspecialchars($imagem_produto) ?>"
                                                        alt="<?= htmlspecialchars($produto['nome']) ?>"
                                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                                        loading="lazy">
                                                <?php else: ?>
                                                    <!-- Fallback image/placeholder if needed, keeping style -->
                                                    <div class="w-full h-full bg-gray-900 flex items-center justify-center text-gray-500">
                                                        <i class="fas fa-image text-4xl"></i>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent">
                                                </div>

                                                <div class="absolute top-4 left-4 flex items-center gap-2">
                                                    <span
                                                        class="px-3 py-1 rounded-full text-xs font-black tracking-wide bg-green-500/10 border border-green-500/20 text-green-300">
                                                        ATIVO
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="p-6 flex flex-col flex-grow">
                                                <div class="mb-4 flex-grow">
                                                    <h3 class="text-2xl font-black text-white tracking-tight leading-tight mb-2">
                                                        <?= htmlspecialchars($produto['nome']) ?>
                                                    </h3>
                                                    <p class="text-gray-300/80 text-sm leading-relaxed line-clamp-3">
                                                        <?= htmlspecialchars($produto['descricao_curta']) ?>
                                                    </p>
                                                </div>

                                                <div class="mt-auto">
                                                    <div class="flex items-end justify-between gap-4 mb-4">
                                                        <div class="text-xs uppercase tracking-widest text-white/50 font-bold">A partir
                                                            de</div>
                                                        <div class="text-lg font-black text-white">
                                                            <?= formatarPreco($produto['preco']) ?></div>
                                                    </div>

                                                    <a href="produto.php?id=<?= $produto['id'] ?>"
                                                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black py-3 transition-all shadow-[0_10px_25px_rgba(220,38,38,0.22)] hover:shadow-[0_14px_34px_rgba(220,38,38,0.30)]">
                                                        Ver planos
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path d="M5 12h14"></path>
                                                            <path d="m13 5 7 7-7 7"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Setas de Navegação (Desktop) -->
                            <div class="swiper-button-next produtos-next-<?= $categoria_id ?>" style="color: #ff0000; right: 0;">
                            </div>
                            <div class="swiper-button-prev produtos-prev-<?= $categoria_id ?>" style="color: #ff0000; left: 0;">
                            </div>

                            <!-- Paginação (Opcional) -->
                            <div class="swiper-pagination produtos-pagination-<?= $categoria_id ?>"
                                style="position: relative; margin-top: 2rem;"></div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div style="text-align: center; margin-top: 3rem;">
                <a href="busca.php?todos=1" class="btn-large"
                    style="background: linear-gradient(45deg, #ff0000, #ff3333); color: white; padding: 15px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);">
                    <i class="fas fa-arrow-right"></i>
                    Ver Todos os Produtos
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>


<!-- CTA Final no Estilo Adsly -->
<section class="adsly-cta">
    <div class="container">
        <h2>Pronto Para Dominar o Jogo?</h2>
        <p>Junte-se à elite do mercado digital. Tenha acesso às estratégias e ferramentas que realmente trazem
            resultado.</p>
        <a href="busca.php" class="btn-large">
            <i class="fas fa-rocket"></i>
            Começar Agora
        </a>
    </div>
</section>

<!-- CSS para Carrossel Swiper de Produtos -->
<style>
    /* Setas de navegação - Desktop */
    @media (min-width: 768px) {

        .swiper-button-next,
        .swiper-button-prev {
            display: flex !important;
            width: 50px !important;
            height: 50px !important;
            background: rgba(255, 0, 0, 0.1) !important;
            border-radius: 50% !important;
            border: 2px solid rgba(255, 0, 0, 0.3) !important;
            transition: all 0.3s ease !important;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: rgba(255, 0, 0, 0.2) !important;
            border-color: rgba(255, 0, 0, 0.5) !important;
            transform: scale(1.1) !important;
        }

        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 20px !important;
            font-weight: bold !important;
        }
    }

    /* Esconde setas no mobile */
    @media (max-width: 767px) {

        .swiper-button-next,
        .swiper-button-prev {
            display: none !important;
        }

        .produtos-swiper {
            padding: 0 20px 50px !important;
        }
    }

    /* Paginação customizada */
    .swiper-pagination-bullet {
        background: rgba(255, 0, 0, 0.5) !important;
        opacity: 1 !important;
        width: 12px !important;
        height: 12px !important;
    }

    .swiper-pagination-bullet-active {
        background: #ff0000 !important;
        width: 30px !important;
        border-radius: 6px !important;
    }

    /* Ajustes nos cards dentro do Swiper */
    .swiper-slide .product-card-adsly {
        height: 100%;
        margin: 0;
    }

    /* Padding do carrossel */
    .produtos-swiper {
        padding: 0 60px 50px !important;
    }

    @media (max-width: 767px) {
        .produtos-swiper {
            padding: 0 20px 50px !important;
        }
    }
</style>

<!-- JavaScript Específico da Homepage -->


<!-- Swiper JS para Carrossel de Produtos -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializa carrosséis de produtos por categoria
        <?php foreach ($produtos_por_categoria as $categoria_id => $produtos): ?>
            const swiper<?= $categoria_id ?> = new Swiper('.produtos-swiper-<?= $categoria_id ?>', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: <?= count($produtos) > 4 ? 'true' : 'false' ?>,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                // Deslize para mobile/Android
                touchEventsTarget: 'container',
                allowTouchMove: true,
                grabCursor: true,
                touchRatio: 1,
                touchAngle: 45,

                // Setas de navegação (visíveis no desktop)
                navigation: {
                    nextEl: '.produtos-next-<?= $categoria_id ?>',
                    prevEl: '.produtos-prev-<?= $categoria_id ?>',
                },

                // Paginação
                pagination: {
                    el: '.produtos-pagination-<?= $categoria_id ?>',
                    clickable: true,
                    dynamicBullets: true,
                },

                // Responsividade
                breakpoints: {
                    480: {
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 24,
                    },
                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 30,
                    }
                }
            });
        <?php endforeach; ?>
    });
</script>

<!-- Alta Performance Section -->
<section class="relative py-20">
    <!-- Background Mesh Effect -->
    <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_center,_rgba(255,0,0,0.05)_0%,_transparent_70%)] pointer-events-none">
    </div>

    <div class="container relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-black uppercase tracking-wider text-white">
                ALTA <span
                    class="relative inline-block text-red-600 after:content-[''] after:absolute after:-bottom-2 after:left-0 after:w-full after:h-1 after:bg-red-600">PERFORMANCE</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto">
            <!-- Velocidade Card -->
            <div
                class="bg-[#0a0a0a] border border-white/5 p-10 rounded-xl flex flex-col items-center text-center hover:bg-[#111] transition-all duration-300 group hover:-translate-y-1">
                <div class="mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-red-600" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m12 14 4-4" />
                        <path d="M3.34 19a10 10 0 1 1 17.32 0" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white uppercase tracking-wide mb-3">VELOCIDADE</h3>
                <p class="text-gray-400 font-medium">Entrega instantânea via PIX.</p>
            </div>

            <!-- Segurança Card -->
            <div
                class="bg-[#0a0a0a] border border-white/5 p-10 rounded-xl flex flex-col items-center text-center hover:bg-[#111] transition-all duration-300 group hover:-translate-y-1">
                <div class="mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-red-600" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 11c0 3-1.5 5.5-3.5 7.5S14 21 14 21s1.5-4.5 3.5-6.5S22 8 22 11Z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white uppercase tracking-wide mb-3">SEGURANÇA</h3>
                <p class="text-gray-400 font-medium">Tecnologia de proteção Triple-Layer.</p>
            </div>

            <!-- Suporte Card -->
            <a href="https://discord.gg/hpjCtT7CU7" target="_blank" rel="noopener"
                class="bg-[#0a0a0a] border border-white/5 p-10 rounded-xl flex flex-col items-center text-center hover:bg-[#111] transition-all duration-300 group hover:-translate-y-1">
                <div class="mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-red-600" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white uppercase tracking-wide mb-3">SUPORTE</h3>
                <p class="text-gray-400 font-medium">Nossa equipe entra via AnyDesk.</p>
            </a>

            <!-- Comunidade VIP Card -->
            <a href="https://discord.gg/hpjCtT7CU7" target="_blank" rel="noopener"
                class="bg-[#0a0a0a] border border-white/5 p-10 rounded-xl flex flex-col items-center text-center hover:bg-[#111] transition-all duration-300 group hover:-translate-y-1">
                <div class="mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-red-600" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white uppercase tracking-wide mb-3">COMUNIDADE VIP</h3>
                <p class="text-gray-400 font-medium">Acesso exclusivo ao Discord.</p>
            </a>
        </div>
    </div>
</section>

<!-- Scripts for Animation -->
<script>
    (function () {
        const items = document.querySelectorAll('[data-reveal]');
        const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!items.length) return;

        if (prefersReduced || !('IntersectionObserver' in window)) {
            items.forEach(el => el.classList.add('is-in'));
            return;
        }
        const io = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add('is-in');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.18 });
        items.forEach(el => io.observe(el));
    })();

    (function () {
        const cards = Array.from(document.querySelectorAll('.product-card'));
        cards.forEach((card) => {
            const onMove = (e) => {
                const r = card.getBoundingClientRect();
                const x = ((e.clientX - r.left) / r.width) * 100;
                const y = ((e.clientY - r.top) / r.height) * 100;
                card.style.setProperty('--mx', x + '%');
                card.style.setProperty('--my', y + '%');
            };
            card.addEventListener('mousemove', onMove);
            card.addEventListener('mouseleave', () => {
                card.style.removeProperty('--mx');
                card.style.removeProperty('--my');
            });
        });
    })();
</script>

<?php
require_once 'templates/footer.php';
?>