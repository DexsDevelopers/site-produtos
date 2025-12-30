<?php
// produto.php - Página de Produto no Estilo Adsly
session_start();
require_once 'config.php';

// --- RASTREAMENTO AUTOMÁTICO DE AFILIAÇÃO ---
if (isset($_GET['ref'])) {
    require_once 'includes/affiliate_system.php';
    $affiliateSystem = new AffiliateSystem($pdo);
    
    $affiliate_code = $_GET['ref'];
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
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
    $redirect_url = 'produto.php';
    if (!empty($params)) {
        $redirect_url .= '?' . http_build_query($params);
    }
    header('Location: ' . $redirect_url);
    exit();
}

$produto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produto_selecionado = null;

try {
    // Busca os dados do produto
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto_selecionado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se o produto existe, busca as avaliações dele
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

        // Calcula a média das notas
        $total_avaliacoes = count($avaliacoes);
        $soma_notas = 0;
        foreach ($avaliacoes as $avaliacao) {
            $soma_notas += $avaliacao['nota'];
        }
        $media_notas = ($total_avaliacoes > 0) ? round($soma_notas / $total_avaliacoes, 1) : 0;
    }
} catch (PDOException $e) {
    die("Erro ao carregar a página do produto.");
}

if (!$produto_selecionado) {
    header('Location: index.php');
    exit();
}

// Define meta tags específicas para o produto
$page_title = htmlspecialchars($produto_selecionado['nome']);
$page_description = htmlspecialchars($produto_selecionado['descricao_curta']);
$page_keywords = 'produto, ' . strtolower(str_replace(' ', ', ', $produto_selecionado['nome'])) . ', comprar, loja online';
$page_image = htmlspecialchars($produto_selecionado['imagem']);

require_once 'templates/header.php';
?>

<!-- Structured Data para Produto -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "<?= htmlspecialchars($produto_selecionado['nome']) ?>",
    "description": "<?= htmlspecialchars($produto_selecionado['descricao_curta']) ?>",
    "image": "<?= htmlspecialchars($produto_selecionado['imagem']) ?>",
    "offers": {
        "@type": "Offer",
        "price": "<?= $produto_selecionado['preco'] ?>",
        "priceCurrency": "BRL",
        "availability": "https://schema.org/InStock",
        "seller": {
            "@type": "Organization",
            "name": "Minha Loja"
        }
    },
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?= $media_notas ?>",
        "reviewCount": "<?= $total_avaliacoes ?>"
    }
}
</script>

<!-- CSS Específico da Página de Produto no Estilo Adsly -->
<style>
/* Estilo Adsly para Página de Produto - Cores Vermelho e Preto com Efeitos Dopaminérgicos */
.adsly-product-hero {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    padding: 60px 0;
    color: white;
    position: relative;
    overflow: hidden;
}

.adsly-product-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 50%, rgba(255, 0, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 0, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(255, 0, 0, 0.1) 0%, transparent 50%);
    animation: pulse 4s ease-in-out infinite;
}

.adsly-product-hero .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 2;
}

.adsly-product-hero h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    line-height: 1.2;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
    animation: glow 2s ease-in-out infinite alternate;
}

.adsly-product-hero .subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

.adsly-product-hero .price {
    font-size: 2.5rem;
    font-weight: 700;
    color: #ff0000;
    margin-bottom: 2rem;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.5);
    animation: priceGlow 2s ease-in-out infinite alternate;
}

@keyframes priceGlow {
    from { text-shadow: 0 0 20px rgba(255, 0, 0, 0.5); }
    to { text-shadow: 0 0 30px rgba(255, 0, 0, 0.8), 0 0 40px rgba(255, 0, 0, 0.3); }
}

.adsly-product-hero .rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.adsly-product-hero .rating .stars {
    color: #ffd700;
    font-size: 1.2rem;
}

.adsly-product-hero .rating .count {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
}

