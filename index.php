<?php
// index.php - Página Principal no Estilo Adsly
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

<!-- CSS Específico da Homepage no Estilo Adsly -->
<link rel="stylesheet" href="assets/css/homepage.css">
<style>
/* Estilo Adsly - Cores Vermelho e Preto com Efeitos Dopaminérgicos */
.adsly-hero {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    padding: 80px 0;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.adsly-hero::before {
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

@keyframes pulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.6; }
}

.adsly-hero h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    position: relative;
    z-index: 2;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from { text-shadow: 0 0 20px rgba(255, 0, 0, 0.3); }
    to { text-shadow: 0 0 30px rgba(255, 0, 0, 0.6), 0 0 40px rgba(255, 0, 0, 0.3); }
}

.adsly-hero .subtitle {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    position: relative;
    z-index: 2;
}

.adsly-hero .cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    position: relative;
    z-index: 2;
}

.adsly-hero .btn-primary {
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
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
}

.adsly-hero .btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.adsly-hero .btn-primary:hover::before {
    left: 100%;
}

.adsly-hero .btn-primary:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}

.adsly-hero .btn-secondary {
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

.adsly-hero .btn-secondary::before {
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

.adsly-hero .btn-secondary:hover::before {
    width: 100%;
}

.adsly-hero .btn-secondary:hover {
    color: white;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.3);
}

/* Cards no estilo Adsly */
.adsly-cards {
    padding: 80px 0;
    background: #000000;
    position: relative;
}

.adsly-cards::before {
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

.adsly-cards .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 2;
}

.adsly-cards h2 {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: white;
}

.adsly-cards .subtitle {
    text-align: center;
    font-size: 1.1rem;
    color: #cccccc;
    margin-bottom: 3rem;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.adsly-card {
    background: linear-gradient(145deg, #1a0000, #000000);
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1), 0 0 0 1px rgba(255, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
}

.adsly-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 0, 0, 0.1), transparent);
    transition: left 0.6s;
}

.adsly-card:hover::before {
    left: 100%;
}

.adsly-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(255, 0, 0, 0.2), 0 0 20px rgba(255, 0, 0, 0.1);
}

.adsly-card .icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(45deg, #ff0000, #ff3333);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
    animation: iconPulse 2s ease-in-out infinite;
}

@keyframes iconPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.adsly-card h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: white;
}

.adsly-card p {
    color: #cccccc;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.adsly-card .btn {
    background: linear-gradient(45deg, #ff0000, #ff3333);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
}

.adsly-card .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.adsly-card .btn:hover::before {
    left: 100%;
}

.adsly-card .btn:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}

/* Seção de produtos no estilo Adsly */
.adsly-products {
    padding: 80px 0;
    background: #000000;
    position: relative;
}

.adsly-products::before {
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

.adsly-products h2 {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: white;
    position: relative;
    z-index: 2;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
}

.adsly-products .subtitle {
    text-align: center;
    font-size: 1.1rem;
    color: #cccccc;
    margin-bottom: 3rem;
    position: relative;
    z-index: 2;
}

.products-grid-adsly {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    margin-top: 3rem;
    position: relative;
    z-index: 2;
}

@media (max-width: 1200px) {
    .products-grid-adsly {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .products-grid-adsly {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .products-grid-adsly {
        grid-template-columns: 1fr;
    }
}

.product-card-adsly {
    background: linear-gradient(145deg, #1a0000, #000000);
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1), 0 0 0 1px rgba(255, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 0, 0, 0.2);
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.product-card-adsly:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(255, 0, 0, 0.2), 0 0 20px rgba(255, 0, 0, 0.1);
}

.product-card-adsly .product-image {
    height: 200px;
    overflow: hidden;
    position: relative;
    background: linear-gradient(45deg, #ff0000, #ff3333);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.product-card-adsly .product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    border-radius: 0;
}

.product-card-adsly:hover .product-image img {
    transform: scale(1.1);
}

/* Fallback para quando não há imagem */
.product-card-adsly .product-image::before {
    content: '🛍️';
    font-size: 3rem;
    color: white;
    position: absolute;
    z-index: 1;
    opacity: 0.3;
}

.product-image-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(45deg, #ff0000, #ff3333);
    color: white;
    text-align: center;
    padding: 1rem;
}

.fallback-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.fallback-content i {
    font-size: 2rem;
    opacity: 0.8;
}

.fallback-content span {
    font-size: 0.9rem;
    font-weight: 500;
    opacity: 0.9;
}

.product-card-adsly .product-info {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.product-card-adsly .product-name {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: white;
    flex-shrink: 0;
}

.product-card-adsly .product-description {
    color: #cccccc;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    line-height: 1.5;
    flex-grow: 1;
}

.product-card-adsly .product-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ff0000;
    margin-bottom: 1rem;
    text-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
    flex-shrink: 0;
}

.product-card-adsly .btn {
    width: 100%;
    background: linear-gradient(45deg, #ff0000, #ff3333);
    color: white;
    padding: 12px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    text-align: center;
    transition: all 0.3s ease;
    display: block;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
    flex-shrink: 0;
    margin-top: auto;
}

.product-card-adsly .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.product-card-adsly .btn:hover::before {
    left: 100%;
}

.product-card-adsly .btn:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}

/* CTA Final no estilo Adsly */
.adsly-cta {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    padding: 80px 0;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.adsly-cta::before {
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

.adsly-cta h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    position: relative;
    z-index: 2;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
}

.adsly-cta p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    position: relative;
    z-index: 2;
}

.adsly-cta .btn-large {
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

.adsly-cta .btn-large::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.adsly-cta .btn-large:hover::before {
    left: 100%;
}

.adsly-cta .btn-large:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}

/* Banners Principais */
.adsly-banners-principais {
    padding: 60px 0;
    background: #000000;
    position: relative;
}

.adsly-banners-principais::before {
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

.banners-principais-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    position: relative;
    z-index: 2;
}

.banner-principal {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(255, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(255, 0, 0, 0.2);
}

.banner-principal:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 25px 50px rgba(255, 0, 0, 0.2);
}

.banner-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.banner-image {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.banner-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.banner-principal:hover .banner-image img {
    transform: scale(1.1);
}

.banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(255, 0, 0, 0.3) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.4s ease;
}

.banner-principal:hover .banner-overlay {
    opacity: 1;
}

.banner-content {
    text-align: center;
    color: white;
    padding: 2rem;
}

.banner-content h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}

.banner-content p {
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    opacity: 0.9;
}

.banner-cta {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(45deg, #ff0000, #ff3333);
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
}

.banner-cta:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}

/* Banners de Categorias */
.adsly-banners-categorias {
    padding: 80px 0;
    background: #000000;
    position: relative;
}

.adsly-banners-categorias::before {
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

.section-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: white;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
    position: relative;
    z-index: 2;
}

.section-subtitle {
    text-align: center;
    font-size: 1.1rem;
    color: #cccccc;
    margin-bottom: 3rem;
    position: relative;
    z-index: 2;
}

.banners-categorias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    position: relative;
    z-index: 2;
}

.banner-categoria {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 0, 0, 0.2);
}

.banner-categoria:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(255, 0, 0, 0.2);
}

