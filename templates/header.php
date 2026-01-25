<?php
// templates/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Busca categorias do banco de dados para o menu
$categorias_menu = [];
try {
    // Verifica se $pdo está disponível (config.php deve ser carregado antes)
    if (!isset($pdo)) {
        // Tenta carregar config.php se não estiver carregado
        if (file_exists(__DIR__ . '/../config.php')) {
            require_once __DIR__ . '/../config.php';
        }
    }

    if (isset($pdo)) {
        // Busca apenas id e nome, ordena por ordem e depois por nome
        $stmt_categorias = $pdo->query("SELECT id, nome FROM categorias ORDER BY ordem ASC, nome ASC");
        $categorias_menu = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

        // Log para debug (apenas se não encontrar categorias)
        if (empty($categorias_menu)) {
            error_log("Nenhuma categoria encontrada no banco de dados para o menu");
        } else {
            error_log("Categorias encontradas para o menu: " . count($categorias_menu));
        }
    } else {
        error_log("PDO não está disponível no header.php para buscar categorias");
    }
} catch (PDOException $e) {
    error_log("Erro PDO ao buscar categorias para o menu: " . $e->getMessage());
    $categorias_menu = [];
} catch (Exception $e) {
    error_log("Erro geral ao buscar categorias para o menu: " . $e->getMessage());
    $categorias_menu = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

    <!-- SEO Meta Tags -->
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Minha Loja | O Mercado é dos Tubarões</title>
    <meta name="description"
        content="<?= isset($page_description) ? $page_description : 'Produtos para vários jogos: entrega rápida, segurança e suporte.' ?>" />
    <meta name="keywords"
        content="<?= isset($page_keywords) ? $page_keywords : 'loja online, produtos, compras, e-commerce, qualidade, preços baixos' ?>" />
    <meta name="author" content="Minha Loja" />
    <meta name="robots" content="index, follow" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" />
    <meta property="og:title"
        content="<?= isset($page_title) ? $page_title . ' - ' : '' ?>Minha Loja - O Mercado é dos Tubarões" />
    <meta property="og:description"
        content="<?= isset($page_description) ? $page_description : 'Descubra produtos incríveis na nossa loja online. Qualidade, preços competitivos e entrega rápida.' ?>" />
    <meta property="og:image"
        content="<?= isset($page_image) ? $page_image : 'https://i.ibb.co/xq66KBdr/Design-sem-nome-4.png' ?>" />
    <meta property="og:site_name" content="Minha Loja" />
    <meta property="og:locale" content="pt_BR" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" />
    <meta property="twitter:title"
        content="<?= isset($page_title) ? $page_title . ' - ' : '' ?>Minha Loja - O Mercado é dos Tubarões" />
    <meta property="twitter:description"
        content="<?= isset($page_description) ? $page_description : 'Descubra produtos incríveis na nossa loja online. Qualidade, preços competitivos e entrega rápida.' ?>" />
    <meta property="twitter:image"
        content="<?= isset($page_image) ? $page_image : 'https://i.ibb.co/xq66KBdr/Design-sem-nome-4.png' ?>" />

    <!-- Favicon -->
    <!-- Favicon -->
    <link rel="icon" href="https://i.ibb.co/xq66KBdr/Design-sem-nome-4.png" type="image/png">
    <link rel="apple-touch-icon" href="https://i.ibb.co/xq66KBdr/Design-sem-nome-4.png">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" />

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Minha Loja",
        "url": "<?= 'https://' . $_SERVER['HTTP_HOST'] ?>",
        "logo": "https://i.ibb.co/xq66KBdr/Design-sem-nome-4.png",
        "description": "Loja online com produtos de qualidade e preços competitivos",
        "sameAs": [
            "https://www.instagram.com/minhaloja",
            "https://www.youtube.com/minhaloja"
        ]
    }
    </script>

    <!-- Fonts Otimizadas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Fonts Otimizadas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&family=Syncopate:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- CSS Libraries Essenciais -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- CSS de Temas -->
    <link rel="stylesheet" href="assets/css/themes.css" />

    <!-- CSS Global Vermelho e Preto -->
    <link rel="stylesheet" href="assets/css/global-red-black.css" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- JavaScript Otimizado -->
    <script src="https://unpkg.com/scrollreveal@4.0.9/dist/scrollreveal.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-red': '#DC2626',
                        'brand-red-dark': '#B91C1C',
                        'brand-red-light': '#EF4444',
                        'brand-pink': '#ff6666',
                        'brand-purple': '#ff0000',
                        'brand-blue': '#ff0000',
                        'brand-cyan': '#ff0000',
                        'brand-black': '#111111',
                        'ff-orange': '#FF9900',
                        'ff-black': '#111111',
                        'ff-gray': '#1F1F1F',
                        'brand-gray': {
                            50: '#ffffff',
                            100: '#f5f5f5',
                            200: '#e5e5e5',
                            300: '#d4d4d4',
                            400: '#a3a3a3',
                            500: '#737373',
                            600: '#525252',
                            700: '#404040',
                            800: '#262626',
                            900: '#171717',
                            DEFAULT: '#111111',
                            light: '#1F1F1F',
                            text: '#cccccc'
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        heading: ['Syncopate', 'sans-serif'],
                        display: ['Syncopate', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'scale-in': 'scaleIn 0.4s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.9)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' }
                        }
                    },
                    backdropBlur: {
                        xs: '2px',
                    }
                }
            }
        }
    </script>

    <style>
        /* Reset e Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #000000;
            overflow-x: hidden;
            color: #ffffff;
            line-height: 1.6;
        }

        /* Background com Efeitos Dopaminérgicos */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
            animation: backgroundPulse 8s ease-in-out infinite;
        }

        @keyframes backgroundPulse {

            0%,
            100% {
                background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
            }

            50% {
                background: linear-gradient(135deg, #1a0000 0%, #000000 50%, #1a0000 100%);
            }
        }

        /* Cursor Glow Dopaminérgico */
        #cursor-glow {
            position: fixed;
            left: 0;
            top: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 0, 0, 0.2) 0%, rgba(255, 0, 0, 0.1) 30%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: -1;
            transition: all 0.3s ease;
            animation: cursorGlow 2s ease-in-out infinite alternate;
        }

        @keyframes cursorGlow {
            from {
                background: radial-gradient(circle, rgba(255, 0, 0, 0.2) 0%, rgba(255, 0, 0, 0.1) 30%, transparent 70%);
            }

            to {
                background: radial-gradient(circle, rgba(255, 0, 0, 0.3) 0%, rgba(255, 0, 0, 0.15) 30%, transparent 70%);
            }
        }

        /* Navbar Glassmorphism Dopaminérgico */
        .frosted-glass-nav {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 0, 0, 0.2);
            box-shadow: 0 8px 32px rgba(255, 0, 0, 0.1);
            position: relative;
        }

        .frosted-glass-nav::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 0, 0, 0.05), transparent);
            animation: navbarShimmer 3s ease-in-out infinite;
        }

        @keyframes navbarShimmer {

            0%,
            100% {
                transform: translateX(-100%);
            }

            50% {
                transform: translateX(100%);
            }
        }

        /* Títulos com Gradiente Vermelho Animado */
        .gradient-title {
            background: linear-gradient(135deg, #ff0000, #ff3333, #ff6666, #ff0000);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
            animation: gradient-shift 3s ease infinite;
            text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
        }

        @keyframes gradient-shift {

            0%,
            100% {
                background-position: 0% 50%;
                text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
            }

            50% {
                background-position: 100% 50%;
                text-shadow: 0 0 30px rgba(255, 0, 0, 0.6);
            }
        }

        /* Cards com Glassmorphism Dopaminérgico */
        .glass-card {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 0, 0, 0.2);
            box-shadow: 0 8px 32px rgba(255, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 0, 0, 0.1), transparent);
            transition: left 0.6s;
        }

        .glass-card:hover::before {
            left: 100%;
        }

        /* Swiper Customizado Dopaminérgico */
        .swiper-button-next,
        .swiper-button-prev {
            color: #fff;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            border: 1px solid rgba(255, 0, 0, 0.3);
            box-shadow: 0 4px 15px rgba(255, 0, 0, 0.2);
        }

        .swiper:hover .swiper-button-next,
        .swiper:hover .swiper-button-prev {
            opacity: 1;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: linear-gradient(45deg, #ff0000, #ff3333);
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(255, 0, 0, 0.4);
        }

        .swiper-pagination-bullet-active {
            background: linear-gradient(45deg, #ff0000, #ff3333);
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }

        /* Menu Lateral Animado */
        #side-menu-panel .menu-item {
            opacity: 0;
            transform: translateX(-30px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #side-menu-panel.is-open .menu-item {
            opacity: 1;
            transform: translateX(0);
        }

        #side-menu-panel.is-open .menu-item:nth-child(1) {
            transition-delay: 0.1s;
        }

        #side-menu-panel.is-open .menu-item:nth-child(2) {
            transition-delay: 0.15s;
        }

        #side-menu-panel.is-open .menu-item:nth-child(3) {
            transition-delay: 0.2s;
        }

        #side-menu-panel.is-open .menu-item:nth-child(4) {
            transition-delay: 0.25s;
        }

        /* Scrollbar Personalizada Dopaminérgica */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #000000;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ff0000, #ff3333);
            border-radius: 4px;
            box-shadow: 0 0 5px rgba(255, 0, 0, 0.3);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #ff3333, #ff0000);
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }

        /* Lazy Loading */
        img[loading="lazy"] {
            opacity: 0;
            transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        img[loading="lazy"].loaded {
            opacity: 1;
        }

        /* Responsividade */
        @media (max-width: 640px) {

            .swiper-button-next,
            .swiper-button-prev {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-brand-black text-brand-gray-text antialiased">
    <div id="cursor-glow"></div>
    <nav id="main-nav" class="fixed top-0 left-0 w-full transition-colors duration-300 z-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
            <!-- Desktop Layout -->
            <div class="hidden md:flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <a href="index.php" class="flex items-center">
                            <img src="https://i.ibb.co/xq66KBdr/Design-sem-nome-4.png" alt="Minha Loja"
                                class="h-6 lg:h-8 object-contain" />
                        </a>
                    </div>

                    <!-- Menu de Categorias Desktop -->
                    <?php if (!empty($categorias_menu)): ?>
                        <div class="relative group">
                            <button
                                class="flex items-center gap-2 text-white hover:text-brand-red transition-colors px-3 py-2">
                                <span class="font-medium">Categorias</span>
                                <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div
                                class="absolute top-full left-0 mt-2 w-64 bg-brand-black border border-brand-gray-light rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                <div class="py-2">
                                    <?php foreach ($categorias_menu as $categoria): ?>
                                        <a href="categoria.php?id=<?= $categoria['id'] ?>"
                                            class="block px-4 py-2 text-white hover:bg-brand-gray-light hover:text-brand-red transition-colors">
                                            <?= htmlspecialchars($categoria['nome']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex-1 justify-center px-8">
                    <form action="busca.php" method="GET" class="w-full max-w-lg mx-auto">
                        <div class="relative">
                            <input type="search" name="termo" placeholder="Buscar por produtos..."
                                class="w-full bg-brand-gray/50 border border-brand-gray-light text-white rounded-full py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-brand-red transition-all duration-300">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <?php $total_itens_carrinho = isset($_SESSION['carrinho']) ? count($_SESSION['carrinho']) : 0; ?>
                    <a href="carrinho.php" class="hover:text-brand-red transition-colors relative p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <?php if ($total_itens_carrinho > 0): ?>
                            <span id="cart-count"
                                class="absolute -top-1 -right-1 bg-brand-red text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold">
                                <?= $total_itens_carrinho ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="minha_conta.php" class="text-white hover:text-brand-red transition-colors p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                        <a href="afiliado_dashboard.php" class="text-white hover:text-brand-red transition-colors p-2"
                            title="Dashboard do Afiliado">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <a href="login.php"
                            class="bg-brand-red px-4 py-2 rounded-md text-white text-sm font-semibold hover:bg-brand-red-dark transition-colors">
                            Entrar
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Layout -->
            <div class="md:hidden flex justify-between items-center">
                <button id="menu-btn" aria-label="Abrir menu" class="p-2 -ml-2">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <a href="index.php" class="flex items-center">
                    <img src="https://i.ibb.co/xq66KBdr/Design-sem-nome-4.png" alt="Minha Loja"
                        class="h-6 object-contain" />
                </a>

                <div class="flex items-center gap-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="afiliado_dashboard.php" class="text-white hover:text-brand-red transition-colors p-2"
                            title="Dashboard do Afiliado">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </a>
                    <?php endif; ?>

                    <?php if ($total_itens_carrinho > 0): ?>
                        <a href="carrinho.php" class="relative p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span id="cart-count-mobile"
                                class="absolute -top-1 -right-1 bg-brand-red text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold">
                                <?= $total_itens_carrinho ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <a href="carrinho.php" class="p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Search Bar -->
            <div class="md:hidden mt-4">
                <form action="busca.php" method="GET">
                    <div class="relative">
                        <input type="search" name="termo" placeholder="Buscar produtos..."
                            class="w-full bg-brand-gray/50 border border-brand-gray-light text-white rounded-lg py-3 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-brand-red">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </nav>
    <div id="side-menu-container" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div id="side-menu-overlay" class="absolute inset-0 bg-black/70 opacity-0 transition-opacity duration-300">
        </div>
        <div id="side-menu-panel"
            class="relative h-full w-4/5 max-w-xs bg-brand-black p-6 flex flex-col transition-transform duration-300 ease-in-out -translate-x-full border-r border-brand-gray-light">
            <div class="flex justify-between items-center mb-10"><a href="index.php"><img
                        src="https://i.ibb.co/xq66KBdr/Design-sem-nome-4.png" alt="Minha Loja"
                        class="h-6 object-contain" /></a><button id="close-menu-btn" aria-label="Fechar menu"
                    class="p-2 text-gray-500 hover:text-white transition-colors"><svg class="w-6 h-6" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button></div>
            <nav class="flex-grow">
                <ul class="flex flex-col gap-2 font-semibold text-lg">
                    <!-- Link para Todos os Produtos -->
                    <li class="menu-item">
                        <a href="busca.php?todos=1"
                            class="menu-link block p-3 rounded-md text-white hover:bg-brand-gray-light hover:text-brand-red transition-colors">
                            <i class="fas fa-th mr-2"></i>
                            Todos os Produtos
                        </a>
                    </li>

                    <!-- Categorias no Menu Mobile -->
                    <?php if (!empty($categorias_menu)): ?>
                        <li class="menu-item">
                            <div
                                class="px-3 py-2 text-brand-gray-text text-sm font-semibold uppercase tracking-wider border-t border-brand-gray-light pt-4 mt-2">
                                Categorias
                            </div>
                        </li>
                        <?php foreach ($categorias_menu as $categoria): ?>
                            <li class="menu-item">
                                <a href="categoria.php?id=<?= $categoria['id'] ?>"
                                    class="menu-link block p-3 rounded-md text-white hover:bg-brand-gray-light hover:text-brand-red transition-colors">
                                    <i class="fas fa-tag mr-2 text-brand-red"></i>
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="menu-item">
                            <div class="px-3 py-2 text-brand-gray-text text-sm">
                                Nenhuma categoria disponível
                            </div>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="menu-item border-t border-brand-gray-light pt-4 mt-2">
                            <a href="afiliado_dashboard.php"
                                class="menu-link block p-3 rounded-md text-white hover:bg-brand-gray-light hover:text-brand-red transition-colors">
                                <i class="fas fa-chart-line mr-2"></i>
                                Dashboard do Afiliado
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="mt-auto pt-6 border-t border-brand-gray-light">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="menu-item">
                        <p class="text-brand-gray-text mb-2 text-sm">Olá, <?= htmlspecialchars($_SESSION['user_nome']) ?>!
                        </p><a href="logout.php"
                            class="block w-full text-center bg-brand-gray-light px-4 py-3 rounded-md text-white font-semibold hover:bg-brand-red transition-colors">Sair</a>
                    </div><?php else: ?>
                    <div class="menu-item"><a href="login.php"
                            class="block w-full text-center bg-brand-red px-4 py-3 rounded-md text-white font-semibold hover:bg-brand-red-dark transition-colors">Entrar
                            / Criar Conta</a></div><?php endif; ?>
                <div class="mt-8 text-center menu-item">
                    <p class="text-sm text-brand-gray-text mb-4">Siga-nos</p>
                    <div class="flex items-center justify-center space-x-6">
                        <a href="#" target="_blank" class="text-gray-500 hover:text-white transition-colors"
                            aria-label="Instagram"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664 4.771 4.919 4.919C8.416 2.175 8.796 2.163 12 2.163zm0 1.802C9.042 3.965 8.769 3.975 7.482 4.026c-2.903.134-4.045 1.273-4.178 4.177-.052 1.282-.062 1.555-.062 4.797s.01 3.515.062 4.797c.133 2.904 1.274 4.044 4.178 4.177 1.282.051 1.555.062 4.797.062s3.515-.01 4.797-.062c2.904-.133 4.045-1.273 4.178-4.177.052-1.282.062-1.555.062-4.797s-.01-3.515-.062-4.797c-.133-2.904-1.274-4.044-4.178-4.177-1.282-.052-1.555-.062-4.797-.062zm0 4.638c-2.403 0-4.36 1.957-4.36 4.36s1.957 4.36 4.36 4.36 4.36-1.957 4.36-4.36-1.957-4.36-4.36-4.36zm0 7.162c-1.548 0-2.802-1.254-2.802-2.802s1.254-2.802 2.802-2.802 2.802 1.254 2.802 2.802-1.254 2.802-2.802 2.802zm4.965-7.734c-.786 0-1.425.639-1.425 1.425s.639 1.425 1.425 1.425 1.425-.639 1.425-1.425-.639-1.425-1.425-1.425z" />
                            </svg></a>
                        <a href="#" target="_blank" class="text-gray-500 hover:text-white transition-colors"
                            aria-label="YouTube"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M21.582,6.186c-0.23-0.86-0.908-1.538-1.768-1.768C18.267,4,12,4,12,4S5.733,4,4.186,4.418 c-0.86,0.23-1.538,0.908-1.768,1.768C2,7.733,2,12,2,12s0,4.267,0.418,5.814c0.23,0.86,0.908,1.538,1.768,1.768 C5.733,20,12,20,12,20s6.267,0,7.814-0.418c0.861-0.23,1.538-0.908,1.768-1.768C22,16.267,22,12,22,12S22,7.733,21.582,6.186z M10,15.464V8.536L16,12L10,15.464z" />
                            </svg></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts Otimizados -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="assets/js/lightweight.js"></script>
    <script src="assets/js/carousel-fix.js"></script>
    <script src="assets/js/theme-manager.js"></script>

    <main>