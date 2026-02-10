<?php
// categoria.php — MACARIO BRAZIL
session_start();
require_once 'config.php';

$categoria_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$itens_por_pagina = 12;

try {
    // Info da Categoria
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->execute([$categoria_id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$categoria) {
        header('Location: index.php');
        exit;
    }

    // Paginação
    $offset = ($pagina - 1) * $itens_por_pagina;
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = ?");
    $count_stmt->execute([$categoria_id]);
    $total_produtos = $count_stmt->fetchColumn();
    $total_paginas = ceil($total_produtos / $itens_por_pagina);

    // Produtos
    $stmt_prod = $pdo->prepare("SELECT * FROM produtos WHERE categoria_id = ? ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt_prod->bindValue(1, $categoria_id, PDO::PARAM_INT);
    $stmt_prod->bindValue(2, $itens_por_pagina, PDO::PARAM_INT);
    $stmt_prod->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt_prod->execute();
    $produtos = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);

}
catch (PDOException $e) {
    error_log("Erro categoria.php: " . $e->getMessage());
    header('Location: index.php');
    exit;
}

$page_title = $categoria['nome'];
require_once 'templates/header.php';
?>

<div class="container" style="padding-top: 40px; min-height: 80vh;">

    <!-- Hero da Categoria -->
    <div
        style="text-align: center; margin-bottom: 60px; padding: 60px 0; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
        <?php if (!empty($categoria['imagem_capa'])): ?>
        <!-- Se tivesse imagem de capa, poderia ir aqui como background -->
        <?php
endif; ?>
        <span
            style="font-size: 0.8rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--text-muted);">Coleção</span>
        <h1 style="font-size: clamp(2.5rem, 6vw, 4rem); margin: 16px 0 0;">
            <?= htmlspecialchars($categoria['nome'])?>
        </h1>
        <p
            style="color: var(--text-secondary); margin-top: 16px; max-width: 600px; margin-left: auto; margin-right: auto;">
            <?= $total_produtos?> produtos exclusivos selecionados para você.
        </p>
    </div>

    <!-- Grid -->
    <?php if (!empty($produtos)): ?>
    <div class="products-grid">
        <?php foreach ($produtos as $produto): ?>
        <a href="produto.php?id=<?= $produto['id']?>" class="product-card">
            <div class="product-image">
                <?php if (!empty($produto['imagem']) && file_exists($produto['imagem'])): ?>
                <img src="<?= htmlspecialchars($produto['imagem'])?>" alt="<?= htmlspecialchars($produto['nome'])?>"
                    loading="lazy" />
                <?php
        else: ?>
                <div class="product-image-placeholder">
                    <i class="fas fa-image"></i>
                </div>
                <?php
        endif; ?>
                <div class="product-badge">NOVO</div>
                <button class="product-quick-add" onclick="window.addToCart(<?= $produto['id']?>, event)">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="product-info">
                <span class="product-category-tag">
                    <?= htmlspecialchars($categoria['nome'])?>
                </span>
                <h3 class="product-name">
                    <?= htmlspecialchars($produto['nome'])?>
                </h3>
                <div class="product-price-row">
                    <span class="product-price">
                        <?= formatarPreco($produto['preco'])?>
                    </span>
                </div>
            </div>
        </a>
        <?php
    endforeach; ?>
    </div>

    <!-- Paginação -->
    <?php if ($total_paginas > 1): ?>
    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 60px;">
        <?php if ($pagina > 1): ?>
        <a href="?id=<?= $categoria_id?>&pagina=<?= $pagina - 1?>"
            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-secondary);">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php
        endif; ?>

        <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
        <a href="?id=<?= $categoria_id?>&pagina=<?= $i?>"
            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); border-radius: var(--radius-md); <?= $i == $pagina ? 'background: var(--text-primary); color: var(--bg-primary); border-color: var(--text-primary);' : 'color: var(--text-secondary);'?>">
            <?= $i?>
        </a>
        <?php
        endfor; ?>

        <?php if ($pagina < $total_paginas): ?>
        <a href="?id=<?= $categoria_id?>&pagina=<?= $pagina + 1?>"
            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-secondary);">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php
        endif; ?>
    </div>
    <?php
    endif; ?>

    <?php
else: ?>
    <div style="text-align: center; padding: 100px 0;">
        <i class="fas fa-box-open" style="font-size: 4rem; color: var(--border-color); margin-bottom: 24px;"></i>
        <h3>Categoria Vazia</h3>
        <p style="color: var(--text-muted);">Não há produtos nesta categoria no momento.</p>
        <a href="index.php" class="btn-text" style="margin-top: 24px; display: inline-block;">Voltar para Início</a>
    </div>
    <?php
endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>