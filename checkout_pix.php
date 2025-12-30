<?php
// checkout_pix.php - Página de Checkout com QR Code PIX
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

// Se recebeu produto_id via GET, adiciona ao carrinho primeiro
if (isset($_GET['produto_id']) && !empty($_GET['produto_id'])) {
    $produto_id = (int)$_GET['produto_id'];
    $quantidade = max(1, (int)($_GET['quantidade'] ?? 1));
    
    if ($produto_id > 0) {
        // Busca dados do produto
        $stmt = $pdo->prepare("SELECT id, nome, preco, imagem FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produto) {
            // Inicializa carrinho se não existir
            if (!isset($_SESSION['carrinho'])) {
                $_SESSION['carrinho'] = [];
            }
            
            // Adiciona produto ao carrinho (substitui se já existir para garantir quantidade correta)
            $_SESSION['carrinho'][$produto_id] = [
                'id' => $produto['id'],
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'imagem' => $produto['imagem'],
                'quantidade' => $quantidade
            ];
            
            // Redireciona para remover o produto_id da URL
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

// Carrega header (config.php já foi carregado na linha 7)
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
    if (!isset($fileStorage) || !is_object($fileStorage)) {
        // Tenta inicializar se não estiver disponível
        if (file_exists(__DIR__ . '/includes/file_storage.php')) {
            require_once __DIR__ . '/includes/file_storage.php';
            $fileStorage = new FileStorage();
        } else {
            throw new Exception('FileStorage não disponível');
        }
    }
    $chave_pix = $fileStorage->getChavePix();
    $nome_pix = $fileStorage->getNomePix();
    $cidade_pix = $fileStorage->getCidadePix();
} catch (Exception $e) {
    // Se houver erro, define valores vazios
    $chave_pix = '';
    $nome_pix = '';
    $cidade_pix = '';
    error_log("Erro ao carregar configuração PIX: " . $e->getMessage());
}

// Usa apenas a chave PIX simples (sem QR Code, sem código EMV)

// Verifica métodos de pagamento configurados
$pix_manual_habilitado = false;
$pix_sumup_habilitado = false;
$cartao_sumup_habilitado = false;
$sumup_habilitado = false;
$sumup = null;

try {
    if (file_exists(__DIR__ . '/includes/sumup_api.php')) {
        require_once __DIR__ . '/includes/sumup_api.php';
        if (class_exists('SumUpAPI')) {
            $sumup = new SumUpAPI($pdo);
            $payment_methods = $sumup->getPaymentMethods();
            $sumup_habilitado = $sumup->isConfigured();
            
            $pix_manual_habilitado = isset($payment_methods['pix_manual_enabled']) && $payment_methods['pix_manual_enabled'] && !empty($chave_pix);
            $pix_sumup_habilitado = isset($payment_methods['pix_sumup_enabled']) && $payment_methods['pix_sumup_enabled'] && $sumup_habilitado;
            $cartao_sumup_habilitado = isset($payment_methods['cartao_sumup_enabled']) && $payment_methods['cartao_sumup_enabled'] && $sumup_habilitado;
        }
    }
} catch (Throwable $e) {
    // Captura qualquer erro (Exception ou Error)
    error_log("Erro ao carregar métodos de pagamento: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    $pix_manual_habilitado = !empty($chave_pix); // Se houver chave PIX, assume PIX manual habilitado
    $pix_sumup_habilitado = false;
    $cartao_sumup_habilitado = false;
    $sumup_habilitado = false;
}
?>

<style>
/* Estilo Dark Moderno - Mesmo visual do header */
.checkout-pix-container {
    background: #000000;
    min-height: 100vh;
    padding: 140px 0 80px;
    position: relative;
    overflow: hidden;
}

/* Glows sutis vermelho/laranja nas bordas */
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

.checkout-pix-container > * {
    position: relative;
    z-index: 1;
}

/* Cards modernos */
.pix-card {
    background: rgba(26, 26, 26, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.pix-card:hover {
    border-color: rgba(255, 69, 0, 0.3);
    box-shadow: 0 8px 32px rgba(255, 69, 0, 0.1);
}

.pix-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 69, 0, 0.05), transparent);
    transition: left 0.6s ease;
}

.pix-card:hover::before {
    left: 100%;
}

/* QR Code Container */
.qr-code-container {
    background: #ffffff;
    padding: 1.5rem;
    border-radius: 12px;
    display: inline-block;
    margin: 1.5rem 0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.qr-code-container img {
    display: block;
    border-radius: 8px;
}

/* Botão de Copiar */
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

.copy-button:active {
    transform: translateY(0);
}

/* Código PIX */
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

/* Títulos */
.checkout-pix-container h1,
.checkout-pix-container h2,
.checkout-pix-container h3 {
    color: #ffffff;
    font-weight: 700;
}

/* Texto */
.checkout-pix-container p,
.checkout-pix-container li,
.checkout-pix-container span {
    color: rgba(255, 255, 255, 0.9);
}

/* Preço destacado */
.price-highlight {
    color: #ff4500;
    font-weight: 700;
    text-shadow: 0 0 10px rgba(255, 69, 0, 0.3);
}

/* Container do Total - Sticky apenas no desktop */
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

/* Responsividade */
@media (max-width: 768px) {
    .checkout-pix-container {
        padding: 120px 0 60px;
    }
    
    .pix-card {
        padding: 1.5rem;
    }
    
    .qr-code-container {
        padding: 1rem;
    }
    
    .qr-code-container img {
        width: 200px !important;
        height: 200px !important;
    }
    
    /* Remove sticky no mobile para evitar sobreposição */
    .pix-card.sticky {
        position: relative !important;
        top: auto !important;
        max-height: none !important;
        overflow-y: visible !important;
    }
    
    /* Remove sticky do total no mobile */
    .pix-card .sticky.bottom-0 {
        position: relative !important;
        bottom: auto !important;
    }
    
    /* Garante ordem correta no mobile */
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
                Escolha a forma de pagamento
            </p>
            
            <!-- Seleção de Método de Pagamento -->
            
            <?php 
            // Verifica se há métodos disponíveis
            $metodos_disponiveis = [];
            if ($pix_manual_habilitado) $metodos_disponiveis[] = 'pix_manual';
            if ($pix_sumup_habilitado) $metodos_disponiveis[] = 'pix_sumup';
            if ($cartao_sumup_habilitado) $metodos_disponiveis[] = 'cartao_sumup';
            
            if (empty($metodos_disponiveis)): ?>
                <div class="pix-card text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-4"></i>
                    <h2 class="text-2xl font-bold text-white mb-4">Nenhum método de pagamento configurado</h2>
                    <p class="text-white/70 mb-6">
                        Configure pelo menos um método de pagamento no painel administrativo.
                    </p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="admin/gerenciar_sumup.php" class="copy-button inline-block">
                            <i class="fas fa-cog mr-2"></i>
                            Configurar Métodos de Pagamento
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
            <!-- Resumo do Pedido (sempre visível) -->
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
                                <span class="text-white font-semibold ml-4"><?= formatarPreco($item['preco'] * $item['quantidade']) ?></span>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="flex justify-between items-center pt-4 border-t-2 border-white/20 pb-2 total-container">
                                <span class="text-lg font-bold text-white">Total</span>
                                <span class="text-2xl font-black price-highlight"><?= formatarPreco($total_preco) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Métodos de Pagamento -->
                <div class="lg:col-span-2">
                    <!-- Seleção de Método de Pagamento -->
                    <?php if (count($metodos_disponiveis) > 1): ?>
                    <div class="mb-8 flex flex-col sm:flex-row gap-4 justify-center">
                        <?php if ($pix_manual_habilitado): ?>
                        <button 
                            onclick="selecionarMetodo('pix_manual')" 
                            id="btn-pix-manual"
                            class="metodo-pagamento flex-1 max-w-md bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-4 px-8 rounded-lg transition-all shadow-lg hover:shadow-xl transform hover:scale-105"
                        >
                            <i class="fas fa-qrcode mr-2"></i>
                            PIX Manual
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($pix_sumup_habilitado): ?>
                        <button 
                            onclick="selecionarMetodo('pix_sumup')" 
                            id="btn-pix-sumup"
                            class="metodo-pagamento flex-1 max-w-md bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-8 rounded-lg transition-all shadow-lg hover:shadow-xl transform hover:scale-105"
                        >
                            <i class="fas fa-qrcode mr-2"></i>
                            PIX SumUp
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($cartao_sumup_habilitado): ?>
                        <button 
                            onclick="selecionarMetodo('cartao_sumup')" 
                            id="btn-cartao-sumup"
                            class="metodo-pagamento flex-1 max-w-md bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold py-4 px-8 rounded-lg transition-all shadow-lg hover:shadow-xl transform hover:scale-105"
                        >
                            <i class="fas fa-credit-card mr-2"></i>
                            Cartão SumUp
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Área de Pagamento PIX Manual -->
                    <?php if ($pix_manual_habilitado): ?>
                    <div id="area-pix-manual" class="<?= count($metodos_disponiveis) > 1 ? 'hidden' : '' ?> mb-8">
                        <div class="pix-card text-center">
                            <div class="mb-8">
                                <h2 class="text-2xl font-bold text-white mb-3">
                                    Chave PIX para Pagamento
                                </h2>
                                <p class="text-white/70 text-base">
                                    Copie a chave PIX abaixo e cole no app do seu banco
                                </p>
                            </div>
                            
                            <!-- Chave PIX Copiável -->
                            <div class="mb-8">
                                <label class="block text-sm font-medium text-white/90 mb-3">
                                    Chave PIX:
                                </label>
                                <div class="pix-code text-center mb-4" id="pix-code" style="font-size: 1.1rem; padding: 1.5rem;">
                                    <?= htmlspecialchars($chave_pix) ?>
                                </div>
                                <button onclick="copiarCodigoPix()" class="copy-button">
                                    <i class="fas fa-copy mr-2"></i>
                                    Copiar Chave PIX
                                </button>
                            </div>
                            
                            <!-- Informações do Recebedor -->
                            <div class="mt-8 pt-8 border-t border-white/10">
                                <h3 class="text-lg font-semibold text-white mb-4">Informações do Recebedor</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-white/60 mb-1">Nome</p>
                                        <p class="text-white font-medium"><?= htmlspecialchars($nome_pix) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-white/60 mb-1">Chave PIX</p>
                                        <p class="text-white font-mono text-xs break-all"><?= htmlspecialchars($chave_pix) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-white/60 mb-1">Cidade</p>
                                        <p class="text-white font-medium"><?= htmlspecialchars($cidade_pix) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Instruções -->
                            <div class="mt-8 pt-8 border-t border-white/10">
                                <h3 class="text-lg font-semibold text-white mb-4">
                                    Como Pagar
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white font-bold flex-shrink-0">1</div>
                                        <div>
                                            <p class="text-white font-medium mb-1">Abra o app do banco</p>
                                            <p class="text-white/60 text-sm">Acesse a opção PIX no seu aplicativo</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white font-bold flex-shrink-0">2</div>
                                        <div>
                                            <p class="text-white font-medium mb-1">Cole a chave PIX</p>
                                            <p class="text-white/60 text-sm">Cole a chave PIX copiada no app do banco</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white font-bold flex-shrink-0">3</div>
                                        <div>
                                            <p class="text-white font-medium mb-1">Confirme o pagamento</p>
                                            <p class="text-white/60 text-sm">Verifique os dados e confirme</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white font-bold flex-shrink-0">4</div>
                                        <div>
                                            <p class="text-white font-medium mb-1">Aguarde confirmação</p>
                                            <p class="text-white/60 text-sm">O pagamento é confirmado instantaneamente</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
            
            <!-- Área de Pagamento PIX via SumUp -->
            <?php if ($pix_sumup_habilitado): ?>
            <div id="area-pix-sumup" class="<?= count($metodos_disponiveis) > 1 ? 'hidden' : '' ?> mb-8">
                <div class="pix-card text-center">
                    <h2 class="text-2xl font-bold text-white mb-4">
                        <i class="fas fa-qrcode mr-2"></i>
                        Pagamento via PIX SumUp
                    </h2>
                    <p class="text-white/70 mb-6">
                        Clique no botão abaixo para gerar o código PIX automaticamente
                    </p>
                    <button 
                        onclick="processarPixSumUp()" 
                        id="btn-processar-pix-sumup"
                        class="copy-button bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800"
                    >
                        <i class="fas fa-qrcode mr-2"></i>
                        Gerar PIX SumUp
                    </button>
                    <div id="pix-sumup-loading" class="hidden mt-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
                        <p class="text-white/70 mt-2">Gerando código PIX...</p>
                    </div>
                    <div id="pix-sumup-result" class="hidden mt-6"></div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Área de Pagamento Cartão via SumUp -->
            <?php if ($cartao_sumup_habilitado): ?>
            <div id="area-cartao-sumup" class="<?= count($metodos_disponiveis) > 1 ? 'hidden' : '' ?> mb-8">
                <div class="pix-card text-center">
                    <h2 class="text-2xl font-bold text-white mb-4">
                        <i class="fas fa-credit-card mr-2"></i>
                        Pagamento via Cartão SumUp
                    </h2>
                    <p class="text-white/70 mb-6">
                        Clique no botão abaixo para ser redirecionado ao checkout seguro da SumUp
                    </p>
                    <button 
                        onclick="processarCartaoSumUp()" 
                        id="btn-processar-cartao-sumup"
                        class="copy-button bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800"
                    >
                        <i class="fas fa-lock mr-2"></i>
                        Finalizar Pagamento com Cartão
                    </button>
                    <div id="cartao-sumup-loading" class="hidden mt-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
                        <p class="text-white/70 mt-2">Processando...</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (empty($metodos_disponiveis)): ?>
            <div class="pix-card text-center">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-4"></i>
                <h2 class="text-2xl font-bold text-white mb-4">Nenhum método de pagamento configurado</h2>
                <p class="text-white/70 mb-6">
                    Configure pelo menos um método de pagamento no painel administrativo.
                </p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="admin/gerenciar_sumup.php" class="copy-button inline-block">
                        <i class="fas fa-cog mr-2"></i>
                        Configurar Métodos de Pagamento
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function copiarCodigoPix() {
    const pixCodeElement = document.getElementById('pix-code');
    const codigo = pixCodeElement.textContent.trim();
    
    // Tenta usar a API moderna do clipboard primeiro
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(codigo).then(function() {
            mostrarFeedbackSucesso();
        }).catch(function(err) {
            // Se falhar, tenta método alternativo
            copiarTextoFallback(codigo);
        });
    } else {
        // Fallback para navegadores antigos
        copiarTextoFallback(codigo);
    }
}

function copiarTextoFallback(texto) {
    // Cria um elemento temporário
    const textarea = document.createElement('textarea');
    textarea.value = texto;
    textarea.style.position = 'fixed';
    textarea.style.left = '-999999px';
    textarea.style.top = '-999999px';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            mostrarFeedbackSucesso();
        } else {
            alert('Não foi possível copiar automaticamente. Selecione o texto e pressione Ctrl+C');
        }
    } catch (err) {
        alert('Erro ao copiar. Selecione o texto manualmente e pressione Ctrl+C');
    } finally {
        document.body.removeChild(textarea);
    }
}

function mostrarFeedbackSucesso() {
    const button = document.querySelector('.copy-button');
    if (button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-2"></i>Copiado!';
        button.style.background = 'linear-gradient(45deg, #10B981, #059669)';
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.style.background = '';
        }, 2000);
    }
}

