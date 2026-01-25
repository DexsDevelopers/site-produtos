<?php
// index_mobile_otimizado.php - Versão Otimizada para Mobile
session_start();
require_once 'config.php';
require_once 'templates/header.php';

// --- BUSCA DADOS OTIMIZADA ---
$banners_principais = $pdo->query("SELECT * FROM banners WHERE tipo = 'principal' AND ativo = 1 ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

// Busca produtos por categoria de forma otimizada
$produtos_por_categoria = [];
foreach ($categorias as $categoria) {
    $produtos = $pdo->query("SELECT id, nome, preco, imagem, checkout_link FROM produtos WHERE categoria_id = {$categoria['id']} LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($produtos)) {
        $produtos_por_categoria[$categoria['id']] = $produtos;
    }
}
?>

<style>
    /* CSS OTIMIZADO PARA MOBILE */
    * {
        -webkit-tap-highlight-color: transparent;
        /* Remove blue tap box */
    }

    .product-card:active {
        transform: scale(0.96);
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(220, 38, 38, 0.5);
        /* Brand red border */
    }

    @keyframes pulse-red {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
        }

        70% {
            transform: scale(1.05);
            box-shadow: 0 0 0 6px rgba(220, 38, 38, 0);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
        }
    }

    .badge-novo {
        animation: pulse-red 2s infinite;
    }

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
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .gradient-title {
        background: linear-gradient(135deg, #e53e3e, #ff6b6b, #ffd93d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateY(-2px);
    }

    .product-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .product-card:hover {
        background: rgba(255, 255, 255, 0.06);
        transform: translateY(-2px);
    }

    /* LOADING SKELETON */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    /* OTIMIZAÇÕES MOBILE */
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
</style>

<!-- Hero Section Otimizada -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-brand-red/20 via-transparent to-brand-blue/20"></div>

    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
        <h1 class="gradient-title text-4xl md:text-6xl font-black mb-6 leading-tight">
            O Mercado é dos Tubarões
        </h1>
        <p class="text-brand-gray-text text-lg md:text-xl mb-8 max-w-2xl mx-auto">
            Descubra produtos incríveis que vão transformar sua vida. Qualidade, preços competitivos e entrega
            instantânea.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#produtos"
                class="bg-brand-red hover:bg-brand-red-dark text-white font-bold py-4 px-8 rounded-lg transition-all duration-300 transform hover:scale-105">
                Ver Produtos
            </a>
            <a href="#sobre"
                class="glass-card text-white font-bold py-4 px-8 rounded-lg transition-all duration-300 transform hover:scale-105">
                Saiba Mais
            </a>
        </div>
    </div>
</section>

<!-- Banners Principais Otimizados -->
<?php if (!empty($banners_principais)): ?>
    <section class="py-16 px-4">
        <div class="w-full max-w-7xl mx-auto">
            <div class="swiper banner-carousel">
                <div class="swiper-wrapper">
                    <?php foreach ($banners_principais as $banner): ?>
                        <div class="swiper-slide">
                            <div class="glass-card rounded-2xl overflow-hidden">
                                <img src="<?= htmlspecialchars($banner['imagem']) ?>"
                                    alt="<?= htmlspecialchars($banner['titulo']) ?>" class="w-full h-64 md:h-80 object-cover"
                                    loading="lazy">
                                <div class="p-6">
                                    <h3 class="text-xl font-bold text-white mb-2"><?= htmlspecialchars($banner['titulo']) ?>
                                    </h3>
                                    <p class="text-brand-gray-text"><?= htmlspecialchars($banner['descricao']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Produtos por Categoria Otimizados -->
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
                        <h3 class="text-2xl font-bold text-white mb-8 text-center"><?= htmlspecialchars($categoria['nome']) ?>
                        </h3>

                        <div class="swiper product-carousel">
                            <div class="swiper-wrapper">
                                <?php foreach ($produtos_por_categoria[$categoria['id']] as $produto): ?>
                                    <div class="swiper-slide">
                                        <a href="produto.php?id=<?= $produto['id'] ?>" class="block">
                                            <div class="product-card rounded-xl p-4 h-full">
                                                <div class="relative mb-4">
                                                    <img src="<?= htmlspecialchars($produto['imagem']) ?>"
                                                        alt="<?= htmlspecialchars($produto['nome']) ?>"
                                                        class="w-full h-32 md:h-40 object-cover rounded-lg" loading="lazy">
                                                    <div
                                                        class="absolute top-2 right-2 bg-brand-red text-white text-xs px-2 py-1 rounded-full badge-novo">
                                                        Novo
                                                    </div>
                                                </div>
                                                <div class="space-y-2">
                                                    <h4 class="text-white font-bold text-sm md:text-base line-clamp-2">
                                                        <?= htmlspecialchars($produto['nome']) ?>
                                                    </h4>
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-brand-red font-bold text-lg">
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
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- JavaScript Otimizado para Mobile -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializa Swiper apenas se necessário
        if (typeof Swiper !== 'undefined') {
            // Banner Carousel
            new Swiper('.banner-carousel', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 1,
                    },
                    768: {
                        slidesPerView: 2,
                    },
                    1024: {
                        slidesPerView: 3,
                    }
                }
            });

            // Product Carousel
            new Swiper('.product-carousel', {
                slidesPerView: 2,
                spaceBetween: 16,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    640: {
                        slidesPerView: 3,
                    },
                    768: {
                        slidesPerView: 4,
                    },
                    1024: {
                        slidesPerView: 5,
                    }
                }
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
                        img.src = img.dataset.src;
                        img.classList.remove('skeleton');
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    });
</script>

<?php require_once 'templates/footer.php'; ?>