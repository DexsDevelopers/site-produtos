<?php
// admin/gerenciar_categorias_avancado.php - Gerenciamento Avançado de Categorias
require_once 'secure.php';
require_once 'templates/header_admin.php';

// Busca todas as categorias ordenadas
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY ordem ASC, nome ASC')->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$total_categorias = count($categorias);
$categorias_ativas = count(array_filter($categorias, fn($c) => $c['ativa']));
$categorias_destaque = count(array_filter($categorias, fn($c) => $c['destaque']));
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Gerenciar Categorias</h1>
            <p class="text-admin-gray-400 mt-2">Organize e configure suas categorias de produtos</p>
        </div>
        <div class="flex gap-4 mt-4 sm:mt-0">
            <a href="editar_categoria.php" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>
                Nova Categoria
            </a>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="admin-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-admin-primary/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tags text-admin-primary text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-admin-gray-400">Total</p>
                    <p class="text-2xl font-bold text-white"><?= $total_categorias ?></p>
                </div>
            </div>
        </div>

        <div class="admin-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-admin-success/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-admin-success text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-admin-gray-400">Ativas</p>
                    <p class="text-2xl font-bold text-white"><?= $categorias_ativas ?></p>
                </div>
            </div>
        </div>

        <div class="admin-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-admin-warning/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-admin-warning text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-admin-gray-400">Destaque</p>
                    <p class="text-2xl font-bold text-white"><?= $categorias_destaque ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="mb-6 p-4 rounded-lg <?= $_SESSION['admin_message_type'] === 'success' ? 'bg-admin-success/20 text-admin-success border border-admin-success/30' : ($_SESSION['admin_message_type'] === 'warning' ? 'bg-admin-warning/20 text-admin-warning border border-admin-warning/30' : 'bg-admin-error/20 text-admin-error border border-admin-error/30') ?>">
            <div class="flex items-center">
                <i class="fas <?= $_SESSION['admin_message_type'] === 'success' ? 'fa-check-circle' : ($_SESSION['admin_message_type'] === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle') ?> mr-2"></i>
                <?= htmlspecialchars($_SESSION['admin_message']) ?>
            </div>
        </div>
        <?php 
        unset($_SESSION['admin_message']);
        unset($_SESSION['admin_message_type']);
        ?>
    <?php endif; ?>

    <!-- Lista de Categorias -->
    <div class="admin-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-white">Categorias</h2>
            <div class="flex gap-2">
                <button onclick="toggleReorder()" class="btn-secondary">
                    <i class="fas fa-sort mr-2"></i>
                    Reordenar
                </button>
                <button onclick="toggleBulkActions()" class="btn-secondary">
                    <i class="fas fa-check-square mr-2"></i>
                    Ações em Lote
                </button>
            </div>
        </div>

        <?php if (!empty($categorias)): ?>
            <div id="categoriasList" class="space-y-4">
                <?php foreach ($categorias as $categoria): ?>
                    <div class="categoria-item bg-admin-gray-800 rounded-lg p-6 border border-admin-gray-700 hover:border-admin-primary/30 transition-all" data-id="<?= $categoria['id'] ?>">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <!-- Ícone -->
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: <?= $categoria['cor'] ?>">
                                    <i class="<?= $categoria['icone'] ?> text-white text-lg"></i>
                                </div>
                                
                                <!-- Informações -->
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="text-lg font-semibold text-white"><?= htmlspecialchars($categoria['nome']) ?></h3>
                                        
                                        <!-- Status badges -->
                                        <div class="flex space-x-2">
                                            <?php if ($categoria['ativa']): ?>
                                                <span class="px-2 py-1 bg-admin-success/20 text-admin-success text-xs rounded-full">
                                                    <i class="fas fa-check-circle mr-1"></i>Ativa
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 bg-admin-error/20 text-admin-error text-xs rounded-full">
                                                    <i class="fas fa-times-circle mr-1"></i>Inativa
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($categoria['destaque']): ?>
                                                <span class="px-2 py-1 bg-admin-warning/20 text-admin-warning text-xs rounded-full">
                                                    <i class="fas fa-star mr-1"></i>Destaque
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($categoria['descricao']): ?>
                                        <p class="text-admin-gray-400 text-sm mt-1"><?= htmlspecialchars($categoria['descricao']) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-admin-gray-500">
                                        <span>Ordem: <?= $categoria['ordem'] ?></span>
                                        <span>•</span>
                                        <span>Criada: <?= date('d/m/Y', strtotime($categoria['data_criacao'])) ?></span>
                                        <?php if ($categoria['data_atualizacao']): ?>
                                            <span>•</span>
                                            <span>Atualizada: <?= date('d/m/Y', strtotime($categoria['data_atualizacao'])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Ações -->
                            <div class="flex items-center space-x-2">
                                <a href="editar_categoria.php?id=<?= $categoria['id'] ?>" 
                                   class="p-2 text-admin-primary hover:bg-admin-primary/20 rounded-lg transition-colors"
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <a href="processa_categoria_avancado.php?toggle_status=<?= $categoria['id'] ?>" 
                                   class="p-2 <?= $categoria['ativa'] ? 'text-admin-warning hover:bg-admin-warning/20' : 'text-admin-success hover:bg-admin-success/20' ?> rounded-lg transition-colors"
                                   title="<?= $categoria['ativa'] ? 'Desativar' : 'Ativar' ?>">
                                    <i class="fas fa-<?= $categoria['ativa'] ? 'pause' : 'play' ?>"></i>
                                </a>
                                
                                <a href="processa_categoria_avancado.php?deletar=<?= $categoria['id'] ?>" 
                                   class="p-2 text-admin-error hover:bg-admin-error/20 rounded-lg transition-colors"
                                   onclick="return confirm('Tem certeza que deseja deletar esta categoria?')"
                                   title="Deletar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-tags text-admin-gray-500 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold text-white mb-2">Nenhuma categoria encontrada</h3>
                <p class="text-admin-gray-400 mb-6">Comece criando sua primeira categoria</p>
                <a href="editar_categoria.php" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Primeira Categoria
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Reordenação -->
<div id="reorderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-admin-gray-800 rounded-xl p-6 max-w-2xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-white">Reordenar Categorias</h3>
                <button type="button" onclick="closeReorder()" class="text-admin-gray-400 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <p class="text-admin-gray-400 mb-6">Arraste e solte para reordenar as categorias</p>
            
            <div id="reorderList" class="space-y-2 max-h-96 overflow-y-auto">
                <!-- Lista será preenchida via JavaScript -->
            </div>
            
            <div class="flex justify-end gap-4 mt-6">
                <button onclick="closeReorder()" class="btn-secondary">Cancelar</button>
                <button onclick="saveReorder()" class="btn-primary">Salvar Ordem</button>
            </div>
        </div>
    </div>
</div>

<script>
let isReorderMode = false;
let originalOrder = [];

function toggleReorder() {
    isReorderMode = !isReorderMode;
    
    if (isReorderMode) {
        openReorderModal();
    } else {
        closeReorder();
    }
}

function openReorderModal() {
    const modal = document.getElementById('reorderModal');
    const reorderList = document.getElementById('reorderList');
    
    // Carregar categorias na ordem atual
    const categorias = Array.from(document.querySelectorAll('.categoria-item'));
    originalOrder = categorias.map(item => ({
        id: item.dataset.id,
        nome: item.querySelector('h3').textContent,
        element: item.cloneNode(true)
    }));
    
    reorderList.innerHTML = '';
    originalOrder.forEach((categoria, index) => {
        const item = document.createElement('div');
        item.className = 'bg-admin-gray-700 rounded-lg p-4 cursor-move border border-admin-gray-600 hover:border-admin-primary/30 transition-all';
        item.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas fa-grip-vertical text-admin-gray-400"></i>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: ${categoria.element.querySelector('.w-12').style.backgroundColor}">
                    <i class="${categoria.element.querySelector('i').className} text-white text-sm"></i>
                </div>
                <span class="text-white font-medium">${categoria.nome}</span>
                <span class="text-admin-gray-400 text-sm ml-auto">#${index + 1}</span>
            </div>
        `;
        item.dataset.id = categoria.id;
        reorderList.appendChild(item);
    });
    
    modal.classList.remove('hidden');
}

function closeReorder() {
    const modal = document.getElementById('reorderModal');
    if (modal) {
        modal.classList.add('hidden');
    }
    isReorderMode = false;
}

function saveReorder() {
    const reorderList = document.getElementById('reorderList');
    const items = Array.from(reorderList.children);
    const newOrder = items.map(item => item.dataset.id);
    
    // Enviar nova ordem via AJAX
    const formData = new FormData();
    formData.append('reordenar', '1');
    newOrder.forEach((id, index) => {
        formData.append('categorias[]', id);
    });
    
    fetch('processa_categoria_avancado.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        location.reload();
    })
    .catch(error => {
        console.error('Erro ao reordenar:', error);
        alert('Erro ao reordenar categorias');
    });
}

function toggleBulkActions() {
    // Implementar ações em lote
    alert('Funcionalidade de ações em lote será implementada em breve!');
}

// Tornar a lista arrastável
document.addEventListener('DOMContentLoaded', function() {
    const reorderList = document.getElementById('reorderList');
    
    if (reorderList) {
        // Implementar drag and drop
        let draggedElement = null;
        
        reorderList.addEventListener('dragstart', function(e) {
            draggedElement = e.target;
            e.target.style.opacity = '0.5';
        });
        
        reorderList.addEventListener('dragend', function(e) {
            e.target.style.opacity = '1';
            draggedElement = null;
        });
        
        reorderList.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        reorderList.addEventListener('drop', function(e) {
            e.preventDefault();
            if (draggedElement && e.target !== draggedElement) {
                reorderList.insertBefore(draggedElement, e.target.nextSibling);
            }
        });
    }
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>

