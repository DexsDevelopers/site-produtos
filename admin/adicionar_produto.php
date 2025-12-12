<?php
// admin/adicionar_produto.php
require_once 'secure.php';
require_once 'templates/header_admin.php';
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY ordem ASC')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="w-full max-w-4xl mx-auto">
    <h1 class="text-3xl font-black text-white mb-8">Adicionar Novo Produto</h1>
    <div class="admin-card rounded-xl p-8">
        <?php if (isset($_SESSION['admin_message'])) { echo '<div class="bg-admin-primary/20 text-admin-primary p-4 rounded-lg mb-6 text-center">' . $_SESSION['admin_message'] . '</div>'; unset($_SESSION['admin_message']); } ?>
        <form action="salvar_produto.php" method="POST" enctype="multipart/form-data">
            <div class="space-y-6">
                <div><label for="nome" class="block text-sm font-medium text-admin-gray-300 mb-2">Nome do Produto</label><input type="text" name="nome" required class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                <div><label for="descricao_curta" class="block text-sm font-medium text-admin-gray-300 mb-2">Descrição Curta (para o card da vitrine)</label><input type="text" name="descricao_curta" maxlength="100" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                <div><label for="descricao" class="block text-sm font-medium text-admin-gray-300 mb-2">Descrição Completa</label><textarea name="descricao" rows="5" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none resize-vertical"></textarea></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label for="preco" class="block text-sm font-medium text-admin-gray-300 mb-2">Preço (ex: 197.00)</label><input type="text" name="preco" required class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                    <div><label for="preco_antigo" class="block text-sm font-medium text-admin-gray-300 mb-2">Preço Antigo (opcional)</label><input type="text" name="preco_antigo" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                </div>
                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-admin-gray-300 mb-2">Categoria</label>
                    <select name="categoria_id" required class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none">
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categorias as $categoria): ?><option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nome']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label for="imagem" class="block text-sm font-medium text-admin-gray-300 mb-2">Imagem do Produto</label><input type="file" name="imagem" required accept="image/*" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-admin-primary file:text-white hover:file:bg-blue-600 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
            </div>
            <button type="submit" name="adicionar" class="w-full mt-8 bg-admin-primary hover:bg-blue-600 text-white font-bold text-lg py-4 rounded-lg transition-colors">Adicionar Produto</button>
        </form>
    </div>
</div>
<?php require_once 'templates/footer_admin.php'; ?>