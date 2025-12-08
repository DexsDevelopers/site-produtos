<?php
// carrinho.php - Página do Carrinho de Compras
session_start();
require_once 'config.php';

// Processa ações do carrinho ANTES do header para evitar HTML em respostas JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpa output apenas se o buffer foi iniciado por nós (não por auto_prepend)
    // Verifica se há buffer e se não foi iniciado por auto_prepend_file
    $ob_level = ob_get_level();
    if ($ob_level > 0) {
        // Verifica se o buffer contém apenas nosso output (não de auto_prepend)
        $buffer_content = ob_get_contents();
        // Só limpa se o buffer estiver vazio ou contiver apenas whitespace
        // Isso evita limpar tokens de segurança ou headers importantes
        if (trim($buffer_content) === '' || strlen($buffer_content) < 100) {
            ob_clean();
        }
    }
    
    try {
        $action = $_POST['action'] ?? '';
        $produto_id = (int)($_POST['produto_id'] ?? 0);
        
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }
    
    switch ($action) {
        case 'add':
            if ($produto_id > 0) {
                // Busca dados do produto
                $stmt = $pdo->prepare("SELECT id, nome, preco, imagem, checkout_link FROM produtos WHERE id = ?");
                $stmt->execute([$produto_id]);
                $produto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($produto) {
                    if (isset($_SESSION['carrinho'][$produto_id])) {
                        $_SESSION['carrinho'][$produto_id]['quantidade']++;
                    } else {
                        $_SESSION['carrinho'][$produto_id] = [
                            'id' => $produto['id'],
                            'nome' => $produto['nome'],
                            'preco' => $produto['preco'],
                            'imagem' => $produto['imagem'],
                            'checkout_link' => $produto['checkout_link'],
                            'quantidade' => 1
                        ];
                    }
                }
            }
            break;
            
        case 'remove':
            if ($produto_id > 0 && isset($_SESSION['carrinho'][$produto_id])) {
                unset($_SESSION['carrinho'][$produto_id]);
            }
            break;
            
        case 'update':
            $quantidade = max(1, (int)($_POST['quantidade'] ?? 1));
            if ($produto_id > 0 && isset($_SESSION['carrinho'][$produto_id])) {
                $_SESSION['carrinho'][$produto_id]['quantidade'] = $quantidade;
            }
            break;
            
        case 'clear':
            $_SESSION['carrinho'] = [];
            break;
    }
    
        // Responde diferentemente para AJAX
        if ($action === 'update') {
            // Resposta JSON para AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Quantidade atualizada para ' . $quantidade]);
            exit();
        } else {
            // Redireciona para outras ações
            header('Location: carrinho.php');
            exit();
        }
    } catch (Exception $e) {
        // Tratamento de erro
        if ($action === 'update') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
            exit();
        } else {
            header('Location: carrinho.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}

// Inclui o header APENAS para requisições GET (visualização da página)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    require_once 'templates/header.php';
}
?>

<!-- CSS Específico da Página do Carrinho com Cores Vermelho e Preto -->
<style>
.carrinho-container {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    min-height: 100vh;
    padding: 120px 0 60px;
    position: relative;
    overflow: hidden;
}

.carrinho-container::before {
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

.carrinho-container h1 {
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from { text-shadow: 0 0 20px rgba(255, 0, 0, 0.3); }
    to { text-shadow: 0 0 30px rgba(255, 0, 0, 0.6), 0 0 40px rgba(255, 0, 0, 0.3); }
}

.carrinho-card {
    background: linear-gradient(145deg, #1a0000, #000000);
    border: 1px solid rgba(255, 0, 0, 0.2);
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.carrinho-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 0, 0, 0.1), transparent);
    transition: left 0.6s;
}

.carrinho-card:hover::before {
    left: 100%;
}

.carrinho-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 20px 40px rgba(255, 0, 0, 0.2), 0 0 20px rgba(255, 0, 0, 0.1);
}

.btn-carrinho {
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
    border: none;
    cursor: pointer;
}

.btn-carrinho::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-carrinho:hover::before {
    left: 100%;
}

.btn-carrinho:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}

.preco-carrinho {
    color: #ff0000;
    text-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
    animation: priceGlow 2s ease-in-out infinite alternate;
}

@keyframes priceGlow {
    from { text-shadow: 0 0 10px rgba(255, 0, 0, 0.3); }
    to { text-shadow: 0 0 20px rgba(255, 0, 0, 0.6), 0 0 30px rgba(255, 0, 0, 0.3); }
}

