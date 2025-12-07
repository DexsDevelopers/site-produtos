<?php
// admin/gerenciar_categorias.php
require_once 'secure.php';
require_once 'templates/header_admin.php';

// Busca todas as categorias, AGORA ORDENADAS PELA NOVA COLUNA 'ordem'
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY ordem ASC')->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1">
        <h2 class="text-2xl font-semibold text-white mb-4">Adicionar Nova Categoria</h2>
        <div class="bg-brand-gray-light p-6 rounded-lg">
            <form action="processa_categoria.php" method="POST">
                <div>
                    <label for="nome" class="block text-sm font-medium text-brand-gray-text">Nome da Categoria</label>
                    <input type="text" name="nome" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray text-white">
                </div>
                <button type="submit" name="adicionar" class="w-full mt-4 bg-brand-red hover:bg-brand-red-dark text-white font-bold py-3 rounded-lg">
                    Salvar Categoria
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold text-white">Categorias Existentes</h2>
            <a href="editar_categoria.php" class="bg-brand-red hover:bg-brand-red-dark text-white font-bold py-2 px-4 rounded-lg text-sm">
                <i class="fas fa-plus mr-1"></i>
                Nova Categoria
            </a>
        </div>
        <div class="bg-brand-gray-light p-6 rounded-lg">
            <?php
            if (isset($_SESSION['admin_message'])) {
                echo '<div class="bg-blue-500/20 text-blue-300 p-3 rounded-lg mb-4 text-center">' . $_SESSION['admin_message'] . '</div>';
                unset($_SESSION['admin_message']);
            }
            ?>
            <table class="w-full text-left text-sm">
                <thead class="bg-brand-black text-xs text-gray-400 uppercase">
                    <tr>
                        <th class="px-4 py-3">Ordem</th>
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                    <tr class="border-b border-brand-gray">
                        <td class="px-4 py-3 font-medium text-white flex items-center gap-2">
                            <a href="processa_ordem_categoria.php?id=<?= $categoria['id'] ?>&direcao=up" title="Mover para Cima">▲</a>
                            <a href="processa_ordem_categoria.php?id=<?= $categoria['id'] ?>&direcao=down" title="Mover para Baixo">▼</a>
                        </td>
                        <td class="px-4 py-3 text-white"><?= htmlspecialchars($categoria['nome']) ?></td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <a href="editar_categoria.php?id=<?= $categoria['id'] ?>" class="font-medium text-blue-500 hover:underline">
                                    Editar
                                </a>
                                <a href="processa_categoria.php?deletar=<?= $categoria['id'] ?>" class="font-medium text-red-500 hover:underline" onclick="return confirm('Tem certeza?');">
                                    Deletar
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer_admin.php';
?>