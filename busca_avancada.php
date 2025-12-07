<?php
// busca_avancada.php - Sistema de Busca Avançada
session_start();
require_once 'config.php';
require_once 'includes/advanced_filters.php';

$filters = new AdvancedFilters($pdo);

// Processar filtros
$filtros = [
    'termo' => $_GET['termo'] ?? '',
    'categoria_id' => $_GET['categoria_id'] ?? '',
    'preco_min' => $_GET['preco_min'] ?? '',
    'preco_max' => $_GET['preco_max'] ?? '',
    'avaliacao_min' => $_GET['avaliacao_min'] ?? '',
    'disponivel' => $_GET['disponivel'] ?? '',
    'marca' => $_GET['marca'] ?? '',
    'tags' => !empty($_GET['tags']) ? explode(',', $_GET['tags']) : [],
    'ordenacao' => $_GET['ordenacao'] ?? 'relevancia',
    'pagina' => (int)($_GET['pagina'] ?? 0),
    'limite' => 20
];

// Buscar produtos
$produtos = $filters->buscarProdutosComFiltros($filtros);
$total_produtos = $filters->contarProdutosComFiltros($filtros);
$opcoes = $filters->getOpcoesFiltros($filtros);

// Calcular paginação
$total_paginas = ceil($total_produtos / $filtros['limite']);
$pagina_atual = $filtros['pagina'] + 1;

$page_title = 'Busca Avançada';
require_once 'templates/header.php';
?>

