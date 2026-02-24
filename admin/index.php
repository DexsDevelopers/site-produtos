<?php
// admin/index.php - Dashboard Premium
require_once 'secure.php';
$page_title = 'Dashboard';
require_once 'templates/header_admin.php';

// Inicializa variaveis
$total_produtos = 0;
$total_usuarios = 0;
$vendas_hoje = 0;
$faturamento = 0;

try {
    // 0. Visitas Hoje
    $visitas_hoje = 0;
    $visitas_mobile = 0;
    $visitas_desktop = 0;

    // Contagem total única por IP
    $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM site_visitas WHERE data_visita = CURDATE()");
    if ($stmt)
        $visitas_hoje = $stmt->fetchColumn();

    // Contagem por dispositivo
    $stmt = $pdo->query("SELECT dispositivo, COUNT(DISTINCT ip_address) as total FROM site_visitas WHERE data_visita = CURDATE() GROUP BY dispositivo");
    if ($stmt) {
        $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $visitas_mobile = isset($stats['Mobile']) ? $stats['Mobile'] : 0;
        $visitas_desktop = isset($stats['Desktop']) ? $stats['Desktop'] : 0;
    }

    // 1. Total Produtos
    $stmt = $pdo->query("SELECT COUNT(*) FROM produtos");
    if ($stmt)
        $total_produtos = $stmt->fetchColumn();

    // 2. Total Usuarios
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    if ($stmt)
        $total_usuarios = $stmt->fetchColumn();

    // 3. Vendas Hoje
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE date(data_pedido) = CURDATE()");
    if ($stmt)
        $vendas_hoje = $stmt->fetchColumn();

    // 4. Faturamento Mês Atual
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(valor_total), 0) 
        FROM pedidos 
        WHERE MONTH(data_pedido) = MONTH(CURRENT_DATE()) 
        AND YEAR(data_pedido) = YEAR(CURRENT_DATE())
        AND status IN ('pago', 'entregue', 'enviado')
    ");
    if ($stmt)
        $faturamento = $stmt->fetchColumn();

}
catch (PDOException $e) {
}
?>

<div class="space-y-8">
    <!-- Welcome Section -->
    <div class="admin-card rounded-2xl p-6 relative overflow-hidden">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Painel de Controle</h1>
                <p class="text-admin-gray-400">Visão geral da sua loja MACARIO BRAZIL</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <div class="flex items-center gap-2 text-sm text-admin-gray-400 bg-white/5 px-4 py-2 rounded-full">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?php echo date('d/m/Y'); ?></span>
                </div>
            </div>
        </div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-5 gap-6">
        <!-- Visitas Hoje -->
        <div class="stat-card rounded-xl p-6 group cursor-default relative overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-admin-primary/20 rounded-lg flex items-center justify-center group-hover:bg-white group-hover:text-black transition-colors">
                    <i class="fas fa-eye text-xl"></i>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="text-[9px] font-black text-blue-400 bg-blue-400/10 px-2 py-0.5 rounded uppercase tracking-tighter flex items-center gap-1">
                        <i class="fas fa-desktop text-[8px]"></i> <?php echo $visitas_desktop; ?>
                    </span>
                    <span class="text-[9px] font-black text-admin-primary bg-admin-primary/10 px-2 py-0.5 rounded uppercase tracking-tighter flex items-center gap-1">
                        <i class="fas fa-mobile-alt text-[8px]"></i> <?php echo $visitas_mobile; ?>
                    </span>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-white mb-1"><?php echo $visitas_hoje; ?></h3>
            <p class="text-sm text-admin-gray-400">Visitantes Únicos</p>
            
            <?php
$total_v = $visitas_hoje ? $visitas_hoje : 1;
$pct_mobile = round(($visitas_mobile / $total_v) * 100);
?>
            <div class="mt-4 h-1 w-full bg-white/5 rounded-full overflow-hidden">
                <div class="h-full bg-admin-primary transition-all duration-1000" style="width: <?php echo $pct_mobile; ?>%"></div>
            </div>
        </div>

        <!-- Produtos -->
        <div class="stat-card rounded-xl p-6 group cursor-default">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-admin-primary/20 rounded-lg flex items-center justify-center group-hover:bg-white group-hover:text-black transition-colors">
                    <i class="fas fa-box text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Estoque</span>
            </div>
            <h3 class="text-3xl font-bold text-white mb-1"><?php echo $total_produtos; ?></h3>
            <p class="text-sm text-admin-gray-400">Itens Ativos</p>
        </div>

        <!-- Clientes -->
        <div class="stat-card rounded-xl p-6 group cursor-default">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-admin-primary/20 rounded-lg flex items-center justify-center group-hover:bg-white group-hover:text-black transition-colors">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Base</span>
            </div>
            <h3 class="text-3xl font-bold text-white mb-1"><?php echo $total_usuarios; ?></h3>
            <p class="text-sm text-admin-gray-400">Clientes Cadastrados</p>
        </div>

        <!-- Pedidos Hoje -->
        <div class="stat-card rounded-xl p-6 group cursor-default">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center group-hover:bg-green-500 group-hover:text-white transition-colors">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-green-500 uppercase tracking-widest">Hoje</span>
            </div>
            <h3 class="text-3xl font-bold text-white mb-1"><?php echo $vendas_hoje; ?></h3>
            <p class="text-sm text-admin-gray-400">Novos Pedidos</p>
        </div>

        <!-- Faturamento -->
        <div class="stat-card rounded-xl p-6 group cursor-default lg:col-span-2 xl:col-span-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-admin-primary/20 rounded-lg flex items-center justify-center group-hover:bg-white group-hover:text-black transition-colors">
                    <i class="fas fa-wallet text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Mês</span>
            </div>
            <h3 class="text-2xl font-bold text-white mb-1"><?php echo formatarPreco($faturamento); ?></h3>
            <p class="text-sm text-admin-gray-400">Receita Bruta</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="admin-card rounded-2xl p-8 bg-admin-gray-800/40 border border-white/5">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <i class="fas fa-bolt text-yellow-500"></i>
                Ações Rápidas
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <a href="adicionar_produto.php" class="flex flex-col items-center gap-3 p-4 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-white/20 transition-all group">
                    <i class="fas fa-plus-circle text-2xl group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs font-medium text-admin-gray-400 group-hover:text-white">Novo Produto</span>
                </a>
                <a href="gerenciar_banners.php" class="flex flex-col items-center gap-3 p-4 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-white/20 transition-all group">
                    <i class="fas fa-image text-2xl group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs font-medium text-admin-gray-400 group-hover:text-white">Banners</span>
                </a>
                <a href="pedidos.php" class="flex flex-col items-center gap-3 p-4 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-white/20 transition-all group">
                    <i class="fas fa-shopping-bag text-2xl group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs font-medium text-admin-gray-400 group-hover:text-white">Pedidos</span>
                </a>
            </div>
        </div>

        <!-- Latest Info -->
        <div class="admin-card rounded-2xl p-8 bg-admin-gray-800/40 border border-white/5">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <i class="fas fa-info-circle text-blue-500"></i>
                Status do Sistema
            </h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 rounded-xl bg-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                        <span class="text-sm font-medium text-white">Loja Online</span>
                    </div>
                    <span class="text-[10px] font-bold text-admin-gray-500 uppercase">Operacional</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-sm font-medium text-white">Banco de Dados</span>
                    </div>
                    <span class="text-[10px] font-bold text-admin-gray-500 uppercase">Conectado</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>
