<?php
// admin/templates/header_admin.php - Header V2 (Macario Brazil Design System)
require_once dirname(__DIR__) . '/../config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>
        <?= isset($page_title) ? $page_title . ' - ' : ''?>Admin — MACARIO BRAZIL
    </title>

    <!-- CSS Customizado (Macario Design System) -->
    <link rel="stylesheet" href="../assets/css/admin_macario.css?v=<?= time()?>">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css" />

    <!-- Tailwind CSS (Utilizado apenas para estrutura de layout, cores sobrescritas pelo admin_macario.css) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'admin-primary': '#ffffff', /* Branco agora é a cor primária */
                        'admin-secondary': '#b3b3b3',
                        'admin-success': '#10B981',
                        'admin-warning': '#F59E0B',
                        'admin-danger': '#EF4444',
                        'admin-dark': '#000000',
                        'admin-gray': {
                            50: '#111111',
                            100: '#111111',
                            200: '#111111',
                            300: '#333333',
                            400: '#666666',
                            500: '#888888',
                            600: '#999999',
                            700: '#b3b3b3',
                            800: '#0a0a0a', /* Backgrounds de cards */
                            900: '#000000'
                        }
                    },
                    fontFamily: {
                        sans: ['Space Grotesk', 'sans-serif'],
                        display: ['Syne', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        /* Ajustes finos que o CSS externo não pegou ou específicos do layout inline */
        .admin-sidebar {
            z-index: 1000;
        }
    </style>
</head>

<body class="bg-admin-dark text-white antialiased">
    <!-- Mobile Menu Overlay -->
    <div id="mobile-overlay" class="mobile-overlay fixed inset-0 bg-black/80 z-40 hidden transition-opacity opacity-0">
    </div>

    <!-- Sidebar -->
    <div id="sidebar"
        class="admin-sidebar fixed top-0 left-0 h-full w-64 bg-admin-gray-800 border-r border-white/10 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-50 overflow-y-auto">
        <div class="p-6">
            <!-- Close Button (Mobile Only) -->
            <div class="flex justify-end lg:hidden mb-4">
                <button id="close-menu-btn" class="text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Logo -->
            <div class="flex items-center gap-3 mb-10">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                    <img src="../472402418_460144646946890_6218335060120212885_n.jpg" alt="Logo"
                        class="w-full h-full object-cover rounded-lg">
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white font-display uppercase tracking-wider">Admin</h1>
                    <p class="text-xs text-admin-gray-400 font-sans tracking-wide">MACARIO BRAZIL</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="space-y-1">
                <a href="index.php"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''?>">
                    <i class="fas fa-tachometer-alt w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <a href="gerenciar_produtos.php"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_produtos.php' ? 'active' : ''?>">
                    <i class="fas fa-box w-5 text-center"></i>
                    <span>Produtos</span>
                </a>

                <a href="adicionar_produto.php"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'adicionar_produto.php' ? 'active' : ''?>">
                    <i class="fas fa-plus-circle w-5 text-center"></i>
                    <span>Adicionar Produto</span>
                </a>

                <a href="gerenciar_categorias.php"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_categorias.php' ? 'active' : ''?>">
                    <i class="fas fa-tags w-5 text-center"></i>
                    <span>Categorias</span>
                </a>

                <a href="gerenciar_tamanhos.php"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_tamanhos.php' ? 'active' : ''?>">
                    <i class="fas fa-ruler-combined w-5 text-center"></i>
                    <span>Tamanhos</span>
                </a>

                <a href="gerenciar_banners.php"
                    class="flex items-center gap-3 text-admin-gray-400 hover:text-white hover:bg-white/5 px-4 py-3 rounded-xl transition-all group <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_banners.php' ? 'active text-white bg-white/5' : ''?>">
                    <i class="fas fa-image w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Banners</span>
                </a>

                <a href="pedidos.php"
                    class="flex items-center gap-3 text-admin-gray-400 hover:text-white hover:bg-white/5 px-4 py-3 rounded-xl transition-all group <?= basename($_SERVER['PHP_SELF']) == 'pedidos.php' ? 'active text-white bg-white/5' : ''?>">
                    <i class="fas fa-shopping-bag w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Pedidos</span>
                </a>

                <a href="usuarios.php"
                    class="flex items-center gap-3 text-admin-gray-400 hover:text-white hover:bg-white/5 px-4 py-3 rounded-xl transition-all group <?= basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'active text-white bg-white/5' : ''?>">
                    <i class="fas fa-users w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Clientes</span>
                </a>

                <a href="carrinhos_abandonados.php"
                    class="flex items-center gap-3 text-admin-gray-400 hover:text-white hover:bg-white/5 px-4 py-3 rounded-xl transition-all group <?= basename($_SERVER['PHP_SELF']) == 'carrinhos_abandonados.php' ? 'active text-white bg-white/5' : ''?>">
                    <i class="fas fa-cart-arrow-down w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Carrinho Abandonado</span>
                </a>

                <a href="gerenciar_cupons.php"
                    class="flex items-center gap-3 text-admin-gray-400 hover:text-white hover:bg-white/5 px-4 py-3 rounded-xl transition-all group <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_cupons.php' ? 'active text-white bg-white/5' : ''?>">
                    <i class="fas fa-ticket-alt w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Cupons</span>
                </a>

                <a href="gerenciar_afiliados.php"
                    class="flex items-center gap-3 text-admin-gray-400 hover:text-white hover:bg-white/5 px-4 py-3 rounded-xl transition-all group <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_afiliados.php' ? 'active text-white bg-white/5' : ''?>">
                    <i class="fas fa-handshake w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Afiliados</span>
                </a>

                <a href="gestao_midias.php"
                    class="flex items-center gap-3 text-admin-gray-400 hover:text-white hover:bg-white/5 px-4 py-3 rounded-xl transition-all group <?= basename($_SERVER['PHP_SELF']) == 'gestao_midias.php' ? 'active text-white bg-white/5' : ''?>">
                    <i class="fas fa-photo-video w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Mídias</span>
                </a>

                <a href="gerenciar_pagamentos.php"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl <?=(basename($_SERVER['PHP_SELF']) == 'gerenciar_pagamentos.php' || basename($_SERVER['PHP_SELF']) == 'gerenciar_pix.php') ? 'active' : ''?>">
                    <i class="fas fa-credit-card w-5 text-center"></i>
                    <span>Pagamentos</span>
                </a>

                <div class="h-px bg-white/10 my-6"></div>

                <a href="../index.php" target="_blank"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl">
                    <i class="fas fa-external-link-alt w-5 text-center"></i>
                    <span>Ver Loja</span>
                </a>

                <a href="../logout.php"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-red-400 hover:text-red-300 hover:bg-red-500/10 !important">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span>Sair</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Bottom Navigation (Mobile Only) -->
    <!-- Bottom Navigation (Mobile Only) Modernized -->
    <nav
        class="fixed bottom-0 left-0 right-0 h-[80px] bg-black/90 backdrop-blur-xl border-t border-white/5 flex items-end justify-around lg:hidden z-50 pb-5 px-2 shadow-[0_-5px_20px_rgba(0,0,0,0.5)]">

        <a href="index.php"
            class="flex-1 flex flex-col items-center justify-end pb-2 gap-1 text-gray-500 hover:text-white transition-all active:text-white <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? '!text-white' : ''?>">
            <div class="h-6 flex items-center"><i class="fas fa-home text-xl mb-0.5"></i></div>
            <span class="text-[10px] font-medium tracking-wide">Início</span>
        </a>

        <a href="pedidos.php"
            class="flex-1 flex flex-col items-center justify-end pb-2 gap-1 text-gray-500 hover:text-white transition-all active:text-white <?= basename($_SERVER['PHP_SELF']) == 'pedidos.php' ? '!text-white' : ''?>">
            <div class="h-6 flex items-center"><i class="fas fa-box text-xl mb-0.5"></i></div>
            <span class="text-[10px] font-medium tracking-wide">Pedidos</span>
        </a>

        <!-- Central Add Button -->
        <div class="relative w-16 flex justify-center z-10 pointer-events-none">
            <div class="absolute -top-10 flex flex-col items-center pointer-events-auto">
                <a href="adicionar_produto.php"
                    class="w-14 h-14 bg-white rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.4)] border-4 border-black active:scale-95 transition-transform hover:shadow-[0_0_25px_rgba(255,255,255,0.6)]">
                    <i class="fas fa-plus text-black text-xl"></i>
                </a>
                <span class="mt-1 text-[10px] font-medium text-gray-400 tracking-wide">Novo</span>
            </div>
        </div>

        <a href="gestao_midias.php"
            class="flex-1 flex flex-col items-center justify-end pb-2 gap-1 text-gray-500 hover:text-white transition-all active:text-white <?= basename($_SERVER['PHP_SELF']) == 'gestao_midias.php' ? '!text-white' : ''?>">
            <div class="h-6 flex items-center"><i class="fas fa-photo-video text-xl mb-0.5"></i></div>
            <span class="text-[10px] font-medium tracking-wide">Mídias</span>
        </a>

        <button id="mobile-menu-btn"
            class="flex-1 flex flex-col items-center justify-end pb-2 gap-1 text-gray-500 hover:text-white transition-all active:text-white">
            <div class="h-6 flex items-center"><i class="fas fa-bars text-xl mb-0.5"></i></div>
            <span class="text-[10px] font-medium tracking-wide">Menu</span>
        </button>
    </nav>

    <!-- Main Content Wrapper -->
    <div id="main-wrapper" class="transition-all duration-300 lg:ml-64 flex flex-col min-h-screen">
        <!-- Top Bar -->
        <header
            class="h-16 sticky top-0 bg-black/80 backdrop-blur-md border-b border-white/10 z-30 flex items-center justify-between px-4 lg:px-8">
            <div class="flex items-center gap-4">
                <h2 class="text-xl font-bold font-display uppercase tracking-wide text-white">
                    <?= isset($page_title) ? $page_title : 'Painel'?>
                </h2>
                <!-- Breadcrumb could go here -->
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden sm:block text-right">
                    <p class="text-sm font-medium text-white">
                        <?= htmlspecialchars($_SESSION['user_nome'] ?? 'Admin')?>
                    </p>
                    <p class="text-xs text-admin-gray-400 uppercase tracking-wider">Administrador</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center border border-white/20">
                    <i class="fas fa-user text-black font-bold"></i>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 lg:p-8 pb-24 lg:pb-8">