.total-carrinho {
    background: linear-gradient(145deg, #1a0000, #000000);
    border: 2px solid rgba(255, 0, 0, 0.3);
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
}

.total-carrinho::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(255, 0, 0, 0.05), transparent);
    animation: totalShimmer 3s ease-in-out infinite;
}

@keyframes totalShimmer {
    0%, 100% { transform: translateX(-100%); }
    50% { transform: translateX(100%); }
}
</style>

<div class="carrinho-container">
<?php
// Processa ações do carrinho já foi movido para o topo do arquivo (linhas 6-86)
// Calcula totais
$total_itens = 0;
$total_preco = 0;
$carrinho_itens = $_SESSION['carrinho'] ?? [];

foreach ($carrinho_itens as $item) {
    $total_itens += $item['quantidade'];
    $total_preco += $item['preco'] * $item['quantidade'];
}
?>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <h1 class="text-3xl md:text-4xl font-black text-white mb-8">Carrinho de Compras</h1>
        
        <?php if (empty($carrinho_itens)): ?>
            <!-- Carrinho Vazio -->
            <div class="text-center py-16 bg-brand-gray/50 rounded-xl">
                <svg class="mx-auto h-24 w-24 text-brand-gray-text mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h2 class="text-2xl font-bold text-white mb-4">Seu carrinho está vazio</h2>
                <p class="text-brand-gray-text mb-8">Que tal adicionar alguns produtos incríveis?</p>
                <a href="index.php" class="bg-brand-red hover:bg-brand-red-dark text-white font-bold py-3 px-8 rounded-lg transition-colors">
                    Continuar Comprando
                </a>
            </div>
        <?php else: ?>
            <!-- Carrinho com Itens -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Lista de Itens -->
                <div class="lg:col-span-2 space-y-4">
                    <?php foreach ($carrinho_itens as $item): ?>
                        <div class="cart-item bg-brand-black border border-brand-gray-light rounded-xl p-6 flex flex-col sm:flex-row gap-4">
                            <!-- Imagem do Produto -->
                            <div class="flex-shrink-0">
                                <img src="<?= htmlspecialchars($item['imagem']) ?>" 
                                     alt="<?= htmlspecialchars($item['nome']) ?>" 
                                     class="w-24 h-24 sm:w-32 sm:h-32 object-cover rounded-lg"
                                     loading="lazy">
                            </div>
                            
                            <!-- Informações do Produto -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-bold text-white truncate"><?= htmlspecialchars($item['nome']) ?></h3>
                                <p class="text-2xl font-bold text-brand-red mt-2"><?= formatarPreco($item['preco']) ?></p>
                                
                                <!-- Controles de Quantidade -->
                                <div class="mt-4 flex items-center gap-4">
                                    <form method="POST" class="flex items-center gap-2" onsubmit="return updateQuantity(this)">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                        <label class="text-sm text-brand-gray-text">Qtd:</label>
                                        <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" 
                                               min="1" max="99" 
                                               class="w-16 bg-gray-800 border border-gray-600 text-white rounded px-2 py-1 text-center focus:border-blue-500 focus:outline-none" 
                                               style="color: white !important; background-color: #1f2937 !important; border-color: #4b5563 !important;">
                                        <button type="submit" class="bg-admin-primary hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors">
                                            Atualizar
                                        </button>
                                    </form>
                                    
                                    <!-- Botão Remover -->
                                    <form method="POST" class="inline" onsubmit="return removeItem(this)">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-300 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Subtotal -->
                            <div class="text-right">
                                <p class="text-sm text-brand-gray-text">Subtotal</p>
                                <p class="text-xl font-bold text-white"><?= formatarPreco($item['preco'] * $item['quantidade']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Botão Limpar Carrinho -->
                    <div class="text-center pt-4">
                        <form method="POST" class="inline" onsubmit="return clearCart(this)">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="text-brand-gray-text hover:text-red-400 transition-colors">
                                Limpar Carrinho
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Resumo do Pedido -->
                <div class="lg:col-span-1">
                    <div class="bg-brand-gray/30 rounded-xl p-6 sticky top-24">
                        <h3 class="text-xl font-bold text-white mb-6">Resumo do Pedido</h3>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-brand-gray-text">Itens (<?= $total_itens ?>)</span>
                                <span class="text-white"><?= formatarPreco($total_preco) ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-brand-gray-text">Frete</span>
                                <span class="text-green-400">Grátis</span>
                            </div>
                            
                            <div class="border-t border-brand-gray-light pt-4">
                                <div class="flex justify-between text-xl font-bold">
                                    <span class="text-white">Total</span>
                                    <span class="text-brand-red"><?= formatarPreco($total_preco) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 space-y-4">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="checkout_pix.php" class="block w-full bg-brand-red hover:bg-brand-red-dark text-white font-bold py-4 rounded-lg transition-colors text-center">
                                    <i class="fas fa-qrcode mr-2"></i>
                                    Finalizar Compra (PIX)
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="block w-full bg-brand-red hover:bg-brand-red-dark text-white font-bold py-4 rounded-lg transition-colors text-center">
                                    Fazer Login para Finalizar Compra
                                </a>
                            <?php endif; ?>
                            
                            <a href="index.php" class="block w-full text-center bg-brand-gray-light hover:bg-brand-gray text-white font-bold py-3 rounded-lg transition-colors">
                                Continuar Comprando
                            </a>
                        </div>
                        
                        <!-- Informações de Segurança -->
                        <div class="mt-6 text-center">
                            <div class="flex items-center justify-center gap-2 text-sm text-brand-gray-text">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                Compra 100% Segura
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Função para atualizar quantidade com validação
function updateQuantity(form) {
    const quantidade = form.querySelector('input[name="quantidade"]').value;
    
    // Validação
    if (quantidade < 1 || quantidade > 99) {
        alert('Quantidade deve ser entre 1 e 99');
        return false;
    }
    
    // Mostra loading
    const button = form.querySelector('button[type="submit"]');
    const originalText = button.textContent;
    button.textContent = 'Atualizando...';
    button.disabled = true;
    
    // Envia via AJAX
    const formData = new FormData(form);
    
    fetch('carrinho.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro de rede: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Recarrega a página para mostrar as mudanças
            window.location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro desconhecido'));
            button.textContent = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar quantidade: ' + error.message);
        button.textContent = originalText;
        button.disabled = false;
    });
    
    return false; // Previne o submit normal
}