.adsly-product-hero .cta-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.adsly-product-hero .btn-primary {
    background: linear-gradient(45deg, #ff0000, #ff3333);
    color: white;
    padding: 15px 30px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    cursor: pointer;
    font-size: 1.1rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
}

.adsly-product-hero .btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.adsly-product-hero .btn-primary:hover::before {
    left: 100%;
}

.adsly-product-hero .btn-primary:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}

.adsly-product-hero .btn-secondary {
    background: transparent;
    color: white;
    border: 2px solid #ff0000;
    padding: 15px 30px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    overflow: hidden;
}

.adsly-product-hero .btn-secondary::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 0;
    height: 100%;
    background: #ff0000;
    transition: width 0.3s ease;
    z-index: -1;
}

.adsly-product-hero .btn-secondary:hover::before {
    width: 100%;
}

.adsly-product-hero .btn-secondary:hover {
    color: white;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.3);
}

/* Seção da Imagem do Produto */
.adsly-product-image {
    padding: 60px 0;
    background: #000000;
    position: relative;
}

.adsly-product-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 30%, rgba(255, 0, 0, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(255, 0, 0, 0.05) 0%, transparent 50%);
    pointer-events: none;
}

.adsly-product-image .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 2;
}

.product-image-container {
    display: flex;
    justify-content: center;
    align-items: center;
    max-width: 600px;
    margin: 0 auto;
    background: linear-gradient(145deg, #1a0000, #000000);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 20px 40px rgba(255, 0, 0, 0.1);
    border: 1px solid rgba(255, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
}

.product-image-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 0, 0, 0.1), transparent);
    transition: left 0.6s;
}

.product-image-container:hover::before {
    left: 100%;
}

.product-main-image {
    width: 100%;
    height: auto;
    max-height: 500px;
    object-fit: contain;
    border-radius: 15px;
    transition: transform 0.3s ease;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.product-image-container:hover .product-main-image {
    transform: scale(1.05);
}

.product-image-fallback {
    width: 100%;
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(45deg, #1a0000, #000000);
    border-radius: 15px;
    border: 2px dashed rgba(255, 0, 0, 0.3);
}

.fallback-content {
    text-align: center;
    padding: 2rem;
}

.fallback-content i {
    animation: pulse 2s ease-in-out infinite;
}

/* Seção de detalhes */
.adsly-product-details {
    padding: 80px 0;
    background: #000000;
    position: relative;
}

.adsly-product-details::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 10% 20%, rgba(255, 0, 0, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 90% 80%, rgba(255, 0, 0, 0.05) 0%, transparent 50%);
    pointer-events: none;
}

.adsly-product-details .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 2;
}

.adsly-product-details h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
    color: white;
    text-align: center;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
}

.adsly-product-details .description {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #cccccc;
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

/* Seção de características */
.adsly-features {
    padding: 80px 0;
    background: #000000;
    position: relative;
}

.adsly-features::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 30%, rgba(255, 0, 0, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(255, 0, 0, 0.05) 0%, transparent 50%);
    pointer-events: none;
}

.adsly-features .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 2;
}

.adsly-features h2 {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 3rem;
    color: white;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.feature-card {
    background: linear-gradient(145deg, #1a0000, #000000);
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1), 0 0 0 1px rgba(255, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 0, 0, 0.2);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 0, 0, 0.1), transparent);
    transition: left 0.6s;
}

.feature-card:hover::before {
    left: 100%;
}

.feature-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(255, 0, 0, 0.2), 0 0 20px rgba(255, 0, 0, 0.1);
}

.feature-card .icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(45deg, #ff0000, #ff3333);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
    animation: iconPulse 2s ease-in-out infinite;
}

.feature-card h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: white;
}

.feature-card p {
    color: #cccccc;
    line-height: 1.6;
}

/* Seção de avaliações */
.adsly-reviews {
    padding: 80px 0;
    background: #000000;
    position: relative;
}

.adsly-reviews::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 30%, rgba(255, 0, 0, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(255, 0, 0, 0.05) 0%, transparent 50%);
    pointer-events: none;
}

.adsly-reviews .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 2;
}

