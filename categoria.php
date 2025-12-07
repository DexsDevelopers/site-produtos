<?php
// categoria.php
session_start();
require_once 'config.php';

// --- BUSCA DE DADOS ---
$categoria_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Busca o nome da categoria para usar como título
try {
    $stmt_cat = $pdo->prepare("SELECT nome FROM categorias WHERE id = ?");
    $stmt_cat->execute([$categoria_id]);
    $categoria = $stmt_cat->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Em caso de erro, redireciona para a home
    header('Location: index.php');
    exit();
}

// Se a categoria não existe, redireciona para a home
if (!$categoria) {
    header('Location: index.php');
    exit();
}

// --- LÓGICA DE PAGINAÇÃO ---
$produtos_por_pagina = 12; // Quantos produtos você quer mostrar por página
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_atual < 1) {
    $pagina_atual = 1;
}
$offset = ($pagina_atual - 1) * $produtos_por_pagina;

// Conta o total de produtos na categoria para calcular o número de páginas
$total_produtos_stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = ?");
$total_produtos_stmt->execute([$categoria_id]);
$total_produtos = $total_produtos_stmt->fetchColumn();
$total_paginas = ceil($total_produtos / $produtos_por_pagina);

// Busca os produtos da página atual
$stmt_produtos = $pdo->prepare("SELECT * FROM produtos WHERE categoria_id = ? ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt_produtos->bindValue(1, $categoria_id, PDO::PARAM_INT);
$stmt_produtos->bindValue(2, $produtos_por_pagina, PDO::PARAM_INT);
$stmt_produtos->bindValue(3, $offset, PDO::PARAM_INT);
$stmt_produtos->execute();
$produtos = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);


require_once 'templates/header.php';
?>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <div class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-black text-white">Categoria: <?= htmlspecialchars($categoria['nome']) ?></h1>
            <p class="text-brand-gray-text mt-2"><?= $total_produtos ?> produtos encontrados</p>
        </div>

        <?php if (empty($produtos)): ?>
            <div class="text-center py-16 bg-brand-gray/50 rounded-lg">
                <p class="text-2xl text-white">Nenhum produto encontrado nesta categoria.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($produtos as $produto): ?>
                    <a href="produto.php?id=<?= $produto['id'] ?>" class="block group">
                        <div class="bg-brand-black border border-brand-gray-light rounded-xl overflow-hidden transition-all duration-300 hover:border-brand-red hover:shadow-xl hover:shadow-brand-red/10">
                            <div class="aspect-square overflow-hidden">
                                <img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110">
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-lg text-white truncate"><?= htmlspecialchars($produto['nome']) ?></h3>
                                <p class="text-sm text-brand-gray-text mt-1 truncate"><?= htmlspecialchars($produto['descricao_curta']) ?></p>
                                <p class="text-xl font-bold text-white mt-3"><?= formatarPreco($produto['preco']) ?></p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($total_paginas > 1): ?>
            <nav class="flex justify-center items-center gap-4 mt-12">
                <?php if ($pagina_atual > 1): ?>
                    <a href="categoria.php?id=<?= $categoria_id ?>&pagina=<?= $pagina_atual - 1 ?>" class="text-white bg-brand-gray-light py-2 px-4 rounded-lg hover:bg-brand-red">&larr; Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="categoria.php?id=<?= $categoria_id ?>&pagina=<?= $i ?>" class="<?= ($i == $pagina_atual) ? 'bg-brand-red' : 'bg-brand-gray-light' ?> text-white font-bold py-2 px-4 rounded-lg hover:bg-brand-red-dark"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="categoria.php?id=<?= $categoria_id ?>&pagina=<?= $pagina_atual + 1 ?>" class="text-white bg-brand-gray-light py-2 px-4 rounded-lg hover:bg-brand-red">Próxima &rarr;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>