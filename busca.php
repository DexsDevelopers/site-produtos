<?php
// busca.php (VERSÃO MELHORADA COM FILTROS E SEGURANÇA)
session_start();
require_once 'config.php';
require_once 'templates/header.php';
?>

<!-- CSS Específico da Página de Busca com Cores Vermelho e Preto -->
<style>
.busca-container {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    min-height: 100vh;
    padding: 120px 0 60px;
    position: relative;
    overflow: hidden;
}

.busca-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 30%, rgba(255, 0, 0, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 0, 0, 0.05) 0%, transparent 50%);
    animation: backgroundPulse 8s ease-in-out infinite;
    pointer-events: none;
}

@keyframes backgroundPulse {
    0%, 100% { 
        background: radial-gradient(circle at 20% 30%, rgba(255, 0, 0, 0.05) 0%, transparent 50%),
                    radial-gradient(circle at 80% 70%, rgba(255, 0, 0, 0.05) 0%, transparent 50%);
    }
    50% { 
        background: radial-gradient(circle at 20% 30%, rgba(255, 0, 0, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 70%, rgba(255, 0, 0, 0.1) 0%, transparent 50%);
    }
}

.busca-container h1 {
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from { text-shadow: 0 0 20px rgba(255, 0, 0, 0.3); }
    to { text-shadow: 0 0 30px rgba(255, 0, 0, 0.6), 0 0 40px rgba(255, 0, 0, 0.3); }
}

.filtros-container {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 0, 0, 0.2);
    box-shadow: 0 8px 32px rgba(255, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.filtros-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 0, 0, 0.1), transparent);
    transition: left 0.6s;
}

.filtros-container:hover::before {
    left: 100%;
}

.produto-card {
    background: linear-gradient(145deg, #1a0000, #000000);
    border: 1px solid rgba(255, 0, 0, 0.2);
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.produto-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 0, 0, 0.1), transparent);
    transition: left 0.6s;
}

.produto-card:hover::before {
    left: 100%;
}

.produto-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(255, 0, 0, 0.2), 0 0 20px rgba(255, 0, 0, 0.1);
    border-color: rgba(255, 0, 0, 0.4);
}

.btn-buscar {
    background: linear-gradient(45deg, #ff0000, #ff3333);
    color: white;
    padding: 12px 24px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
}

.btn-buscar::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-buscar:hover::before {
    left: 100%;
}

.btn-buscar:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}

.preco-produto {
    color: #ff0000;
    text-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
    animation: priceGlow 2s ease-in-out infinite alternate;
}

@keyframes priceGlow {
    from { text-shadow: 0 0 10px rgba(255, 0, 0, 0.3); }
    to { text-shadow: 0 0 20px rgba(255, 0, 0, 0.6), 0 0 30px rgba(255, 0, 0, 0.3); }
}
</style>

<div class="busca-container">
<?php

// Sanitiza e valida os parâmetros de entrada
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

