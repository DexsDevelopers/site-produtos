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
    $produtos = [];
    $erro = "Erro ao buscar produtos.";
}
?>

<div class="space-y-6">
    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Produtos</h1>
            <p class="text-admin-gray-400">Gerencie seu catálogo completo</p>
        </div>
        <a href="adicionar_produto.php"
            class="btn btn-primary bg-white text-black px-6 py-3 rounded-full font-bold uppercase tracking-wider text-sm hover:opacity-90 transition-opacity">
            <i class="fas fa-plus mr-2"></i> Novo Produto
        </a>
    </div>

    <!-- Tabela de Produtos -->
    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
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
                                    <?php if (!empty($produto['imagem']) && file_exists('../' . $produto['imagem'])): ?>
                                    <img class="h-full w-full object-cover"
                                        src="../<?= htmlspecialchars($produto['imagem'])?>" alt="">
                                    <?php
        else: ?>
                                    <div class="h-full w-full flex items-center justify-center text-admin-gray-500">
                                        <i class="fas fa-image"></i>
                                    </div>
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
                            <?= number_format($produto['preco'], 2, ',', '.')?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-3">
                                <a href="../produto.php?id=<?= $produto['id']?>" target="_blank"
                                    class="text-admin-gray-400 hover:text-white transition-colors" title="Ver na Loja">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="editar_produto.php?id=<?= $produto['id']?>"
                                    class="text-admin-primary hover:text-white transition-colors" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="deletar_produto.php?id=<?= $produto['id']?>"
                                    onclick="return confirm('Tem certeza?')"
                                    class="text-red-500 hover:text-red-400 transition-colors" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php
    endforeach; ?>
                    <?php
else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-admin-gray-500">
                                <i class="fas fa-box-open text-4xl mb-3 opacity-50"></i>
                                <p class="text-lg">Nenhum produto encontrado</p>
                                <a href="adicionar_produto.php"
                                    class="mt-4 text-admin-primary hover:underline">Adicionar o primeiro produto</a>
                            </div>
                        </td>
                    </tr>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>