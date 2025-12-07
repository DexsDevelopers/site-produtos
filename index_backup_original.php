<?php
// index.php (VERSÃO DEFINITIVA "GLOW UP")
session_start();
require_once 'config.php';

// --- RASTREAMENTO AUTOMÁTICO DE AFILIAÇÃO ---
if (isset($_GET['ref'])) {
    require_once 'includes/affiliate_system.php';
    $affiliateSystem = new AffiliateSystem($pdo);
    
    $affiliate_code = $_GET['ref'];
    $product_id = null; // Página inicial
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    $result = $affiliateSystem->registerClick($affiliate_code, $product_id, $ip_address);
    
    if ($result['success']) {
        $_SESSION['affiliate_tracking'] = [
            'affiliate_code' => $affiliate_code,
            'click_id' => $result['click_id'],
            'timestamp' => time()
        ];
    }
    
    // Remover parâmetro ref da URL
    $params = $_GET;
    unset($params['ref']);
    $redirect_url = 'index.php';
    if (!empty($params)) {
        $redirect_url .= '?' . http_build_query($params);
    }
    header('Location: ' . $redirect_url);
    exit();
}

require_once 'templates/header.php';

// --- BUSCA DADOS OTIMIZADA PARA DESKTOP E MOBILE ---
$banners_principais = $pdo->query("SELECT * FROM banners WHERE tipo = 'principal' AND ativo = 1 ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$banners_categorias = $pdo->query("SELECT * FROM banners WHERE tipo = 'categoria' AND ativo = 1 ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);

// Busca produtos por categoria de forma otimizada
$produtos_por_categoria = [];
foreach ($categorias as $categoria) {
    $produtos = $pdo->query("SELECT id, nome, preco, imagem, checkout_link, descricao_curta FROM produtos WHERE categoria_id = {$categoria['id']} ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($produtos)) {
        $produtos_por_categoria[$categoria['id']] = $produtos;
    }
}
?>

