<?php
// admin/gerenciar_produtos.php - Página para gerenciar todos os produtos
$page_title = 'Gerenciar Produtos';
require_once 'secure.php';
require_once 'templates/header_admin.php';

// Parâmetros de busca e paginação
$termo_busca = htmlspecialchars(trim($_GET['busca'] ?? ''), ENT_QUOTES, 'UTF-8');
$categoria_filtro = (int)($_GET['categoria'] ?? 0);
$ordenar = htmlspecialchars(trim($_GET['ordenar'] ?? 'id_desc'), ENT_QUOTES, 'UTF-8');
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$itens_por_pagina = 20;

// Busca categorias para o filtro
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome ASC')->fetchAll(PDO::FETCH_ASSOC);

// Constrói a query SQL
$where_conditions = [];
$params = [];

if (!empty($termo_busca)) {
    $where_conditions[] = "(p.nome LIKE ? OR p.descricao_curta LIKE ? OR p.descricao LIKE ?)";
    $termo_like = "%$termo_busca%";
    $params[] = $termo_like;
    $params[] = $termo_like;
    $params[] = $termo_like;
}

if ($categoria_filtro > 0) {
    $where_conditions[] = "p.categoria_id = ?";
    $params[] = $categoria_filtro;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Determina a ordenação
$order_clause = "ORDER BY ";
switch ($ordenar) {
    case 'nome_asc':
        $order_clause .= "p.nome ASC";
        break;
    case 'nome_desc':
        $order_clause .= "p.nome DESC";
        break;
    case 'preco_asc':
        $order_clause .= "p.preco ASC";
        break;
    case 'preco_desc':
        $order_clause .= "p.preco DESC";
        break;
    case 'id_asc':
        $order_clause .= "p.id ASC";
        break;
    case 'id_desc':
    default:
        $order_clause .= "p.id DESC";
        break;
}

// Conta o total de produtos
$count_sql = "SELECT COUNT(*) FROM produtos p $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_produtos = $count_stmt->fetchColumn();
$total_paginas = ceil($total_produtos / $itens_por_pagina);

// Calcula o offset para paginação
$offset = ($pagina - 1) * $itens_por_pagina;

// Busca os produtos com paginação
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        $where_clause 
        $order_clause 
        LIMIT $itens_por_pagina OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas rápidas
try {
    $total_produtos_geral = $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
    $total_produtos_ativos = $pdo->query("SELECT COUNT(*) FROM produtos WHERE status = 'ativo' OR status IS NULL")->fetchColumn();
    $preco_medio = $pdo->query('SELECT AVG(preco) FROM produtos')->fetchColumn();
} catch (Exception $e) {
    $total_produtos_geral = $total_produtos_ativos = 0;
    $preco_medio = 0;
}
?>

<style>
    .produto-card-admin {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
        border: 1px solid rgba(59, 130, 246, 0.2);
        transition: all 0.3s ease;
    }
    
    .produto-card-admin:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.2);
        border-color: rgba(59, 130, 246, 0.4);
    }
    
    .produto-imagem-admin {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .badge-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-ativo {
        background: rgba(16, 185, 129, 0.2);
        color: #10B981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    
    .badge-inativo {
        background: rgba(239, 68, 68, 0.2);
        color: #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
</style>

<div class="space-y-6">
    <!-- Estatísticas Rápidas -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat-card rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-admin-gray-400">Total de Produtos</p>
                    <p class="text-2xl font-bold text-white"><?= number_format($total_produtos_geral) ?></p>
                </div>
                <div class="w-12 h-12 bg-admin-primary/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-admin-primary text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-admin-gray-400">Produtos Ativos</p>
                    <p class="text-2xl font-bold text-white"><?= number_format($total_produtos_ativos) ?></p>
                </div>
                <div class="w-12 h-12 bg-admin-success/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-admin-success text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-admin-gray-400">Preço Médio</p>
                    <p class="text-2xl font-bold text-white">R$ <?= number_format($preco_medio, 2, ',', '.') ?></p>
                </div>
                <div class="w-12 h-12 bg-admin-warning/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-admin-warning text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Cabeçalho com Ações -->
    <div class="admin-card rounded-xl p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white mb-2">Gerenciar Produtos</h2>
                <p class="text-admin-gray-400">Gerencie todos os produtos da sua loja</p>
            </div>
            <a href="adicionar_produto.php" class="inline-flex items-center gap-2 bg-admin-primary hover:bg-admin-primary/90 text-white font-semibold py-2 px-4 rounded-lg transition-all">
                <i class="fas fa-plus"></i>
                Adicionar Produto
            </a>
        </div>

        <!-- Filtros e Busca -->
        <form method="GET" class="space-y-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Campo de Busca -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-admin-gray-300 mb-2">Buscar Produto</label>
                    <input type="text" name="busca" value="<?= htmlspecialchars($termo_busca) ?>" 
                           placeholder="Nome, descrição..." 
                           class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-admin-primary">
                </div>
                
                <!-- Filtro por Categoria -->
                <div>
                    <label class="block text-sm font-medium text-admin-gray-300 mb-2">Categoria</label>
                    <select name="categoria" class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-admin-primary">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoria_filtro == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Ordenação -->
                <div>
                    <label class="block text-sm font-medium text-admin-gray-300 mb-2">Ordenar por</label>
                    <select name="ordenar" class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-admin-primary">
                        <option value="id_desc" <?= $ordenar == 'id_desc' ? 'selected' : '' ?>>Mais Recentes</option>
                        <option value="id_asc" <?= $ordenar == 'id_asc' ? 'selected' : '' ?>>Mais Antigos</option>
                        <option value="nome_asc" <?= $ordenar == 'nome_asc' ? 'selected' : '' ?>>Nome A-Z</option>
                        <option value="nome_desc" <?= $ordenar == 'nome_desc' ? 'selected' : '' ?>>Nome Z-A</option>
                        <option value="preco_asc" <?= $ordenar == 'preco_asc' ? 'selected' : '' ?>>Preço: Menor</option>
                        <option value="preco_desc" <?= $ordenar == 'preco_desc' ? 'selected' : '' ?>>Preço: Maior</option>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="bg-admin-primary hover:bg-admin-primary/90 text-white font-semibold py-2 px-6 rounded-lg transition-all">
                    <i class="fas fa-search mr-2"></i>
                    Buscar
                </button>
                <a href="gerenciar_produtos.php" class="bg-admin-gray-700 hover:bg-admin-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition-all">
                    <i class="fas fa-redo mr-2"></i>
                    Limpar
                </a>
            </div>
        </form>

        <!-- Mensagens de Sucesso/Erro -->
        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="bg-admin-success/20 border border-admin-success/30 text-admin-success px-4 py-3 rounded-lg mb-4">
                <?= htmlspecialchars($_SESSION['admin_message']) ?>
            </div>
            <?php unset($_SESSION['admin_message']); ?>
        <?php endif; ?>

        <!-- Informações de Resultados -->
        <div class="flex items-center justify-between mb-4">
            <p class="text-admin-gray-400">
                Mostrando <?= count($produtos) ?> de <?= number_format($total_produtos) ?> produto(s)
                <?php if ($total_paginas > 1): ?>
                    - Página <?= $pagina ?> de <?= $total_paginas ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Lista de Produtos -->
    <?php if (empty($produtos)): ?>
        <div class="admin-card rounded-xl p-12 text-center">
            <i class="fas fa-box-open text-6xl text-admin-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold text-white mb-2">Nenhum produto encontrado</h3>
            <p class="text-admin-gray-400 mb-6">
                <?php if (!empty($termo_busca) || $categoria_filtro > 0): ?>
                    Tente ajustar os filtros de busca.
                <?php else: ?>
                    Comece adicionando seu primeiro produto.
                <?php endif; ?>
            </p>
            <a href="adicionar_produto.php" class="inline-flex items-center gap-2 bg-admin-primary hover:bg-admin-primary/90 text-white font-semibold py-2 px-6 rounded-lg transition-all">
                <i class="fas fa-plus"></i>
                Adicionar Produto
            </a>
        </div>
    <?php else: ?>
        <div class="admin-card rounded-xl p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-admin-gray-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-admin-gray-300">Imagem</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-admin-gray-300">Nome</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-admin-gray-300">Categoria</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-admin-gray-300">Preço</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-admin-gray-300">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-admin-gray-300">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): ?>
                            <tr class="border-b border-admin-gray-800 hover:bg-admin-gray-800/50 transition-colors">
                                <td class="py-4 px-4">
                                    <?php if (!empty($produto['imagem'])): ?>
                                        <img src="../<?= htmlspecialchars($produto['imagem']) ?>" 
                                             alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                             class="produto-imagem-admin">
                                    <?php else: ?>
                                        <div class="w-20 h-20 bg-admin-gray-700 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-image text-admin-gray-500"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4">
                                    <div>
                                        <p class="font-semibold text-white"><?= htmlspecialchars($produto['nome']) ?></p>
                                        <?php if (!empty($produto['descricao_curta'])): ?>
                                            <p class="text-sm text-admin-gray-400 mt-1 line-clamp-2">
                                                <?= htmlspecialchars(mb_substr($produto['descricao_curta'], 0, 60)) ?><?= mb_strlen($produto['descricao_curta']) > 60 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-admin-gray-300">
                                        <?= htmlspecialchars($produto['categoria_nome'] ?? 'Sem categoria') ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="font-bold text-admin-primary">
                                        R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <?php 
                                    $status = $produto['status'] ?? 'ativo';
                                    $status_class = ($status == 'ativo' || $status == '') ? 'badge-ativo' : 'badge-inativo';
                                    $status_text = ($status == 'ativo' || $status == '') ? 'Ativo' : 'Inativo';
                                    ?>
                                    <span class="badge-status <?= $status_class ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <a href="../produto.php?id=<?= $produto['id'] ?>" 
                                           target="_blank"
                                           class="text-admin-primary hover:text-admin-primary/80 transition-colors"
                                           title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="editar_produto.php?id=<?= $produto['id'] ?>" 
                                           class="text-admin-warning hover:text-admin-warning/80 transition-colors"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="deletar_produto.php?id=<?= $produto['id'] ?>" 
                                           onclick="return confirm('Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.');"
                                           class="text-admin-danger hover:text-admin-danger/80 transition-colors"
                                           title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($total_paginas > 1): ?>
                <div class="flex items-center justify-between mt-6 pt-6 border-t border-admin-gray-700">
                    <div class="text-admin-gray-400 text-sm">
                        Página <?= $pagina ?> de <?= $total_paginas ?>
                    </div>
                    <div class="flex gap-2">
                        <?php if ($pagina > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>" 
                               class="bg-admin-gray-700 hover:bg-admin-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-all">
                                <i class="fas fa-chevron-left mr-2"></i>
                                Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($pagina < $total_paginas): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>" 
                               class="bg-admin-primary hover:bg-admin-primary/90 text-white font-semibold py-2 px-4 rounded-lg transition-all">
                                Próxima
                                <i class="fas fa-chevron-right ml-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_admin.php';
?>

