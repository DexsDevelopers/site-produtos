<?php
// admin/index.php - Dashboard Premium
require_once 'secure.php';
$page_title = 'Dashboard';
require_once 'templates/header_admin.php';

// Métricas Reais
try {
    // Totais com tratamento de erro
    $total_produtos = $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn() ?: 0;
    $total_usuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() ?: 0;

    // Vendas hoje
    $hoje = date('Y-m-d');
    $vendas_hoje = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE date(data_pedido) = '$hoje'")->fetchColumn() ?: 0;

    // Faturamento Mensal (pedidos pagos/entregues)
    $mes_atual = date('m');
    $faturamento = $pdo->query("
        SELECT SUM(valor_total) FROM pedidos 
        WHERE strftime('%m', data_pedido) = '$mes_atual' 
        AND status IN ('pago', 'entregue', 'enviado')
    ")->fetchColumn() ?: 0;

    // Afiliados (tabela pode nao existir se o script setup nao rodou, entao try/catch silencioso)
    try {
        $total_afiliados = $pdo->query('SELECT COUNT(*) FROM afiliados')->fetchColumn() ?: 0;
    }
    catch (Exception $e) {
        $total_afiliados = 0;
    }

}
catch (Exception $e) {
    // Fallback
    $total_produtos = 0;
    $total_usuarios = 0;
    $vendas_hoje = 0;
    $faturamento = 0;
}
?>

<!-- Dashboard Principal -->
<div class="space-y-8">
    <!-- Welcome Section -->
    <div class="admin-card rounded-2xl p-6 relative overflow-hidden">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    Painel de Controle
                </h1>
                <p class="text-admin-gray-400">Visão geral da sua loja MACARIO BRAZIL</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <div class="flex items-center gap-2 text-sm text-admin-gray-400 bg-white/5 px-4 py-2 rounded-full">
                    <i class="fas fa-calendar-alt"></i>
                    <span>
                        <?= date('d/m/Y')?>
                    </span>
                </div>
            </div>
        </div>
        <!-- Efeito de fundo -->
        <div
            class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none">
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Vendas Hoje -->
        <div class="stat-card rounded-xl p-6 group cursor-default">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="w-12 h-12 bg-admin-primary/20 rounded-lg flex items-center justify-center group-hover:bg-white group-hover:text-black transition-colors">
                    <i class="fas fa-shopping-bag text-xl"></i>
                </div>
                <span class="text-xs font-semibold text-green-400 bg-green-400/10 px-2 py-1 rounded">Hoje</span>
            </div>
            <h3 class="text-3xl font-bold text-white mb-1">
                <?= $vendas_hoje?>
            </h3>
            <p class="text-sm text-admin-gray-400">Vendas confirmadas</p>
        </div>

        <!-- Faturamento Mês -->
        <div class="stat-card rounded-xl p-6 group cursor-default">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center text-green-400 group-hover:bg-green-400 group-hover:text-black transition-colors">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
                <span class="text-xs font-semibold text-admin-gray-500 bg-white/5 px-2 py-1 rounded">Este Mês</span>
            </div>
            <h3 class="text-3xl font-bold text-white mb-1">R$
                <?= number_format($faturamento, 2, ',', '.')?>
            </h3>
            <p class="text-sm text-admin-gray-400">Receita bruta</p>
        </div>

        <!-- Total Produtos -->
        <div class="stat-card rounded-xl p-6 group cursor-default">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center text-purple-400 group-hover:bg-purple-400 group-hover:text-black transition-colors">
                    <i class="fas fa-box text-xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-white mb-1">
                <?= $total_produtos?>
            </h3>
            <p class="text-sm text-admin-gray-400">Produtos ativos</p>
        </div>

        <!-- Total Usuários -->
        <div class="stat-card rounded-xl p-6 group cursor-default">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center text-blue-400 group-hover:bg-blue-400 group-hover:text-black transition-colors">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-white mb-1">
                <?= $total_usuarios?>
            </h3>
            <p class="text-sm text-admin-gray-400">Clientes cadastrados</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Ações Rápidas -->
        <div class="lg:col-span-1 space-y-6">
            <div class="admin-card rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Gestão Rápida</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="adicionar_produto.php"
                        class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-colors text-center gap-2 group">
                        <i class="fas fa-plus text-admin-primary group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs font-medium text-white">Add Produto</span>
                    </a>
                    <a href="gerenciar_cupons.php"
                        class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-colors text-center gap-2 group">
                        <i class="fas fa-ticket-alt text-yellow-400 group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs font-medium text-white">Cupons</span>
                    </a>
                    <a href="gerenciar_afiliados.php"
                        class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-colors text-center gap-2 group">
                        <i class="fas fa-handshake text-blue-400 group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs font-medium text-white">Afiliados</span>
                    </a>
                    <a href="gerenciar_banners.php"
                        class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-colors text-center gap-2 group">
                        <i class="fas fa-image text-purple-400 group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs font-medium text-white">Banners</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Produtos Recentes -->
        <div class="lg:col-span-2">
            <div class="admin-card rounded-xl p-6 h-full">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white">Últimos Produtos</h3>
                    <a href="gerenciar_produtos.php" class="text-sm text-admin-gray-400 hover:text-white">Ver todos</a>
                </div>

                <div class="space-y-3">
                    <?php
try {
    $produtos_recentes = $pdo->query('SELECT * FROM produtos ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
    $produtos_recentes = [];
}
?>

                    <?php if (!empty($produtos_recentes)): ?>
                    <?php foreach ($produtos_recentes as $produto): ?>
                    <div class="flex items-center gap-4 p-3 hover:bg-white/5 rounded-lg transition-colors">
                        <div class="w-12 h-12 bg-admin-gray-800 rounded-lg overflow-hidden">
                            <img src="../<?= htmlspecialchars($produto['imagem'])?>"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-white truncate">
                                <?= htmlspecialchars($produto['nome'])?>
                            </h4>
                            <p class="text-xs text-admin-gray-400">R$
                                <?= number_format($produto['preco'], 2, ',', '.')?>
                            </p>
                        </div>
                        <a href="editar_produto.php?id=<?= $produto['id']?>"
                            class="text-admin-gray-500 hover:text-white">
                            <i class="fas fa-pen"></i>
                        </a>
                    </div>
                    <?php
    endforeach; ?>
                    <?php
else: ?>
                    <p class="text-center text-admin-gray-500 py-4">Sem produtos recentes.</p>
                    <?php
endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>