.banner-categoria .banner-image {
    height: 250px;
}

.banner-categoria .banner-content h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.banner-categoria .banner-content p {
    font-size: 1rem;
    margin-bottom: 1rem;
}

.banner-categoria .banner-cta {
    padding: 10px 20px;
    font-size: 0.9rem;
}

/* Responsividade */
@media (max-width: 1200px) {
    .banners-principais-grid {
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    }
}

@media (max-width: 768px) {
    .adsly-hero h1 {
        font-size: 2.5rem;
    }

    .adsly-hero .cta-buttons {
        flex-direction: column;
        align-items: center;
    }

    .banners-principais-grid {
        grid-template-columns: 1fr;
    }

    .banners-categorias-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }

    .banner-principal .banner-image {
        height: 250px;
    }

    .banner-categoria .banner-image {
        height: 200px;
    }

    .section-title {
        font-size: 2rem;
    }

    .cards-grid {
        grid-template-columns: 1fr;
    }

    .products-grid-adsly {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .banners-categorias-grid {
        grid-template-columns: 1fr;
    }

    .banner-content {
        padding: 1.5rem;
    }

    .banner-content h3 {
        font-size: 1.5rem;
    }
}
</style>

<!-- Hero Section no Estilo Adsly -->
<section class="adsly-hero">
    <div class="container">
        <h1>O Mercado é dos <span style="color: #ff0000;">Tubarões</span></h1>
        <p class="subtitle">Descubra produtos incríveis com qualidade premium e preços imbatíveis</p>
        <div class="cta-buttons">
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
</section>

