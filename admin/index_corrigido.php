<?php
// admin/index_corrigido.php - Dashboard Corrigido
$page_title = 'Dashboard';
require_once 'secure.php';
require_once 'templates/header_admin.php';
?>

<!-- Dashboard Principal -->
<div class="space-y-8">
    <!-- Welcome Section -->
    <div class="admin-card rounded-2xl p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    Bem-vindo, <?= htmlspecialchars($_SESSION['user_nome'] ?? 'Admin') ?>! ðŸ‘‹
                </h1>
                <p class="text-admin-gray-400">Aqui estÃ¡ um resumo do que estÃ¡ acontecendo na sua loja hoje.</p>
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
        <?php
        try {
            // Busca estatÃ­sticas
            $total_produtos = $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
            $total_usuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
            $total_categorias = $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn();
            $total_banners = $pdo->query('SELECT COUNT(*) FROM banners')->fetchColumn();
        } catch (Exception $e) {
            $total_produtos = $total_usuarios = $total_categorias = $total_banners = 0;
        }
        ?>
        
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
                <span class="text-admin-gray-400 ml-2">vs mÃªs anterior</span>
            </div>
        </div>

        <!-- Total UsuÃ¡rios -->
        <div class="stat-card rounded-xl p-6 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-admin-gray-400">Total de UsuÃ¡rios</p>
                    <p class="text-3xl font-bold text-white"><?= $total_usuarios ?></p>
                </div>
                <div class="w-12 h-12 bg-admin-success/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-admin-success text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <i class="fas fa-arrow-up text-admin-success mr-1"></i>
                <span class="text-admin-success">+8%</span>
                <span class="text-admin-gray-400 ml-2">vs mÃªs anterior</span>
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
                <span class="text-admin-gray-400 ml-2">vs mÃªs anterior</span>
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
                <span class="text-admin-gray-400 ml-2">novos este mÃªs</span>
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
                    <a href="adicionar_produto.php" class="text-admin-primary hover:text-admin-secondary text-sm font-medium flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        Adicionar Novo
                    </a>
                </div>

                <div class="space-y-4">
                    <?php
                    try {
                        $produtos_recentes = $pdo->query('SELECT * FROM produtos ORDER BY id DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        $produtos_recentes = [];
                    }
                    ?>
                    
                    <?php if (!empty($produtos_recentes)): ?>
                        <?php foreach ($produtos_recentes as $produto): ?>
                        <div class="flex items-center gap-4 p-4 bg-admin-gray-800/50 rounded-lg hover:bg-admin-gray-700/50 transition-colors">
                            <div class="w-16 h-16 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-white text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-white truncate"><?= htmlspecialchars($produto['nome']) ?></h4>
                                <p class="text-sm text-admin-gray-400"><?= formatarPreco($produto['preco']) ?></p>
                                <p class="text-xs text-admin-gray-500">ID: <?= $produto['id'] ?></p>
                            </div>
                            <div class="flex gap-2">
                                <a href="editar_produto.php?id=<?= $produto['id'] ?>" 
                                   class="text-admin-primary hover:text-admin-secondary text-sm p-2 rounded-lg hover:bg-admin-gray-700 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="../produto.php?id=<?= $produto['id'] ?>" 
                                   class="text-admin-success hover:text-green-300 text-sm p-2 rounded-lg hover:bg-admin-gray-700 transition-colors" 
                                   target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-box text-admin-gray-500 text-4xl mb-4"></i>
                            <p class="text-admin-gray-400">Nenhum produto encontrado</p>
                            <a href="adicionar_produto.php" class="text-admin-primary hover:text-admin-secondary text-sm font-medium mt-2 inline-block">
                                Adicionar primeiro produto
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- UsuÃ¡rios Recentes -->
            <div class="admin-card rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">UsuÃ¡rios Recentes</h3>
                <div class="space-y-3">
                    <?php
                    try {
                        $usuarios_recentes = $pdo->query('SELECT id, nome, email, data_cadastro FROM usuarios ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        $usuarios_recentes = [];
                    }
                    ?>
                    
                    <?php if (!empty($usuarios_recentes)): ?>
                        <?php foreach ($usuarios_recentes as $usuario): ?>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-admin-primary to-admin-secondary rounded-full flex items-center justify-center text-white text-sm font-bold">
                                <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-white text-sm font-medium truncate"><?= htmlspecialchars($usuario['nome']) ?></p>
                                <p class="text-admin-gray-400 text-xs truncate"><?= htmlspecialchars($usuario['email']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-admin-gray-400 text-sm">Nenhum usuÃ¡rio encontrado</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AÃ§Ãµes RÃ¡pidas -->
            <div class="admin-card rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">AÃ§Ãµes RÃ¡pidas</h3>
                <div class="space-y-3">
                    <a href="adicionar_produto.php" 
                       class="block w-full bg-admin-primary hover:bg-blue-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Adicionar Produto
                    </a>
                    <a href="gerenciar_categorias.php" 
                       class="block w-full bg-admin-gray-700 hover:bg-admin-gray-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-tags mr-2"></i>
                        Gerenciar Categorias
                    </a>
                    <a href="gerenciar_banners.php" 
                       class="block w-full bg-admin-gray-700 hover:bg-admin-gray-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-image mr-2"></i>
                        Gerenciar Banners
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>
