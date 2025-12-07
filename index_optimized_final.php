<?php
// index_optimized_final.php - Versão Final Otimizada para Mobile e Desktop
session_start();
require_once 'config.php';
require_once 'cache_otimizado.php';
require_once 'includes/image_optimizer.php';
require_once 'templates/header.php';

// --- CONFIGURAÇÕES DE PERFORMANCE ---
$page_title = 'Início';
$page_description = 'Descubra produtos incríveis com qualidade premium e preços imbatíveis. O mercado é dos tubarões!';
$page_keywords = 'loja online, produtos, compras, e-commerce, qualidade, preços baixos, tubarões';

// --- BUSCA DADOS COM CACHE OTIMIZADO ---
$banners_principais = getCachedData('banners_principais', function() use ($pdo) {
    return $pdo->query("SELECT * FROM banners WHERE tipo = 'principal' AND ativo = 1 ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
}, 1800); // Cache por 30 minutos

$banners_categorias = getCachedData('banners_categorias', function() use ($pdo) {
    return $pdo->query("SELECT * FROM banners WHERE tipo = 'categoria' AND ativo = 1 ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
}, 1800);

$categorias = getCachedData('categorias', function() use ($pdo) {
    return $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);
}, 3600); // Cache por 1 hora

// Busca produtos por categoria com cache otimizado
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

<!-- CSS Otimizado Inline para Performance -->
<link rel="stylesheet" href="assets/css/optimized.css?v=<?= time() ?>">

<style>
    /* Otimizações específicas da página */
    .hero-section {
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
    }
    
    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #0A0A0A 0%, #1E293B 50%, #0F172A 100%);
        z-index: -1;
    }
    
    .product-grid {
        display: grid;
        gap: 1.5rem;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }
    
    @media (max-width: 640px) {
        .product-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
    
    @media (min-width: 1024px) {
        .product-grid {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }
    
    /* Otimizações de performance */
    .gpu-accelerated {
        transform: translateZ(0);
        will-change: transform;
    }
    
    .smooth-scroll {
        scroll-behavior: smooth;
    }
</style>

<div id="cursor-glow"></div>

<!-- Hero Section Otimizado -->
<section class="hero-section">
    <div class="hero-bg"></div>
    <div class="relative w-full py-8 px-4 z-10">
        <div class="max-w-7xl mx-auto">
            <!-- Título Principal -->
            <div class="text-center mb-12 pt-16 md:pt-8">
                <h1 class="text-4xl md:text-6xl font-black mb-4 gradient-title animate-fade-in-up">
                    O Mercado é dos
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-brand-red to-brand-pink">
                        Tubarões
                    </span>
                </h1>
                <p class="text-lg md:text-xl text-brand-gray-300 mb-8 max-w-2xl mx-auto animate-fade-in-up" style="animation-delay: 0.2s;">
                    Descubra produtos incríveis com qualidade premium e preços imbatíveis
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up" style="animation-delay: 0.4s;">
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
            <div class="swiper main-banner-carousel rounded-xl overflow-hidden shadow-xl max-w-4xl mx-auto animate-fade-in-scale" style="animation-delay: 0.6s;">
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
</section>

<!-- Seção de Categorias Otimizada -->
<section id="categorias" class="py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-8 scroll-reveal-section">
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
            <div class="scroll-reveal-section">
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
                
                <div class="product-grid">
                    <?php foreach ($produtos_por_categoria[$categoria['id']] as $index => $produto): ?>
                    <div class="h-full">
                        <a href="produto.php?id=<?= $produto['id'] ?>" class="block group">
                            <div class="glass-card rounded-xl overflow-hidden h-full">
                                <div class="aspect-square overflow-hidden">
                                    <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                                         alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
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
                                        <span class="text-xl font-bold text-brand-red">
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

<!-- Depoimentos Otimizados -->
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

<!-- CTA Final -->
<div class="w-full max-w-7xl mx-auto py-16 px-4 scroll-reveal-section">
    <div class="bg-gradient-to-r from-brand-red/80 to-red-800/80 rounded-2xl p-8 md:p-12 text-center flex flex-col items-center">
        <h2 class="text-4xl font-black text-white">Pronto Para Dominar o Jogo?</h2>
        <p class="mt-4 max-w-2xl text-lg text-red-100">Junte-se à elite do mercado digital. Tenha acesso às estratégias e ferramentas que realmente trazem resultado.</p>
        <a href="#" class="mt-8 bg-white hover:bg-gray-200 text-brand-black px-10 py-3 rounded-lg font-bold text-lg transition-all transform hover:scale-105">
            Começar Agora
        </a>
    </div>
</div>

<!-- JavaScript Otimizado -->
<script src="assets/js/optimized.js?v=<?= time() ?>"></script>

<?php require_once 'templates/footer.php'; ?>