<!-- Banners Principais -->
<?php if (!empty($banners_principais)): ?>
<section class="adsly-banners-principais">
    <div class="container">
        <div class="banners-principais-grid">
            <?php foreach ($banners_principais as $banner): ?>
            <div class="banner-principal">
                <a href="<?= htmlspecialchars($banner['link']) ?>" class="banner-link">
                    <div class="banner-image">
                        <img src="<?= htmlspecialchars($banner['imagem']) ?>" 
                             alt="<?= htmlspecialchars($banner['titulo']) ?>"
                             loading="lazy">
                        <div class="banner-overlay">
                            <div class="banner-content">
                                <h3><?= htmlspecialchars($banner['titulo']) ?></h3>
                                <?php if (!empty($banner['descricao'])): ?>
                                <p><?= htmlspecialchars($banner['descricao']) ?></p>
                                <?php endif; ?>
                                <span class="banner-cta">
                                    <i class="fas fa-arrow-right"></i>
                                    Saiba Mais
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Banners de Categorias -->
<?php if (!empty($banners_categorias)): ?>
<section class="adsly-banners-categorias">
    <div class="container">
        <h2 class="section-title">Nossas Categorias</h2>
        <p class="section-subtitle">Explore nossa seleção de produtos</p>
        
        <div class="banners-categorias-grid">
            <?php foreach ($banners_categorias as $banner): ?>
            <div class="banner-categoria">
                <a href="<?= htmlspecialchars($banner['link']) ?>" class="banner-link">
                    <div class="banner-image">
                        <img src="<?= htmlspecialchars($banner['imagem']) ?>" 
                             alt="<?= htmlspecialchars($banner['titulo']) ?>"
                             loading="lazy">
                        <div class="banner-overlay">
                            <div class="banner-content">
                                <h3><?= htmlspecialchars($banner['titulo']) ?></h3>
                                <?php if (!empty($banner['descricao'])): ?>
                                <p><?= htmlspecialchars($banner['descricao']) ?></p>
                                <?php endif; ?>
                                <span class="banner-cta">
                                    <i class="fas fa-arrow-right"></i>
                                    Explorar
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Cards de Funcionalidades no Estilo Adsly -->
<section class="adsly-cards">
    <div class="container">
        <h2>Por Que Escolher Nossa Plataforma?</h2>
        <p class="subtitle">Oferecemos as melhores ferramentas e recursos para sua jornada digital</p>
        
        <div class="cards-grid">
            <div class="adsly-card">
                <div class="icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3>Performance Otimizada</h3>
                <p>Nossa plataforma foi desenvolvida com foco em velocidade e performance, garantindo uma experiência fluida para todos os usuários.</p>
                <a href="busca.php" class="btn">
                    <i class="fas fa-arrow-right"></i>
                                            Saiba Mais
                                        </a>
                                </div>
            
            <div class="adsly-card">
                <div class="icon">
                    <i class="fas fa-shield-alt"></i>
                            </div>
                <h3>Segurança Garantida</h3>
                <p>Proteção total dos seus dados com criptografia avançada e protocolos de segurança de última geração.</p>
                <a href="busca.php" class="btn">
                    <i class="fas fa-arrow-right"></i>
                    Saiba Mais
                </a>
                        </div>
            
            <div class="adsly-card">
                <div class="icon">
                    <i class="fas fa-headset"></i>
                    </div>
                <h3>Suporte 24/7</h3>
                <p>Nossa equipe de suporte está sempre disponível para ajudar você em qualquer momento do dia.</p>
                <a href="busca.php" class="btn">
                    <i class="fas fa-arrow-right"></i>
                    Saiba Mais
                </a>
            </div>
        </div>
    </div>
</section>

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
                <h3 class="categoria-titulo" style="font-size: 2rem; font-weight: 700; margin-bottom: 2rem; color: white; text-align: center; text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);">
                    <?= htmlspecialchars($categoria_info['nome']) ?>
                </h3>
                
                <!-- Carrossel Swiper para Produtos -->
                <div class="swiper produtos-swiper produtos-swiper-<?= $categoria_id ?>" style="position: relative;">
                    <div class="swiper-wrapper">
                        <?php foreach ($produtos as $produto): ?>
                        <div class="swiper-slide">
                            <div class="product-card-adsly">
                                <div class="product-image">
                                    <?php 
                                    $imagem_produto = $produto['imagem'];
                                    $imagem_existe = !empty($imagem_produto) && file_exists(__DIR__ . '/' . $imagem_produto);
                                    ?>
                                    
                                    <?php if ($imagem_existe): ?>
                                        <img src="<?= htmlspecialchars($imagem_produto) ?>"
                                             alt="<?= htmlspecialchars($produto['nome']) ?>"
                                             loading="lazy"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <?php endif; ?>

                                    <!-- Fallback para quando não há imagem -->
                                    <div class="product-image-fallback" style="<?= $imagem_existe ? 'display: none;' : 'display: flex;' ?>">
                                        <div class="fallback-content">
                                            <i class="fas fa-image"></i>
                                            <span><?= htmlspecialchars($produto['nome']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name"><?= htmlspecialchars($produto['nome']) ?></h3>
                                    <p class="product-description"><?= htmlspecialchars($produto['descricao_curta']) ?></p>
                                    <div class="product-price"><?= formatarPreco($produto['preco']) ?></div>
                                    <a href="produto.php?id=<?= $produto['id'] ?>" class="btn">
                                        <i class="fas fa-shopping-cart"></i>
                                        Comprar Agora
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Setas de Navegação (Desktop) -->
                    <div class="swiper-button-next produtos-next-<?= $categoria_id ?>" style="color: #ff0000; right: 0;"></div>
                    <div class="swiper-button-prev produtos-prev-<?= $categoria_id ?>" style="color: #ff0000; left: 0;"></div>
                    
                    <!-- Paginação (Opcional) -->
                    <div class="swiper-pagination produtos-pagination-<?= $categoria_id ?>" style="position: relative; margin-top: 2rem;"></div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="busca.php" class="btn-large" style="background: linear-gradient(45deg, #ff0000, #ff3333); color: white; padding: 15px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);">
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
        <p>Junte-se à elite do mercado digital. Tenha acesso às estratégias e ferramentas que realmente trazem resultado.</p>
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
<script src="assets/js/homepage.js"></script>

<!-- Swiper JS para Carrossel de Produtos -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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

<?php
require_once 'templates/footer.php';
?>