<style>
    /* FUNDO ANIMADO E CURSOR DE LUZ */
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -2;
        background: linear-gradient(300deg, #1a0000, #00081a, #1a001a, #000000);
        background-size: 400% 400%;
        animation: gradient-animation 25s ease infinite;
    }

    @keyframes gradient-animation {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    #cursor-glow {
        position: fixed;
        left: 0;
        top: 0;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(229, 62, 62, 0.15) 0%, rgba(229, 62, 62, 0) 60%);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
        z-index: -1;
        transition: opacity 0.3s;
    }
    
    /* TÍTULOS COM GRADIENTE */
    .gradient-title {
        background: linear-gradient(90deg, #ffffff, #a0aec0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
    }

    /* CARD DE DEPOIMENTO COM EFEITO DE VIDRO */
    .testimonial-card {
        background: rgba(26, 32, 44, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* GRID DE PRODUTOS SIMPLES */
    
    /* Cards de Produtos - Layout Estático */
    .glass-card {
        transition: none;
    }
    
    /* Swiper apenas para banner principal */
    .swiper-button-next,
    .swiper-button-prev {
        color: #e53e3e;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        margin-top: -20px;
    }
    
    .swiper-button-next:after,
    .swiper-button-prev:after {
        font-size: 16px;
    }
    
    .swiper-pagination-bullet {
        background: #e53e3e;
        opacity: 0.3;
    }
    
    .swiper-pagination-bullet-active {
        opacity: 1;
    }

    /* Responsividade do Grid */
    @media (max-width: 640px) {
        .grid {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
    }

    @media (min-width: 641px) and (max-width: 1024px) {
        .grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1025px) and (max-width: 1280px) {
        .grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (min-width: 1281px) {
        .grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    
    /* Banner Grande - Responsivo */
    .main-banner-carousel {
        max-width: 100%;
    }
    
    @media (max-width: 768px) {
        .main-banner-carousel {
            max-width: 100%;
        }
    }
    
    @media (min-width: 1024px) {
        .main-banner-carousel {
            max-width: 896px; /* max-w-4xl */
        }
    }
</style>

<div id="cursor-glow"></div>

<!-- Hero Section Otimizado -->
<div class="relative w-full py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Título Principal -->
        <div class="text-center mb-12 pt-16 md:pt-8">
            <h1 class="text-4xl md:text-6xl font-black mb-4 gradient-title">
                O Mercado é dos
                <span class="block text-transparent bg-clip-text bg-gradient-to-r from-brand-red to-brand-pink">
                    Tubarões
                </span>
            </h1>
            <p class="text-lg md:text-xl text-brand-gray-300 mb-8 max-w-2xl mx-auto">
                Descubra produtos incríveis com qualidade premium e preços imbatíveis
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#produtos" class="btn-modern text-lg px-6 py-3 inline-flex items-center gap-2">
                    <i class="fas fa-shopping-bag"></i>
                    Explorar Produtos
                </a>
                <a href="busca.php" class="glass-card text-lg px-6 py-3 inline-flex items-center gap-2 hover:bg-brand-gray-700 transition-all duration-300">
                    <i class="fas fa-search"></i>
                    Buscar
                </a>
            </div>
        </div>
        
        <!-- Banner Principal - Formato Grande -->
        <?php if (!empty($banners_principais)): ?>
        <div class="swiper main-banner-carousel rounded-xl overflow-hidden shadow-xl max-w-4xl mx-auto" data-aos="fade-up">
            <div class="swiper-wrapper">
                <?php foreach ($banners_principais as $index => $banner): ?>
                <div class="swiper-slide relative aspect-video bg-cover bg-center group" style="background-image: url('<?= htmlspecialchars($banner['imagem']) ?>');">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6 text-white z-10">
                        <h3 class="text-2xl md:text-3xl font-bold mb-2 transform group-hover:translate-y-0 translate-y-2 transition-transform duration-300">
                            <?= htmlspecialchars($banner['titulo']) ?>
                        </h3>
                        <p class="text-base md:text-lg opacity-90 transform group-hover:translate-y-0 translate-y-2 transition-transform duration-300 delay-100">
                            Descubra mais sobre este produto
                        </p>
                    </div>
                    <?php if (!empty($banner['link'])): ?>
                        <a href="<?= htmlspecialchars($banner['link']) ?>" class="absolute inset-0" aria-label="<?= htmlspecialchars($banner['titulo']) ?>"></a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Seção de Categorias Otimizada -->
<section id="categorias" class="py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-8">
            <h2 class="text-3xl md:text-4xl font-black mb-4 gradient-title">
                Nossas Categorias
            </h2>
            <p class="text-lg text-brand-gray-300 max-w-2xl mx-auto">
                Explore nossa seleção de produtos
            </p>
        </div>
        
        <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide">
            <?php foreach ($banners_categorias as $index => $banner): ?>
            <a href="<?= htmlspecialchars($banner['link']) ?>" 
               class="flex-shrink-0 flex flex-col items-center gap-3 group min-w-[100px]">
                <div class="w-20 h-20 rounded-xl glass-card p-2 group-hover:scale-105 transition-transform duration-300">
                    <img src="<?= htmlspecialchars($banner['imagem']) ?>" 
                         alt="<?= htmlspecialchars($banner['titulo']) ?>" 
                         class="w-full h-full object-cover rounded-lg"
                         loading="lazy">
                </div>
                <span class="text-sm font-semibold text-white group-hover:text-brand-red transition-colors duration-300 text-center">
                    <?= htmlspecialchars($banner['titulo']) ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Seção de Produtos Otimizada -->
<section id="produtos" class="py-12">
    <div class="max-w-7xl mx-auto px-4 space-y-12">
        <?php foreach ($categorias as $categoria): ?>
            <?php if (!empty($produtos_por_categoria[$categoria['id']])): ?>
            <div>
                <div class="text-center mb-8">
                    <h2 class="text-3xl md:text-4xl font-black mb-4 gradient-title">
                        <?= htmlspecialchars($categoria['nome']) ?>
                    </h2>
                    <a href="categoria.php?id=<?= $categoria['id'] ?>" 
                       class="btn-modern inline-flex items-center gap-2">
                        Ver Todos
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach ($produtos_por_categoria[$categoria['id']] as $index => $produto): ?>
                    <div class="h-full">
                        <a href="produto.php?id=<?= $produto['id'] ?>" class="block group">
                            <div class="glass-card rounded-xl overflow-hidden">
                                <div class="aspect-square overflow-hidden">
                                    <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                                         alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                         class="w-full h-full object-cover"
                                         loading="lazy">
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-lg text-white mb-2">
                                        <?= htmlspecialchars($produto['nome']) ?>
                                    </h3>
                                    <p class="text-sm text-brand-gray-300 mb-3 line-clamp-2">
                                        <?= htmlspecialchars($produto['descricao_curta']) ?>
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xl font-bold text-white">
                                            <?= formatarPreco($produto['preco']) ?>
                                        </span>
                                        <div class="flex items-center text-yellow-400">
                                            <i class="fas fa-star text-xs"></i>
                                            <i class="fas fa-star text-xs"></i>
                                            <i class="fas fa-star text-xs"></i>
                                            <i class="fas fa-star text-xs"></i>
                                            <i class="fas fa-star text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<div class="w-full max-w-7xl mx-auto py-16 px-4">
    <div class="scroll-reveal-section">
        <div class="text-center mb-12">
            <h2 class="gradient-title text-3xl md:text-4xl font-black">O Que Dizem Sobre Nós</h2>
            <p class="text-brand-gray-text mt-2">Experiências compartilhadas por nossos clientes.</p>
        </div>
        <div class="swiper testimonial-carousel">
            <div class="swiper-wrapper pb-8">
                <?php $depoimentos = [
                    ["R", "Rafael", "Processo de compra muito simples e seguro, interface fácil de entender."],
                    ["G", "Gissele", "Suporte on 24h, e um diferencial incrível. Sempre me ajudam quando preciso."],
                    ["J", "Jonas", "A plataforma é sensacional e o suporte é realmente eficiente. Estou impressionado!"],
                    ["L", "Lucas", "Melhor Loja que ja usei. Atendimento top e produtos de qualidade. Recomendo para todos."],
                    ["M", "Mariana", "A entrega dos produtos digitais é instantânea. Zero dor de cabeça, tudo funciona perfeitamente."],
                    ["P", "Pedro", "Os cursos são diretos ao ponto e o conteúdo é de altíssimo nível. Valeu cada centavo."],
                    ["C", "Carla", "Finalmente encontrei um lugar confiável para comprar. A curadoria de produtos é excelente."],
                    ["F", "Fernando", "O que mais me impressionou foi a comunidade. Aprendi mais com outros membros do que em qualquer outro lugar."],
                    ["A", "Ana", "Design limpo, navegação rápida e checkout sem complicações. Experiência de compra nota 10."],
                    ["T", "Thiago", "Como iniciante, eu estava perdido. A didática dos materiais me deu a confiança que eu precisava."],
                    ["V", "Vanessa", "Já recuperei o investimento em menos de uma semana. Os métodos realmente funcionam."],
                    ["B", "Bruno", "O material bônus por si só já valeria o preço. Conteúdo de valor surreal."],
                    ["S", "Sofia", "Achei que era mais um curso genérico, mas a qualidade e o suporte me surpreenderam. Recomendo!"],
                    ["D", "Daniel", "A organização da plataforma é impecável. É fácil encontrar o que você precisa e acompanhar seu progresso."],
                    ["E", "Eduarda", "O melhor investimento que fiz na minha carreira digital até agora. Simples assim."]
                ]; ?>
                <?php foreach($depoimentos as $depoimento): ?>
                <div class="swiper-slide h-full">
                    <div class="testimonial-card rounded-xl p-6 h-full flex flex-col text-left">
                        <div class="flex text-yellow-400">★★★★★</div>
                        <p class="text-gray-300 mt-4 flex-grow italic">"<?= $depoimento[2] ?>"</p>
                        <div class="flex items-center mt-6 pt-4 border-t border-white/10">
                            <div class="w-12 h-12 rounded-full bg-brand-red flex items-center justify-center text-xl font-bold text-white"><?= $depoimento[0] ?></div>
                            <div class="ml-4"><p class="font-bold text-white"><?= $depoimento[1] ?></p><p class="text-sm text-brand-gray-text">Cliente Verificado</p></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</div>

<div class="w-full max-w-7xl mx-auto py-16 px-4 scroll-reveal-section">
    <div class="bg-gradient-to-r from-brand-red/80 to-red-800/80 rounded-2xl p-8 md:p-12 text-center flex flex-col items-center">
        <h2 class="text-4xl font-black text-white">Pronto Para Dominar o Jogo?</h2>
        <p class="mt-4 max-w-2xl text-lg text-red-100">Junte-se à elite do mercado digital. Tenha acesso às estratégias e ferramentas que realmente trazem resultado.</p>
        <a href="#" class="mt-8 bg-white hover:bg-gray-200 text-brand-black px-10 py-3 rounded-lg font-bold text-lg transition-all transform hover:scale-105">
            Começar Agora
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // --- LÓGICA DO CURSOR DE LUZ ---
    const glow = document.getElementById('cursor-glow');
    if (glow) {
        document.addEventListener('mousemove', (e) => {
            glow.style.left = e.pageX + 'px';
            glow.style.top = e.pageY + 'px';
        });
        document.addEventListener('mouseleave', () => { glow.style.opacity = '0'; });
        document.addEventListener('mouseenter', () => { glow.style.opacity = '1'; });
    }

    // --- LÓGICA DO NAVBAR COM EFEITO DE SCROLL ---
    const mainNav = document.getElementById('main-nav');
    if (mainNav) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                mainNav.classList.add('frosted-glass-nav', 'shadow-lg', 'shadow-brand-red/20');
            } else {
                mainNav.classList.remove('frosted-glass-nav', 'shadow-lg', 'shadow-brand-red/20');
            }
        });
    }

    // --- LÓGICA DO MENU LATERAL (NÃO PRECISA MAIS, POIS ESTÁ NO HEADER) ---
    // A lógica de menu, toast e AJAX continua no header.php e footer.php para funcionar em todas as páginas.
    // Se você quiser que o AJAX e TOAST funcionem, o JS deles precisa estar no footer, e não aqui.
    // Por simplicidade do teste, vamos focar só nas animações da home.

    // --- LÓGICA DAS ANIMAÇÕES DE ROLAGEM APRIMORADAS ---
    if (typeof ScrollReveal !== 'undefined') {
        const sr = ScrollReveal({
            distance: '60px',
            duration: 1500,
            easing: 'cubic-bezier(0.5, 0, 0, 1)',
            reset: false
        });
        sr.reveal('.main-banner-carousel', { origin: 'top', delay: 200 });
        sr.reveal('.flex.gap-4.overflow-x-auto', { origin: 'bottom', delay: 400 });
        sr.reveal('.scroll-reveal-section', { origin: 'bottom', interval: 150, delay: 200 });
    }

    // --- INICIALIZAÇÃO DE TODOS OS CARROSSÉIS ---
    const mainBanner = new Swiper('.main-banner-carousel', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    });

    const productCarousels = new Swiper('.product-carousel', {
        loop: false,
        slidesPerView: 2.2,
        spaceBetween: 16,
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        breakpoints: { 640: { slidesPerView: 3, spaceBetween: 20 }, 1024: { slidesPerView: 4, spaceBetween: 24 }, 1280: { slidesPerView: 5, spaceBetween: 24 }}
    });

    const testimonialCarousel = new Swiper('.testimonial-carousel', {
        loop: true,
        autoplay: { delay: 6000, disableOnInteraction: false, },
        slidesPerView: 1,
        spaceBetween: 20,
        pagination: { el: '.swiper-pagination', clickable: true, },
        breakpoints: { 768: { slidesPerView: 2, spaceBetween: 24 }, 1024: { slidesPerView: 3, spaceBetween: 30 } }
    });
});
</script>

<?php
require_once 'templates/footer.php';
?>