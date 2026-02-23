<?php
// admin/gerenciar_afiliados.php - Gestão de Afiliados Premium com Busca
require_once 'secure.php';
$page_title = 'Afiliados';

// Filtros
$search = $_GET['search'] ?? '';

// Processar adição via POST (mantendo lógica original)
if (isset($_POST['adicionar'])) {
    $email_usuario = $_POST['email'];
    $codigo = strtoupper($_POST['codigo']);
    $chave_pix = $_POST['chave_pix'];

    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email_usuario]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("INSERT INTO afiliados (usuario_id, codigo, chave_pix) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $codigo, $chave_pix]);
            $_SESSION['admin_message'] = "Afiliado cadastrado com sucesso!";
        } else {
            $_SESSION['admin_message'] = "Usuário não encontrado!";
        }
    } catch (Exception $e) {
        $_SESSION['admin_message'] = "Erro: " . $e->getMessage();
    }
    header("Location: gerenciar_afiliados.php");
    exit();
}

// Listar afiliados com filtro
try {
    $sql = "SELECT a.*, u.nome, u.email 
            FROM afiliados a 
            JOIN usuarios u ON a.usuario_id = u.id 
            WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (u.nome LIKE :search OR u.email LIKE :search OR a.codigo LIKE :search)";
        $params[':search'] = "%$search%";
    }

    $sql .= " ORDER BY a.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $afiliados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Estatísticas
    $total_afiliados = count($afiliados);
    $saldo_total = array_sum(array_column($afiliados, 'saldo'));

} catch (Exception $e) {
    $afiliados = [];
    $total_afiliados = 0;
    $saldo_total = 0;
}

require_once 'templates/header_admin.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Afiliados</h1>
            <p class="text-admin-gray-400">Gerencie seus parceiros e comissões</p>
        </div>
        <button onclick="document.getElementById('form-novo').scrollIntoView({behavior:'smooth'})" 
            class="w-full sm:w-auto px-6 py-3 bg-white text-black font-bold rounded-xl hover:opacity-90 transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
            <i class="fas fa-plus"></i> NOVO AFILIADO
        </button>
    </div>

    <?php if (isset($_SESSION['admin_message'])): ?>
    <div class="p-4 rounded-xl bg-admin-primary/10 border border-admin-primary/20 text-white text-center text-sm">
        <?= $_SESSION['admin_message'] ?><?php unset($_SESSION['admin_message']); ?>
    </div>
    <?php endif; ?>

    <!-- Estatísticas -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="admin-card p-4 flex items-center gap-4 bg-white/5 border border-white/5 rounded-2xl">
            <div class="w-10 h-10 rounded-xl bg-admin-primary/10 flex items-center justify-center text-admin-primary">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <p class="text-[10px] text-admin-gray-500 font-bold uppercase tracking-widest">Ativos</p>
                <p class="text-lg font-bold text-white"><?= $total_afiliados ?></p>
            </div>
        </div>
        <div class="admin-card p-4 flex items-center gap-4 bg-white/5 border border-white/5 rounded-2xl">
            <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500">
                <i class="fas fa-wallet"></i>
            </div>
            <div>
                <p class="text-[10px] text-admin-gray-500 font-bold uppercase tracking-widest">Saldo Total</p>
                <p class="text-lg font-bold text-white"><?= formatarPreco($saldo_total) ?></p>
            </div>
        </div>
    </div>

    <!-- Filtro -->
    <div class="admin-card p-4 bg-admin-gray-800/40 border border-white/5 rounded-2xl">
        <form method="GET" id="search-form" class="relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-admin-gray-500"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                placeholder="Buscar por nome, email ou código do parceiro..." 
                class="w-full bg-admin-gray-900 border border-white/10 rounded-xl py-4 pl-12 pr-4 text-sm text-white focus:border-white/30 focus:outline-none transition-all placeholder:text-admin-gray-600">
        </form>
    </div>

    <!-- Lista -->
    <div class="admin-card overflow-hidden bg-admin-gray-800/20 border border-white/5 rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Parceiro</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Código</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Saldo</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Chave PIX</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($afiliados)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center text-admin-gray-500">
                            Nenhum parceiro encontrado.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($afiliados as $af): ?>
                    <tr class="group hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white font-bold text-xs">
                                    <?= strtoupper(substr($af['nome'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white"><?= htmlspecialchars($af['nome']) ?></div>
                                    <div class="text-[10px] text-admin-gray-500 font-mono"><?= htmlspecialchars($af['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="px-3 py-1 bg-admin-primary/10 text-admin-primary rounded-lg text-[10px] font-bold border border-admin-primary/20 tracking-widest">
                                <?= htmlspecialchars($af['codigo']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-sm font-bold text-green-400"><?= formatarPreco($af['saldo']) ?></div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-[10px] text-admin-gray-400 font-mono italic"><?= htmlspecialchars($af['chave_pix'] ?: '-') ?></div>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <button class="w-8 h-8 rounded-lg bg-white/5 text-admin-gray-400 hover:text-white transition-all">
                                <i class="fas fa-ellipsis-v text-xs"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Novo Afiliado Form (No rodapé para não poluir o topo) -->
    <div id="form-novo" class="admin-card p-6 bg-admin-gray-800/40 border border-white/5 rounded-2xl shadow-xl mt-12 animate-fade-in">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
            <i class="fas fa-user-plus text-admin-primary"></i> Novo Parceiro de Afiliação
        </h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest mb-2 ml-1">E-mail do Usuário Existente</label>
                <input type="email" name="email" required placeholder="parceiro@email.com" 
                    class="w-full bg-admin-gray-900 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-white/30 focus:outline-none transition-all">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest mb-2 ml-1">Código Promocional</label>
                <input type="text" name="codigo" required placeholder="EX: MEUCUPOM10" 
                    class="w-full bg-admin-gray-900 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-white/30 focus:outline-none transition-all uppercase">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest mb-2 ml-1">Chave PIX para Comissões</label>
                <input type="text" name="chave_pix" placeholder="CPF, Email, Celular ou Aleatória" 
                    class="w-full bg-admin-gray-900 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-white/30 focus:outline-none transition-all">
            </div>
            <div class="md:col-span-3">
                <button type="submit" name="adicionar" 
                    class="w-full bg-white text-black font-bold py-4 rounded-xl hover:opacity-90 transition-all shadow-lg active:scale-95 uppercase tracking-widest text-xs">
                    Confirmar Cadastro de Afiliado
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const searchForm = document.getElementById('search-form');
    let timeout = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => searchForm.submit(), 600);
    });

    if (searchInput.value) {
        searchInput.focus();
        const val = searchInput.value;
        searchInput.value = ''; searchInput.value = val;
    }
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
