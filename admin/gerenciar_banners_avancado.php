<?php
// admin/gerenciar_banners_avancado.php - Gerenciamento Avançado de Banners
require_once 'secure.php';
require_once 'templates/header_admin.php';

// Busca todos os banners ordenados
$banners = $pdo->query('SELECT * FROM banners ORDER BY posicao ASC, id DESC')->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$total_banners = count($banners);
$banners_ativos = count(array_filter($banners, fn($b) => $b['ativo']));
$banners_por_tipo = array_count_values(array_column($banners, 'tipo'));
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Gerenciar Banners</h1>
            <p class="text-admin-gray-400 mt-2">Gerencie os banners da sua loja</p>
        </div>
        <div class="flex gap-4 mt-4 sm:mt-0">
            <a href="editar_banner.php" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>
                Novo Banner
            </a>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="admin-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-admin-primary/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-image text-admin-primary text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-admin-gray-400">Total</p>
                    <p class="text-2xl font-bold text-white"><?= $total_banners ?></p>
                </div>
            </div>
        </div>

        <div class="admin-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-admin-success/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-admin-success text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-admin-gray-400">Ativos</p>
                    <p class="text-2xl font-bold text-white"><?= $banners_ativos ?></p>
                </div>
            </div>
        </div>

        <div class="admin-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-admin-warning/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-admin-warning text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-admin-gray-400">Principais</p>
                    <p class="text-2xl font-bold text-white"><?= $banners_por_tipo['principal'] ?? 0 ?></p>
                </div>
            </div>
        </div>

        <div class="admin-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-admin-secondary/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tags text-admin-secondary text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-admin-gray-400">Categorias</p>
                    <p class="text-2xl font-bold text-white"><?= $banners_por_tipo['categoria'] ?? 0 ?></p>
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

    <!-- Filtros -->
    <div class="admin-card rounded-xl p-6 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <div>
                <label for="filtroTipo" class="block text-sm font-medium text-admin-gray-300 mb-2">Filtrar por Tipo</label>
                <select id="filtroTipo" class="px-4 py-2 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white">
                    <option value="">Todos os tipos</option>
                    <option value="principal">Principal</option>
                    <option value="categoria">Categoria</option>
                    <option value="promocao">Promoção</option>
                    <option value="destaque">Destaque</option>
                </select>
            </div>
            
            <div>
                <label for="filtroStatus" class="block text-sm font-medium text-admin-gray-300 mb-2">Filtrar por Status</label>
                <select id="filtroStatus" class="px-4 py-2 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white">
                    <option value="">Todos</option>
                    <option value="ativo">Ativos</option>
                    <option value="inativo">Inativos</option>
                </select>
            </div>
            
            <div class="flex-1">
                <label for="buscaBanner" class="block text-sm font-medium text-admin-gray-300 mb-2">Buscar</label>
                <input type="text" id="buscaBanner" placeholder="Buscar por título..." class="w-full px-4 py-2 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white">
            </div>
        </div>
    </div>

    <!-- Lista de Banners -->
    <div class="admin-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-white">Banners</h2>
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

        <?php if (!empty($banners)): ?>
            <div id="bannersList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($banners as $banner): ?>
                    <div class="banner-item bg-admin-gray-800 rounded-lg overflow-hidden border border-admin-gray-700 hover:border-admin-primary/30 transition-all" data-id="<?= $banner['id'] ?>" data-tipo="<?= $banner['tipo'] ?>" data-ativo="<?= $banner['ativo'] ?>">
                        <!-- Imagem -->
                        <div class="aspect-video bg-admin-gray-700 relative overflow-hidden">
                            <img src="../<?= htmlspecialchars($banner['imagem']) ?>" 
                                 alt="<?= htmlspecialchars($banner['titulo']) ?>" 
                                 class="w-full h-full object-cover">
                            
                            <!-- Overlay com informações -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 hover:opacity-100 transition-opacity">
                                <div class="absolute bottom-4 left-4 right-4 text-white">
                                    <?php if ($banner['titulo']): ?>
                                        <h3 class="font-semibold text-sm"><?= htmlspecialchars($banner['titulo']) ?></h3>
                                    <?php endif; ?>
                                    <?php if ($banner['subtitulo']): ?>
                                        <p class="text-xs opacity-90"><?= htmlspecialchars($banner['subtitulo']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Status badge -->
                            <div class="absolute top-2 right-2">
                                <?php if ($banner['ativo']): ?>
                                    <span class="px-2 py-1 bg-admin-success/80 text-white text-xs rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i>Ativo
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-admin-error/80 text-white text-xs rounded-full">
                                        <i class="fas fa-times-circle mr-1"></i>Inativo
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Informações -->
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-1 bg-admin-primary/20 text-admin-primary text-xs rounded-full">
                                    <?= ucfirst($banner['tipo']) ?>
                                </span>
                                <span class="text-xs text-admin-gray-400">#<?= $banner['posicao'] ?></span>
                            </div>
                            
                            <div class="space-y-1 text-sm">
                                <?php if ($banner['titulo']): ?>
                                    <p class="font-medium text-white truncate"><?= htmlspecialchars($banner['titulo']) ?></p>
                                <?php endif; ?>
                                
                                <?php if ($banner['link']): ?>
                                    <p class="text-admin-gray-400 text-xs truncate">
                                        <i class="fas fa-link mr-1"></i>
                                        <?= htmlspecialchars($banner['link']) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="text-admin-gray-500 text-xs">
                                    Criado: <?= date('d/m/Y', strtotime($banner['data_criacao'])) ?>
                                </p>
                            </div>
                            
                            <!-- Ações -->
                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-admin-gray-700">
                                <div class="flex space-x-2">
                                    <a href="editar_banner.php?id=<?= $banner['id'] ?>" 
                                       class="p-2 text-admin-primary hover:bg-admin-primary/20 rounded-lg transition-colors"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <a href="processa_banner_avancado.php?toggle_status=<?= $banner['id'] ?>" 
                                       class="p-2 <?= $banner['ativo'] ? 'text-admin-warning hover:bg-admin-warning/20' : 'text-admin-success hover:bg-admin-success/20' ?> rounded-lg transition-colors"
                                       title="<?= $banner['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                        <i class="fas fa-<?= $banner['ativo'] ? 'pause' : 'play' ?>"></i>
                                    </a>
                                    
                                    <a href="../<?= htmlspecialchars($banner['imagem']) ?>" 
                                       target="_blank"
                                       class="p-2 text-admin-secondary hover:bg-admin-secondary/20 rounded-lg transition-colors"
                                       title="Ver Imagem">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                                
                                <a href="processa_banner_avancado.php?deletar=<?= $banner['id'] ?>" 
                                   class="p-2 text-admin-error hover:bg-admin-error/20 rounded-lg transition-colors"
                                   onclick="return confirm('Tem certeza que deseja deletar este banner?')"
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
                <i class="fas fa-image text-admin-gray-500 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold text-white mb-2">Nenhum banner encontrado</h3>
                <p class="text-admin-gray-400 mb-6">Comece criando seu primeiro banner</p>
                <a href="editar_banner.php" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Primeiro Banner
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Reordenação -->
<div id="reorderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" onclick="closeReorderOnOverlay(event)">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-admin-gray-800 rounded-xl p-6 max-w-4xl w-full" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-white">Reordenar Banners</h3>
                <button type="button" id="closeReorderBtn" class="text-admin-gray-400 hover:text-white hover:bg-admin-gray-700 p-2 rounded-lg transition-all">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <p class="text-admin-gray-400 mb-6">Arraste e solte para reordenar os banners</p>
            
            <div id="reorderList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
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

// Filtros
document.getElementById('filtroTipo').addEventListener('change', filterBanners);
document.getElementById('filtroStatus').addEventListener('change', filterBanners);
document.getElementById('buscaBanner').addEventListener('input', filterBanners);

function filterBanners() {
    const tipo = document.getElementById('filtroTipo').value;
    const status = document.getElementById('filtroStatus').value;
    const busca = document.getElementById('buscaBanner').value.toLowerCase();
    
    const banners = document.querySelectorAll('.banner-item');
    
    banners.forEach(banner => {
        const bannerTipo = banner.dataset.tipo;
        const bannerAtivo = banner.dataset.ativo === '1';
        const titulo = banner.querySelector('h3, p').textContent.toLowerCase();
        
        let show = true;
        
        if (tipo && bannerTipo !== tipo) show = false;
        if (status === 'ativo' && !bannerAtivo) show = false;
        if (status === 'inativo' && bannerAtivo) show = false;
        if (busca && !titulo.includes(busca)) show = false;
        
        banner.style.display = show ? 'block' : 'none';
    });
}

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
    
    // Carregar banners na ordem atual
    const banners = Array.from(document.querySelectorAll('.banner-item'));
    originalOrder = banners.map(item => ({
        id: item.dataset.id,
        titulo: item.querySelector('h3, p').textContent,
        imagem: item.querySelector('img').src,
        element: item.cloneNode(true)
    }));
    
    reorderList.innerHTML = '';
    originalOrder.forEach((banner, index) => {
        const item = document.createElement('div');
        item.className = 'bg-admin-gray-700 rounded-lg p-4 cursor-move border border-admin-gray-600 hover:border-admin-primary/30 transition-all';
        item.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas fa-grip-vertical text-admin-gray-400"></i>
                <img src="${banner.imagem}" alt="${banner.titulo}" class="w-12 h-8 object-cover rounded">
                <span class="text-white font-medium truncate">${banner.titulo}</span>
                <span class="text-admin-gray-400 text-sm ml-auto">#${index + 1}</span>
            </div>
        `;
        item.dataset.id = banner.id;
        reorderList.appendChild(item);
    });
    
    modal.classList.remove('hidden');
}

function closeReorder() {
    const modal = document.getElementById('reorderModal');
    if (modal) {
        modal.classList.add('hidden');
        console.log('Modal reorder fechado');
    }
    isReorderMode = false;
}

function closeReorderOnOverlay(event) {
    if (event.target === event.currentTarget) {
        closeReorder();
    }
}

// Event listeners para o modal
document.addEventListener('DOMContentLoaded', function() {
    const closeBtn = document.getElementById('closeReorderBtn');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeReorder();
        });
    }
    
    // Fechar com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('reorderModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeReorder();
            }
        }
    });
});

function saveReorder() {
    const reorderList = document.getElementById('reorderList');
    const items = Array.from(reorderList.children);
    const newOrder = items.map(item => item.dataset.id);
    
    // Enviar nova ordem via AJAX
    const formData = new FormData();
    formData.append('reordenar', '1');
    newOrder.forEach((id, index) => {
        formData.append('banners[]', id);
    });
    
    fetch('processa_banner_avancado.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        location.reload();
    })
    .catch(error => {
        console.error('Erro ao reordenar:', error);
        alert('Erro ao reordenar banners');
    });
}

function toggleBulkActions() {
    // Implementar ações em lote
    alert('Funcionalidade de ações em lote será implementada em breve!');
}
</script>

<?php require_once 'templates/footer_admin.php'; ?>

