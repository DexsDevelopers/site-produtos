<?php
// admin/gerenciar_categorias.php - Premium Design
require_once 'secure.php';
$page_title = 'Categorias';
require_once 'templates/header_admin.php';

// Busca todas as categorias
try {
    $stmt = $pdo->query('SELECT * FROM categorias ORDER BY parent_id ASC, ordem ASC');
    $todas_categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organiza em hierarquia
    $categorias_hierarquia = [];
    $categorias_pais = [];

    foreach ($todas_categorias as $cat) {
        if (empty($cat['parent_id'])) {
            $categorias_hierarquia[$cat['id']] = $cat;
            $categorias_hierarquia[$cat['id']]['subcategorias'] = [];
            $categorias_pais[] = $cat;
        }
    }

    foreach ($todas_categorias as $cat) {
        if (!empty($cat['parent_id']) && isset($categorias_hierarquia[$cat['parent_id']])) {
            $categorias_hierarquia[$cat['parent_id']]['subcategorias'][] = $cat;
        }
    }

    // Lista plana para o loop da tabela (mantendo a ordem hierÃ¡rquica)
    $categorias = [];
    foreach ($categorias_hierarquia as $pai) {
        $categorias[] = $pai;
        if (!empty($pai['subcategorias'])) {
            foreach ($pai['subcategorias'] as $sub) {
                $sub['is_sub'] = true;
                $categorias[] = $sub;
            }
        }
    }
}
catch (Exception $e) {
    // Se a coluna parent_id nÃ£o existir, tenta migrar automaticamente
    if (strpos($e->getMessage(), 'Unknown column \'parent_id\'') !== false) {
        $pdo->exec("ALTER TABLE categorias ADD COLUMN parent_id INT DEFAULT NULL");
        header("Location: gerenciar_categorias.php");
        exit();
    }
    else if (strpos($e->getMessage(), 'Unknown column \'exibir_home\'') !== false) {
        require_once 'migrar_destaques.php';
        header("Location: gerenciar_categorias.php");
        exit();
    }
    else {
        $categorias = [];
        $categorias_pais = [];
    }
}
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Form Section -->
    <div class="lg:col-span-1">
        <div class="sticky top-24 space-y-6">
            <h2 class="text-2xl font-bold text-white">Nova Categoria <!-- V: <?= date('Y-m-d H:i:s')?> --></h2>

            <div class="admin-card p-6">
                <form action="processa_categoria.php" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="nome"
                                class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Nome
                                da Categoria</label>
                            <input type="text" name="nome" required placeholder="Ex: TÃªnis" class="w-full">
                        </div>
                        <!-- Tipo de Categoria -->
                        <div>
                            <label
                                class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Tipo
                                de Categoria</label>
                            <div class="flex gap-4 mb-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="tipo_categoria" value="principal" checked
                                        onclick="document.getElementById('parent_select_container').classList.add('hidden'); document.getElementById('parent_id_select').value = '';"
                                        class="text-admin-primary focus:ring-admin-primary bg-admin-gray-900 border-white/10">
                                    <span class="text-white text-sm">Principal</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="tipo_categoria" value="subcategoria"
                                        onclick="document.getElementById('parent_select_container').classList.remove('hidden')"
                                        class="text-admin-primary focus:ring-admin-primary bg-admin-gray-900 border-white/10">
                                    <span class="text-white text-sm">Subcategoria</span>
                                </label>
                            </div>
                        </div>

                        <!-- Select de Pai (Oculto inicialmente) -->
                        <div id="parent_select_container" class="hidden">
                            <label for="parent_id"
                                class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Selecione
                                a Categoria Pai</label>
                            <select name="parent_id" id="parent_id_select"
                                class="w-full bg-admin-gray-900 border border-white/10 text-white p-2 rounded-lg focus:border-admin-primary focus:ring-1 focus:ring-admin-primary outline-none">
                                <option value="" selected disabled>Escolha uma categoria...</option>
                                <?php foreach ($categorias_pais as $pai): ?>
                                <option value="<?= $pai['id']?>">
                                    <?= htmlspecialchars($pai['nome'])?>
                                </option>
                                <?php
endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="adicionar"
                            class="btn btn-primary w-full bg-white text-black hover:bg-gray-200">
                            Adicionar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- List Section -->
    <div class="lg:col-span-2">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-white">Categorias Ativas</h2>
            <div class="flex items-center gap-4">
                <a href="corrigir_ordem_categorias.php" class="text-xs text-admin-primary hover:underline">
                    <i class="fas fa-sync-alt mr-1"></i> Corrigir Ordem
                </a>
                <span class="text-sm text-admin-gray-400">
                    <?= count($categorias)?> categorias
                </span>
            </div>
        </div>

        <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="mb-6 p-4 rounded-lg bg-admin-primary/10 border border-admin-primary/20 text-white text-center">
            <?= $_SESSION['admin_message']?>
            <?php unset($_SESSION['admin_message']); ?>
        </div>
        <?php
endif; ?>

        <div class="admin-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-admin-gray-800/50">
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider w-24">
                                Ordem</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                                Nome / Subcat</th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                                Home</th>
                            <th
                                class="px-6 py-4 text-right text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                                AÃ§Ãµes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-admin-gray-500">
                                Nenhuma categoria cadastrada.
                            </td>
                        </tr>
                        <?php
else: ?>
                        <?php foreach ($categorias as $index => $categoria): ?>
                        <tr
                            class="group hover:bg-white/5 transition-colors <?= isset($categoria['is_sub']) ? 'bg-white/[0.02]' : ''?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="flex flex-col gap-1">
                                        <?php if ($index > 0): ?>
                                        <a href="processa_ordem_categoria.php?id=<?= $categoria['id']?>&direcao=up"
                                            class="text-admin-gray-500 hover:text-white p-1 hover:bg-white/10 rounded transition-colors"
                                            title="Mover para cima">
                                            <i class="fas fa-chevron-up text-xs"></i>
                                        </a>
                                        <?php
        endif; ?>

                                        <?php if ($index < count($categorias) - 1): ?>
                                        <a href="processa_ordem_categoria.php?id=<?= $categoria['id']?>&direcao=down"
                                            class="text-admin-gray-500 hover:text-white p-1 hover:bg-white/10 rounded transition-colors"
                                            title="Mover para baixo">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </a>
                                        <?php
        endif; ?>
                                    </div>
                                    <span class="text-xs font-mono text-admin-gray-500 ml-2">
                                        <?= $categoria['ordem']?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                                <div class="flex items-center gap-2">
                                    <?php if (isset($categoria['is_sub'])): ?>
                                    <span class="text-admin-gray-600 ml-4"><i
                                            class="fas fa-level-up-alt fa-rotate-90"></i></span>
                                    <span class="text-admin-gray-300">
                                        <?= htmlspecialchars($categoria['nome'])?>
                                    </span>
                                    <?php
        else: ?>
                                    <i class="fas fa-folder text-admin-primary/50 mr-1"></i>
                                    <span class="text-white font-bold">
                                        <?= htmlspecialchars($categoria['nome'])?>
                                    </span>
                                    <?php
        endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="processa_visibilidade_categoria.php?id=<?= $categoria['id']?>&visivel=<?= $categoria['exibir_home'] ? 0 : 1?>"
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-colors <?= $categoria['exibir_home'] ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-admin-gray-800 text-admin-gray-500 border border-white/5'?>"
                                    title="<?= $categoria['exibir_home'] ? 'VisÃ­vel na Home' : 'Oculto na Home'?>">
                                    <i class="fas <?= $categoria['exibir_home'] ? 'fa-eye' : 'fa-eye-slash'?>"></i>
                                    <?= $categoria['exibir_home'] ? 'Na Home' : 'Oculto'?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div
                                    class="flex items-center justify-end gap-3 opacity-60 group-hover:opacity-100 transition-opacity">
                                    <a href="editar_categoria.php?id=<?= $categoria['id']?>"
                                        class="text-admin-primary hover:text-white transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="processa_categoria.php?deletar=<?= $categoria['id']?>"
                                        class="text-red-500 hover:text-red-400 transition-colors"
                                        onclick="return confirm('Tem certeza? Isso pode afetar produtos vinculados.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
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
</div>

<?php require_once 'templates/footer_admin.php'; ?>