<?php
// admin/gerenciar_cupons.php - Gestão de Cupons Premium
require_once 'secure.php';
$page_title = 'Cupons';
require_once 'templates/header_admin.php';

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
    }
    catch (Exception $e) {
        $_SESSION['admin_message'] = "Erro: " . $e->getMessage();
    }
}

if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $pdo->prepare("UPDATE cupons SET ativo = NOT ativo WHERE id = ?")->execute([$id]);
    header("Location: gerenciar_cupons.php");
    exit();
}

if (isset($_GET['deletar'])) {
    $id = $_GET['deletar'];
    $pdo->prepare("DELETE FROM cupons WHERE id = ?")->execute([$id]);
    header("Location: gerenciar_cupons.php");
    exit();
}

// Listar
$cupons = $pdo->query("SELECT * FROM cupons ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Cupons de Desconto</h1>
            <p class="text-admin-gray-400">Gerencie promoções e códigos promocionais</p>
        </div>
    </div>

    <!-- Novo Cupom -->
    <div class="admin-card p-6">
        <h3 class="text-xl font-bold text-white mb-4">Novo Cupom</h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label
                    class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Código</label>
                <input type="text" name="codigo" required placeholder="DESCONTO10" class="w-full uppercase">
            </div>
            <div>
                <label
                    class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Tipo</label>
                <select name="tipo" class="w-full">
                    <option value="porcentagem">Porcentagem (%)</option>
                    <option value="fixo">Valor Fixo (R$)</option>
                </select>
            </div>
            <div>
                <label
                    class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Valor</label>
                <input type="number" step="0.01" name="valor" required placeholder="10.00" class="w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Limite (0 =
                    Ilimitado)</label>
                <input type="number" name="usos_max" placeholder="0" class="w-full">
            </div>
            <div class="md:col-span-4">
                <button type="submit" name="adicionar" class="btn btn-primary bg-white text-black hover:bg-gray-200">
                    Criar Cupom
                </button>
            </div>
        </form>
    </div>

    <!-- Lista -->
    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-admin-gray-800/50">
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Código</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Desconto</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Usos</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-4 text-right text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($cupons)): ?>
                    <tr>
                        <td colspan="5" class="p-6 text-center text-admin-gray-500">Nenhum cupom encontrado.</td>
                    </tr>
                    <?php
else: ?>
                    <?php foreach ($cupons as $cupom): ?>
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-white">
                            <?= htmlspecialchars($cupom['codigo'])?>
                        </td>
                        <td class="px-6 py-4 text-green-400 font-medium">
                            <?= $cupom['tipo'] == 'porcentagem' ? number_format($cupom['valor'], 0) . '%' : 'R$ ' . number_format($cupom['valor'], 2, ',', '.')?>
                        </td>
                        <td class="px-6 py-4 text-sm text-admin-gray-400">
                            <?= $cupom['usos_atuais']?> /
                            <?= $cupom['usos_max'] == 0 ? '∞' : $cupom['usos_max']?>
                        </td>
                        <td class="px-6 py-4">
                            <a href="?toggle=<?= $cupom['id']?>"
                                class="text-xs px-2 py-1 rounded <?= $cupom['ativo'] ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'?>">
                                <?= $cupom['ativo'] ? 'Ativo' : 'Inativo'?>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="?deletar=<?= $cupom['id']?>" onclick="return confirm('Excluir cupom?')"
                                class="text-red-500 hover:text-red-400">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php
    endforeach; ?>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>