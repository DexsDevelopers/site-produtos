<?php
// checkout_pix.php - Página de Checkout PIX Manual
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

ob_start();
session_start();
require_once 'config.php';

// Se recebeu produto_id via GET, adiciona ao carrinho primeiro
if (isset($_GET['produto_id']) && !empty($_GET['produto_id'])) {
    $produto_id = (int)$_GET['produto_id'];
    $quantidade = max(1, (int)($_GET['quantidade'] ?? 1));
    
    if ($produto_id > 0) {
        $stmt = $pdo->prepare("SELECT id, nome, preco, imagem FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produto) {
            if (!isset($_SESSION['carrinho'])) {
                $_SESSION['carrinho'] = [];
            }
            
            $_SESSION['carrinho'][$produto_id] = [
                'id' => $produto['id'],
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'imagem' => $produto['imagem'],
                'quantidade' => $quantidade
            ];
            
            header('Location: checkout_pix.php');
            exit();
        }
    }
}

// Verifica se há itens no carrinho
if (empty($_SESSION['carrinho'])) {
    header('Location: carrinho.php');
    exit();
}

$output = ob_get_clean();
if (!empty($output)) {
    error_log("Output inesperado antes do header: " . substr($output, 0, 200));
    ob_start();
}

try {
    require_once 'templates/header.php';
} catch (Exception $e) {
    error_log("Erro ao carregar header: " . $e->getMessage());
    die("Erro ao carregar arquivos: " . $e->getMessage());
}

$carrinho_itens = $_SESSION['carrinho'];
$total_itens = 0;
$total_preco = 0;

foreach ($carrinho_itens as $item) {
    $total_itens += $item['quantidade'];
    $total_preco += $item['preco'] * $item['quantidade'];
}

// Busca configuração PIX
try {
    if (isset($fileStorage) && is_object($fileStorage)) {
        $chave_pix = $fileStorage->getChavePix();
        $nome_pix = $fileStorage->getNomePix();
        $cidade_pix = $fileStorage->getCidadePix();
    } else {
        $chave_pix = '';
        $nome_pix = '';
        $cidade_pix = '';
    }
} catch (Exception $e) {
    $chave_pix = '';
    $nome_pix = '';
    $cidade_pix = '';
    error_log("Erro ao carregar configuração PIX: " . $e->getMessage());
}
?>

<style>
.checkout-pix-container {
    background: #000000;
    min-height: 100vh;
    padding: 140px 0 80px;
    position: relative;
    overflow: hidden;
}

.checkout-pix-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 0% 100%, rgba(255, 69, 0, 0.08) 0%, transparent 40%),
        radial-gradient(circle at 100% 0%, rgba(255, 0, 0, 0.06) 0%, transparent 40%);
    pointer-events: none;
    z-index: 0;
}

.pix-card {
    background: linear-gradient(135deg, rgba(20, 20, 20, 0.95), rgba(10, 10, 10, 0.98));
    border-radius: 20px;
    padding: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(10px);
    position: relative;
    z-index: 1;
}

