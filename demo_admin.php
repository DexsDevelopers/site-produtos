<?php
// demo_admin.php - Demonstração do Painel Administrativo Moderno
session_start();
require_once 'config.php';

// Simula dados para demonstração
$total_produtos = 156;
$total_usuarios = 89;
$total_categorias = 12;
$total_banners = 8;

$produtos_demo = [
    ['id' => 1, 'nome' => 'iPhone 15 Pro Max', 'preco' => 8999.99, 'imagem' => 'assets/uploads/produto_1.jpg'],
    ['id' => 2, 'nome' => 'MacBook Air M2', 'preco' => 7999.99, 'imagem' => 'assets/uploads/produto_2.jpg'],
    ['id' => 3, 'nome' => 'AirPods Pro', 'preco' => 1999.99, 'imagem' => 'assets/uploads/produto_3.jpg'],
    ['id' => 4, 'nome' => 'Apple Watch Series 9', 'preco' => 3999.99, 'imagem' => 'assets/uploads/produto_4.jpg'],
    ['id' => 5, 'nome' => 'iPad Pro 12.9"', 'preco' => 6999.99, 'imagem' => 'assets/uploads/produto_5.jpg'],
    ['id' => 6, 'nome' => 'Magic Keyboard', 'preco' => 1299.99, 'imagem' => 'assets/uploads/produto_6.jpg']
];

$usuarios_demo = [
    ['nome' => 'João Silva', 'email' => 'joao@email.com'],
    ['nome' => 'Maria Santos', 'email' => 'maria@email.com'],
    ['nome' => 'Pedro Costa', 'email' => 'pedro@email.com'],
    ['nome' => 'Ana Oliveira', 'email' => 'ana@email.com'],
    ['nome' => 'Carlos Lima', 'email' => 'carlos@email.com']
];

