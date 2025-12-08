<?php
// admin/templates/header_admin.php - Header Moderno e Profissional
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            min-height: 100vh;
        }
        
        .admin-sidebar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .admin-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .admin-nav-item {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .admin-nav-item:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateX(4px);
        }
        
        .admin-nav-item.active {
            background: rgba(59, 130, 246, 0.2);
            border-right: 3px solid #3B82F6;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.2);
        }
        
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .mobile-menu.open {
            transform: translateX(0);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #3B82F6, #8B5CF6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-admin-dark text-white">
    <!-- Mobile Menu Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>
    
    <!-- Sidebar -->
    <div id="sidebar" class="admin-sidebar fixed left-0 top-0 h-full w-64 z-50 lg:translate-x-0 mobile-menu">
        <div class="p-6">
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
                
                <a href="gerenciar_pix.php" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_pix.php' ? 'active' : '' ?>">
                    <i class="fas fa-qrcode w-5"></i>
                    <span>Gerenciar PIX</span>
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
    
    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Top Bar -->
        <header class="bg-admin-gray-800/50 backdrop-blur-lg border-b border-admin-gray-700 sticky top-0 z-40">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg text-admin-gray-400 hover:text-white hover:bg-admin-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Page Title -->
                    <div class="flex-1 lg:flex-none">
                        <h2 class="text-xl font-semibold text-white">
                            <?= isset($page_title) ? $page_title : 'Dashboard' ?>
                        </h2>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex items-center gap-4">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-medium text-white"><?= htmlspecialchars($_SESSION['user_nome'] ?? 'Admin') ?></p>
                            <p class="text-xs text-admin-gray-400">Administrador</p>
                        </div>
                        <div class="w-8 h-8 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-4 sm:p-6 lg:p-8">