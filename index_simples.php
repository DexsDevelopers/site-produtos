<?php
// index_simples.php - Versão simplificada da página inicial
session_start();
require_once 'config.php';

// Busca dados básicos
try {
    $banners_principais = $pdo->query("SELECT * FROM banners WHERE tipo = 'principal' AND ativo = 1 ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $banners_categorias = $pdo->query("SELECT * FROM banners WHERE tipo = 'categoria' AND ativo = 1 ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    $categorias = $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);
    $todos_produtos = $pdo->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);

    $produtos_por_categoria = [];
    foreach ($todos_produtos as $produto) {
        if (!empty($produto['categoria_id'])) {
            $produtos_por_categoria[$produto['categoria_id']][] = $produto;
        }
    }
} catch (Exception $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Minha Loja - O Mercado é dos Tubarões</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: Arial, sans-serif;
        }

        .gradient-title {
            background: linear-gradient(90deg, #ffffff, #a0aec0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body>
    <!-- Header Simples -->
    <nav class="bg-black p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold text-red-500">Minha Loja</h1>
            <div class="flex gap-4">
                <a href="busca.php" class="text-white hover:text-red-500">Buscar</a>
                <a href="carrinho.php" class="text-white hover:text-red-500">Carrinho</a>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="max-w-7xl mx-auto p-4">
        <h2 class="gradient-title text-4xl font-bold text-center mb-8">Bem-vindo à Nossa Loja!</h2>

        <!-- Banners Principais -->
        <?php if (!empty($banners_principais)): ?>
            <div class="mb-12">
                <h3 class="text-2xl font-bold mb-4">Destaques</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($banners_principais as $banner): ?>
                        <div class="bg-gray-800 rounded-lg overflow-hidden">
                            <img src="<?= htmlspecialchars($banner['imagem']) ?>"
                                alt="<?= htmlspecialchars($banner['titulo']) ?>" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h4 class="text-xl font-bold"><?= htmlspecialchars($banner['titulo']) ?></h4>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Categorias -->
        <?php if (!empty($banners_categorias)): ?>
            <div class="mb-12">
                <h3 class="text-2xl font-bold mb-4">Categorias</h3>
                <div class="flex gap-4 overflow-x-auto">
                    <?php foreach ($banners_categorias as $banner): ?>
                        <a href="<?= htmlspecialchars($banner['link']) ?>" class="flex-shrink-0 text-center">
                            <div class="w-20 h-20 bg-gray-800 rounded-full mb-2 flex items-center justify-center">
                                <img src="<?= htmlspecialchars($banner['imagem']) ?>"
                                    alt="<?= htmlspecialchars($banner['titulo']) ?>"
                                    class="w-16 h-16 object-cover rounded-full">
                            </div>
                            <span class="text-sm"><?= htmlspecialchars($banner['titulo']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Produtos por Categoria -->
        <?php foreach ($categorias as $categoria): ?>
            <?php if (!empty($produtos_por_categoria[$categoria['id']])): ?>
                <div class="mb-12">
                    <h3 class="text-2xl font-bold mb-6"><?= htmlspecialchars($categoria['nome']) ?></h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <?php foreach ($produtos_por_categoria[$categoria['id']] as $produto): ?>
                            <a href="produto.php?id=<?= $produto['id'] ?>"
                                class="bg-gray-800 rounded-lg overflow-hidden hover:bg-gray-700 transition-colors">
                                <img src="<?= htmlspecialchars($produto['imagem']) ?>"
                                    alt="<?= htmlspecialchars($produto['nome']) ?>" class="w-full h-32 object-cover">
                                <div class="p-3">
                                    <h4 class="font-bold text-sm truncate"><?= htmlspecialchars($produto['nome']) ?></h4>
                                    <p class="text-red-500 font-bold"><?= formatarPreco($produto['preco']) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-black p-8 text-center">
        <p>&copy; 2025 Minha Loja. Todos os direitos reservados.</p>
    </footer>
</body>

</html>