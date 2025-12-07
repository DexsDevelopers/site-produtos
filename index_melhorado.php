<?php
// index_melhorado.php - Página Principal Otimizada e Organizada
session_start();
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
    $categorias = $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca produtos em destaque
    $produtos_destaque = $pdo->query("SELECT id, nome, preco, imagem, descricao_curta FROM produtos ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca produtos por categoria
    $produtos_por_categoria = [];
    foreach ($categorias as $categoria) {
        $produtos = $pdo->query("SELECT id, nome, preco, imagem, descricao_curta FROM produtos WHERE categoria_id = {$categoria['id']} ORDER BY id DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($produtos)) {
            $produtos_por_categoria[$categoria['id']] = $produtos;
        }
    }
} catch (Exception $e) {
    $banners_principais = $banners_categorias = $categorias = $produtos_destaque = [];
    $produtos_por_categoria = [];
}

// Define meta tags
$page_title = 'Início';
$page_description = 'Descubra produtos incríveis na nossa loja online. Qualidade, preços competitivos e entrega rápida.';
$page_keywords = 'loja online, produtos, compras, e-commerce, qualidade, preços baixos';

require_once 'templates/header.php';
?>

<!-- CSS Específico da Homepage -->
<link rel="stylesheet" href="assets/css/homepage.css">

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    O Mercado é dos
                    <span class="gradient-text">Tubarões</span>
                </h1>
                <p class="hero-subtitle">
                    Descubra produtos incríveis com qualidade premium e preços imbatíveis
                </p>
                <div class="hero-actions">
                    <a href="#produtos" class="btn-primary">
                        <i class="fas fa-shopping-bag"></i>
                        Explorar Produtos
                    </a>
                    <a href="busca.php" class="btn-secondary">
                        <i class="fas fa-search"></i>
                        Buscar
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Banner Principal -->
<?php if (!empty($banners_principais)): ?>
<section class="banner-section">
    <div class="container">
        <div class="banner-carousel">
            <div class="swiper main-banner-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($banners_principais as $banner): ?>
                    <div class="swiper-slide">
                        <div class="banner-slide" style="background-image: url('<?= htmlspecialchars($banner['imagem']) ?>')">
                            <div class="banner-overlay">
                                <div class="banner-content">
                                    <h2 class="banner-title"><?= htmlspecialchars($banner['titulo']) ?></h2>
                                    <p class="banner-description">Descubra mais sobre este produto</p>
                                    <?php if (!empty($banner['link'])): ?>
                                        <a href="<?= htmlspecialchars($banner['link']) ?>" class="btn-primary">
                                            Saiba Mais
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categorias -->
<?php if (!empty($banners_categorias)): ?>
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Nossas Categorias</h2>
            <p class="section-subtitle">Explore nossa seleção de produtos</p>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($banners_categorias as $banner): ?>
            <a href="<?= htmlspecialchars($banner['link']) ?>" class="category-card">
                <div class="category-image">
                    <img src="<?= htmlspecialchars($banner['imagem']) ?>" 
                         alt="<?= htmlspecialchars($banner['titulo']) ?>" 
                         loading="lazy">
                </div>
                <h3 class="category-name"><?= htmlspecialchars($banner['titulo']) ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Produtos em Destaque -->
<?php if (!empty($produtos_destaque)): ?>
<section class="products-section" id="produtos">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Produtos em Destaque</h2>
            <p class="section-subtitle">Os melhores produtos para você</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($produtos_destaque as $produto): ?>
            <div class="product-card">
                <a href="produto.php?id=<?= $produto['id'] ?>" class="product-link">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                             alt="<?= htmlspecialchars($produto['nome']) ?>" 
                             loading="lazy">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($produto['nome']) ?></h3>
                        <p class="product-description"><?= htmlspecialchars($produto['descricao_curta']) ?></p>
                        <div class="product-price"><?= formatarPreco($produto['preco']) ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="section-footer">
            <a href="busca.php" class="btn-primary">
                Ver Todos os Produtos
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Produtos por Categoria -->
<?php foreach ($categorias as $categoria): ?>
    <?php if (!empty($produtos_por_categoria[$categoria['id']])): ?>
    <section class="category-products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?= htmlspecialchars($categoria['nome']) ?></h2>
                <a href="categoria.php?id=<?= $categoria['id'] ?>" class="btn-secondary">
                    Ver Todos
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="products-grid">
                <?php foreach ($produtos_por_categoria[$categoria['id']] as $produto): ?>
                <div class="product-card">
                    <a href="produto.php?id=<?= $produto['id'] ?>" class="product-link">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                                 alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                 loading="lazy">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($produto['nome']) ?></h3>
                            <p class="product-description"><?= htmlspecialchars($produto['descricao_curta']) ?></p>
                            <div class="product-price"><?= formatarPreco($produto['preco']) ?></div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
<?php endforeach; ?>

<!-- Depoimentos -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">O Que Dizem Sobre Nós</h2>
            <p class="section-subtitle">Experiências compartilhadas por nossos clientes</p>
        </div>
        
        <div class="testimonials-carousel">
            <div class="swiper testimonials-swiper">
                <div class="swiper-wrapper">
                    <?php 
                    $depoimentos = [
                        ["R", "Rafael", "Processo de compra muito simples e seguro, interface fácil de entender."],
                        ["G", "Gissele", "Suporte 24h, um diferencial incrível. Sempre me ajudam quando preciso."],
                        ["J", "Jonas", "A plataforma é sensacional e o suporte é realmente eficiente. Estou impressionado!"],
                        ["L", "Lucas", "Melhor loja que já usei. Atendimento top e produtos de qualidade. Recomendo para todos."],
                        ["M", "Mariana", "A entrega dos produtos digitais é instantânea. Zero dor de cabeça, tudo funciona perfeitamente."],
                        ["P", "Pedro", "Os cursos são diretos ao ponto e o conteúdo é de altíssimo nível. Valeu cada centavo."]
                    ];
                    ?>
                    <?php foreach($depoimentos as $depoimento): ?>
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <div class="testimonial-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="testimonial-text">"<?= $depoimento[2] ?>"</p>
                            <div class="testimonial-author">
                                <div class="author-avatar" style="background-color: <?= gerarCorAvatar($depoimento[1]) ?>">
                                    <?= $depoimento[0] ?>
                                </div>
                                <div class="author-info">
                                    <h4 class="author-name"><?= $depoimento[1] ?></h4>
                                    <p class="author-title">Cliente Verificado</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Pronto Para Dominar o Jogo?</h2>
            <p class="cta-description">
                Junte-se à elite do mercado digital. Tenha acesso às estratégias e ferramentas que realmente trazem resultado.
            </p>
            <a href="busca.php" class="btn-primary btn-large">
                Começar Agora
            </a>
        </div>
    </div>
</section>

<!-- JavaScript Específico da Homepage -->
<script src="assets/js/homepage.js"></script>

<?php require_once 'templates/footer.php'; ?>
