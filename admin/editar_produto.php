<?php
// admin/editar_produto.php
require_once 'secure.php';
require_once 'templates/header_admin.php';
$produto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produto = null;
if ($produto_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { die("Erro ao buscar produto: " . $e->getMessage()); }
}
if (!$produto) { $_SESSION['admin_message'] = "Produto não encontrado."; header("Location: index.php"); exit(); }
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY ordem ASC')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="w-full max-w-4xl mx-auto">
    <h1 class="text-3xl font-black text-white mb-8">Editando Produto: <?= htmlspecialchars($produto['nome']) ?></h1>
    <div class="admin-card rounded-xl p-8">
        <?php if (isset($_SESSION['admin_message'])) { echo '<div class="bg-admin-primary/20 text-admin-primary p-4 rounded-lg mb-6 text-center">' . $_SESSION['admin_message'] . '</div>'; unset($_SESSION['admin_message']); } ?>
        <form action="salvar_produto.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $produto['id'] ?>">
            <div class="space-y-6">
                <div><label for="nome" class="block text-sm font-medium text-admin-gray-300 mb-2">Nome do Produto</label><input type="text" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>" required class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                <div><label for="descricao_curta" class="block text-sm font-medium text-admin-gray-300 mb-2">Descrição Curta</label><input type="text" name="descricao_curta" maxlength="100" value="<?= htmlspecialchars($produto['descricao_curta']) ?>" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                <div><label for="descricao" class="block text-sm font-medium text-admin-gray-300 mb-2">Descrição Completa</label><textarea name="descricao" rows="5" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none resize-vertical"><?= htmlspecialchars($produto['descricao']) ?></textarea></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label for="preco" class="block text-sm font-medium text-admin-gray-300 mb-2">Preço</label><input type="text" name="preco" value="<?= htmlspecialchars($produto['preco']) ?>" required class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                    <div><label for="preco_antigo" class="block text-sm font-medium text-admin-gray-300 mb-2">Preço Antigo</label><input type="text" name="preco_antigo" value="<?= htmlspecialchars($produto['preco_antigo']) ?>" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                </div>
                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-admin-gray-300 mb-2">Categoria</label>
                    <select name="categoria_id" required class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none">
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categorias as $categoria): ?><option value="<?= $categoria['id'] ?>" <?= ($produto['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nome']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="imagem" class="block text-sm font-medium text-admin-gray-300 mb-2">Nova Imagem (opcional)</label>
                    <input type="file" name="imagem" accept="image/*" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-admin-primary file:text-white hover:file:bg-blue-600 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none">
                    <p class="text-xs text-admin-gray-400 mt-2">Imagem atual: <img src="../<?= htmlspecialchars($produto['imagem']) ?>" class="h-10 inline-block rounded"></p>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="destaque" id="destaque" value="1" <?= $produto['destaque'] ? 'checked' : '' ?> class="w-5 h-5 bg-admin-gray-800 border-admin-gray-600 rounded text-admin-primary focus:ring-admin-primary/20">
                    <label for="destaque" class="text-sm font-medium text-admin-gray-300">Marcar como Destaque na Home</label>
                </div>
            </div>
            <button type="submit" name="editar" class="w-full mt-8 bg-admin-primary hover:bg-blue-600 text-white font-bold text-lg py-4 rounded-lg transition-colors">Salvar Alterações</button>
        </form>
    </div>
</div>
<?php require_once 'templates/footer_admin.php'; ?>