<?php
// busca.php — MACARIO BRAZIL — Catálogo e Busca
session_start();
require_once 'config.php';

// Sanitiza e valida entrada
$termo = htmlspecialchars(trim($_GET['termo'] ?? ''), ENT_QUOTES, 'UTF-8');
$categoria_id = (int)($_GET['categoria'] ?? 0);
$preco_min = (float)($_GET['preco_min'] ?? 0);
$preco_max = (float)($_GET['preco_max'] ?? 0);
$ordenar = htmlspecialchars(trim($_GET['ordenar'] ?? 'relevancia'), ENT_QUOTES, 'UTF-8');
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$itens_por_pagina = 12;
$mostrar_todos = isset($_GET['todos']) && $_GET['todos'] == '1';

$resultados = [];
$total_resultados = 0;
$total_paginas = 0;

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

if (!empty($termo) || $categoria_id > 0 || $preco_min > 0 || $preco_max > 0 || $mostrar_todos) {
    try {
        $where_conditions = [];
        $params = [];

        if (!empty($termo)) {
            $termo_limpo = preg_replace('/[^a-zA-Z0-9\s]/', '', $termo);
            $termos = explode(' ', $termo_limpo);
            $termos = array_filter($termos, function ($t) {
                return strlen($t) > 1; });

            if (empty($termos))
                $termos = [$termo_limpo];

            $word_conditions = [];
            foreach ($termos as $word) {
                $word_like = "%$word%";
                $word_conditions[] = "(
                    LOWER(p.nome) LIKE LOWER(?) OR 
                    LOWER(p.descricao_curta) LIKE LOWER(?) OR 
                    LOWER(p.descricao) LIKE LOWER(?)
                )";
                $params[] = $word_like;
                $params[] = $word_like;
                $params[] = $word_like;
            }

            if (!empty($word_conditions)) {
                $where_conditions[] = "(" . implode(" AND ", $word_conditions) . ")";
            }
        }

        if ($categoria_id > 0) {
            $where_conditions[] = "categoria_id = ?";
            $params[] = $categoria_id;
        }

        if ($preco_min > 0) {
            $where_conditions[] = "preco >= ?";
            $params[] = $preco_min;
        }

        if ($preco_max > 0) {
            $where_conditions[] = "preco <= ?";
            $params[] = $preco_max;
        }

        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

        // Ordenação
        $order_clause = "ORDER BY ";
        switch ($ordenar) {
            case 'preco_asc':
                $order_clause .= "p.preco ASC";
                break;
            case 'preco_desc':
                $order_clause .= "p.preco DESC";
                break;
            case 'nome':
                $order_clause .= "p.nome ASC";
                break;
            default:
                $order_clause .= "p.id DESC";
                break;
        }

        // Count for pagination
        $count_sql = "SELECT COUNT(*) FROM produtos p $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_resultados = $count_stmt->fetchColumn();
        $total_paginas = ceil($total_resultados / $itens_por_pagina);

        $offset = ($pagina - 1) * $itens_por_pagina;

        // Fetch query
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                $where_clause 
                $order_clause 
                LIMIT $itens_por_pagina OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    catch (PDOException $e) {
        error_log("Erro busca: " . $e->getMessage());
        $resultados = [];
    }
}

$page_title = 'Catálogo';
require_once 'templates/header.php';
?>

<style>
    .filter-panel {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 24px;
        margin-bottom: 40px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        background: var(--bg-tertiary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-family: var(--font-body);
        font-size: 0.9rem;
    }

    .form-control:focus {
        border-color: var(--border-active);
        outline: none;
    }

    .filter-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 48px;
    }

    .page-link {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        border-radius: var(--radius-md);
        transition: all var(--transition-base);
    }

    .page-link:hover,
    .page-link.active {
        background: var(--text-primary);
        color: var(--bg-primary);
        border-color: var(--text-primary);
    }
</style>

<div class="container" style="padding-top: 40px; min-height: 80vh;">

    <!-- Header da Página -->
    <div style="margin-bottom: 40px;">
        <h1 style="font-size:clamp(2rem, 5vw, 3rem); margin-bottom: 12px;">Catálogo</h1>
        <p style="color:var(--text-muted);">
            <?php if ($total_resultados > 0): ?>
            Encontramos
            <?= $total_resultados?> produtos
            <?php
else: ?>
            Explore nossa coleção completa
            <?php
endif; ?>
        </p>
    </div>

    <!-- Painel de Filtros -->
    <div class="filter-panel">
        <form method="GET">
            <input type="hidden" name="todos" value="1">
            <div class="filter-grid">
                <div class="form-group">
                    <label>Buscar</label>
                    <input type="text" name="termo" value="<?= htmlspecialchars($termo)?>" class="form-control"
                        placeholder="O que você procura?">
                </div>
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="categoria" class="form-control">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id']?>" <?=$categoria_id==$cat['id'] ? 'selected' : ''?>>
                            <?= htmlspecialchars($cat['nome'])?>
                        </option>
                        <?php
endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ordenar</label>
                    <select name="ordenar" class="form-control">
                        <option value="relevancia" <?=$ordenar=='relevancia' ? 'selected' : ''?>>Relevância</option>
                        <option value="preco_asc" <?=$ordenar=='preco_asc' ? 'selected' : ''?>>Menor Preço</option>
                        <option value="preco_desc" <?=$ordenar=='preco_desc' ? 'selected' : ''?>>Maior Preço</option>
                        <option value="nome" <?=$ordenar=='nome' ? 'selected' : ''?>>Nome A-Z</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <a href="busca.php?todos=1" class="btn btn-outline btn-sm">Limpar</a>
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Grid de Resultados -->
    <?php if (!empty($resultados)): ?>
    <div class="products-grid">
        <?php foreach ($resultados as $produto): ?>
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
                <button class="product-quick-add" onclick="window.addToCart(<?= $produto['id']?>, event)">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="product-info">
                <?php if (!empty($produto['categoria_nome'])): ?>
                <span class="product-category-tag">
                    <?= htmlspecialchars($produto['categoria_nome'])?>
                </span>
                <?php
        endif; ?>
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
    <div class="pagination">
        <?php if ($pagina > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1]))?>" class="page-link"><i
                class="fas fa-chevron-left"></i></a>
        <?php
        endif; ?>

        <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i]))?>"
            class="page-link <?= $i == $pagina ? 'active' : ''?>">
            <?= $i?>
        </a>
        <?php
        endfor; ?>

        <?php if ($pagina < $total_paginas): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1]))?>" class="page-link"><i
                class="fas fa-chevron-right"></i></a>
        <?php
        endif; ?>
    </div>
    <?php
    endif; ?>

    <?php
elseif ($mostrar_todos || !empty($termo)): ?>
    <div style="text-align: center; padding: 60px 0;">
        <i class="fas fa-search" style="font-size: 3rem; color: var(--border-color); margin-bottom: 24px;"></i>
        <h3>Nenhum produto encontrado</h3>
        <p style="color: var(--text-muted);">Tente ajustar seus filtros ou buscar por outro termo.</p>
    </div>
    <?php
endif; ?>

</div>

<?php require_once 'templates/footer.php'; ?>