.adsly-reviews h2 {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 3rem;
    color: white;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
}

.reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.review-card {
    background: linear-gradient(145deg, #1a0000, #000000);
    border-radius: 15px;
    padding: 2rem;
    border: 1px solid rgba(255, 0, 0, 0.2);
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1);
}

.review-card .rating {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 1rem;
}

.review-card .rating .star {
    color: #ffd700;
    font-size: 1.2rem;
}

.review-card .text {
    color: #cccccc;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    font-style: italic;
}

.review-card .author {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.review-card .author-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(45deg, #ff0000, #ff3333);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
}

.review-card .author-info h4 {
    font-weight: 600;
    color: white;
    margin-bottom: 0.25rem;
}

.review-card .author-info p {
    color: #cccccc;
    font-size: 0.9rem;
}

/* CTA Final */
.adsly-product-cta {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    padding: 80px 0;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.adsly-product-cta::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 30% 40%, rgba(255, 0, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 70% 60%, rgba(255, 0, 0, 0.1) 0%, transparent 50%);
    animation: pulse 4s ease-in-out infinite;
}

.adsly-product-cta h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    position: relative;
    z-index: 2;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
}

.adsly-product-cta p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    position: relative;
    z-index: 2;
}

.adsly-product-cta .btn-large {
    background: linear-gradient(45deg, #ff0000, #ff3333);
    color: white;
    padding: 18px 40px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    z-index: 2;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
}

.adsly-product-cta .btn-large::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.adsly-product-cta .btn-large:hover::before {
    left: 100%;
}

.adsly-product-cta .btn-large:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}