// Função para remover item com popup de confirmação
function removeItem(form) {
    const produtoId = form.querySelector('input[name="produto_id"]').value;
    const produtoNome = form.closest('.cart-item').querySelector('h3').textContent;
    
    mostrarConfirmacaoRemover(produtoNome, () => {
        // Usuário confirmou - remove via AJAX
        const button = form.querySelector('button[type="submit"]');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
        button.disabled = true;
        
        const formData = new FormData(form);
        
        fetch('carrinho.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                // Sucesso - mostra popup e recarrega
                mostrarSucessoRemover(produtoNome);
                setTimeout(() => {
                    window.location.href = 'carrinho.php';
                }, 1500);
            } else {
                throw new Error('Erro ao remover produto');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao remover produto. Tente novamente.');
            button.innerHTML = originalHTML;
            button.disabled = false;
        });
    });
    
    return false;
}

// Função para limpar carrinho
function clearCart(form) {
    if (confirm('Limpar todo o carrinho? Esta ação não pode ser desfeita.')) {
        const button = form.querySelector('button[type="submit"]');
        button.textContent = 'Limpando...';
        button.disabled = true;
        form.submit();
    }
    return false;
}

// Atualiza totais em tempo real (opcional)
document.addEventListener('DOMContentLoaded', function() {
    // Adiciona animação aos itens do carrinho
    const cartItems = document.querySelectorAll('.cart-item');
    cartItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        setTimeout(() => {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
    });
    });
</script>

<style>
/* Popup de Confirmação de Remoção */
.delete-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.delete-popup.show {
    opacity: 1;
    visibility: visible;
}

.delete-popup-content {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border: 2px solid rgba(255, 69, 0, 0.4);
    border-radius: 20px;
    padding: 2.5rem;
    max-width: 450px;
    width: 90%;
    text-align: center;
    position: relative;
    box-shadow: 0 20px 60px rgba(255, 69, 0, 0.4);
    transform: scale(0.8) translateY(20px);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.delete-popup.show .delete-popup-content {
    transform: scale(1) translateY(0);
}

.delete-popup-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #ff4500, #ff6347);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: popupIconBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 10px 30px rgba(255, 69, 0, 0.4);
}

@keyframes popupIconBounce {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.delete-popup-icon i {
    font-size: 2.5rem;
    color: white;
}

.delete-popup-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.75rem;
    animation: fadeInUp 0.5s ease 0.2s both;
}

.delete-popup-message {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 1.5rem;
    line-height: 1.6;
    animation: fadeInUp 0.5s ease 0.3s both;
}

.delete-popup-produto {
    font-weight: 600;
    color: #ff6347;
    margin: 0.5rem 0;
}

.delete-popup-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    animation: fadeInUp 0.5s ease 0.4s both;
}

