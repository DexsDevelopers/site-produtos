<?php
// recomendacoes.php - Página de Recomendações Personalizadas
session_start();
require_once 'config.php';
require_once 'includes/recommendation_system.php';

$recommendations = new RecommendationSystem($pdo);
$usuario_id = $_SESSION['user_id'] ?? null;

// Buscar diferentes tipos de recomendações
$produtos_personalizados = $recommendations->getRecomendacoesPersonalizadas($usuario_id, 12);
$produtos_populares = $recommendations->getProdutosPopulares(8);
$produtos_recentes = $recommendations->getProdutosRecentes(8);
$produtos_oferta = $recommendations->getProdutosEmOferta(8);

$page_title = 'Recomendações para Você';
require_once 'templates/header.php';
?>

<style>
/* Estilos para recomendações */
.recommendation-section {
    margin-bottom: 3rem;
}

.recommendation-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(59, 130, 246, 0.3);
}

.recommendation-title h2 {
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
    margin: 0;
}

.recommendation-title .icon {
    width: 2rem;
    height: 2rem;
    background: linear-gradient(135deg, #3B82F6, #8B5CF6);
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.product-card {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border-color: rgba(59, 130, 246, 0.5);
}

.product-image {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    background: linear-gradient(135deg, #EF4444, #DC2626);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
}

.product-info {
    padding: 1.25rem;
}

.product-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: white;
    margin-bottom: 0.5rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.stars {
    display: flex;
    gap: 0.125rem;
}

.star {
    color: #FBBF24;
    font-size: 0.875rem;
}

.rating-text {
    font-size: 0.875rem;
    color: #94A3B8;
}

.product-price {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.current-price {
    font-size: 1.25rem;
    font-weight: bold;
    color: #EF4444;
}

.old-price {
    font-size: 1rem;
    color: #94A3B8;
    text-decoration: line-through;
}

.discount-badge {
    background: linear-gradient(135deg, #10B981, #059669);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.product-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-primary {
    flex: 1;
    background: linear-gradient(135deg, #3B82F6, #2563EB);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563EB, #1D4ED8);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.75rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(59, 130, 246, 0.5);
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #94A3B8;
}

.empty-state .icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: between;
    margin-bottom: 1.5rem;
}

.view-all {
    background: rgba(59, 130, 246, 0.2);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #60A5FA;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.view-all:hover {
    background: rgba(59, 130, 246, 0.4);
    color: white;
}
</style>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-black text-white mb-4">
                <?= $usuario_id ? 'Recomendações para Você' : 'Produtos em Destaque' ?>
            </h1>
            <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                <?= $usuario_id ? 'Produtos selecionados especialmente para você baseados no seu comportamento' : 'Descubra nossos produtos mais populares e em oferta' ?>
            </p>
        </div>

        <!-- Produtos Personalizados -->
        <?php if (!empty($produtos_personalizados)): ?>
        <div class="recommendation-section">
            <div class="recommendation-title">
                <div class="icon">
                    <i class="fas fa-magic"></i>
                </div>
                <h2>Recomendados para Você</h2>
            </div>
            <div class="product-grid">
                <?php foreach ($produtos_personalizados as $produto): ?>
                    <div class="product-card">
                        <a href="produto.php?id=<?= $produto['id'] ?>" class="block">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                                     alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                     loading="lazy">
                                <?php if ($produto['preco_antigo'] > $produto['preco']): ?>
                                    <?php $desconto = round((($produto['preco_antigo'] - $produto['preco']) / $produto['preco_antigo']) * 100); ?>
                                    <div class="product-badge">
                                        -<?= $desconto ?>%
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($produto['nome']) ?></h3>
                                
                                <?php if ($produto['media_avaliacoes'] > 0): ?>
                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?= ($i <= $produto['media_avaliacoes']) ? '' : 'opacity-30' ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-text">(<?= $produto['total_avaliacoes'] ?>)</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-price">
                                    <span class="current-price"><?= formatarPreco($produto['preco']) ?></span>
                                    <?php if ($produto['preco_antigo'] > $produto['preco']): ?>
                                        <span class="old-price"><?= formatarPreco($produto['preco_antigo']) ?></span>
                                        <span class="discount-badge">
                                            -<?= $desconto ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-actions">
                                    <a href="produto.php?id=<?= $produto['id'] ?>" class="btn-primary">
                                        <i class="fas fa-eye"></i>
                                        Ver Produto
                                    </a>
                                    <button class="btn-secondary" onclick="adicionarAoCarrinho(<?= $produto['id'] ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Produtos Populares -->
        <?php if (!empty($produtos_populares)): ?>
        <div class="recommendation-section">
            <div class="section-header">
                <div class="recommendation-title">
                    <div class="icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h2>Produtos em Alta</h2>
                </div>
                <a href="busca_avancada.php?ordenacao=mais_vendidos" class="view-all">
                    Ver Todos
                </a>
            </div>
            <div class="product-grid">
                <?php foreach ($produtos_populares as $produto): ?>
                    <div class="product-card">
                        <a href="produto.php?id=<?= $produto['id'] ?>" class="block">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                                     alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                     loading="lazy">
                                <div class="product-badge">
                                    <i class="fas fa-fire mr-1"></i>
                                    Em Alta
                                </div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($produto['nome']) ?></h3>
                                
                                <?php if ($produto['media_avaliacoes'] > 0): ?>
                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?= ($i <= $produto['media_avaliacoes']) ? '' : 'opacity-30' ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-text">(<?= $produto['total_avaliacoes'] ?>)</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-price">
                                    <span class="current-price"><?= formatarPreco($produto['preco']) ?></span>
                                </div>
                                
                                <div class="product-actions">
                                    <a href="produto.php?id=<?= $produto['id'] ?>" class="btn-primary">
                                        <i class="fas fa-eye"></i>
                                        Ver Produto
                                    </a>
                                    <button class="btn-secondary" onclick="adicionarAoCarrinho(<?= $produto['id'] ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Produtos em Oferta -->
        <?php if (!empty($produtos_oferta)): ?>
        <div class="recommendation-section">
            <div class="section-header">
                <div class="recommendation-title">
                    <div class="icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <h2>Ofertas Especiais</h2>
                </div>
                <a href="busca_avancada.php?preco_max=100" class="view-all">
                    Ver Todas as Ofertas
                </a>
            </div>
            <div class="product-grid">
                <?php foreach ($produtos_oferta as $produto): ?>
                    <div class="product-card">
                        <a href="produto.php?id=<?= $produto['id'] ?>" class="block">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                                     alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                     loading="lazy">
                                <div class="product-badge">
                                    <i class="fas fa-percentage mr-1"></i>
                                    Oferta
                                </div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($produto['nome']) ?></h3>
                                
                                <div class="product-price">
                                    <span class="current-price"><?= formatarPreco($produto['preco']) ?></span>
                                    <span class="old-price"><?= formatarPreco($produto['preco_antigo']) ?></span>
                                    <span class="discount-badge">
                                        -<?= round($produto['desconto_percentual']) ?>%
                                    </span>
                                </div>
                                
                                <div class="product-actions">
                                    <a href="produto.php?id=<?= $produto['id'] ?>" class="btn-primary">
                                        <i class="fas fa-eye"></i>
                                        Ver Oferta
                                    </a>
                                    <button class="btn-secondary" onclick="adicionarAoCarrinho(<?= $produto['id'] ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Produtos Recentes -->
        <?php if (!empty($produtos_recentes)): ?>
        <div class="recommendation-section">
            <div class="section-header">
                <div class="recommendation-title">
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h2>Recém-Chegados</h2>
                </div>
                <a href="busca_avancada.php?ordenacao=mais_recentes" class="view-all">
                    Ver Todos
                </a>
            </div>
            <div class="product-grid">
                <?php foreach ($produtos_recentes as $produto): ?>
                    <div class="product-card">
                        <a href="produto.php?id=<?= $produto['id'] ?>" class="block">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                                     alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                     loading="lazy">
                                <div class="product-badge">
                                    <i class="fas fa-clock mr-1"></i>
                                    Novo
                                </div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($produto['nome']) ?></h3>
                                
                                <div class="product-price">
                                    <span class="current-price"><?= formatarPreco($produto['preco']) ?></span>
                                </div>
                                
                                <div class="product-actions">
                                    <a href="produto.php?id=<?= $produto['id'] ?>" class="btn-primary">
                                        <i class="fas fa-eye"></i>
                                        Ver Produto
                                    </a>
                                    <button class="btn-secondary" onclick="adicionarAoCarrinho(<?= $produto['id'] ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Estado vazio -->
        <?php if (empty($produtos_personalizados) && empty($produtos_populares) && empty($produtos_oferta) && empty($produtos_recentes)): ?>
            <div class="empty-state">
                <div class="icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Nenhum produto encontrado</h3>
                <p class="text-gray-400 mb-6">Que tal explorar nossa loja?</p>
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-home mr-2"></i>
                    Ir para a Loja
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Função para adicionar ao carrinho
function adicionarAoCarrinho(produtoId) {
    fetch('adicionar_carrinho.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            produto_id: produtoId,
            quantidade: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar notificação de sucesso
            mostrarNotificacao('Produto adicionado ao carrinho!', 'success');
            
            // Atualizar contador do carrinho
            atualizarContadorCarrinho();
        } else {
            mostrarNotificacao('Erro ao adicionar produto: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarNotificacao('Erro ao adicionar produto ao carrinho', 'error');
    });
}

// Função para mostrar notificações
function mostrarNotificacao(mensagem, tipo) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${
        tipo === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = mensagem;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Função para atualizar contador do carrinho
function atualizarContadorCarrinho() {
    fetch('get_carrinho_count.php')
        .then(response => response.json())
        .then(data => {
            const counters = document.querySelectorAll('#cart-count, #cart-count-mobile');
            counters.forEach(counter => {
                counter.textContent = data.count;
                if (data.count > 0) {
                    counter.style.display = 'flex';
                } else {
                    counter.style.display = 'none';
                }
            });
        });
}

// Registrar visualização de produtos
document.addEventListener('DOMContentLoaded', function() {
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        const link = card.querySelector('a');
        if (link) {
            const href = link.getAttribute('href');
            const produtoId = href.match(/id=(\d+)/);
            if (produtoId) {
                // Registrar visualização quando o produto entra na tela
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            fetch('registrar_visualizacao.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    produto_id: produtoId[1]
                                })
                            });
                            observer.unobserve(entry.target);
                        }
                    });
                });
                observer.observe(card);
            }
        }
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>
