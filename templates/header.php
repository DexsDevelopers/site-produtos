<?php
// templates/header.php — MACARIO BRAZIL
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Busca categorias do banco de dados para o menu
$categorias_menu = [];
try {
    if (!isset($pdo)) {
        if (file_exists(__DIR__ . '/../config.php')) {
            require_once __DIR__ . '/../config.php';
        }
    }

    if (isset($pdo)) {
        $stmt_categorias = $pdo->query("SELECT id, nome FROM categorias ORDER BY ordem ASC, nome ASC");
        $categorias_menu = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
    }
}
catch (Exception $e) {
    error_log("Erro ao buscar categorias: " . $e->getMessage());
    $categorias_menu = [];
}

$total_itens_carrinho = isset($_SESSION['carrinho']) ? count($_SESSION['carrinho']) : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />

    <!-- SEO -->
    <title>
        <?= isset($page_title) ? $page_title . ' | ' : ''?>MACARIO BRAZIL — Estilo & Cultura
    </title>
    <meta name="description"
        content="<?= isset($page_description) ? $page_description : 'MACARIO BRAZIL — Roupas, tênis, eletrônicos e produtos digitais. Estilo premium com entrega para todo o Brasil.'?>" />
    <meta name="keywords"
        content="<?= isset($page_keywords) ? $page_keywords : 'macario brazil, roupas, tenis, sneakers, eletrônicos, produtos digitais, e-commerce, moda, streetwear'?>" />
    <meta name="author" content="MACARIO BRAZIL" />
    <meta name="robots" content="index, follow" />

    <!-- Open Graph -->
    <meta property="og:type" content="website" />
    <meta property="og:url"
        content="<?='https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/')?>" />
    <meta property="og:title" content="<?= isset($page_title) ? $page_title . ' | ' : ''?>MACARIO BRAZIL" />
    <meta property="og:description"
        content="<?= isset($page_description) ? $page_description : 'Roupas, tênis, eletrônicos e produtos digitais. Estilo premium.'?>" />
    <meta property="og:image"
        content="<?= isset($page_image) ? $page_image : '472402418_460144646946890_6218335060120212885_n.jpg'?>" />
    <meta property="og:site_name" content="MACARIO BRAZIL" />
    <meta property="og:locale" content="pt_BR" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />

    <!-- Favicon -->
    <link rel="icon" href="472402418_460144646946890_6218335060120212885_n.jpg" type="image/jpeg">
    <link rel="apple-touch-icon" href="472402418_460144646946890_6218335060120212885_n.jpg">

    <!-- Canonical -->
    <link rel="canonical"
        href="<?='https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/')?>" />

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "MACARIO BRAZIL",
        "url": "<?='https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')?>",
        "logo": "472402418_460144646946890_6218335060120212885_n.jpg",
        "description": "Roupas, tênis, eletrônicos e produtos digitais premium"
    }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#ffffff', dark: '#000000' },
                    },
                    fontFamily: {
                        display: ['Syne', 'sans-serif'],
                        body: ['Space Grotesk', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- AOS Animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Design System -->
    <link rel="stylesheet" href="assets/css/macario.css?v=3" />
</head>

<body>
    <!-- ── Announcement Bar ── -->
    <div class="announcement-bar" id="announcement-bar">
        <div class="announcement-track">
            <div class="announcement-slide">
                <i class="fas fa-truck"></i>
                <span>FRETE GRÁTIS PARA TODO O BRASIL</span>
            </div>
            <div class="announcement-slide">
                <i class="fas fa-credit-card"></i>
                <span>PARCELE EM ATÉ 12X SEM JUROS</span>
            </div>
            <div class="announcement-slide">
                <i class="fas fa-shield-alt"></i>
                <span>COMPRA 100% SEGURA</span>
            </div>
        </div>
    </div>

    <!-- ── Navigation ── -->
    <nav class="nav-masario" id="main-nav">
        <div class="container">
            <div class="nav-inner">
                <!-- Hamburger (Mobile) -->
                <button class="nav-hamburger" id="menu-btn" aria-label="Abrir menu">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Logo -->
                <a href="index.php" class="nav-logo">
                    <img src="472402418_460144646946890_6218335060120212885_n.jpg" alt="MACARIO BRAZIL" />
                </a>

                <!-- Desktop Menu -->
                <div class="nav-menu">
                    <a href="index.php" class="nav-link active">Início</a>
                    <a href="busca.php?todos=1" class="nav-link">Catálogo</a>
                    <?php if (!empty($categorias_menu)): ?>
                    <div class="nav-dropdown">
                        <button class="nav-link nav-dropdown-trigger">
                            Categorias
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                style="margin-left:4px">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="nav-dropdown-menu">
                            <?php foreach ($categorias_menu as $categoria): ?>
                            <a href="categoria.php?id=<?= $categoria['id']?>" class="nav-dropdown-item">
                                <?= htmlspecialchars($categoria['nome'])?>
                            </a>
                            <?php
    endforeach; ?>
                        </div>
                    </div>
                    <?php
endif; ?>
                    <a href="suporte.php" class="nav-link">Contato</a>
                </div>

                <!-- Search Bar (Desktop) -->
                <div class="nav-search">
                    <form action="busca.php" method="GET" class="search-wrapper">
                        <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="search" name="termo" class="search-input" placeholder="Buscar produtos..." />
                    </form>
                </div>

                <!-- Actions -->
                <div class="nav-actions">
                    <!-- Cart -->
                    <a href="carrinho.php" class="nav-action-btn" aria-label="Carrinho">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <?php if ($total_itens_carrinho > 0): ?>
                        <span class="cart-badge" id="cart-count">
                            <?= $total_itens_carrinho?>
                        </span>
                        <?php
endif; ?>
                    </a>

                    <!-- User -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="minha_conta.php" class="nav-action-btn" aria-label="Minha conta">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                    <?php
else: ?>
                    <a href="login.php" class="nav-login-btn">Entrar</a>
                    <?php
endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- ── Mobile Side Menu ── -->
    <div class="mobile-menu-overlay" id="mobile-overlay"></div>
    <div class="mobile-menu-panel" id="mobile-panel">
        <div class="mobile-menu-header">
            <a href="index.php">
                <img src="472402418_460144646946890_6218335060120212885_n.jpg" alt="MACARIO BRAZIL" height="24" />
            </a>
            <button class="mobile-menu-close" id="close-menu-btn" aria-label="Fechar menu">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Search -->
        <div class="mobile-search">
            <form action="busca.php" method="GET" class="search-wrapper">
                <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="search" name="termo" class="search-input" placeholder="Buscar produtos..." />
            </form>
        </div>

        <nav class="mobile-menu-nav">
            <div class="mobile-menu-section">
                <a href="index.php" class="mobile-menu-link"><i class="fas fa-home"></i> Início</a>
                <a href="busca.php?todos=1" class="mobile-menu-link"><i class="fas fa-th"></i> Catálogo</a>
                <a href="carrinho.php" class="mobile-menu-link">
                    <i class="fas fa-shopping-bag"></i> Carrinho
                    <?php if ($total_itens_carrinho > 0): ?>
                    <span
                        style="background:#fff;color:#000;font-size:0.65rem;padding:2px 7px;border-radius:20px;margin-left:auto;font-weight:700;">
                        <?= $total_itens_carrinho?>
                    </span>
                    <?php
endif; ?>
                </a>
            </div>

            <?php if (!empty($categorias_menu)): ?>
            <div class="mobile-menu-section">
                <div class="mobile-menu-label">Categorias</div>
                <?php foreach ($categorias_menu as $categoria): ?>
                <a href="categoria.php?id=<?= $categoria['id']?>" class="mobile-menu-link">
                    <i class="fas fa-tag"></i>
                    <?= htmlspecialchars($categoria['nome'])?>
                </a>
                <?php
    endforeach; ?>
            </div>
            <?php
endif; ?>

            <div class="mobile-menu-section">
                <div class="mobile-menu-label">Ajuda</div>
                <a href="suporte.php" class="mobile-menu-link"><i class="fas fa-headset"></i> Contato</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="minha_conta.php" class="mobile-menu-link"><i class="fas fa-user"></i> Minha Conta</a>
                <a href="afiliado_dashboard.php" class="mobile-menu-link"><i class="fas fa-chart-line"></i>
                    Afiliados</a>
                <?php
endif; ?>
            </div>
        </nav>

        <div class="mobile-menu-footer">
            <?php if (isset($_SESSION['user_id'])): ?>
            <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:12px;">Olá,
                <?= htmlspecialchars($_SESSION['user_nome'])?>!
            </p>
            <a href="logout.php" class="btn btn-outline" style="width:100%;text-align:center;">Sair</a>
            <?php
else: ?>
            <a href="login.php" class="btn btn-primary" style="width:100%;text-align:center;">Entrar / Criar Conta</a>
            <?php
endif; ?>
        </div>
    </div>

    <!-- Spacer for fixed nav -->
    <div class="nav-spacer"></div>

    <main>