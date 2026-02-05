<?php
// admin/templates/header_admin.php - Header Moderno e Profissional
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0F172A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Admin Panel">
    <meta name="mobile-web-app-capable" content="yes">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Painel Administrativo</title>
    
    <!-- Fonts Modernas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'admin-primary': '#3B82F6',
                        'admin-secondary': '#8B5CF6',
                        'admin-success': '#10B981',
                        'admin-warning': '#F59E0B',
                        'admin-danger': '#EF4444',
                        'admin-dark': '#0F172A',
                        'admin-gray': {
                            50: '#F8FAFC',
                            100: '#F1F5F9',
                            200: '#E2E8F0',
                            300: '#CBD5E1',
                            400: '#94A3B8',
                            500: '#64748B',
                            600: '#475569',
                            700: '#334155',
                            800: '#1E293B',
                            900: '#0F172A'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
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
                    }
                }
            }
        }
    </script>
    
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            min-height: 100vh;
            padding-bottom: 80px; /* Espaço para bottom nav no mobile */
        }
        
        @media (min-width: 1024px) {
            body {
                padding-bottom: 0;
            }
        }
        
        /* Sidebar - Desktop sempre visível, Mobile escondido */
        .admin-sidebar {
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            width: 280px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Desktop: sempre visível */
        @media (min-width: 1024px) {
            .admin-sidebar {
                transform: translateX(0);
            }
        }
        
        /* Mobile: escondido por padrão */
        @media (max-width: 1023px) {
            .admin-sidebar {
                width: 85%;
                max-width: 320px;
                transform: translateX(-100%);
            }
            
            .admin-sidebar.is-open {
                transform: translateX(0);
            }
        }
        
        .admin-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 640px) {
            .admin-card {
                padding: 1rem !important;
                border-radius: 1rem !important;
            }
        }
        
        .admin-nav-item {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            -webkit-tap-highlight-color: rgba(59, 130, 246, 0.2);
        }
        
        .admin-nav-item:active {
            background: rgba(59, 130, 246, 0.2);
            transform: scale(0.98);
        }
        
        @media (min-width: 1024px) {
        .admin-nav-item:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateX(4px);
            }
        }
        
        .admin-nav-item.active {
            background: rgba(59, 130, 246, 0.25);
            border-right: 3px solid #3B82F6;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }
        
        @media (min-width: 1024px) {
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.2);
        }
        }
        
        /* Overlay para mobile */
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .mobile-overlay.is-visible {
            opacity: 1;
            visibility: visible;
        }
        
        @media (min-width: 1024px) {
            .mobile-overlay {
                display: none;
            }
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        @media (max-width: 640px) {
            .chart-container {
                height: 250px;
            }
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #3B82F6, #8B5CF6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Bottom Navigation Bar (Mobile) */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.5rem 0;
            z-index: 50;
            display: none;
        }
        
        @media (max-width: 1023px) {
            .bottom-nav {
                display: block;
            }
        }
        
        .bottom-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            color: rgba(148, 163, 184, 0.7);
            transition: all 0.2s;
            text-decoration: none;
            -webkit-tap-highlight-color: rgba(59, 130, 246, 0.2);
        }
        
        .bottom-nav-item:active {
            transform: scale(0.95);
        }
        
        .bottom-nav-item.active {
            color: #3B82F6;
        }
        
        .bottom-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        
        .bottom-nav-item span {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        /* Mobile Optimizations */
        @media (max-width: 640px) {
            h1, h2, h3 {
                font-size: 1.5rem !important;
            }
            
            input, textarea, select {
                font-size: 16px !important; /* Previne zoom no iOS */
            }
            
            table {
                font-size: 0.875rem;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
                min-height: 44px; /* Tamanho mínimo para touch */
            }
        }
        
        /* Table Responsive */
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            thead {
                display: none;
            }
            
            tbody tr {
                display: block;
                margin-bottom: 1rem;
                background: rgba(30, 41, 59, 0.6);
                border-radius: 0.5rem;
                padding: 1rem;
            }
            
            tbody td {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem 0;
                border: none;
            }
            
            tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: rgba(148, 163, 184, 0.8);
                margin-right: 1rem;
            }
        }
        
        /* Form Mobile */
        @media (max-width: 640px) {
            form {
                padding: 0;
            }
            
            .form-group {
                margin-bottom: 1.5rem;
            }
            
            label {
                font-size: 0.875rem;
                margin-bottom: 0.5rem;
            }
        }
        
        /* Safe Area for Notch */
        @supports (padding: max(0px)) {
            .bottom-nav {
                padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
            }
        }
        
        /* Main Content Wrapper */
        .main-wrapper {
            width: 100%;
            transition: margin-left 0.3s ease;
        }
        
        @media (min-width: 1024px) {
            .main-wrapper {
                margin-left: 280px;
            }
        }
        
        @media (max-width: 1023px) {
            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-admin-dark text-white">
    <!-- Mobile Menu Overlay -->
    <div id="mobile-overlay" class="mobile-overlay"></div>
    
    <!-- Sidebar -->
    <div id="sidebar" class="admin-sidebar">
        <div class="p-6">
            <!-- Close Button (Mobile Only) -->
            <button id="close-menu-btn" class="absolute top-4 right-4 p-2 rounded-lg text-admin-gray-400 hover:text-white hover:bg-admin-gray-700 transition-all lg:hidden">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <!-- Logo -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Admin Panel</h1>
                    <p class="text-xs text-admin-gray-400">Painel Administrativo</p>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="space-y-2">
                <a href="index.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="gerenciar_produtos.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_produtos.php' ? 'active' : '' ?>">
                    <i class="fas fa-box w-5"></i>
                    <span>Gerenciar Produtos</span>
                </a>
                
                <a href="adicionar_produto.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'adicionar_produto.php' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle w-5"></i>
                    <span>Adicionar Produto</span>
                </a>
                
                <a href="gerenciar_categorias.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_categorias.php' ? 'active' : '' ?>">
                    <i class="fas fa-tags w-5"></i>
                    <span>Categorias</span>
                </a>
                
                <a href="gerenciar_banners.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_banners.php' ? 'active' : '' ?>">
                    <i class="fas fa-image w-5"></i>
                    <span>Banners</span>
                </a>
                
                <a href="pedidos.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'pedidos.php' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span>Pedidos</span>
                </a>
                
                <a href="gerenciar_pagamentos.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white <?= (basename($_SERVER['PHP_SELF']) == 'gerenciar_pagamentos.php' || basename($_SERVER['PHP_SELF']) == 'gerenciar_pix.php') ? 'active' : '' ?>">
                    <i class="fas fa-credit-card w-5"></i>
                    <span>Pagamentos</span>
                </a>
                
                <div class="border-t border-admin-gray-700 my-4"></div>
                
                <a href="../index.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white">
                    <i class="fas fa-external-link-alt w-5"></i>
                    <span>Ver Loja</span>
                </a>
                
                <a href="../logout.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-red-400 hover:text-red-300">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Sair</span>
                </a>
            </nav>
        </div>
    </div>
    
    <!-- Bottom Navigation (Mobile Only) -->
    <nav class="bottom-nav">
        <div class="flex justify-around items-center">
            <a href="index.php" class="bottom-nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="gerenciar_produtos.php" class="bottom-nav-item <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_produtos.php' ? 'active' : '' ?>">
                <i class="fas fa-box"></i>
                <span>Produtos</span>
            </a>
            <a href="adicionar_produto.php" class="bottom-nav-item <?= basename($_SERVER['PHP_SELF']) == 'adicionar_produto.php' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Adicionar</span>
            </a>
            <a href="pedidos.php" class="bottom-nav-item <?= basename($_SERVER['PHP_SELF']) == 'pedidos.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Pedidos</span>
            </a>
            <button id="bottom-menu-btn" class="bottom-nav-item">
                <i class="fas fa-bars"></i>
                <span>Mais</span>
            </button>
        </div>
    </nav>
    
    <!-- Main Content Wrapper -->
    <div id="main-wrapper" class="main-wrapper">
        <!-- Top Bar -->
        <header class="bg-admin-gray-800/50 backdrop-blur-lg border-b border-admin-gray-700 sticky top-0 z-40 safe-area-top">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-14 lg:h-16">
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg text-admin-gray-400 active:bg-admin-gray-700 active:scale-95 transition-all" aria-label="Menu">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Page Title -->
                    <div class="flex-1 lg:flex-none ml-2 lg:ml-0">
                        <h2 class="text-lg lg:text-xl font-semibold text-white truncate">
                            <?= isset($page_title) ? $page_title : 'Dashboard' ?>
                        </h2>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex items-center gap-2 lg:gap-4">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-medium text-white truncate max-w-[120px]"><?= htmlspecialchars($_SESSION['user_nome'] ?? 'Admin') ?></p>
                            <p class="text-xs text-admin-gray-400 hidden lg:block">Administrador</p>
                        </div>
                        <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-white text-xs lg:text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-3 sm:p-4 lg:p-6 xl:p-8 pb-20 lg:pb-8">