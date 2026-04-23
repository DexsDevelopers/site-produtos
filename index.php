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
    $banners_principais = $pdo->query("SELECT * FROM banners WHERE tipo = 'principal' AND ativo = 1 ORDER BY posicao ASC, id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // Fallback: se não há banners 'principal', mostra qualquer banner ativo
    if (empty($banners_principais)) {
        $banners_principais = $pdo->query("SELECT * FROM banners WHERE ativo = 1 ORDER BY posicao ASC, id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    }

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

    // Fallback: se nenhuma categoria tem exibir_home = 1, carrega todas as categorias com produtos
    if (empty($produtos_por_categoria)) {
        $todas_cats = $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($todas_cats as $categoria) {
            $stmt = $pdo->prepare("SELECT id, nome, preco, imagem, descricao_curta FROM produtos WHERE categoria_id = ? ORDER BY id DESC");
            $stmt->execute([$categoria['id']]);
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($produtos)) {
                $categorias[] = $categoria;
                $produtos_por_categoria[$categoria['id']] = $produtos;
            }
        }
    }

    // Busca produtos marcados como destaque
    $destaques = $pdo->query("SELECT id, nome, preco, imagem, descricao_curta FROM produtos WHERE destaque = 1 ORDER BY id DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);

    // Fallback: se nenhum produto está marcado como destaque, mostra os 12 mais recentes
    if (empty($destaques)) {
        $destaques = $pdo->query("SELECT id, nome, preco, imagem, descricao_curta FROM produtos ORDER BY id DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
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
    /* ── Legacy compat (kept minimal) ── */
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

    .hero-banner-swiper {
        width: 100%;
        background: #000;
        min-height: 320px;
    }
    .hero-banner-swiper .swiper-slide {
        height: auto !important;
        position: relative;
        cursor: pointer;
    }
    .hero-banner-swiper .swiper-slide a {
        display: block;
        width: 100%;
    }
    .hero-banner-swiper .swiper-slide img {
        width: 100%;
        height: auto;
        max-height: 600px;
        object-fit: cover;
        object-position: center;
        display: block;
    }
    .hero-banner-swiper .swiper-pagination-bullet {
        background: rgba(255,255,255,0.5);
        opacity: 1;
        width: 8px;
        height: 8px;
    }
    .hero-banner-swiper .swiper-pagination-bullet-active {
        background: #fff;
        width: 24px;
        border-radius: 4px;
    }
    .hero-banner-swiper .swiper-button-next,
    .hero-banner-swiper .swiper-button-prev {
        color: #fff;
        background: rgba(0,0,0,0.4);
        width: 44px;
        height: 44px;
        border-radius: 50%;
        backdrop-filter: blur(4px);
    }
    .hero-banner-swiper .swiper-button-next::after,
    .hero-banner-swiper .swiper-button-prev::after {
        font-size: 16px;
        font-weight: 700;
    }
    /* Banners por dispositivo */
    @media (max-width: 767px)  { .banner-desktop-only { display: none !important; } }
    @media (min-width: 768px)  { .banner-mobile-only  { display: none !important; } }
    /* Fallback hero (sem banners) */
    .hero-fallback { background:linear-gradient(135deg,#000 0%,#111 40%,#0a0a0a 100%); min-height:60vh; display:flex; align-items:center; }
</style>

<?php
// ── Fake reviews data for social proof ──
$nomes_review = ['Ana Carolina S.','Pedro Henrique M.','Juliana Costa','Rafael Oliveira','Mariana Santos','Lucas Ferreira','Camila Rodrigues','Gabriel Almeida','Isabela Souza','Thiago Pereira','Larissa Lima','Matheus Cardoso'];
$cidades_review = ['SP','RJ','BH','Curitiba','Porto Alegre','Salvador','Brasília','Recife','Fortaleza','Florianópolis','Goiânia','Manaus'];
$textos_review = [
    'Produto de qualidade incrível! Chegou antes do prazo e a embalagem estava impecável.',
    'Superou minhas expectativas! Material premium e acabamento perfeito. Recomendo demais!',
    'Melhor compra que fiz esse ano. Entrega rápida e atendimento nota 10.',
    'Já é a terceira vez que compro aqui. Qualidade sempre consistente, virei cliente fiel!',
    'Meu marido amou o presente! A qualidade é visível, parece muito mais caro do que é.',
    'Chegou super rápido, embalagem linda. O produto é exatamente como nas fotos.',
    'Atendimento excelente! Tive uma dúvida e responderam em minutos. Produto top!',
    'Qualidade absurda pelo preço. Já indiquei pra todos os meus amigos.',
    'Fiquei impressionada com o acabamento. Nota 10 em tudo!',
    'Comprei com receio mas chegou perfeito. Loja super confiável, já quero comprar mais!',
    'Entrega antes do prazo e produto original. Sem defeitos, zero reclamações.',
    'Virei fã da marca! Qualidade, preço e atendimento impecáveis.'
];

function fakeRating($id) {
    $ratings = [4.7, 4.8, 4.9, 5.0, 4.6, 4.8, 4.9, 5.0, 4.7, 4.8];
    return $ratings[$id % count($ratings)];
}
function fakeReviewCount($id) {
    return 47 + (($id * 17 + 23) % 453);
}
function fakeSoldCount($id) {
    return 120 + (($id * 31 + 11) % 880);
}
function renderStars($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $html = '';
    for ($i = 0; $i < $full; $i++) $html .= '<i class="fas fa-star"></i>';
    if ($half) $html .= '<i class="fas fa-star-half-alt"></i>';
    for ($i = $full + $half; $i < 5; $i++) $html .= '<i class="far fa-star"></i>';
    return $html;
}
$badges_pool = ['Mais Vendido','Top','Novo','Exclusivo','Limitado','Best Seller'];
?>

<style>
    /* ── Product Card ── */
    .product-card-new { background:#111; border:1px solid rgba(255,255,255,0.06); border-radius:16px; overflow:hidden; transition:all .4s cubic-bezier(.25,.46,.45,.94); display:flex; flex-direction:column; height:100%; }
    .product-card-new:hover { transform:translateY(-6px); border-color:rgba(255,255,255,0.15); box-shadow:0 20px 40px rgba(0,0,0,.5),0 0 30px rgba(255,255,255,.03); }
    .product-card-new .card-img { position:relative; aspect-ratio:1/1; overflow:hidden; background:#0a0a0a; }
    .product-card-new .card-img img { width:100%; height:100%; object-fit:cover; transition:transform .6s cubic-bezier(.25,.46,.45,.94); }
    .product-card-new:hover .card-img img { transform:scale(1.08); }
    .product-card-new .card-body { padding:14px 16px 18px; display:flex; flex-direction:column; flex:1; gap:4px; }
    .product-card-new .card-tag { font-size:.6rem; text-transform:uppercase; letter-spacing:1.5px; color:rgba(255,255,255,.4); font-weight:600; }
    .product-card-new .card-title { font-family:'Space Grotesk',sans-serif; font-size:.85rem; font-weight:600; color:#fff; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .product-card-new .card-price { font-family:'Syne',sans-serif; font-size:1.05rem; font-weight:700; color:#fff; margin-top:auto; }
    .product-card-new .card-badge { position:absolute; top:10px; left:10px; background:#fff; color:#000; font-size:.55rem; font-weight:800; text-transform:uppercase; letter-spacing:1px; padding:4px 10px; border-radius:20px; z-index:2; }
    .product-card-new .card-badge-hot { background:linear-gradient(135deg,#ff6b35,#f7931e); color:#fff; }
    .product-card-new .card-action { position:absolute; bottom:10px; right:10px; width:36px; height:36px; background:rgba(255,255,255,.1); backdrop-filter:blur(10px); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:.75rem; opacity:0; transform:translateY(8px); transition:all .3s ease; z-index:2; }
    .product-card-new:hover .card-action { opacity:1; transform:translateY(0); }
    .card-stars { color:#f59e0b; font-size:.6rem; display:flex; align-items:center; gap:4px; }
    .card-stars .count { color:rgba(255,255,255,.35); font-size:.6rem; font-weight:500; }
    .card-sold { font-size:.6rem; color:rgba(255,255,255,.3); }
    .card-installment { font-size:.65rem; color:rgba(255,255,255,.4); margin-top:2px; }

    /* ── Section styles ── */
    .section-divider { height:1px; background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent); }
    .glow-text { text-shadow:0 0 40px rgba(255,255,255,.1); }
    .swiper-slide { height:auto !important; }
    .img-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#0a0a0a,#151515); color:rgba(255,255,255,.15); font-size:2rem; }

    /* ── Category pills ── */
    .cat-pill { display:flex; flex-direction:column; align-items:center; gap:8px; text-decoration:none; transition:all .3s ease; flex-shrink:0; }
    .cat-pill:hover .cat-icon-circle { background:#fff; color:#000; transform:translateY(-2px); }
    .cat-icon-circle { width:56px; height:56px; border-radius:50%; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.1rem; transition:all .3s ease; }
    .cat-pill-name { font-size:.7rem; color:rgba(255,255,255,.6); font-weight:500; text-align:center; letter-spacing:.5px; }

    /* ── Trust bar ── */
    .trust-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:24px 20px; text-align:center; transition:all .3s ease; }
    .trust-card:hover { border-color:rgba(255,255,255,.12); background:rgba(255,255,255,.05); }

    /* ── CTA Banner ── */
    .cta-banner { background:linear-gradient(135deg,#111 0%,#1a1a1a 50%,#111 100%); border:1px solid rgba(255,255,255,.06); border-radius:20px; overflow:hidden; position:relative; }
    .cta-banner::before { content:''; position:absolute; top:-50%; right:-30%; width:400px; height:400px; background:radial-gradient(circle,rgba(255,255,255,.03) 0%,transparent 70%); pointer-events:none; }

    /* ── Stats counter ── */
    .stats-section { background:linear-gradient(180deg,transparent,rgba(255,255,255,.02),transparent); border-top:1px solid rgba(255,255,255,.05); border-bottom:1px solid rgba(255,255,255,.05); }
    .stat-number { font-family:'Syne',sans-serif; font-size:2rem; font-weight:800; color:#fff; line-height:1; }
    @media(min-width:768px){ .stat-number { font-size:2.5rem; } }

    /* ── Testimonials ── */
    .testimonial-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:16px; padding:24px; transition:all .3s ease; height:100%; display:flex; flex-direction:column; }
    .testimonial-card:hover { border-color:rgba(255,255,255,.12); background:rgba(255,255,255,.05); }
    .testimonial-stars { color:#f59e0b; font-size:.75rem; margin-bottom:12px; }
    .testimonial-text { color:rgba(255,255,255,.6); font-size:.85rem; line-height:1.6; flex:1; margin-bottom:16px; font-style:italic; }
    .testimonial-author { display:flex; align-items:center; gap:12px; }
    .testimonial-avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#333,#555); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:.8rem; flex-shrink:0; }
    .testimonial-name { font-size:.8rem; font-weight:600; color:#fff; }
    .testimonial-location { font-size:.65rem; color:rgba(255,255,255,.35); }
    .testimonial-verified { font-size:.6rem; color:#22c55e; display:flex; align-items:center; gap:4px; margin-top:2px; }

    /* ── Hero banner swiper ── */
    .hero-banner-swiper { width:100%; background:#000; min-height:320px; }
    .hero-banner-swiper .swiper-slide { height:auto !important; position:relative; cursor:pointer; }
    .hero-banner-swiper .swiper-slide a { display:block; width:100%; }
    .hero-banner-swiper .swiper-slide img { width:100%; height:auto; max-height:600px; object-fit:cover; object-position:center; display:block; }
    .hero-banner-swiper .swiper-pagination-bullet { background:rgba(255,255,255,.5); opacity:1; width:8px; height:8px; }
    .hero-banner-swiper .swiper-pagination-bullet-active { background:#fff; width:24px; border-radius:4px; }
    .hero-banner-swiper .swiper-button-next,
    .hero-banner-swiper .swiper-button-prev { color:#fff; background:rgba(0,0,0,.4); width:44px; height:44px; border-radius:50%; backdrop-filter:blur(4px); }
    .hero-banner-swiper .swiper-button-next::after,
    .hero-banner-swiper .swiper-button-prev::after { font-size:16px; font-weight:700; }
    @media(max-width:767px){ .banner-desktop-only{display:none!important} }
    @media(min-width:768px){ .banner-mobile-only{display:none!important} }
    .hero-fallback { background:linear-gradient(135deg,#000 0%,#111 40%,#0a0a0a 100%); min-height:60vh; display:flex; align-items:center; }

    /* ── Viewing now pulse ── */
    @keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    .live-dot { width:6px; height:6px; border-radius:50%; background:#22c55e; display:inline-block; animation:livePulse 1.5s infinite; }

    /* ── Brand logos ── */
    @keyframes scrollLogos { 0%{transform:translateX(0)} 100%{transform:translateX(-50%)} }
    .logos-track { display:flex; gap:48px; animation:scrollLogos 20s linear infinite; }
</style>

<!-- ══════════════════════════════════════════
     HERO BANNER
     ══════════════════════════════════════════ -->
<?php if (!empty($banners_principais)): ?>
<section class="hero-banner-swiper swiper" id="hero-slider">
    <div class="swiper-wrapper">
        <?php foreach ($banners_principais as $banner):
            $disp = $banner['dispositivo'] ?? 'todos';
            $extraClass = '';
            if ($disp === 'desktop') $extraClass = ' banner-desktop-only';
            elseif ($disp === 'mobile') $extraClass = ' banner-mobile-only';
        ?>
        <div class="swiper-slide<?= $extraClass ?>">
            <?php if (!empty($banner['link'])): ?>
            <a href="<?= htmlspecialchars($banner['link']) ?>">
            <?php endif; ?>
                <img src="<?= htmlspecialchars($banner['imagem']) ?>"
                     alt="<?= htmlspecialchars($banner['titulo'] ?? 'Banner') ?>"
                     loading="eager" />
            <?php if (!empty($banner['link'])): ?>
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($banners_principais) > 1): ?>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <?php endif; ?>
</section>
<?php else: ?>
<section class="hero-fallback">
    <div class="container mx-auto px-6">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 rounded-full px-4 py-1.5 mb-6">
                <span class="live-dot"></span>
                <span class="text-[11px] text-white/60 font-body font-medium"><?= rand(28,89) ?> pessoas comprando agora</span>
            </div>
            <h1 class="font-display text-5xl md:text-7xl font-extrabold text-white leading-tight mb-6 glow-text">
                Estilo &<br><span class="text-white/80">Cultura</span>
            </h1>
            <p class="text-white/50 text-base md:text-lg font-body max-w-md mb-10 leading-relaxed">
                Roupas, tênis, eletrônicos e produtos digitais com qualidade premium. Entrega para todo o Brasil.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="busca.php?todos=1" class="inline-flex items-center gap-3 bg-white text-black px-7 py-3.5 rounded-full font-semibold text-sm tracking-wide hover:bg-white/90 transition-all duration-300 font-body">
                    <i class="fas fa-shopping-bag text-xs"></i> Ver Catálogo
                </a>
            </div>
            <div class="flex items-center gap-6 mt-8">
                <div class="flex -space-x-2">
                    <?php for($a=0;$a<4;$a++): ?>
                    <div class="w-8 h-8 rounded-full border-2 border-black bg-gradient-to-br from-gray-600 to-gray-800 flex items-center justify-center text-[10px] text-white font-bold"><?= substr($nomes_review[$a],0,1) ?></div>
                    <?php endfor; ?>
                </div>
                <div>
                    <div class="text-[11px] text-yellow-500"><?= renderStars(4.9) ?></div>
                    <div class="text-[11px] text-white/40 font-body">+15.000 clientes satisfeitos</div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     SOCIAL PROOF STATS
     ══════════════════════════════════════════ -->
<section class="stats-section py-10" data-aos="fade-up" data-aos-duration="600">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <div class="stat-number" data-count="15847">0</div>
                <div class="text-[11px] text-white/40 font-body uppercase tracking-widest mt-1">Clientes Ativos</div>
            </div>
            <div>
                <div class="stat-number" data-count="52300">0</div>
                <div class="text-[11px] text-white/40 font-body uppercase tracking-widest mt-1">Pedidos Entregues</div>
            </div>
            <div>
                <div class="stat-number flex items-center justify-center gap-1"><span data-count="4">0</span>.<span data-count="9" class="stat-number">0</span><i class="fas fa-star text-yellow-500 text-lg ml-1"></i></div>
                <div class="text-[11px] text-white/40 font-body uppercase tracking-widest mt-1">Avaliação Média</div>
            </div>
            <div>
                <div class="stat-number" data-count="98">0</div>
                <div class="text-[11px] text-white/40 font-body uppercase tracking-widest mt-1">% Satisfação</div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     CATEGORY BAR
     ══════════════════════════════════════════ -->
<?php if (!empty($categorias)): ?>
<section class="py-8 border-b border-white/5" data-aos="fade-up">
    <div class="container mx-auto px-6">
        <div class="flex gap-6 overflow-x-auto pb-2" style="scrollbar-width:none;-ms-overflow-style:none;">
            <a href="busca.php?todos=1" class="cat-pill">
                <div class="cat-icon-circle"><i class="fas fa-fire"></i></div>
                <span class="cat-pill-name">Em Alta</span>
            </a>
            <?php
    $cat_icons = ['roupas'=>'fa-tshirt','roupa'=>'fa-tshirt','camiseta'=>'fa-tshirt','camisa'=>'fa-tshirt','tenis'=>'fa-shoe-prints','tênis'=>'fa-shoe-prints','sneakers'=>'fa-shoe-prints','calçado'=>'fa-shoe-prints','eletronico'=>'fa-laptop','eletrônico'=>'fa-laptop','digital'=>'fa-code','acessorio'=>'fa-gem','acessório'=>'fa-gem','bone'=>'fa-hat-cowboy','boné'=>'fa-hat-cowboy','conjunto'=>'fa-layer-group','relogio'=>'fa-clock','relógio'=>'fa-clock','bolsa'=>'fa-shopping-bag','joia'=>'fa-ring','fone'=>'fa-headphones','celular'=>'fa-mobile-alt','game'=>'fa-gamepad','jogo'=>'fa-gamepad','streaming'=>'fa-play','marketing'=>'fa-bullhorn','air'=>'fa-shoe-prints'];
    foreach ($categorias as $cat):
        $icon = 'fa-tag';
        $nome_lower = mb_strtolower($cat['nome']);
        foreach ($cat_icons as $key => $val) { if (strpos($nome_lower, $key) !== false) { $icon = $val; break; } }
?>
            <a href="categoria.php?id=<?= $cat['id']?>" class="cat-pill">
                <div class="cat-icon-circle"><i class="fas <?= $icon?>"></i></div>
                <span class="cat-pill-name"><?= htmlspecialchars($cat['nome'])?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     FEATURED PRODUCTS (Destaques)
     ══════════════════════════════════════════ -->
<?php if (!empty($destaques)): ?>
<section class="py-16" id="produtos">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between mb-10" data-aos="fade-up">
            <div>
                <span class="text-[10px] uppercase tracking-[3px] text-white/40 font-body block mb-2">Curadoria</span>
                <h2 class="font-display text-2xl md:text-3xl font-bold text-white">Destaques da Semana</h2>
            </div>
            <a href="busca.php?todos=1" class="text-white/50 hover:text-white text-xs font-medium tracking-wider uppercase transition-colors font-body flex items-center gap-2">
                Ver tudo <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
        <div class="swiper destaques-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($destaques as $idx => $produto):
                    $rating = fakeRating($produto['id']);
                    $reviews = fakeReviewCount($produto['id']);
                    $sold = fakeSoldCount($produto['id']);
                    $badge = $badges_pool[$produto['id'] % count($badges_pool)];
                    $isHot = ($idx < 4 || $sold > 500);
                ?>
                <div class="swiper-slide">
                    <a href="produto.php?id=<?= $produto['id']?>" class="product-card-new block">
                        <div class="card-img">
                            <?php if (!empty($produto['imagem']) && file_exists($produto['imagem'])): ?>
                            <img src="<?= htmlspecialchars($produto['imagem'])?>" alt="<?= htmlspecialchars($produto['nome'])?>" loading="lazy" />
                            <?php else: ?>
                            <div class="img-placeholder"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <div class="card-badge <?= $isHot ? 'card-badge-hot' : '' ?>"><?= $badge ?></div>
                            <div class="card-action"><i class="fas fa-arrow-right"></i></div>
                        </div>
                        <div class="card-body">
                            <div class="card-stars">
                                <?= renderStars($rating) ?>
                                <span class="count">(<?= $reviews ?>)</span>
                            </div>
                            <h3 class="card-title"><?= htmlspecialchars($produto['nome'])?></h3>
                            <span class="card-price"><?= formatarPreco($produto['preco'])?></span>
                            <span class="card-installment">ou 12x de <?= formatarPreco($produto['preco'] / 12) ?></span>
                            <span class="card-sold"><i class="fas fa-fire-alt" style="color:#f59e0b;"></i> <?= $sold ?>+ vendidos</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="section-divider mx-auto" style="max-width:80%;"></div>

<!-- ══════════════════════════════════════════
     TRUST BAR (moved up)
     ══════════════════════════════════════════ -->
<section class="py-14">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" data-aos="fade-up">
            <div class="trust-card">
                <div class="text-white/80 text-xl mb-3"><i class="fas fa-truck"></i></div>
                <div class="text-white font-semibold text-sm font-body mb-1">Frete Grátis</div>
                <div class="text-white/35 text-xs font-body leading-relaxed">Para todo o Brasil sem mínimo.</div>
            </div>
            <div class="trust-card">
                <div class="text-white/80 text-xl mb-3"><i class="fas fa-shield-alt"></i></div>
                <div class="text-white font-semibold text-sm font-body mb-1">Compra 100% Segura</div>
                <div class="text-white/35 text-xs font-body leading-relaxed">Criptografia SSL + antifraude.</div>
            </div>
            <div class="trust-card">
                <div class="text-white/80 text-xl mb-3"><i class="fas fa-undo"></i></div>
                <div class="text-white font-semibold text-sm font-body mb-1">30 Dias p/ Troca</div>
                <div class="text-white/35 text-xs font-body leading-relaxed">Primeira troca grátis garantida.</div>
            </div>
            <div class="trust-card">
                <div class="text-white/80 text-xl mb-3"><i class="fab fa-pix"></i></div>
                <div class="text-white font-semibold text-sm font-body mb-1">PIX com Desconto</div>
                <div class="text-white/35 text-xs font-body leading-relaxed">Pague via PIX e economize mais.</div>
            </div>
        </div>
    </div>
</section>

<div class="section-divider mx-auto" style="max-width:80%;"></div>

<!-- ══════════════════════════════════════════
     CTA BANNER
     ══════════════════════════════════════════ -->
<section class="py-16">
    <div class="container mx-auto px-6">
        <div class="cta-banner p-10 md:p-16" data-aos="fade-up">
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
                <div class="max-w-lg">
                    <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 rounded-full px-3 py-1 mb-4">
                        <span class="live-dot"></span>
                        <span class="text-[10px] text-white/60 font-body uppercase tracking-widest">Oferta por tempo limitado</span>
                    </div>
                    <h2 class="font-display text-3xl md:text-4xl font-bold text-white mb-4">Coleção Premium</h2>
                    <p class="text-white/45 font-body text-sm leading-relaxed mb-6">
                        Produtos selecionados com qualidade e estilo incomparáveis. Frete grátis + parcele em até 12x sem juros.
                    </p>
                    <a href="busca.php?todos=1" class="inline-flex items-center gap-3 bg-white text-black px-7 py-3 rounded-full font-semibold text-sm tracking-wide hover:bg-white/90 transition-all hover:scale-105 font-body">
                        Comprar Agora <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
                <div class="hidden md:flex items-center gap-8 text-center">
                    <div>
                        <div class="text-3xl font-display font-extrabold text-white">12x</div>
                        <div class="text-[10px] text-white/40 uppercase tracking-widest">Sem Juros</div>
                    </div>
                    <div class="w-px h-12 bg-white/10"></div>
                    <div>
                        <div class="text-3xl font-display font-extrabold text-white">24h</div>
                        <div class="text-[10px] text-white/40 uppercase tracking-widest">Envio Rápido</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="section-divider mx-auto" style="max-width:80%;"></div>

<!-- ══════════════════════════════════════════
     PRODUCTS BY CATEGORY
     ══════════════════════════════════════════ -->
<?php if (!empty($produtos_por_categoria)): ?>
<?php foreach ($produtos_por_categoria as $categoria_id => $produtos):
    $cat_name = "Categoria";
    foreach ($categorias as $c) { if ($c['id'] == $categoria_id) $cat_name = $c['nome']; }
?>
<section class="py-16">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between mb-10" data-aos="fade-up">
            <div>
                <span class="text-[10px] uppercase tracking-[3px] text-white/40 font-body block mb-2">Coleção</span>
                <h2 class="font-display text-2xl md:text-3xl font-bold text-white"><?= htmlspecialchars($cat_name)?></h2>
            </div>
            <a href="categoria.php?id=<?= $categoria_id?>" class="text-white/50 hover:text-white text-xs font-medium tracking-wider uppercase transition-colors font-body flex items-center gap-2">
                Ver tudo <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
        <div class="swiper produtos-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($produtos as $produto):
                    $rating = fakeRating($produto['id']);
                    $reviews = fakeReviewCount($produto['id']);
                    $sold = fakeSoldCount($produto['id']);
                ?>
                <div class="swiper-slide">
                    <a href="produto.php?id=<?= $produto['id']?>" class="product-card-new block">
                        <div class="card-img">
                            <?php if (!empty($produto['imagem']) && file_exists($produto['imagem'])): ?>
                            <img src="<?= htmlspecialchars($produto['imagem'])?>" alt="<?= htmlspecialchars($produto['nome'])?>" loading="lazy" />
                            <?php else: ?>
                            <div class="img-placeholder"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <?php if ($sold > 400): ?>
                            <div class="card-badge card-badge-hot">Mais Vendido</div>
                            <?php endif; ?>
                            <div class="card-action"><i class="fas fa-arrow-right"></i></div>
                        </div>
                        <div class="card-body">
                            <div class="card-stars">
                                <?= renderStars($rating) ?>
                                <span class="count">(<?= $reviews ?>)</span>
                            </div>
                            <h3 class="card-title"><?= htmlspecialchars($produto['nome'])?></h3>
                            <span class="card-price"><?= formatarPreco($produto['preco'])?></span>
                            <span class="card-installment">ou 12x de <?= formatarPreco($produto['preco'] / 12) ?></span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="section-divider mx-auto mt-16" style="max-width:60%;"></div>
</section>
<?php endforeach; ?>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     CUSTOMER TESTIMONIALS
     ══════════════════════════════════════════ -->
<section class="py-16">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12" data-aos="fade-up">
            <span class="text-[10px] uppercase tracking-[3px] text-white/40 font-body block mb-2">Avaliações Reais</span>
            <h2 class="font-display text-2xl md:text-3xl font-bold text-white mb-3">Nossos Clientes Aprovam</h2>
            <div class="flex items-center justify-center gap-2">
                <span class="text-yellow-500 text-sm"><?= renderStars(4.9) ?></span>
                <span class="text-white/50 text-sm font-body">4.9/5 baseado em +2.400 avaliações</span>
            </div>
        </div>
        <div class="swiper testimonials-swiper" data-aos="fade-up">
            <div class="swiper-wrapper">
                <?php for ($t = 0; $t < 8; $t++):
                    $nome = $nomes_review[$t % count($nomes_review)];
                    $cidade = $cidades_review[$t % count($cidades_review)];
                    $texto = $textos_review[$t % count($textos_review)];
                    $inicial = mb_substr($nome, 0, 1);
                ?>
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="testimonial-stars"><?= renderStars(($t % 2 == 0) ? 5.0 : 4.8) ?></div>
                        <p class="testimonial-text">"<?= $texto ?>"</p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar"><?= $inicial ?></div>
                            <div>
                                <div class="testimonial-name"><?= $nome ?></div>
                                <div class="testimonial-location"><?= $cidade ?></div>
                                <div class="testimonial-verified"><i class="fas fa-check-circle"></i> Compra verificada</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>

<div class="section-divider mx-auto" style="max-width:80%;"></div>

<!-- ══════════════════════════════════════════
     BRAND PROMISE
     ══════════════════════════════════════════ -->
<section class="py-16">
    <div class="container mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto" data-aos="fade-up">
            <span class="text-[10px] uppercase tracking-[3px] text-white/40 font-body block mb-4">Por que nos escolher</span>
            <h2 class="font-display text-2xl md:text-3xl font-bold text-white mb-6">Mais que uma Loja, uma Experiência</h2>
            <p class="text-white/40 font-body text-sm leading-relaxed mb-10">
                Na MACARIO BRAZIL, cada produto é cuidadosamente selecionado para garantir qualidade, autenticidade e satisfação total. Não vendemos apenas produtos — entregamos confiança.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" data-aos="fade-up">
            <div class="text-center p-8 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-white/10 transition-all">
                <div class="w-14 h-14 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-gem text-white/80 text-xl"></i>
                </div>
                <h3 class="font-display text-white font-bold mb-2">Qualidade Premium</h3>
                <p class="text-white/35 text-xs font-body leading-relaxed">Produtos selecionados com rigoroso controle de qualidade. Só trabalhamos com o melhor.</p>
            </div>
            <div class="text-center p-8 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-white/10 transition-all">
                <div class="w-14 h-14 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bolt text-white/80 text-xl"></i>
                </div>
                <h3 class="font-display text-white font-bold mb-2">Envio Ultrarrápido</h3>
                <p class="text-white/35 text-xs font-body leading-relaxed">Pedidos processados em até 24h. Rastreamento em tempo real disponível para todos os envios.</p>
            </div>
            <div class="text-center p-8 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-white/10 transition-all">
                <div class="w-14 h-14 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-white/80 text-xl"></i>
                </div>
                <h3 class="font-display text-white font-bold mb-2">Satisfação Garantida</h3>
                <p class="text-white/35 text-xs font-body leading-relaxed">Se não gostar, devolvemos seu dinheiro. Sem burocracia, sem letras miúdas.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     SCRIPTS
     ══════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // AOS
    AOS.init({ once:true, duration:600, easing:'ease-out-cubic', offset:0, delay:0 });
    setTimeout(function(){ document.querySelectorAll('[data-aos]').forEach(function(el){ el.style.opacity='1'; el.style.transform='none'; el.style.transition='none'; }); }, 2500);

    // Hero Slider
    if (typeof Swiper !== 'undefined' && document.querySelector('#hero-slider')) {
        new Swiper('#hero-slider', {
            slidesPerView:1, spaceBetween:0, loop:true,
            autoplay:{ delay:5000, disableOnInteraction:false },
            pagination:{ el:'#hero-slider .swiper-pagination', clickable:true },
            navigation:{ nextEl:'#hero-slider .swiper-button-next', prevEl:'#hero-slider .swiper-button-prev' },
            effect:'fade', fadeEffect:{ crossFade:true },
        });
    }

    // Product Swipers
    if (typeof Swiper !== 'undefined') {
        var cfg = {
            slidesPerView:1.4, spaceBetween:14, grabCursor:true,
            breakpoints:{ 480:{slidesPerView:2.3,spaceBetween:14}, 768:{slidesPerView:3.2,spaceBetween:18}, 1024:{slidesPerView:4.2,spaceBetween:20}, 1280:{slidesPerView:5,spaceBetween:22} }
        };
        if (document.querySelector('.destaques-swiper')) new Swiper('.destaques-swiper', cfg);
        document.querySelectorAll('.produtos-swiper').forEach(function(el){ new Swiper(el, cfg); });

        // Testimonials swiper
        if (document.querySelector('.testimonials-swiper')) {
            new Swiper('.testimonials-swiper', {
                slidesPerView:1.1, spaceBetween:16, grabCursor:true,
                autoplay:{ delay:4000, disableOnInteraction:false },
                breakpoints:{ 480:{slidesPerView:1.5}, 768:{slidesPerView:2.3}, 1024:{slidesPerView:3.2} }
            });
        }
    }

    // Counter animation
    var counters = document.querySelectorAll('[data-count]');
    var observed = false;
    function animateCounters() {
        if (observed) return;
        observed = true;
        counters.forEach(function(el) {
            var target = parseInt(el.getAttribute('data-count'));
            var duration = 2000;
            var start = 0;
            var startTime = null;
            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(eased * target).toLocaleString('pt-BR');
                if (progress < 1) requestAnimationFrame(step);
                else el.textContent = target.toLocaleString('pt-BR');
            }
            requestAnimationFrame(step);
        });
    }
    if ('IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function(entries){ entries.forEach(function(e){ if(e.isIntersecting) animateCounters(); }); }, {threshold:0.3});
        var statsEl = document.querySelector('.stats-section');
        if (statsEl) obs.observe(statsEl);
    } else { animateCounters(); }
});
</script>

<?php require_once 'templates/footer.php'; ?>