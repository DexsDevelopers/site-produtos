<?php
// admin/gerenciar_cupons.php - Gestão de Cupons Premium com Filtros
require_once 'secure.php';
$page_title = 'Cupons';

// Processar ações
if (isset($_POST['adicionar'])) {
    $codigo = strtoupper($_POST['codigo']);
    $tipo = $_POST['tipo'];
    $valor = $_POST['valor'];
    $usos_max = $_POST['usos_max'] ?: 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO cupons (codigo, tipo, valor, usos_max, ativo) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$codigo, $tipo, $valor, $usos_max]);
        $_SESSION['admin_message'] = "Cupom criado com sucesso!";
    } catch (Exception $e) { $_SESSION['admin_message'] = "Erro: " . $e->getMessage(); }
    header("Location: gerenciar_cupons.php"); exit();
}

if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $pdo->prepare("UPDATE cupons SET ativo = NOT ativo WHERE id = ?")->execute([$id]);
    header("Location: gerenciar_cupons.php"); exit();
}

if (isset($_GET['deletar'])) {
    $id = $_GET['deletar'];
    $pdo->prepare("DELETE FROM cupons WHERE id = ?")->execute([$id]);
    header("Location: gerenciar_cupons.php"); exit();
}

// Listar
$cupons = $pdo->query("SELECT * FROM cupons ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

require_once 'templates/header_admin.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Cupons</h1>
            <p class="text-admin-gray-400">Impulsione suas vendas com ofertas e descontos</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
             <div class="relative flex-1 sm:flex-none">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-admin-gray-500 text-xs"></i>
                <input type="text" id="coupon-search" placeholder="Filtrar cupons..." 
                    class="w-full sm:w-64 bg-white/5 border border-white/10 rounded-xl py-2 pl-9 pr-4 text-xs text-white focus:border-white/30 focus:outline-none transition-all">
             </div>
        </div>
    </div>

    <?php if (isset($_SESSION['admin_message'])): ?>
    <div class="p-4 rounded-xl bg-admin-primary/10 border border-admin-primary/20 text-white text-center text-sm">
        <?= $_SESSION['admin_message'] ?><?php unset($_SESSION['admin_message']); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Form Section -->
        <div class="lg:col-span-4">
            <div class="sticky top-24">
                <div class="admin-card p-6 bg-admin-gray-800/40 border border-white/5 rounded-2xl shadow-xl">
                    <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-ticket-alt text-admin-primary"></i> Novo Cupom
                    </h2>
                    
                    <form action="gerenciar_cupons.php" method="POST" class="space-y-5">
                        <div>
                            <label class="block text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest mb-2 ml-1">Código Promocional</label>
                            <input type="text" name="codigo" required placeholder="EX: NATAL25" 
                                class="w-full bg-admin-gray-900 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-white/30 focus:outline-none transition-all uppercase">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest mb-3 ml-1">Tipo de Desconto</label>
                            <select name="tipo" class="w-full bg-admin-gray-900 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-white/30 transition-all cursor-pointer">
                                <option value="porcentagem">Porcentagem (%)</option>
                                <option value="fixo">Valor Fixo (R$)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest mb-2 ml-1">Valor</label>
                                <input type="number" step="0.01" name="valor" required placeholder="10.00" 
                                    class="w-full bg-admin-gray-900 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-white/30 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest mb-2 ml-1">Limite de Usos</label>
                                <input type="number" name="usos_max" placeholder="0 = ∞" 
                                    class="w-full bg-admin-gray-900 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-white/30 transition-all">
                            </div>
                        </div>

                        <button type="submit" name="adicionar"
                            class="w-full bg-white text-black font-bold py-4 rounded-xl hover:opacity-90 transition-all shadow-lg active:scale-95">
                            Criar Cupom
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- List Section -->
        <div class="lg:col-span-8">
            <div class="admin-card overflow-hidden bg-admin-gray-800/20 border border-white/5 rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full" id="coupons-table">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Código</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Desconto</th>
                                <th class="px-6 py-4 text-center text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Utilização</th>
                                <th class="px-6 py-4 text-center text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-right text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if (empty($cupons)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center text-admin-gray-500">
                                    <i class="fas fa-ticket-alt text-3xl mb-3 opacity-50 block"></i>
                                    Nenhum cupom gerado.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($cupons as $cupom): ?>
                            <tr class="group hover:bg-white/[0.02] transition-colors" data-code="<?= strtolower(htmlspecialchars($cupom['codigo'])) ?>">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-mono font-black text-white bg-white/5 px-3 py-1.5 rounded-lg border border-white/10">
                                        <?= htmlspecialchars($cupom['codigo']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-green-400">
                                        <?= $cupom['tipo'] == 'porcentagem' ? number_format($cupom['valor'], 0) . '%' : formatarPreco($cupom['valor']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs text-white font-bold"><?= $cupom['usos_atuais'] ?></span>
                                        <div class="w-16 h-1 bg-white/5 rounded-full mt-1 overflow-hidden">
                                            <?php 
                                            $percent = $cupom['usos_max'] > 0 ? min(100, ($cupom['usos_atuais'] / $cupom['usos_max']) * 100) : 0;
                                            ?>
                                            <div class="h-full bg-admin-primary" style="width: <?= $percent ?>%"></div>
                                        </div>
                                        <span class="text-[9px] text-admin-gray-500 mt-1 uppercase">Limite: <?= $cupom['usos_max'] == 0 ? '∞' : $cupom['usos_max'] ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="?toggle=<?= $cupom['id'] ?>"
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest transition-all <?= $cupom['ativo'] ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-red-500/10 text-red-500 border border-red-500/20' ?>">
                                        <i class="fas <?= $cupom['ativo'] ? 'fa-check' : 'fa-times' ?>"></i>
                                        <?= $cupom['ativo'] ? 'Ativo' : 'Pausado' ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="?deletar=<?= $cupom['id'] ?>" onclick="return confirm('Deseja realmente excluir este cupom?')"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-500/5 text-red-500/50 hover:bg-red-500 hover:text-white transition-all">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('coupon-search');
    const tableBody = document.querySelector('#coupons-table tbody');
    const rows = tableBody.querySelectorAll('tr[data-code]');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        rows.forEach(row => {
            const code = row.getAttribute('data-code');
            row.style.display = code.includes(query) ? '' : 'none';
        });
    });
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