/* Responsividade */
@media (max-width: 768px) {
    .adsly-product-hero h1 {
        font-size: 2.5rem;
    }
    
    .adsly-product-hero .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .product-image-container {
        max-width: 100%;
        padding: 1rem;
    }
    
    .product-main-image {
        max-height: 300px;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .reviews-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Hero Section do Produto no Estilo Adsly -->
<section class="adsly-product-hero">
    <div class="container">
        <h1><?= htmlspecialchars($produto_selecionado['nome']) ?></h1>
        <p class="subtitle"><?= htmlspecialchars($produto_selecionado['descricao_curta']) ?></p>
        
        <div class="rating">
            <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= ($i <= $media_notas) ? '' : 'text-gray-400' ?>"></i>
                        <?php endfor; ?>
                    </div>
            <span class="count">(<?= $total_avaliacoes ?> avaliações)</span>
                </div>

        <div class="price"><?= formatarPreco($produto_selecionado['preco']) ?></div>
        
        <div class="cta-buttons">
            <form id="add-to-cart-form" style="display: inline;">
                        <input type="hidden" name="produto_id" value="<?= $produto_selecionado['id'] ?>">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-shopping-cart"></i>
                            Adicionar ao Carrinho
                        </button>
                    </form>
                    
                    <!-- Botão Comprar Agora - Redireciona para checkout baseado nos métodos habilitados -->
                    <?php if ($checkout_url): ?>
                    <a href="<?= $checkout_url ?>?produto_id=<?= $produto_selecionado['id'] ?>&quantidade=1" class="btn-secondary">
                        <i class="fas fa-shopping-bag"></i>
                        <?= htmlspecialchars($checkout_button_text) ?>
                    </a>
                    <?php else: ?>
                    <button class="btn-secondary" disabled title="Nenhum método de pagamento configurado">
                        <i class="fas fa-exclamation-triangle"></i>
                        Pagamento Indisponível
                    </button>
                    <?php endif; ?>
                </div>
            </div>
</section>

<!-- Imagem do Produto -->
<section class="adsly-product-image">
    <div class="container">
        <div class="product-image-container">
            <?php 
            $imagem_produto = $produto_selecionado['imagem'];
            $imagem_existe = !empty($imagem_produto) && file_exists(__DIR__ . '/' . $imagem_produto);
            
            // Debug: mostrar informações da imagem
            if (isset($_GET['debug'])) {
                echo "<!-- DEBUG IMAGEM: " . $imagem_produto . " -->";
                echo "<!-- DEBUG EXISTE: " . ($imagem_existe ? 'SIM' : 'NÃO') . " -->";
                echo "<!-- DEBUG CAMINHO: " . __DIR__ . '/' . $imagem_produto . " -->";
            }
            ?>
            
            <?php if ($imagem_existe): ?>
                <img src="<?= htmlspecialchars($imagem_produto) ?>" 
                     alt="<?= htmlspecialchars($produto_selecionado['nome']) ?>"
                     class="product-main-image"
                     loading="lazy"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <?php endif; ?>
            
            <!-- Fallback quando não há imagem -->
            <div class="product-image-fallback" style="<?= $imagem_existe ? 'display: none;' : 'display: flex;' ?>">
                <div class="fallback-content">
                    <i class="fas fa-image" style="font-size: 4rem; color: #ff0000; margin-bottom: 1rem;"></i>
                    <h3 style="color: white; margin-bottom: 0.5rem;">Imagem do Produto</h3>
                    <p style="color: #cccccc; text-align: center;"><?= htmlspecialchars($produto_selecionado['nome']) ?></p>
                </div>
                </div>
            </div>
        </div>
</section>

<!-- Descrição do Produto -->
<section class="adsly-product-details">
    <div class="container">
        <h2>Sobre Este Produto</h2>
        <div class="description">
                <?= nl2br(htmlspecialchars($produto_selecionado['descricao'])) ?>
            </div>
    </div>
</section>

<!-- Características do Produto -->
<section class="adsly-features">
    <div class="container">
        <h2>Por Que Escolher Este Produto?</h2>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Qualidade Premium</h3>
                <p>Produto desenvolvido com os mais altos padrões de qualidade e atenção aos detalhes.</p>
        </div>

            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h3>Entrega Rápida</h3>
                <p>Receba seu produto rapidamente com nosso sistema de entrega otimizado.</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Garantia Total</h3>
                <p>Produto com garantia completa e suporte técnico especializado.</p>
            </div>
        </div>
                </div>
</section>

<!-- Avaliações dos Clientes -->
<?php if (!empty($avaliacoes)): ?>
<section class="adsly-reviews">
    <div class="container">
        <h2>Avaliações dos Clientes</h2>
        
        <div class="reviews-grid">
                    <?php foreach ($avaliacoes as $avaliacao): ?>
            <div class="review-card">
                <div class="rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star star <?= ($i <= $avaliacao['nota']) ? '' : 'text-gray-300' ?>"></i>
                    <?php endfor; ?>
                </div>
                <div class="text">"<?= htmlspecialchars($avaliacao['comentario']) ?>"</div>
                <div class="author">
                    <div class="author-avatar">
                        <?= strtoupper(substr($avaliacao['nome_usuario'], 0, 1)) ?>
                    </div>
                    <div class="author-info">
                        <h4><?= htmlspecialchars($avaliacao['nome_usuario']) ?></h4>
                        <p>Cliente Verificado</p>
            </div>
        </div>
    </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Final -->
<section class="adsly-product-cta">
    <div class="container">
        <h2>Pronto Para Adquirir Este Produto?</h2>
        <p>Junte-se a milhares de clientes satisfeitos que já escolheram este produto.</p>
        <a href="#add-to-cart" class="btn-large">
            <i class="fas fa-shopping-cart"></i>
            Adicionar ao Carrinho
        </a>
</div>
</section>

<!-- JavaScript para funcionalidade do carrinho -->
<style>
/* Popup de Sucesso - Estilo Moderno */
.cart-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.cart-popup.show {
    opacity: 1;
    visibility: visible;
}

.cart-popup-content {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border: 2px solid rgba(255, 69, 0, 0.3);
    border-radius: 20px;
    padding: 2.5rem;
    max-width: 420px;
    width: 90%;
    text-align: center;
    position: relative;
    box-shadow: 0 20px 60px rgba(255, 69, 0, 0.3);
    transform: scale(0.8) translateY(20px);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.cart-popup.show .cart-popup-content {
    transform: scale(1) translateY(0);
}

.cart-popup-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #10B981, #059669);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: popupIconBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
}

@keyframes popupIconBounce {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.cart-popup-icon i {
    font-size: 2.5rem;
    color: white;
}

.cart-popup-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.75rem;
    animation: fadeInUp 0.5s ease 0.2s both;
}

.cart-popup-message {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 1.5rem;
    line-height: 1.6;
    animation: fadeInUp 0.5s ease 0.3s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.cart-popup-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    animation: fadeInUp 0.5s ease 0.4s both;
}

.cart-popup-btn {
    padding: 0.875rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.cart-popup-btn-primary {
    background: linear-gradient(135deg, #ff4500, #ff6347);
    color: white;
    box-shadow: 0 4px 15px rgba(255, 69, 0, 0.3);
}

.cart-popup-btn-primary:hover {
    background: linear-gradient(135deg, #ff6347, #ff4500);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 69, 0, 0.4);
}

.cart-popup-btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.cart-popup-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

/* Responsivo */
@media (max-width: 640px) {
    .cart-popup-content {
        padding: 2rem 1.5rem;
    }
    
    .cart-popup-title {
        font-size: 1.5rem;
    }
    
    .cart-popup-buttons {
        flex-direction: column;
    }
    
    .cart-popup-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div id="cart-popup" class="cart-popup">
    <div class="cart-popup-content">
        <div class="cart-popup-icon">
            <i class="fas fa-check"></i>
        </div>
        <h2 class="cart-popup-title">Produto Adicionado!</h2>
        <p class="cart-popup-message" id="cart-popup-message">O produto foi adicionado ao seu carrinho com sucesso.</p>
        <div class="cart-popup-buttons">
            <a href="carrinho.php" class="cart-popup-btn cart-popup-btn-primary">
                <i class="fas fa-shopping-cart"></i>
                Ver Carrinho
            </a>
            <button onclick="fecharCartPopup()" class="cart-popup-btn cart-popup-btn-secondary">
                Continuar Comprando
            </button>
        </div>
    </div>
</div>

<script>
function mostrarCartPopup(mensagem) {
    const popup = document.getElementById('cart-popup');
    const messageEl = document.getElementById('cart-popup-message');
    
    if (mensagem) {
        messageEl.textContent = mensagem;
    }
    
    popup.classList.add('show');
    
    // Fecha automaticamente após 4 segundos
    setTimeout(() => {
        fecharCartPopup();
    }, 4000);
}

function fecharCartPopup() {
    const popup = document.getElementById('cart-popup');
    popup.classList.remove('show');
}

// Fecha ao clicar fora do popup
document.getElementById('cart-popup').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharCartPopup();
    }
});

// Adiciona produto ao carrinho
document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const button = form.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    // Animação de loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
    
    const formData = new FormData(form);
    
    fetch('adicionar_carrinho.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualiza contador do carrinho
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                const currentCount = parseInt(cartCount.textContent) || 0;
                cartCount.textContent = data.cart_count || (currentCount + 1);
                cartCount.style.transform = 'scale(1.3)';
                setTimeout(() => {
                    cartCount.style.transform = 'scale(1)';
                }, 300);
            }
            
            // Mostra popup bonito
            const mensagem = data.produto_nome 
                ? `${data.produto_nome} foi adicionado ao carrinho!`
                : 'Produto adicionado ao carrinho com sucesso!';
            mostrarCartPopup(mensagem);
            
            // Feedback visual no botão
            button.innerHTML = '<i class="fas fa-check"></i> Adicionado!';
            button.style.background = 'linear-gradient(135deg, #10B981, #059669)';
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = '';
                button.disabled = false;
            }, 2000);
        } else {
            alert(data.message || 'Erro ao adicionar produto ao carrinho.');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao adicionar produto ao carrinho. Tente novamente.');
        button.innerHTML = originalText;
        button.disabled = false;
    });
});
</script>

<?php
require_once 'templates/footer.php';
?>