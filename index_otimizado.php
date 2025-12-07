<?php
// index_otimizado.php - Versão Otimizada para Desktop e Mobile
session_start();
require_once 'config.php';
require_once 'cache_otimizado.php';
require_once 'templates/header.php';

// --- BUSCA DADOS COM CACHE ---
$banners_principais = getCachedData('banners_principais', function() use ($pdo) {
    return $pdo->query("SELECT * FROM banners WHERE tipo = 'principal' AND ativo = 1 ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
}, 1800); // Cache por 30 minutos

$banners_categorias = getCachedData('banners_categorias', function() use ($pdo) {
    return $pdo->query("SELECT * FROM banners WHERE tipo = 'categoria' AND ativo = 1 ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
}, 1800);

$categorias = getCachedData('categorias', function() use ($pdo) {
    return $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);
}, 3600); // Cache por 1 hora

// Busca produtos por categoria com cache
$produtos_por_categoria = getCachedData('produtos_por_categoria', function() use ($pdo, $categorias) {
    $produtos_por_categoria = [];
    foreach ($categorias as $categoria) {
        $produtos = $pdo->query("SELECT id, nome, preco, imagem, checkout_link, descricao_curta FROM produtos WHERE categoria_id = {$categoria['id']} ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($produtos)) {
            $produtos_por_categoria[$categoria['id']] = $produtos;
        }
    }
    return $produtos_por_categoria;
}, 1800);
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

    /* OTIMIZAÇÕES RESPONSIVAS */
    @media (max-width: 768px) {
        .swiper-slide {
            width: 45% !important;
        }
        
        .product-card {
            padding: 0.75rem;
        }
        
        .product-card img {
            height: 120px;
            object-fit: cover;
        }
    }

    /* OTIMIZAÇÕES DESKTOP */
    @media (min-width: 1024px) {
        .swiper-slide {
            width: 20% !important;
        }
        
        .product-card {
            padding: 1.5rem;
        }
        
        .product-card img {
            height: 200px;
            object-fit: cover;
        }
        
        .glass-card {
            backdrop-filter: blur(15px);
        }
        
        .gradient-title {
            font-size: 3.5rem;
        }
    }

    /* OTIMIZAÇÕES ULTRA WIDE */
    @media (min-width: 1440px) {
        .swiper-slide {
            width: 16.66% !important;
        }
        
        .product-card {
            padding: 2rem;
        }
        
        .product-card img {
            height: 220px;
        }
    }

    /* LOADING SKELETON */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
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
    </div>
</div>

<!-- Banners Principais - Formato Grande -->
<?php if (!empty($banners_principais)): ?>
<section class="py-16 px-4">
    <div class="w-full max-w-7xl mx-auto">
        <div class="swiper main-banner-carousel rounded-xl overflow-hidden shadow-xl max-w-4xl mx-auto">
            <div class="swiper-wrapper">
                <?php foreach ($banners_principais as $banner): ?>
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
    </div>
</section>
<?php endif; ?>

<!-- Produtos por Categoria -->
<section id="produtos" class="py-16 px-4">
    <div class="w-full max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="gradient-title text-3xl md:text-4xl font-black mb-4">Nossos Produtos</h2>
            <p class="text-brand-gray-text">Descubra nossa seleção cuidadosamente curada</p>
        </div>

        <div class="space-y-16">
            <?php foreach ($categorias as $categoria): ?>
                <?php if (isset($produtos_por_categoria[$categoria['id']])): ?>
                <div class="scroll-reveal-section">
                    <h3 class="text-2xl font-bold text-white mb-8 text-center"><?= htmlspecialchars($categoria['nome']) ?></h3>
                    
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
                                            <span class="text-brand-red text-xl font-bold">
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
    </div>
</section>

<!-- Depoimentos -->
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
                <?php foreach ($depoimentos as $depoimento): ?>
                <div class="swiper-slide">
                    <div class="testimonial-card rounded-xl p-6 h-full">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-brand-red rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                                <?= $depoimento[0] ?>
                            </div>
                            <div>
                                <h4 class="text-white font-bold"><?= $depoimento[1] ?></h4>
                                <div class="flex text-yellow-400">
                                    <i class="fas fa-star text-sm"></i>
                                    <i class="fas fa-star text-sm"></i>
                                    <i class="fas fa-star text-sm"></i>
                                    <i class="fas fa-star text-sm"></i>
                                    <i class="fas fa-star text-sm"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-brand-gray-text italic">"<?= $depoimento[2] ?>"</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</div>

<!-- JavaScript Otimizado -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa Swiper apenas se necessário
    if (typeof Swiper !== 'undefined') {
        // Banner Carousel
        new Swiper('.main-banner-carousel', {
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });

        // Product Carousel
        new Swiper('.product-carousel', {
            slidesPerView: 1.5,
            spaceBetween: 16,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            breakpoints: {
                480: { slidesPerView: 2, spaceBetween: 16 },
                640: { slidesPerView: 2.5, spaceBetween: 20 },
                768: { slidesPerView: 3, spaceBetween: 20 },
                1024: { slidesPerView: 4, spaceBetween: 24 },
                1280: { slidesPerView: 5, spaceBetween: 24 },
                1440: { slidesPerView: 6, spaceBetween: 28 },
                1920: { slidesPerView: 7, spaceBetween: 32 }
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });

        // Testimonial Carousel
        new Swiper('.testimonial-carousel', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            breakpoints: {
                640: { slidesPerView: 2 },
                1024: { slidesPerView: 3 },
                1280: { slidesPerView: 4 }
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
    }

    // ScrollReveal otimizado
    if (typeof ScrollReveal !== 'undefined') {
        ScrollReveal().reveal('.gradient-title', { 
            duration: 600,
            distance: '20px',
            origin: 'top',
            reset: false
        });
        
        ScrollReveal().reveal('.product-card', { 
            duration: 400,
            distance: '15px',
            origin: 'bottom',
            interval: 50,
            reset: false
        });
    }

    // Lazy loading para imagens
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.1
        });

        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            imageObserver.observe(img);
        });
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