.copy-button {
    background: linear-gradient(135deg, #ff4500, #ff6347);
    color: white;
    padding: 14px 28px;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(255, 69, 0, 0.2);
    width: 100%;
}

.copy-button::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.copy-button:hover::before {
    width: 300px;
    height: 300px;
}

.copy-button:hover {
    background: linear-gradient(135deg, #ff6347, #ff4500);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 69, 0, 0.4);
}

.pix-code {
    background: rgba(0, 0, 0, 0.4);
    padding: 1.25rem;
    border-radius: 12px;
    font-family: 'Courier New', monospace;
    word-break: break-all;
    font-size: 0.85rem;
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.1);
    line-height: 1.6;
    max-height: 150px;
    overflow-y: auto;
    position: relative;
    z-index: 1;
}

.checkout-pix-container h1,
.checkout-pix-container h2,
.checkout-pix-container h3 {
    color: #ffffff;
    font-weight: 700;
}

.checkout-pix-container p,
.checkout-pix-container li,
.checkout-pix-container span {
    color: rgba(255, 255, 255, 0.9);
}

.price-highlight {
    color: #ff4500;
    font-weight: 700;
    text-shadow: 0 0 10px rgba(255, 69, 0, 0.3);
}

.total-container {
    position: sticky;
    bottom: 0;
    background: inherit;
    z-index: 5;
}

@media (min-width: 1024px) {
    .total-container {
        position: sticky;
        bottom: 0;
    }
}

@media (max-width: 768px) {
    .checkout-pix-container {
        padding: 120px 0 60px;
    }
    
    .pix-card {
        padding: 1.5rem;
    }
    
    .pix-card.sticky {
        position: relative !important;
        top: auto !important;
        max-height: none !important;
        overflow-y: visible !important;
    }
    
    .pix-card .sticky.bottom-0 {
        position: relative !important;
        bottom: auto !important;
    }
    
    .grid.grid-cols-1 {
        display: flex;
        flex-direction: column;
    }
    
    .grid.grid-cols-1 > div:first-child {
        order: 1;
    }
    
    .grid.grid-cols-1 > div:last-child {
        order: 2;
    }
}
</style>

<div class="checkout-pix-container">
    <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="pt-8">
            <h1 class="text-4xl md:text-5xl font-black text-white mb-4 text-center">
                Finalizar Pagamento
            </h1>
            <p class="text-center text-white/70 mb-8 text-lg">
                Pagamento via PIX
            </p>
            
            <?php if (empty($chave_pix)): ?>
                <div class="pix-card text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-4"></i>
                    <h2 class="text-2xl font-bold text-white mb-4">Chave PIX não configurada</h2>
                    <p class="text-white/70 mb-6">
                        Configure a chave PIX no painel administrativo.
                    </p>
                </div>
            <?php else: ?>
            <!-- Resumo do Pedido -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="lg:col-span-1">
                    <div class="pix-card sticky top-24" style="z-index: 10; max-height: calc(100vh - 120px); overflow-y: auto;">
                        <h2 class="text-xl font-bold text-white mb-6 pb-4 border-b border-white/10">
                            Resumo do Pedido
                        </h2>
                        
                        <div class="space-y-4 mb-6">
                            <?php foreach ($carrinho_itens as $item): ?>
                            <div class="flex justify-between items-start py-3 border-b border-white/5">
                                <div class="flex-1">
                                    <p class="text-white text-sm font-medium"><?= htmlspecialchars($item['nome']) ?></p>
                                    <p class="text-white/60 text-xs mt-1">Qtd: <?= $item['quantidade'] ?></p>
                                </div>
                                <p class="text-white font-semibold ml-4">R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="pt-4 border-t border-white/10 total-container">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-white/70">Total de itens:</span>
                                <span class="text-white font-semibold"><?= $total_itens ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-white text-lg font-bold">Total:</span>
                                <span class="price-highlight text-2xl">R$ <?= number_format($total_preco, 2, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Chave PIX -->
                <div class="lg:col-span-2">
                    <div class="pix-card">
                        <h2 class="text-2xl font-bold text-white mb-4">
                            <i class="fas fa-qrcode mr-2"></i>
                            Chave PIX
                        </h2>
                        
                        <p class="text-white/70 mb-6">
                            Copie a chave PIX abaixo e faça o pagamento pelo app do seu banco.
                        </p>
                        
                        <div class="mb-4">
                            <label class="block text-sm text-white/70 mb-2">Chave PIX:</label>
                            <div class="pix-code" id="pix-key-display">
                                <?= htmlspecialchars($chave_pix) ?>
                            </div>
                            <button onclick="copiarCodigoPix()" class="copy-button mt-4">
                                <i class="fas fa-copy mr-2"></i>
                                Copiar Chave PIX
                            </button>
                        </div>
                        
                        <?php if (!empty($nome_pix)): ?>
                        <div class="mt-4 p-3 bg-white/5 rounded-lg border border-white/10">
                            <p class="text-white/70 text-sm">
                                <strong class="text-white">Beneficiário:</strong> <?= htmlspecialchars($nome_pix) ?>
                            </p>
                            <?php if (!empty($cidade_pix)): ?>
                            <p class="text-white/70 text-sm mt-1">
                                <strong class="text-white">Cidade:</strong> <?= htmlspecialchars($cidade_pix) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                            <p class="text-white/90 text-sm font-semibold mb-2">
                                <i class="fas fa-info-circle mr-2"></i>
                                Instruções:
                            </p>
                            <ol class="text-white/70 text-sm space-y-1 list-decimal list-inside">
                                <li>Copie a chave PIX acima</li>
                                <li>Abra o app do seu banco</li>
                                <li>Escolha a opção PIX</li>
                                <li>Cole a chave e confirme o pagamento</li>
                                <li>Envie o comprovante para confirmar seu pedido</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botões de Navegação -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                <a href="carrinho.php" class="copy-button text-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao Carrinho
                </a>
                <a href="index.php" class="copy-button text-center bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800">
                    <i class="fas fa-home mr-2"></i>
                    Continuar Comprando
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function copiarCodigoPix() {
    const pixKeyElement = document.getElementById('pix-key-display');
    if (!pixKeyElement) return;
    
    const codigo = pixKeyElement.textContent.trim();
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(codigo).then(function() {
            mostrarFeedbackSucesso();
        }).catch(function(err) {
            copiarTextoFallback(codigo);
        });
    } else {
        copiarTextoFallback(codigo);
    }
}

function copiarTextoFallback(texto) {
    const textarea = document.createElement('textarea');
    textarea.value = texto;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        mostrarFeedbackSucesso();
    } catch (err) {
        alert('Erro ao copiar. Selecione o código manualmente e pressione Ctrl+C');
    }
    
    document.body.removeChild(textarea);
}

function mostrarFeedbackSucesso() {
    const btn = document.querySelector('.copy-button');
    if (!btn) return;
    
    const textoOriginal = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check mr-2"></i>Copiado!';
    btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
    
    setTimeout(function() {
        btn.innerHTML = textoOriginal;
        btn.style.background = '';
    }, 2000);
}
</script>

<?php require_once 'templates/footer.php'; ?>