// Seleção de método de pagamento
function selecionarMetodo(metodo) {
    // Oculta todas as áreas
    const areas = ['area-pix-manual', 'area-pix-sumup', 'area-cartao-sumup'];
    areas.forEach(area => {
        const el = document.getElementById(area);
        if (el) el.classList.add('hidden');
    });
    
    // Remove destaque dos botões
    document.querySelectorAll('.metodo-pagamento').forEach(btn => {
        btn.classList.remove('ring-4', 'ring-blue-400', 'ring-green-400', 'ring-purple-400');
    });
    
    // Mostra área selecionada e destaca botão
    if (metodo === 'pix_manual') {
        const el = document.getElementById('area-pix-manual');
        if (el) el.classList.remove('hidden');
        const btn = document.getElementById('btn-pix-manual');
        if (btn) btn.classList.add('ring-4', 'ring-green-400');
    } else if (metodo === 'pix_sumup') {
        const el = document.getElementById('area-pix-sumup');
        if (el) el.classList.remove('hidden');
        const btn = document.getElementById('btn-pix-sumup');
        if (btn) btn.classList.add('ring-4', 'ring-blue-400');
    } else if (metodo === 'cartao_sumup') {
        const el = document.getElementById('area-cartao-sumup');
        if (el) el.classList.remove('hidden');
        const btn = document.getElementById('btn-cartao-sumup');
        if (btn) btn.classList.add('ring-4', 'ring-purple-400');
    }
}

