<?php
// admin/templates/header_admin.php - Header V2 (Macario Brazil Design System)
require_once dirname(dirname(__FILE__)) . '/../config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?php echo(isset($page_title) ? $page_title . ' - ' : ''); ?>Admin — MACARIO BRAZIL</title>
    <link rel="stylesheet" href="../assets/css/admin_macario.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'admin-primary': '#ffffff',
                        'admin-secondary': '#b3b3b3',
                        'admin-success': '#10B981',
                        'admin-warning': '#F59E0B',
                        'admin-danger': '#EF4444',
                        'admin-dark': '#000000',
                        'admin-gray': {
                            50: '#111111', 100: '#111111', 200: '#111111',
                            300: '#333333', 400: '#666666', 500: '#888888',
                            600: '#999999', 700: '#b3b3b3', 800: '#0a0a0a',
                            900: '#000000'
                        }
                    },
                    fontFamily: {
                        sans: ['Space Grotesk', 'sans-serif'],
                        display: ['Syne', 'sans-serif'],
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-admin-dark text-white antialiased">
    <div id="mobile-overlay" class="mobile-overlay fixed inset-0 bg-black/80 z-40 hidden transition-opacity opacity-0"></div>
    <div id="sidebar" class="admin-sidebar fixed top-0 left-0 h-full w-64 bg-admin-gray-800 border-r border-white/10 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-50 overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-end lg:hidden mb-4">
                <button id="close-menu-btn" class="text-white p-2"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="flex items-center gap-3 mb-10">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                    <img src="../472402418_460144646946890_6218335060120212885_n.jpg" alt="Logo" class="w-full h-full object-cover rounded-lg">
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white font-display uppercase tracking-wider">Admin</h1>
                    <p class="text-xs text-admin-gray-400 font-sans tracking-wide">MACARIO BRAZIL</p>
                </div>
            </div>
            <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
            <nav class="space-y-1">
                <a href="index.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'index.php' ? 'active' : ''); ?>">
                    <i class="fas fa-tachometer-alt w-5 text-center"></i><span>Dashboard</span>
                </a>
                <a href="gerenciar_produtos.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'gerenciar_produtos.php' ? 'active' : ''); ?>">
                    <i class="fas fa-box w-5 text-center"></i><span>Produtos</span>
                </a>
                <a href="gestao_midias.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'gestao_midias.php' ? 'active' : ''); ?>">
                    <i class="fas fa-images w-5 text-center"></i><span>Mídias</span>
                </a>
                <a href="gerenciar_categorias.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'gerenciar_categorias.php' ? 'active' : ''); ?>">
                    <i class="fas fa-tags w-5 text-center"></i><span>Categorias</span>
                </a>
                <a href="gerenciar_tamanhos.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'gerenciar_tamanhos.php' ? 'active' : ''); ?>">
                    <i class="fas fa-ruler-combined w-5 text-center"></i><span>Tamanhos</span>
                </a>
                <a href="gerenciar_banners.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'gerenciar_banners.php' ? 'active' : ''); ?>">
                    <i class="fas fa-image w-5 text-center"></i><span>Banners</span>
                </a>
                <a href="gerenciar_cupons.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'gerenciar_cupons.php' ? 'active' : ''); ?>">
                    <i class="fas fa-ticket-alt w-5 text-center"></i><span>Cupons</span>
                </a>
                <a href="pedidos.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'pedidos.php' ? 'active' : ''); ?>">
                    <i class="fas fa-shopping-bag w-5 text-center"></i><span>Pedidos</span>
                </a>
                <a href="carrinhos_abandonados.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'carrinhos_abandonados.php' ? 'active' : ''); ?>">
                    <i class="fas fa-shopping-cart w-5 text-center"></i><span>Carrinhos</span>
                </a>
                <a href="gerenciar_afiliados.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'gerenciar_afiliados.php' ? 'active' : ''); ?>">
                    <i class="fas fa-users-cog w-5 text-center"></i><span>Afiliados</span>
                </a>
                <a href="usuarios.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'usuarios.php' ? 'active' : ''); ?>">
                    <i class="fas fa-users w-5 text-center"></i><span>Clientes</span>
                </a>
                <a href="gerenciar_pagamentos.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'gerenciar_pagamentos.php' ? 'active' : ''); ?>">
                    <i class="fas fa-credit-card w-5 text-center"></i><span>Pagamentos</span>
                </a>
                <a href="gerenciar_pix.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?php echo($current_page == 'gerenciar_pix.php' ? 'active' : ''); ?>">
                    <i class="fas fa-qrcode w-5 text-center"></i><span>Gerenciar PIX</span>
                </a>
                <div class="pt-4 mt-4 border-t border-white/5">
                    <a href="../index.php" target="_blank" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl">
                        <i class="fas fa-external-link-alt w-5 text-center"></i><span>Ver Loja</span>
                    </a>
                    <a href="../logout.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-red-400">
                        <i class="fas fa-sign-out-alt w-5 text-center"></i><span>Sair</span>
                    </a>
                </div>
            </nav>
        </div>
    </div>
    <div id="main-wrapper" class="transition-all duration-300 lg:ml-64 flex flex-col min-h-screen">
        <header class="h-16 sticky top-0 bg-black/80 backdrop-blur-md border-b border-white/10 z-30 flex items-center justify-between px-4 lg:px-8">
            <div class="flex items-center gap-4">
                <h2 class="text-xl font-bold font-display uppercase tracking-wide text-white">
                    <?php echo(isset($page_title) ? $page_title : 'Painel'); ?>
                </h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden sm:block text-right">
                    <p class="text-sm font-medium text-white">
                        <?php echo htmlspecialchars(isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : 'Admin'); ?>
                    </p>
                    <p class="text-xs text-admin-gray-400 uppercase tracking-wider">Administrador</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center border border-white/20">
                    <i class="fas fa-user text-black font-bold"></i>
                </div>
            </div>
        </header>
        <main class="flex-1 p-4 lg:p-8 pb-24 lg:pb-8">