$categorias_demo = [
    ['nome' => 'Eletrônicos', 'total_produtos' => 45],
    ['nome' => 'Roupas', 'total_produtos' => 32],
    ['nome' => 'Casa e Decoração', 'total_produtos' => 28],
    ['nome' => 'Esportes', 'total_produtos' => 19],
    ['nome' => 'Livros', 'total_produtos' => 15]
];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Demo - Painel Administrativo Moderno</title>

    <!-- Fonts Modernas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />

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
                <div
                    class="w-10 h-10 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Admin Panel</h1>
                    <p class="text-xs text-admin-gray-400">Painel Administrativo</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="space-y-2">
                <a href="#" class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-white active">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>

                <a href="#"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white">
                    <i class="fas fa-plus-circle w-5"></i>
                    <span>Adicionar Produto</span>
                </a>

                <a href="#"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white">
                    <i class="fas fa-tags w-5"></i>
                    <span>Categorias</span>
                </a>

                <a href="#"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white">
                    <i class="fas fa-image w-5"></i>
                    <span>Banners</span>
                </a>

                <a href="#"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span>Pedidos</span>
                </a>

                <div class="border-t border-admin-gray-700 my-4"></div>

                <a href="index.php"
                    class="admin-nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-admin-gray-300 hover:text-white">
                    <i class="fas fa-external-link-alt w-5"></i>
                    <span>Ver Loja</span>
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
                    <button id="mobile-menu-btn"
                        class="lg:hidden p-2 rounded-lg text-admin-gray-400 hover:text-white hover:bg-admin-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Page Title -->
                    <div class="flex-1 lg:flex-none">
                        <h2 class="text-xl font-semibold text-white">Dashboard</h2>
                    </div>

                    <!-- User Info -->
                    <div class="flex items-center gap-4">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-medium text-white">Admin Demo</p>
                            <p class="text-xs text-admin-gray-400">Administrador</p>
                        </div>
                        <div
                            class="w-8 h-8 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <!-- Dashboard Principal -->
            <div class="space-y-8">
                <!-- Welcome Section -->
                <div class="admin-card rounded-2xl p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-2">
                                Bem-vindo ao Admin Panel! 👋
                            </h1>
                            <p class="text-admin-gray-400">Esta é uma demonstração do painel administrativo moderno e
                                responsivo.</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <div class="flex items-center gap-2 text-sm text-admin-gray-400">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?= date('d/m/Y') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Produtos -->
                    <div class="stat-card rounded-xl p-6 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-admin-gray-400">Total de Produtos</p>
                                <p class="text-3xl font-bold text-white"><?= $total_produtos ?></p>
                            </div>
                            <div class="w-12 h-12 bg-admin-primary/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-admin-primary text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <i class="fas fa-arrow-up text-admin-success mr-1"></i>
                            <span class="text-admin-success">+12%</span>
                            <span class="text-admin-gray-400 ml-2">vs mês anterior</span>
                        </div>
                    </div>

                    <!-- Total Usuários -->
                    <div class="stat-card rounded-xl p-6 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-admin-gray-400">Total de Usuários</p>
                                <p class="text-3xl font-bold text-white"><?= $total_usuarios ?></p>
                            </div>
                            <div class="w-12 h-12 bg-admin-success/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-admin-success text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <i class="fas fa-arrow-up text-admin-success mr-1"></i>
                            <span class="text-admin-success">+8%</span>
                            <span class="text-admin-gray-400 ml-2">vs mês anterior</span>
                        </div>
                    </div>

                    <!-- Total Categorias -->
                    <div class="stat-card rounded-xl p-6 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-admin-gray-400">Categorias</p>
                                <p class="text-3xl font-bold text-white"><?= $total_categorias ?></p>
                            </div>
                            <div class="w-12 h-12 bg-admin-warning/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tags text-admin-warning text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <i class="fas fa-minus text-admin-gray-400 mr-1"></i>
                            <span class="text-admin-gray-400">0%</span>
                            <span class="text-admin-gray-400 ml-2">vs mês anterior</span>
                        </div>
                    </div>

                    <!-- Total Banners -->
                    <div class="stat-card rounded-xl p-6 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-admin-gray-400">Banners</p>
                                <p class="text-3xl font-bold text-white"><?= $total_banners ?></p>
                            </div>
                            <div class="w-12 h-12 bg-admin-secondary/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-image text-admin-secondary text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <i class="fas fa-arrow-up text-admin-success mr-1"></i>
                            <span class="text-admin-success">+3</span>
                            <span class="text-admin-gray-400 ml-2">novos este mês</span>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Produtos Recentes -->
                    <div class="lg:col-span-2">
                        <div class="admin-card rounded-xl p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-semibold text-white">Produtos Recentes</h3>
                                <a href="#"
                                    class="text-admin-primary hover:text-admin-secondary text-sm font-medium flex items-center gap-2">
                                    <i class="fas fa-plus"></i>
                                    Adicionar Novo
                                </a>
                            </div>

                            <div class="space-y-4">
                                <?php foreach ($produtos_demo as $produto): ?>
                                    <div
                                        class="flex items-center gap-4 p-4 bg-admin-gray-800/50 rounded-lg hover:bg-admin-gray-700/50 transition-colors">
                                        <div
                                            class="w-16 h-16 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-white text-xl"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-white truncate"><?= $produto['nome'] ?></h4>
                                            <p class="text-sm text-admin-gray-400">R$
                                                <?= number_format($produto['preco'], 2, ',', '.') ?></p>
                                            <p class="text-xs text-admin-gray-500">ID: <?= $produto['id'] ?></p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button
                                                class="text-admin-primary hover:text-admin-secondary text-sm p-2 rounded-lg hover:bg-admin-gray-700 transition-colors">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                class="text-admin-success hover:text-green-300 text-sm p-2 rounded-lg hover:bg-admin-gray-700 transition-colors">
                                                <i class="fas fa-external-link-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Usuários Recentes -->
                        <div class="admin-card rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Usuários Recentes</h3>
                            <div class="space-y-3">
                                <?php foreach ($usuarios_demo as $usuario): ?>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-full flex items-center justify-center text-white text-sm font-bold">
                                            <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-white text-sm font-medium truncate"><?= $usuario['nome'] ?></p>
                                            <p class="text-admin-gray-400 text-xs truncate"><?= $usuario['email'] ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Categorias com Produtos -->
                        <div class="admin-card rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Categorias</h3>
                            <div class="space-y-3">
                                <?php foreach ($categorias_demo as $categoria): ?>
                                    <div class="flex items-center justify-between">
                                        <span class="text-white text-sm"><?= $categoria['nome'] ?></span>
                                        <span class="text-admin-gray-400 text-xs bg-admin-gray-700 px-2 py-1 rounded-full">
                                            <?= $categoria['total_produtos'] ?> produtos
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Ações Rápidas -->
                        <div class="admin-card rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Ações Rápidas</h3>
                            <div class="space-y-3">
                                <button
                                    class="block w-full bg-admin-primary hover:bg-blue-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Adicionar Produto
                                </button>
                                <button
                                    class="block w-full bg-admin-gray-700 hover:bg-admin-gray-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-tags mr-2"></i>
                                    Gerenciar Categorias
                                </button>
                                <button
                                    class="block w-full bg-admin-gray-700 hover:bg-admin-gray-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-image mr-2"></i>
                                    Gerenciar Banners
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="admin-card rounded-xl p-8 text-center">
                    <h2 class="text-2xl font-bold text-white mb-4">Pronto para Usar o Admin Real?</h2>
                    <p class="text-admin-gray-400 mb-6">Este é apenas uma demonstração. Acesse o painel administrativo
                        real para gerenciar sua loja.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="admin_login.php"
                            class="bg-admin-primary hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-lg transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Acessar Admin Real
                        </a>
                        <a href="index.php"
                            class="bg-admin-gray-700 hover:bg-admin-gray-600 text-white font-bold py-3 px-8 rounded-lg transition-colors">
                            <i class="fas fa-home mr-2"></i>
                            Voltar à Loja
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function () {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobile-overlay');

            if (mobileMenuBtn && sidebar) {
                mobileMenuBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('open');
                    mobileOverlay.classList.toggle('hidden');
                });

                mobileOverlay.addEventListener('click', function () {
                    sidebar.classList.remove('open');
                    mobileOverlay.classList.add('hidden');
                });
            }

            // Auto-hide mobile menu on window resize
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('open');
                    mobileOverlay.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html>