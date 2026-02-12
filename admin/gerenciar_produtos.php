<?php
// admin/gerenciar_produtos.php - Listagem de Produtos Premium Style
require_once 'secure.php';
$page_title = 'Meus Produtos';
require_once 'templates/header_admin.php';

// Busca produtos
try {
    $stmt = $pdo->query("SELECT p.*, c.nome as categoria_nome 
                         FROM produtos p 
                         LEFT JOIN categorias c ON p.categoria_id = c.id 
                         ORDER BY p.id DESC");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
    if (strpos($e->getMessage(), 'Unknown column \'destaque\'') !== false) {
        require_once 'migrar_destaques.php';
        try {
            $stmt = $pdo->query("SELECT p.*, c.nome as categoria_nome 
                                 FROM produtos p 
                                 LEFT JOIN categorias c ON p.categoria_id = c.id 
                                 ORDER BY p.id DESC");
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (Exception $e2) {
            $produtos = [];
        }
    }
    else {
        $produtos = [];
        $erro = "Erro ao buscar produtos.";
    }
}
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Produtos</h1>
            <p class="text-admin-gray-400">Gerencie seu catálogo completo</p>
        </div>
        <a href="adicionar_produto.php"
            class="btn btn-primary bg-white text-black px-6 py-3 rounded-full font-bold uppercase tracking-wider text-sm hover:opacity-90 transition-opacity w-full sm:w-auto text-center">
            <i class="fas fa-plus mr-2"></i> Novo Produto
        </a>
    </div>

    <!-- Tabela Responsiva / Cards -->
    <div class="admin-card overflow-hidden">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-admin-gray-800/50">
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Produto</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Categoria</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Preço</th>
                        <th
                            class="px-6 py-4 text-center text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Vitrine</th>
                        <th
                            class="px-6 py-4 text-right text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (!empty($produtos)): ?>
                    <?php foreach ($produtos as $produto): ?>
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-4">
                                <div
                                    class="h-12 w-12 rounded-lg bg-admin-gray-800 flex-shrink-0 border border-white/10 overflow-hidden">
                                    <?php if (!empty($produto['imagem'])): ?>
                                    <img class="h-full w-full object-cover"
                                        src="../<?= htmlspecialchars($produto['imagem'])?>" alt="">
                                    <?php
        else: ?>
                                    <div class="h-full w-full flex items-center justify-center text-admin-gray-500"><i
                                            class="fas fa-image"></i></div>
                                    <?php
        endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-white truncate max-w-xs"
                                        title="<?= htmlspecialchars($produto['nome'])?>">
                                        <?= htmlspecialchars($produto['nome'])?>
                                    </div>
                                    <div class="text-xs text-admin-gray-500">ID:
                                        <?= $produto['id']?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/10 text-white border border-white/10">
                                <?= htmlspecialchars($produto['categoria_nome'] ?? 'Sem Categoria')?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-gray-300">
                            R$
                            <?= number_format($produto['preco'], 2, ',', '.')?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <a href="toggle_destaque_produto.php?id=<?= $produto['id']?>&destaque=<?= $produto['destaque'] ? 0 : 1?>"
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-colors <?= $produto['destaque'] ? 'bg-admin-primary/10 text-admin-primary border border-admin-primary/20' : 'bg-admin-gray-800 text-admin-gray-500 border border-white/5'?>">
                                <i class="fas <?= $produto['destaque'] ? 'fa-star' : 'fa-star-half-alt'?>"></i>
                                <?= $produto['destaque'] ? 'Destaque' : 'Normal'?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-3">
                                <a href="editar_produto.php?id=<?= $produto['id']?>"
                                    class="text-admin-primary hover:text-white transition-colors"><i
                                        class="fas fa-edit"></i></a>
                                <a href="deletar_produto.php?id=<?= $produto['id']?>"
                                    onclick="return confirm('Tem certeza?')"
                                    class="text-red-500 hover:text-red-400 transition-colors"><i
                                        class="fas fa-trash"></i></a>
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

        <!-- Mobile Layout: Cards -->
        <div class="md:hidden divide-y divide-white/5">
            <?php if (!empty($produtos)): ?>
            <?php foreach ($produtos as $produto): ?>
            <div class="p-4">
                <div class="flex gap-4 mb-4">
                    <div
                        class="h-16 w-16 rounded-xl bg-admin-gray-800 border border-white/10 overflow-hidden flex-shrink-0">
                        <?php if (!empty($produto['imagem'])): ?>
                        <img class="h-full w-full object-cover" src="../<?= htmlspecialchars($produto['imagem'])?>"
                            alt="">
                        <?php
        else: ?>
                        <div class="h-full w-full flex items-center justify-center text-admin-gray-500"><i
                                class="fas fa-image"></i></div>
                        <?php
        endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-white font-bold text-sm truncate">
                            <?= htmlspecialchars($produto['nome'])?>
                        </h3>
                        <p class="text-xs text-admin-gray-500 mt-0.5">
                            <?= htmlspecialchars($produto['categoria_nome'] ?? 'Sem Categoria')?>
                        </p>
                        <div class="text-admin-primary font-bold text-sm mt-1">R$
                            <?= number_format($produto['preco'], 2, ',', '.')?>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2">
                    <a href="toggle_destaque_produto.php?id=<?= $produto['id']?>&destaque=<?= $produto['destaque'] ? 0 : 1?>"
                        class="flex-1 py-2.5 rounded-lg text-center text-[10px] font-bold uppercase border transition-all <?= $produto['destaque'] ? 'bg-admin-primary/10 text-admin-primary border-admin-primary/20' : 'bg-white/5 text-admin-gray-500 border-white/10'?>">
                        <i class="fas fa-star"></i> Vitrine
                    </a>
                    <a href="editar_produto.php?id=<?= $produto['id']?>"
                        class="w-12 h-10 flex items-center justify-center rounded-lg bg-white text-black">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="deletar_produto.php?id=<?= $produto['id']?>" onclick="return confirm('Tem certeza?')"
                        class="w-12 h-10 flex items-center justify-center rounded-lg bg-red-500/10 text-red-500 border border-red-500/20">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
            <?php
    endforeach; ?>
            <?php
else: ?>
            <div class="p-12 text-center text-admin-gray-500">Nenhum produto encontrado</div>
            <?php
endif; ?>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>