.delete-popup-btn {
    padding: 0.875rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.delete-popup-btn-danger {
    background: linear-gradient(135deg, #ff4500, #ff6347);
    color: white;
    box-shadow: 0 4px 15px rgba(255, 69, 0, 0.3);
}

.delete-popup-btn-danger:hover {
    background: linear-gradient(135deg, #ff6347, #ff4500);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 69, 0, 0.4);
}

.delete-popup-btn-cancel {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.delete-popup-btn-cancel:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

/* Popup de Sucesso de Remoção */
.success-delete-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.success-delete-popup.show {
    opacity: 1;
    visibility: visible;
}

.success-delete-popup-content {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border: 2px solid rgba(16, 185, 129, 0.3);
    border-radius: 20px;
    padding: 2.5rem;
    max-width: 420px;
    width: 90%;
    text-align: center;
    position: relative;
    box-shadow: 0 20px 60px rgba(16, 185, 129, 0.3);
    transform: scale(0.8) translateY(20px);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.success-delete-popup.show .success-delete-popup-content {
    transform: scale(1) translateY(0);
}

.success-delete-popup-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #10B981, #059669);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: popupIconBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
}

.success-delete-popup-icon i {
    font-size: 2.5rem;
    color: white;
}

.success-delete-popup-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.75rem;
    animation: fadeInUp 0.5s ease 0.2s both;
}

.success-delete-popup-message {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 1.5rem;
    line-height: 1.6;
    animation: fadeInUp 0.5s ease 0.3s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsivo */
@media (max-width: 640px) {
    .delete-popup-content,
    .success-delete-popup-content {
        padding: 2rem 1.5rem;
    }
    
    .delete-popup-title,
    .success-delete-popup-title {
        font-size: 1.5rem;
    }
    
    .delete-popup-buttons {
        flex-direction: column;
    }
    
    .delete-popup-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Popup de Confirmação -->
<div id="delete-confirm-popup" class="delete-popup">
    <div class="delete-popup-content">
        <div class="delete-popup-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="delete-popup-title">Remover Produto?</h2>
        <p class="delete-popup-message">
            Tem certeza que deseja remover
            <span class="delete-popup-produto" id="delete-produto-nome"></span>
            do seu carrinho?
        </p>
        <div class="delete-popup-buttons">
            <button onclick="confirmarRemover()" class="delete-popup-btn delete-popup-btn-danger">
                <i class="fas fa-trash"></i>
                Sim, Remover
            </button>
            <button onclick="cancelarRemover()" class="delete-popup-btn delete-popup-btn-cancel">
                <i class="fas fa-times"></i>
                Cancelar
            </button>
        </div>
    </div>
</div>

<!-- Popup de Sucesso -->
<div id="success-delete-popup" class="success-delete-popup">
    <div class="success-delete-popup-content">
        <div class="success-delete-popup-icon">
            <i class="fas fa-check"></i>
        </div>
        <h2 class="success-delete-popup-title">Produto Removido!</h2>
        <p class="success-delete-popup-message" id="success-delete-message">
            O produto foi removido do seu carrinho com sucesso.
        </p>
    </div>
</div>

<script>
let confirmCallback = null;
let produtoNomeParaRemover = '';

function mostrarConfirmacaoRemover(nome, callback) {
    produtoNomeParaRemover = nome;
    confirmCallback = callback;
    document.getElementById('delete-produto-nome').textContent = nome;
    document.getElementById('delete-confirm-popup').classList.add('show');
}

function confirmarRemover() {
    if (confirmCallback) {
        confirmCallback();
    }
    fecharConfirmacaoRemover();
}

function cancelarRemover() {
    fecharConfirmacaoRemover();
}

function fecharConfirmacaoRemover() {
    document.getElementById('delete-confirm-popup').classList.remove('show');
    confirmCallback = null;
    produtoNomeParaRemover = '';
}

function mostrarSucessoRemover(nome) {
    document.getElementById('success-delete-message').textContent = 
        `${nome} foi removido do seu carrinho com sucesso.`;
    document.getElementById('success-delete-popup').classList.add('show');
    
    setTimeout(() => {
        fecharSucessoRemover();
    }, 2000);
}

function fecharSucessoRemover() {
    document.getElementById('success-delete-popup').classList.remove('show');
}

// Fecha popups ao clicar fora
document.getElementById('delete-confirm-popup').addEventListener('click', function(e) {
    if (e.target === this) {
        cancelarRemover();
    }
});

document.getElementById('success-delete-popup').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharSucessoRemover();
    }
});
</script>

<?php
require_once 'templates/footer.php';
?>