<style>
/* Estilos para busca avançada */
.filter-panel {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.filter-section {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1.5rem 0;
}

.filter-section:last-child {
    border-bottom: none;
}

.filter-title {
    font-weight: 600;
    color: white;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.filter-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.filter-option:hover {
    background: rgba(255, 255, 255, 0.1);
}

.filter-option input[type="checkbox"] {
    accent-color: #3B82F6;
}

.filter-option input[type="radio"] {
    accent-color: #3B82F6;
}

.price-range {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.price-input {
    flex: 1;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.5rem;
    border-radius: 0.5rem;
}

.price-input:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.search-input {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    width: 100%;
    font-size: 1rem;
}

.search-input:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.product-card {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.pagination button {
    background: rgba(59, 130, 246, 0.2);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #60a5fa;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pagination button:hover {
    background: rgba(59, 130, 246, 0.4);
}

.pagination button.active {
    background: rgba(59, 130, 246, 0.8);
    color: white;
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.results-info {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    padding: 1rem;
    border-radius: 0.75rem;
    margin-bottom: 2rem;
}

.clear-filters {
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.clear-filters:hover {
    background: rgba(239, 68, 68, 0.4);
    color: white;
}
</style>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Painel de Filtros -->
            <div class="lg:col-span-1">
                <div class="filter-panel rounded-xl p-6 sticky top-24">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white">Filtros</h2>
                        <a href="busca_avancada.php" class="clear-filters">
                            <i class="fas fa-times mr-1"></i>
                            Limpar
                        </a>
                    </div>
                    
                    <form method="GET" id="filters-form">
                        <!-- Busca por termo -->
                        <div class="filter-section">
                            <div class="filter-title">
                                <i class="fas fa-search"></i>
                                Buscar
                            </div>
                            <input type="text" 
                                   name="termo" 
                                   value="<?= htmlspecialchars($filtros['termo']) ?>" 
                                   placeholder="Digite o nome do produto..."
                                   class="search-input"
                                   id="search-input">
                        </div>
                        
                        <!-- Categorias -->
                        <div class="filter-section">
                            <div class="filter-title">
                                <i class="fas fa-tags"></i>
                                Categorias
                            </div>
                            <div class="filter-options">
                                <?php foreach ($opcoes['categorias'] as $categoria): ?>
                                    <label class="filter-option">
                                        <input type="radio" 
                                               name="categoria_id" 
                                               value="<?= $categoria['id'] ?>"
                                               <?= ($filtros['categoria_id'] == $categoria['id']) ? 'checked' : '' ?>>
                                        <span><?= htmlspecialchars($categoria['nome']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Faixa de Preço -->
                        <div class="filter-section">
                            <div class="filter-title">
                                <i class="fas fa-dollar-sign"></i>
                                Preço
                            </div>
                            <div class="price-range">
                                <input type="number" 
                                       name="preco_min" 
                                       value="<?= htmlspecialchars($filtros['preco_min']) ?>" 
                                       placeholder="Min"
                                       class="price-input"
                                       min="0">
                                <span class="text-gray-400">até</span>
                                <input type="number" 
                                       name="preco_max" 
                                       value="<?= htmlspecialchars($filtros['preco_max']) ?>" 
                                       placeholder="Max"
                                       class="price-input"
                                       min="0">
                            </div>
                        </div>
                        
                        <!-- Avaliação Mínima -->
                        <div class="filter-section">
                            <div class="filter-title">
                                <i class="fas fa-star"></i>
                                Avaliação Mínima
                            </div>
                            <div class="filter-options">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label class="filter-option">
                                        <input type="radio" 
                                               name="avaliacao_min" 
                                               value="<?= $i ?>"
                                               <?= ($filtros['avaliacao_min'] == $i) ? 'checked' : '' ?>>
                                        <div class="flex">
                                            <?php for ($j = 1; $j <= $i; $j++): ?>
                                                <span class="text-yellow-400">★</span>
                                            <?php endfor; ?>
                                            <span class="text-gray-400 ml-2">e acima</span>
                                        </div>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Disponibilidade -->
                        <div class="filter-section">
                            <div class="filter-title">
                                <i class="fas fa-box"></i>
                                Disponibilidade
                            </div>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="disponivel" 
                                           value="1"
                                           <?= ($filtros['disponivel'] == '1') ? 'checked' : '' ?>>
                                    <span>Em estoque</span>
                                </label>
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="disponivel" 
                                           value="0"
                                           <?= ($filtros['disponivel'] == '0') ? 'checked' : '' ?>>
                                    <span>Esgotado</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Ordenação -->
                        <div class="filter-section">
                            <div class="filter-title">
                                <i class="fas fa-sort"></i>
                                Ordenar por
                            </div>
                            <select name="ordenacao" class="search-input">
                                <option value="relevancia" <?= ($filtros['ordenacao'] == 'relevancia') ? 'selected' : '' ?>>Relevância</option>
                                <option value="preco_asc" <?= ($filtros['ordenacao'] == 'preco_asc') ? 'selected' : '' ?>>Menor preço</option>
                                <option value="preco_desc" <?= ($filtros['ordenacao'] == 'preco_desc') ? 'selected' : '' ?>>Maior preço</option>
                                <option value="nome_asc" <?= ($filtros['ordenacao'] == 'nome_asc') ? 'selected' : '' ?>>Nome A-Z</option>
                                <option value="nome_desc" <?= ($filtros['ordenacao'] == 'nome_desc') ? 'selected' : '' ?>>Nome Z-A</option>
                                <option value="avaliacao" <?= ($filtros['ordenacao'] == 'avaliacao') ? 'selected' : '' ?>>Melhor avaliados</option>
                                <option value="mais_recentes" <?= ($filtros['ordenacao'] == 'mais_recentes') ? 'selected' : '' ?>>Mais recentes</option>
                            </select>
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-brand-red hover:bg-brand-red-dark text-white font-bold py-3 rounded-lg transition-colors">
                            <i class="fas fa-search mr-2"></i>
                            Aplicar Filtros
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Resultados -->
            <div class="lg:col-span-3">
                <!-- Informações dos Resultados -->
                <div class="results-info rounded-xl mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                <?= $total_produtos ?> produto(s) encontrado(s)
                            </h3>
                            <?php if (!empty($filtros['termo'])): ?>
                                <p class="text-sm text-gray-400">
                                    Resultados para: "<?= htmlspecialchars($filtros['termo']) ?>"
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="text-sm text-gray-400">
                            Página <?= $pagina_atual ?> de <?= $total_paginas ?>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Produtos -->
                <?php if (empty($produtos)): ?>
                    <div class="text-center py-16">
                        <i class="fas fa-search text-gray-500 text-6xl mb-4"></i>
                        <h3 class="text-2xl font-bold text-white mb-4">Nenhum produto encontrado</h3>
                        <p class="text-gray-400 mb-6">Tente ajustar os filtros ou buscar por outros termos.</p>
                        <a href="busca_avancada.php" class="clear-filters">
                            <i class="fas fa-refresh mr-2"></i>
                            Limpar Filtros
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($produtos as $produto): ?>
                            <div class="product-card rounded-xl overflow-hidden">
                                <a href="produto.php?id=<?= $produto['id'] ?>" class="block group">
                                    <div class="aspect-square overflow-hidden">
                                        <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                                             alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                             loading="lazy">
                                    </div>
                                    <div class="p-4">
                                        <h3 class="font-bold text-lg text-white mb-2 line-clamp-2">
                                            <?= htmlspecialchars($produto['nome']) ?>
                                        </h3>
                                        <p class="text-sm text-gray-400 mb-2">
                                            <?= htmlspecialchars($produto['categoria_nome']) ?>
                                        </p>
                                        
                                        <!-- Avaliações -->
                                        <?php if ($produto['media_avaliacoes'] > 0): ?>
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="flex">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span class="text-yellow-400 <?= ($i <= $produto['media_avaliacoes']) ? '' : 'text-gray-600' ?>">★</span>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="text-sm text-gray-400">
                                                    (<?= $produto['total_avaliacoes'] ?>)
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="flex items-center justify-between">
                                            <span class="text-xl font-bold text-brand-red">
                                                <?= formatarPreco($produto['preco']) ?>
                                            </span>
                                            <button class="bg-brand-red hover:bg-brand-red-dark text-white px-4 py-2 rounded-lg transition-colors">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Paginação -->
                    <?php if ($total_paginas > 1): ?>
                        <div class="pagination">
                            <button onclick="irParaPagina(<?= max(0, $filtros['pagina'] - 1) ?>)" 
                                    <?= ($filtros['pagina'] == 0) ? 'disabled' : '' ?>>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            
                            <?php for ($i = max(0, $filtros['pagina'] - 2); $i <= min($total_paginas - 1, $filtros['pagina'] + 2); $i++): ?>
                                <button onclick="irParaPagina(<?= $i ?>)" 
                                        class="<?= ($i == $filtros['pagina']) ? 'active' : '' ?>">
                                    <?= $i + 1 ?>
                                </button>
                            <?php endfor; ?>
                            
                            <button onclick="irParaPagina(<?= min($total_paginas - 1, $filtros['pagina'] + 1) ?>)" 
                                    <?= ($filtros['pagina'] >= $total_paginas - 1) ? 'disabled' : '' ?>>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Função para ir para página específica
function irParaPagina(pagina) {
    const form = document.getElementById('filters-form');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'pagina';
    input.value = pagina;
    form.appendChild(input);
    form.submit();
}

// Auto-submit em mudanças de filtros
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filters-form');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        if (input.type === 'radio' || input.type === 'checkbox') {
            input.addEventListener('change', function() {
                // Reset página ao mudar filtros
                const paginaInput = form.querySelector('input[name="pagina"]');
                if (paginaInput) {
                    paginaInput.remove();
                }
                form.submit();
            });
        }
    });
});

// Sugestões de busca (opcional)
document.getElementById('search-input').addEventListener('input', function() {
    const termo = this.value;
    if (termo.length > 2) {
        // Implementar sugestões AJAX aqui se necessário
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