// Processar PIX via SumUp
async function processarPixSumUp() {
    const btn = document.getElementById('btn-processar-pix-sumup');
    const loading = document.getElementById('pix-sumup-loading');
    const result = document.getElementById('pix-sumup-result');
    
    btn.disabled = true;
    loading.classList.remove('hidden');
    result.classList.add('hidden');
    
    try {
        const response = await fetch('sumup_processar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                payment_type: 'pix'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loading.classList.add('hidden');
            result.classList.remove('hidden');
            
            // Mostra código PIX e QR Code se disponível
            let html = '<div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg">';
            html += '<h3 class="text-white font-bold mb-3">Código PIX Gerado</h3>';
            
            if (data.pix_code) {
                html += '<div class="mb-4">';
                html += '<label class="block text-sm text-white/70 mb-2">Código PIX:</label>';
                html += '<div class="p-3 bg-black/50 rounded border border-white/10 font-mono text-sm text-white break-all" id="pix-code-sumup">' + data.pix_code + '</div>';
                html += '<button onclick="copiarCodigoPixSumUp()" class="mt-2 copy-button">';
                html += '<i class="fas fa-copy mr-2"></i>Copiar Código PIX';
                html += '</button>';
                html += '</div>';
            }
            
            if (data.pix_qr_code) {
                html += '<div class="mb-4">';
                html += '<label class="block text-sm text-white/70 mb-2">QR Code PIX:</label>';
                html += '<img src="' + data.pix_qr_code + '" alt="QR Code PIX" class="mx-auto max-w-xs">';
                html += '</div>';
            }
            
            if (data.redirect_url) {
                html += '<a href="' + data.redirect_url + '" target="_blank" class="copy-button inline-block">';
                html += '<i class="fas fa-external-link-alt mr-2"></i>Abrir Checkout SumUp';
                html += '</a>';
            }
            
            html += '</div>';
            result.innerHTML = html;
        } else {
            alert('Erro: ' + data.message);
            btn.disabled = false;
            loading.classList.add('hidden');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao processar PIX. Tente novamente.');
        btn.disabled = false;
        loading.classList.add('hidden');
    }
}

// Processar pagamento Cartão via SumUp
async function processarCartaoSumUp() {
    const btn = document.getElementById('btn-processar-cartao-sumup');
    const loading = document.getElementById('cartao-sumup-loading');
    
    btn.disabled = true;
    loading.classList.remove('hidden');
    
    try {
        const response = await fetch('sumup_processar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                payment_type: 'card'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.redirect_url) {
                // Redireciona para o checkout da SumUp
                window.location.href = data.redirect_url;
            } else {
                alert('Checkout criado! ID: ' + data.checkout_id);
            }
        } else {
            alert('Erro: ' + data.message);
            btn.disabled = false;
            loading.classList.add('hidden');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao processar pagamento. Tente novamente.');
        btn.disabled = false;
        loading.classList.add('hidden');
    }
}

// Copiar código PIX SumUp
function copiarCodigoPixSumUp() {
    const pixCodeElement = document.getElementById('pix-code-sumup');
    if (!pixCodeElement) return;
    
    const codigo = pixCodeElement.textContent.trim();
    
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

// Se houver apenas um método, mostra automaticamente
document.addEventListener('DOMContentLoaded', function() {
    const metodos = <?= json_encode($metodos_disponiveis) ?>;
    if (metodos.length === 1) {
        selecionarMetodo(metodos[0]);
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