// Busca categorias para o filtro
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Se há termo de busca, filtros, ou se foi solicitado para mostrar todos os produtos, executa a consulta
if (!empty($termo) || $categoria_id > 0 || $preco_min > 0 || $preco_max > 0 || $mostrar_todos) {
    try {
        // Constrói a consulta SQL dinamicamente
        $where_conditions = [];
        $params = [];
        
        if (!empty($termo)) {
            // Remove acentos e caracteres especiais para busca mais flexível
            $termo_limpo = preg_replace('/[^a-zA-Z0-9\s]/', '', $termo);
            $termo_busca = "%$termo%";
            $termo_busca_limpo = "%$termo_limpo%";
            
            // Busca case-insensitive em múltiplos campos
            // Busca no nome, descrição curta, descrição completa e até no nome da categoria
            $where_conditions[] = "(
                LOWER(p.nome) LIKE LOWER(?) OR 
                LOWER(p.descricao_curta) LIKE LOWER(?) OR 
                LOWER(p.descricao) LIKE LOWER(?) OR
                LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(p.nome, 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u')) LIKE LOWER(?)
            )";
            $params[] = $termo_busca;
            $params[] = $termo_busca;
            $params[] = $termo_busca;
            $params[] = $termo_busca_limpo;
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
        
        // Determina a ordenação com relevância para busca
        $order_clause = "ORDER BY ";
        if (!empty($termo)) {
            // Se há termo de busca, ordena por relevância (nome que começa com o termo primeiro)
            switch ($ordenar) {
                case 'preco_asc':
                    $order_clause .= "CASE WHEN LOWER(p.nome) LIKE LOWER(?) THEN 0 ELSE 1 END, p.preco ASC";
                    $params[] = $termo . "%";
                    break;
                case 'preco_desc':
                    $order_clause .= "CASE WHEN LOWER(p.nome) LIKE LOWER(?) THEN 0 ELSE 1 END, p.preco DESC";
                    $params[] = $termo . "%";
                    break;
                case 'nome':
                    $order_clause .= "CASE WHEN LOWER(p.nome) LIKE LOWER(?) THEN 0 ELSE 1 END, p.nome ASC";
                    $params[] = $termo . "%";
                    break;
                case 'relevancia':
                default:
                    // Ordena por relevância: produtos que começam com o termo primeiro, depois contêm o termo
                    $order_clause .= "CASE 
                        WHEN LOWER(p.nome) LIKE LOWER(?) THEN 1 
                        WHEN LOWER(p.nome) LIKE LOWER(?) THEN 2 
                        WHEN LOWER(p.descricao_curta) LIKE LOWER(?) THEN 3 
                        ELSE 4 
                    END, p.nome ASC";
                    $params[] = $termo . "%";  // Começa com o termo
                    $params[] = "%" . $termo . "%";  // Contém o termo
                    $params[] = "%" . $termo . "%";  // Na descrição
                    break;
            }
        } else {
            // Sem termo de busca, ordena normalmente
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
                case 'relevancia':
                default:
                    $order_clause .= "p.id DESC";
                    break;
            }
        }
        
        // Prepara parâmetros para contagem (sem os parâmetros de ordenação)
        $params_count = [];
        if (!empty($termo)) {
            $termo_busca = "%$termo%";
            $termo_limpo = preg_replace('/[^a-zA-Z0-9\s]/', '', $termo);
            $termo_busca_limpo = "%$termo_limpo%";
            $params_count[] = $termo_busca;
            $params_count[] = $termo_busca;
            $params_count[] = $termo_busca;
            $params_count[] = $termo_busca_limpo;
        }
        if ($categoria_id > 0) {
            $params_count[] = $categoria_id;
        }
        if ($preco_min > 0) {
            $params_count[] = $preco_min;
        }
        if ($preco_max > 0) {
            $params_count[] = $preco_max;
        }
        
        // Conta o total de resultados
        $count_sql = "SELECT COUNT(*) FROM produtos p $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params_count);
        $total_resultados = $count_stmt->fetchColumn();
        $total_paginas = ceil($total_resultados / $itens_por_pagina);
        
        // Calcula o offset para paginação
        $offset = ($pagina - 1) * $itens_por_pagina;
        
        // Busca os resultados com paginação
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                $where_clause 
                $order_clause 
                LIMIT $itens_por_pagina OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erro na busca: " . $e->getMessage());
        $resultados = [];
    }
}
?>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <h1 class="text-3xl md:text-4xl font-black text-white">
            <?php if ($mostrar_todos): ?>
                Todos os Produtos
            <?php elseif (!empty($termo) || $categoria_id > 0 || $preco_min > 0 || $preco_max > 0): ?>
                Resultados da Busca
            <?php else: ?>
                Buscar Produtos
            <?php endif; ?>
        </h1>
        <p class="text-brand-gray-text mt-2">
            <?php if ($total_resultados > 0): ?>
                Encontramos <?= $total_resultados ?> produto(s) - Página <?= $pagina ?> de <?= $total_paginas ?>
            <?php elseif (!empty($termo) || $categoria_id > 0 || $preco_min > 0 || $preco_max > 0): ?>
                Nenhum produto encontrado com os filtros aplicados
            <?php else: ?>
                Use os filtros abaixo para encontrar o que procura
            <?php endif; ?>
        </p>

        <!-- Filtros de Busca -->
        <div class="mt-8 filtros-container rounded-xl p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Campo de Busca -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Buscar</label>
                        <input type="text" name="termo" value="<?= htmlspecialchars($termo) ?>" 
                               placeholder="Nome do produto..." 
                               class="w-full bg-brand-gray/50 border border-brand-gray-light text-white rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red">
                    </div>
                    
                    <!-- Filtro por Categoria -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Categoria</label>
                        <select name="categoria" class="w-full bg-brand-gray/50 border border-brand-gray-light text-white rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">Todas as categorias</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" <?= $categoria_id == $categoria['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Filtro por Preço Mínimo -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Preço Mín.</label>
                        <input type="number" name="preco_min" value="<?= $preco_min > 0 ? $preco_min : '' ?>" 
                               placeholder="0.00" step="0.01" min="0"
                               class="w-full bg-brand-gray/50 border border-brand-gray-light text-white rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red">
                    </div>
                    
                    <!-- Filtro por Preço Máximo -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Preço Máx.</label>
                        <input type="number" name="preco_max" value="<?= $preco_max > 0 ? $preco_max : '' ?>" 
                               placeholder="999.99" step="0.01" min="0"
                               class="w-full bg-brand-gray/50 border border-brand-gray-light text-white rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red">
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 items-end">
                    <!-- Ordenação -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-white mb-2">Ordenar por</label>
                        <select name="ordenar" class="w-full bg-brand-gray/50 border border-brand-gray-light text-white rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="relevancia" <?= $ordenar == 'relevancia' ? 'selected' : '' ?>>Relevância</option>
                            <option value="nome" <?= $ordenar == 'nome' ? 'selected' : '' ?>>Nome A-Z</option>
                            <option value="preco_asc" <?= $ordenar == 'preco_asc' ? 'selected' : '' ?>>Menor Preço</option>
                            <option value="preco_desc" <?= $ordenar == 'preco_desc' ? 'selected' : '' ?>>Maior Preço</option>
                        </select>
                    </div>
                    
                    <!-- Botões -->
                    <div class="flex gap-2">
                        <button type="submit" class="btn-buscar">
                            Buscar
                        </button>
                        <a href="busca.php" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                            Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Resultados -->
        <div class="mt-10">
            <?php if (!empty($resultados)): ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                    <?php foreach ($resultados as $produto): ?>
                        <a href="produto.php?id=<?= $produto['id'] ?>" class="block group">
                            <div class="produto-card rounded-xl overflow-hidden transition-all duration-300">
                                <div class="aspect-square overflow-hidden">
                                    <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                                         alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                         class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110"
                                         loading="lazy">
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-lg text-white truncate"><?= htmlspecialchars($produto['nome']) ?></h3>
                                    <p class="text-sm text-brand-gray-text mt-1 truncate"><?= htmlspecialchars($produto['descricao_curta']) ?></p>
                                    <?php if (!empty($produto['categoria_nome'])): ?>
                                        <p class="text-xs text-brand-red mt-1"><?= htmlspecialchars($produto['categoria_nome']) ?></p>
                                    <?php endif; ?>
                                    <p class="text-xl preco-produto font-bold mt-3"><?= formatarPreco($produto['preco']) ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginação -->
                <?php if ($total_paginas > 1): ?>
                    <div class="mt-12 flex justify-center">
                        <nav class="flex items-center space-x-2">
                            <?php if ($pagina > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>" 
                                   class="px-3 py-2 bg-brand-gray-light text-white rounded-lg hover:bg-brand-red transition-colors">
                                    Anterior
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>" 
                                   class="px-3 py-2 <?= $i == $pagina ? 'bg-brand-red text-white' : 'bg-brand-gray-light text-white hover:bg-brand-red' ?> rounded-lg transition-colors">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($pagina < $total_paginas): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>" 
                                   class="px-3 py-2 bg-brand-gray-light text-white rounded-lg hover:bg-brand-red transition-colors">
                                    Próxima
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
                
            <?php elseif (!empty($termo) || $categoria_id > 0 || $preco_min > 0 || $preco_max > 0): ?>
                <div class="text-center py-16 bg-brand-gray/50 rounded-lg">
                    <p class="text-2xl text-white">Nenhum produto encontrado.</p>
                    <p class="text-brand-gray-text mt-2">Tente ajustar os filtros ou buscar por um termo diferente.</p>
                    <a href="busca.php" class="inline-block mt-4 bg-brand-red hover:bg-brand-red-dark text-white font-bold py-2 px-6 rounded-lg transition-colors">
                        Limpar Filtros
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>