<?php
// avaliacoes_avancadas.php - Sistema de Avaliações Avançado
session_start();
require_once 'config.php';
require_once 'includes/advanced_reviews.php';

$produto_id = isset($_GET['produto_id']) ? (int)$_GET['produto_id'] : 0;
$filtros = [
    'nota' => $_GET['nota'] ?? 0,
    'com_fotos' => isset($_GET['com_fotos']),
    'ordenacao' => $_GET['ordenacao'] ?? 'data_avaliacao DESC'
];

$reviews = new AdvancedReviews($pdo);

// Buscar produto
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$produto_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header('Location: index.php');
    exit();
}

// Buscar avaliações
$avaliacoes = $reviews->buscarAvaliacoes($produto_id, $filtros);
$estatisticas = $reviews->getEstatisticasAvaliacoes($produto_id);

$page_title = 'Avaliações - ' . $produto['nome'];
require_once 'templates/header.php';
?>

<style>
/* Estilos para avaliações avançadas */
.review-card {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.review-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.star-rating {
    display: flex;
    gap: 2px;
}

.star {
    color: #fbbf24;
    transition: all 0.2s ease;
}

.star:hover {
    transform: scale(1.1);
}

.photo-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 8px;
    margin-top: 12px;
}

.photo-thumb {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.photo-thumb:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

.filter-chip {
    background: rgba(59, 130, 246, 0.2);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #60a5fa;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-chip.active {
    background: rgba(59, 130, 246, 0.8);
    color: white;
}

.filter-chip:hover {
    background: rgba(59, 130, 246, 0.4);
}

.stats-bar {
    background: rgba(255, 255, 255, 0.1);
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
}

.stats-fill {
    background: linear-gradient(90deg, #fbbf24, #f59e0b);
    height: 100%;
    transition: width 0.5s ease;
}
</style>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <!-- Header do Produto -->
        <div class="flex flex-col md:flex-row gap-8 mb-12">
            <div class="flex-shrink-0">
                <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                     alt="<?= htmlspecialchars($produto['nome']) ?>" 
                     class="w-32 h-32 object-cover rounded-xl">
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-white mb-4"><?= htmlspecialchars($produto['nome']) ?></h1>
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold text-yellow-400"><?= $estatisticas['media'] ?? 0 ?></span>
                        <div class="star-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= ($i <= ($estatisticas['media'] ?? 0)) ? 'text-yellow-400' : 'text-gray-600' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <span class="text-gray-400">(<?= $estatisticas['total'] ?? 0 ?> avaliações)</span>
                </div>
                <a href="produto.php?id=<?= $produto_id ?>" 
                   class="inline-flex items-center gap-2 text-brand-red hover:text-brand-red-dark transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao produto
                </a>
            </div>
        </div>

        <!-- Estatísticas de Avaliações -->
        <div class="review-card rounded-xl p-6 mb-8">
            <h3 class="text-xl font-bold text-white mb-6">Distribuição das Avaliações</h3>
            <div class="space-y-3">
                <?php for ($nota = 5; $nota >= 1; $nota--): ?>
                    <?php 
                    $total_nota = $estatisticas['cinco_estrelas'] ?? 0;
                    if ($nota == 4) $total_nota = $estatisticas['quatro_estrelas'] ?? 0;
                    if ($nota == 3) $total_nota = $estatisticas['tres_estrelas'] ?? 0;
                    if ($nota == 2) $total_nota = $estatisticas['duas_estrelas'] ?? 0;
                    if ($nota == 1) $total_nota = $estatisticas['uma_estrela'] ?? 0;
                    
                    $porcentagem = $estatisticas['total'] > 0 ? ($total_nota / $estatisticas['total']) * 100 : 0;
                    ?>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-400 w-8"><?= $nota ?>★</span>
                        <div class="stats-bar flex-1">
                            <div class="stats-fill" style="width: <?= $porcentagem ?>%"></div>
                        </div>
                        <span class="text-sm text-gray-400 w-12"><?= $total_nota ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Filtros -->
        <div class="review-card rounded-xl p-6 mb-8">
            <h3 class="text-lg font-bold text-white mb-4">Filtros</h3>
            <div class="flex flex-wrap gap-3">
                <!-- Filtro por nota -->
                <div class="flex gap-2">
                    <span class="text-gray-400 text-sm">Nota:</span>
                    <?php for ($nota = 5; $nota >= 1; $nota--): ?>
                        <button class="filter-chip <?= ($filtros['nota'] == $nota) ? 'active' : '' ?>" 
                                onclick="filtrarPorNota(<?= $nota ?>)">
                            <?= $nota ?>★
                        </button>
                    <?php endfor; ?>
                    <button class="filter-chip <?= ($filtros['nota'] == 0) ? 'active' : '' ?>" 
                            onclick="filtrarPorNota(0)">
                        Todas
                    </button>
                </div>
                
                <!-- Filtro por fotos -->
                <button class="filter-chip <?= $filtros['com_fotos'] ? 'active' : '' ?>" 
                        onclick="filtrarComFotos()">
                    <i class="fas fa-camera mr-1"></i>
                    Com fotos
                </button>
                
                <!-- Ordenação -->
                <select onchange="alterarOrdenacao(this.value)" 
                        class="bg-gray-800 border border-gray-600 text-white rounded px-3 py-2">
                    <option value="data_avaliacao DESC" <?= ($filtros['ordenacao'] == 'data_avaliacao DESC') ? 'selected' : '' ?>>Mais recentes</option>
                    <option value="data_avaliacao ASC" <?= ($filtros['ordenacao'] == 'data_avaliacao ASC') ? 'selected' : '' ?>>Mais antigas</option>
                    <option value="nota DESC" <?= ($filtros['ordenacao'] == 'nota DESC') ? 'selected' : '' ?>>Melhores notas</option>
                    <option value="nota ASC" <?= ($filtros['ordenacao'] == 'nota ASC') ? 'selected' : '' ?>>Piores notas</option>
                </select>
            </div>
        </div>

        <!-- Lista de Avaliações -->
        <div class="space-y-6">
            <?php if (empty($avaliacoes)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-star text-gray-500 text-4xl mb-4"></i>
                    <p class="text-gray-400">Nenhuma avaliação encontrada com os filtros selecionados.</p>
                </div>
            <?php else: ?>
                <?php foreach ($avaliacoes as $avaliacao): ?>
                    <div class="review-card rounded-xl p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-brand-red to-brand-pink rounded-full flex items-center justify-center text-white font-bold">
                                    <?= strtoupper(substr($avaliacao['nome_usuario'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-white"><?= htmlspecialchars($avaliacao['nome_usuario']) ?></h4>
                                    <div class="flex items-center gap-2">
                                        <div class="star-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?= ($i <= $avaliacao['nota']) ? 'text-yellow-400' : 'text-gray-600' ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-sm text-gray-400">
                                            <?= date('d/m/Y', strtotime($avaliacao['data_avaliacao'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botões de utilidade -->
                            <div class="flex items-center gap-2">
                                <button class="text-gray-400 hover:text-green-400 transition-colors" 
                                        onclick="marcarUtil(<?= $avaliacao['id'] ?>, true)">
                                    <i class="fas fa-thumbs-up"></i>
                                    <?= $avaliacao['util_sim'] ?>
                                </button>
                                <button class="text-gray-400 hover:text-red-400 transition-colors" 
                                        onclick="marcarUtil(<?= $avaliacao['id'] ?>, false)">
                                    <i class="fas fa-thumbs-down"></i>
                                    <?= $avaliacao['util_nao_util'] ?>
                                </button>
                            </div>
                        </div>
                        
                        <p class="text-gray-300 mb-4"><?= nl2br(htmlspecialchars($avaliacao['comentario'])) ?></p>
                        
                        <!-- Fotos da avaliação -->
                        <?php if (!empty($avaliacao['fotos'])): ?>
                            <div class="photo-gallery">
                                <?php 
                                $fotos = explode(',', $avaliacao['fotos']);
                                foreach ($fotos as $foto): 
                                    if (!empty(trim($foto))):
                                ?>
                                    <img src="<?= htmlspecialchars(trim($foto)) ?>" 
                                         alt="Foto da avaliação" 
                                         class="photo-thumb"
                                         onclick="abrirGaleria('<?= htmlspecialchars(trim($foto)) ?>')">
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Resposta do vendedor -->
                        <?php if (!empty($avaliacao['resposta'])): ?>
                            <div class="mt-4 p-4 bg-gray-800 rounded-lg border-l-4 border-brand-red">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-store text-brand-red"></i>
                                    <span class="font-bold text-white">Resposta da Loja</span>
                                </div>
                                <p class="text-gray-300"><?= nl2br(htmlspecialchars($avaliacao['resposta'])) ?></p>
                                <span class="text-sm text-gray-400">
                                    <?= date('d/m/Y H:i', strtotime($avaliacao['resposta_data'])) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para galeria de fotos -->
<div id="photo-modal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="fecharGaleria()" 
                class="absolute -top-4 -right-4 bg-white text-black rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-200 transition-colors">
            <i class="fas fa-times"></i>
        </button>
        <img id="modal-photo" src="" alt="Foto da avaliação" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
</div>

<script>
// Funções para filtros
function filtrarPorNota(nota) {
    const url = new URL(window.location);
    if (nota === 0) {
        url.searchParams.delete('nota');
    } else {
        url.searchParams.set('nota', nota);
    }
    window.location.href = url.toString();
}

function filtrarComFotos() {
    const url = new URL(window.location);
    if (url.searchParams.has('com_fotos')) {
        url.searchParams.delete('com_fotos');
    } else {
        url.searchParams.set('com_fotos', '1');
    }
    window.location.href = url.toString();
}

function alterarOrdenacao(ordenacao) {
    const url = new URL(window.location);
    url.searchParams.set('ordenacao', ordenacao);
    window.location.href = url.toString();
}

// Funções para galeria
function abrirGaleria(fotoUrl) {
    document.getElementById('modal-photo').src = fotoUrl;
    document.getElementById('photo-modal').classList.remove('hidden');
    document.getElementById('photo-modal').classList.add('flex');
}

function fecharGaleria() {
    document.getElementById('photo-modal').classList.add('hidden');
    document.getElementById('photo-modal').classList.remove('flex');
}

// Função para marcar utilidade
function marcarUtil(avaliacaoId, util) {
    // Implementar AJAX para marcar utilidade
    fetch('processa_utilidade_avaliacao.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            avaliacao_id: avaliacaoId,
            util: util
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recarregar página para mostrar mudanças
            window.location.reload();
        }
    });
}

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharGaleria();
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
