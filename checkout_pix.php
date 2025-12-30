<?php
// checkout_pix.php - Página de Checkout com QR Code PIX
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desabilita display de erros para evitar output antes do header
ini_set('log_errors', 1); // Mantém log de erros

// Inicia buffer de saída para capturar qualquer output inesperado
ob_start();

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

// Limpa qualquer output inesperado antes do header
$output = ob_get_clean();
if (!empty($output)) {
    error_log("Output inesperado antes do header: " . substr($output, 0, 200));
    ob_start(); // Reinicia buffer
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

// Busca configuração PIX (FileStorage já vem do config.php)
try {
    if (isset($fileStorage) && is_object($fileStorage)) {
        $chave_pix = $fileStorage->getChavePix();
        $nome_pix = $fileStorage->getNomePix();
        $cidade_pix = $fileStorage->getCidadePix();
    } else {
        // Fallback se FileStorage não estiver disponível
        $chave_pix = '';
        $nome_pix = '';
        $cidade_pix = '';
    }
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
                    <div id="pix-sumup-result" class="hidden mt-6">
                        <!-- Resultado será inserido aqui via JavaScript -->
                    </div>
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
                    
                    <!-- Container para SumUpCard (formulário de cartão) -->
                    <div id="sumup-card-container" class="hidden mt-6">
                        <div class="p-4 bg-white/5 rounded-lg border border-white/10">
                            <h3 class="text-white font-bold mb-4">Dados do Cartão</h3>
                            <div id="sumup-card"></div>
                            <button id="btn-pagar-cartao" class="hidden mt-4 copy-button w-full">
                                <i class="fas fa-lock mr-2"></i>
                                Finalizar Pagamento
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                <a href="carrinho.php" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-semibold py-3 px-6 rounded-lg transition-all border border-white/20">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao Carrinho
                </a>
                <a href="index.php" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-semibold py-3 px-6 rounded-lg transition-all border border-white/20">
                    <i class="fas fa-home"></i>
                    Continuar Comprando
                </a>
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
        
        // Verifica se a resposta é OK
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Erro HTTP:', response.status, errorText);
            throw new Error('Erro ao comunicar com o servidor: ' + response.status);
        }
        
        // Tenta parsear JSON
        let data;
        try {
            data = await response.json();
        } catch (jsonError) {
            const textResponse = await response.text();
            console.error('Erro ao parsear JSON:', jsonError, 'Resposta:', textResponse);
            throw new Error('Resposta inválida do servidor');
        }
        
        if (data.success) {
            loading.classList.add('hidden');
            
            // Log para debug
            console.log('SumUp Response completa:', data);
            console.log('pix_code:', data.pix_code);
            console.log('pix_qr_code:', data.pix_qr_code);
            console.log('redirect_url:', data.redirect_url);
            console.log('raw_data completo:', JSON.stringify(data.raw_data, null, 2));
            
            // Verifica se há objeto pix no raw_data
            if (data.raw_data && data.raw_data.pix) {
                console.log('PIX encontrado no raw_data:', JSON.stringify(data.raw_data.pix, null, 2));
            } else {
                console.log('PIX NÃO encontrado no raw_data. Chaves disponíveis:', Object.keys(data.raw_data || {}));
            }
            
            // Mostra código PIX e QR Code se disponível
            let html = '<div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg">';
            html += '<h3 class="text-white font-bold mb-3">Código PIX Gerado</h3>';
            
            // Verifica se há código PIX
            if (data.pix_code) {
                html += '<div class="mb-4">';
                html += '<label class="block text-sm text-white/70 mb-2">Código PIX (Copia e Cola):</label>';
                html += '<div class="p-3 bg-black/50 rounded border border-white/10 font-mono text-sm text-white break-all" id="pix-code-sumup">' + data.pix_code + '</div>';
                html += '<button onclick="copiarCodigoPixSumUp()" class="mt-2 copy-button">';
                html += '<i class="fas fa-copy mr-2"></i>Copiar Código PIX';
                html += '</button>';
                html += '</div>';
            }
            
            // Verifica se há QR Code
            if (data.pix_qr_code) {
                html += '<div class="mb-4">';
                html += '<label class="block text-sm text-white/70 mb-2">QR Code PIX:</label>';
                html += '<div class="flex justify-center">';
                html += '<img src="' + data.pix_qr_code + '" alt="QR Code PIX" class="w-64 h-64 border border-white/10 rounded-lg p-2 bg-white">';
                html += '</div>';
                html += '</div>';
            }
            
            // Se houver redirect_url, mostra botão para abrir checkout
            // A SumUp pode não retornar o código PIX via API, mas sim através da página de checkout
            if (data.redirect_url) {
                html += '<div class="mt-4">';
                html += '<p class="text-white/90 text-sm mb-3 font-semibold">✓ Checkout criado com sucesso!</p>';
                html += '<p class="text-white/70 text-sm mb-3">O código PIX será exibido na página do checkout da SumUp. Clique no botão abaixo para acessar:</p>';
                html += '<a href="' + data.redirect_url + '" target="_blank" class="copy-button inline-block bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 w-full text-center">';
                html += '<i class="fas fa-external-link-alt mr-2"></i>Abrir Checkout SumUp para Gerar PIX';
                html += '</a>';
                html += '</div>';
            } else if (data.checkout_id) {
                // Se não houver redirect_url, mostra opção para abrir checkout manualmente
                // E também inicia polling para verificar se o código PIX fica disponível via API
                html += '<div class="mt-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded">';
                html += '<p class="text-white/90 text-sm mb-2 font-semibold">⏳ Aguardando geração do código PIX...</p>';
                html += '<p class="text-white/70 text-xs mb-3">ID do Checkout: ' + data.checkout_id + '</p>';
                html += '<div id="pix-polling-status" class="text-white/70 text-xs mb-3">Verificando código PIX via API...</div>';
                
                // Botão alternativo para abrir checkout na SumUp
                html += '<p class="text-white/70 text-xs mb-2">Ou acesse diretamente o checkout da SumUp:</p>';
                html += '<a href="https://me.sumup.com/checkout/' + data.checkout_id + '" target="_blank" class="copy-button inline-block bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 w-full text-center text-sm py-2">';
                html += '<i class="fas fa-external-link-alt mr-2"></i>Abrir Checkout SumUp';
                html += '</a>';
                html += '</div>';
                
                // Inicia polling para verificar se o código PIX fica disponível
                iniciarPollingPix(data.checkout_id);
            }
            
            // Se não houver código PIX nem QR Code, mas houver checkout_id, informa o usuário
            if (!data.pix_code && !data.pix_qr_code) {
                if (data.redirect_url) {
                    html += '<div class="mb-4 p-3 bg-blue-500/10 border border-blue-500/30 rounded">';
                    html += '<p class="text-white/90 text-sm mb-2">✓ Checkout criado com sucesso!</p>';
                    html += '<p class="text-white/70 text-xs">O código PIX será exibido na página do checkout da SumUp. Clique no botão acima para acessar.</p>';
                    html += '</div>';
                } else {
                    html += '<div class="mb-4 p-3 bg-yellow-500/10 border border-yellow-500/30 rounded">';
                    html += '<p class="text-white/90 text-sm">Checkout criado! ID: ' + (data.checkout_id || 'N/A') + '</p>';
                    html += '<p class="text-white/70 text-xs mt-2">O código PIX pode não estar disponível imediatamente. Verifique o status do checkout.</p>';
                    html += '</div>';
                }
            }
            
            html += '</div>';
            result.innerHTML = html;
            result.classList.remove('hidden'); // Mostra o resultado
        } else {
            console.error('Erro SumUp:', data);
            alert('Erro: ' + (data.message || 'Erro desconhecido ao gerar PIX'));
            btn.disabled = false;
            loading.classList.add('hidden');
        }
    } catch (error) {
        console.error('Erro completo:', error);
        console.error('Stack trace:', error.stack);
        
        // Mensagem de erro mais detalhada
        let errorMessage = 'Erro ao processar PIX.';
        if (error.message) {
            errorMessage += ' ' + error.message;
        }
        
        alert(errorMessage + '\n\nVerifique o console para mais detalhes.');
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
            loading.classList.add('hidden');
            
            if (data.redirect_url) {
                // Se houver redirect_url, redireciona para o checkout da SumUp
                window.location.href = data.redirect_url;
            } else if (data.checkout_id) {
                // Se não houver redirect_url mas houver checkout_id, inicializa SumUpCard
                await inicializarSumUpCard(data.checkout_id);
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

// Iniciar polling para verificar código PIX
function iniciarPollingPix(checkoutId) {
    const statusElement = document.getElementById('pix-polling-status');
    const resultElement = document.getElementById('pix-sumup-result');
    let tentativas = 0;
    const maxTentativas = 10; // 10 tentativas = 30 segundos (3s cada)
    
    const verificarPix = async () => {
        try {
            tentativas++;
            
            if (statusElement) {
                statusElement.textContent = `Verificando código PIX... (${tentativas}/${maxTentativas})`;
            }
            
            const response = await fetch(`sumup_verificar_pix.php?checkout_id=${encodeURIComponent(checkoutId)}`);
            
            if (!response.ok) {
                throw new Error('Erro HTTP: ' + response.status);
            }
            
            const data = await response.json();
            
            // Log para debug
            console.log(`Polling tentativa ${tentativas}:`, data);
            if (data.debug) {
                console.log('Debug info:', data.debug);
            }
            
            if (data.success && data.pix_code) {
                // Código PIX encontrado!
                if (statusElement) {
                    statusElement.textContent = '✓ Código PIX gerado com sucesso!';
                }
                
                // Atualiza a interface com o código PIX
                let html = '<div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg">';
                html += '<h3 class="text-white font-bold mb-3">Código PIX Gerado</h3>';
                
                html += '<div class="mb-4">';
                html += '<label class="block text-sm text-white/70 mb-2">Código PIX (Copia e Cola):</label>';
                html += '<div class="p-3 bg-black/50 rounded border border-white/10 font-mono text-sm text-white break-all" id="pix-code-sumup">' + data.pix_code + '</div>';
                html += '<button onclick="copiarCodigoPixSumUp()" class="mt-2 copy-button">';
                html += '<i class="fas fa-copy mr-2"></i>Copiar Código PIX';
                html += '</button>';
                html += '</div>';
                
                if (data.pix_qr_code) {
                    html += '<div class="mb-4">';
                    html += '<label class="block text-sm text-white/70 mb-2">QR Code PIX:</label>';
                    html += '<div class="flex justify-center">';
                    html += '<img src="' + data.pix_qr_code + '" alt="QR Code PIX" class="w-64 h-64 border border-white/10 rounded-lg p-2 bg-white">';
                    html += '</div>';
                    html += '</div>';
                }
                
                html += '</div>';
                
                if (resultElement) {
                    resultElement.innerHTML = html;
                    resultElement.classList.remove('hidden');
                }
                
                return; // Para o polling
            } else if (data.checkout_status === 'PAID' || data.checkout_status === 'FAILED') {
                // Checkout finalizado (pago ou falhou)
                if (statusElement) {
                    statusElement.textContent = data.checkout_status === 'PAID' 
                        ? '✓ Pagamento confirmado!' 
                        : '✗ Pagamento falhou';
                }
                return; // Para o polling
            } else if (tentativas >= maxTentativas) {
                // Limite de tentativas atingido
                console.log('Limite de tentativas atingido. Última resposta:', data);
                
                if (statusElement) {
                    statusElement.textContent = '⏱ Tempo esgotado. O código PIX pode não estar disponível via API.';
                }
                
                // Mostra mensagem alternativa com informações de debug
                if (resultElement) {
                    let html = '<div class="p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">';
                    html += '<h3 class="text-white font-bold mb-3">Aguardando Código PIX</h3>';
                    html += '<p class="text-white/90 text-sm mb-2">O código PIX pode não estar disponível imediatamente via API.</p>';
                    html += '<p class="text-white/70 text-xs mb-2">ID do Checkout: ' + checkoutId + '</p>';
                    html += '<p class="text-white/70 text-xs mb-2">Status: ' + (data.checkout_status || 'PENDING') + '</p>';
                    
                    // Mostra informações de debug se disponíveis
                    if (data.debug) {
                        html += '<details class="mt-2 text-xs">';
                        html += '<summary class="text-white/70 cursor-pointer">Informações de Debug</summary>';
                        html += '<pre class="mt-2 p-2 bg-black/50 rounded text-white/70 text-xs overflow-auto">';
                        html += JSON.stringify(data.debug, null, 2);
                        html += '</pre>';
                        html += '</details>';
                    }
                    
                    html += '<p class="text-white/70 text-xs mt-4">Verifique o status do pagamento no painel da SumUp ou tente novamente mais tarde.</p>';
                    html += '</div>';
                    resultElement.innerHTML = html;
                    resultElement.classList.remove('hidden');
                }
                return; // Para o polling
            } else {
                // Continua verificando
                setTimeout(verificarPix, 3000); // Verifica novamente em 3 segundos
            }
        } catch (error) {
            console.error('Erro ao verificar PIX:', error);
            
            if (tentativas >= maxTentativas) {
                if (statusElement) {
                    statusElement.textContent = '✗ Erro ao verificar código PIX. Tente novamente.';
                }
                return; // Para o polling
            } else {
                setTimeout(verificarPix, 3000); // Tenta novamente em 3 segundos
            }
        }
    };
    
    // Inicia a primeira verificação após 3 segundos
    setTimeout(verificarPix, 3000);
}

// Inicializa SumUpCard para pagamento com cartão
<?php if ($cartao_sumup_habilitado && $sumup_habilitado): ?>
let sumupCardInstance = null;

async function inicializarSumUpCard(checkoutId) {
    try {
        // Obtém chave pública da SumUp
        const response = await fetch('sumup_get_public_key.php');
        const data = await response.json();
        
        if (data.success && data.public_key) {
            // Carrega o SDK da SumUp se ainda não estiver carregado
            if (typeof SumUpCard === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://gateway.sumup.com/gateway/ecom/card/v2/sumup-card.js';
                script.onload = function() {
                    montarSumUpCard(checkoutId, data.public_key);
                };
                document.head.appendChild(script);
            } else {
                montarSumUpCard(checkoutId, data.public_key);
            }
        } else {
            console.error('Erro ao obter chave pública SumUp:', data.message);
            alert('Erro ao carregar formulário de pagamento. Tente novamente.');
        }
    } catch (error) {
        console.error('Erro ao inicializar SumUpCard:', error);
        alert('Erro ao carregar formulário de pagamento. Tente novamente.');
    }
}

async function processarPagamentoCartao(checkoutId, token) {
    try {
        const response = await fetch('sumup_processar_pagamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                checkout_id: checkoutId,
                token: token
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Redireciona para página de sucesso
            window.location.href = 'pagamento_sucesso.php?checkout_id=' + checkoutId;
        } else {
            alert('Erro ao processar pagamento: ' + data.message);
        }
    } catch (error) {
        console.error('Erro ao processar pagamento:', error);
        alert('Erro ao processar pagamento. Tente novamente.');
    }
}

function montarSumUpCard(checkoutId, publicKey) {
    const container = document.getElementById('sumup-card-container');
    if (!container) return;
    
    container.innerHTML = '<div id="sumup-card"></div>';
    container.classList.remove('hidden');
    
    try {
        sumupCardInstance = SumUpCard.mount({
            checkoutId: checkoutId,
            publicKey: publicKey,
            containerId: 'sumup-card',
            onLoad: function() {
                console.log('SumUpCard carregado com sucesso');
                const btnPagar = document.getElementById('btn-pagar-cartao');
                if (btnPagar) {
                    btnPagar.classList.remove('hidden');
                }
            },
            onError: function(error) {
                console.error('Erro no SumUpCard:', error);
                container.innerHTML = '<p class="text-red-400 text-center">Erro ao carregar formulário de cartão. Tente novamente.</p>';
            },
            onTokenize: function(token) {
                // Token gerado - processa o pagamento
                console.log('Token gerado:', token);
                processarPagamentoCartao(checkoutId, token);
            }
        });
    } catch (error) {
        console.error('Erro ao montar SumUpCard:', error);
    }
}
<?php endif; ?>

// Se houver apenas um método, mostra automaticamente
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($metodos_disponiveis)): ?>
    const metodos = <?= json_encode($metodos_disponiveis, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    if (metodos && metodos.length === 1) {
        selecionarMetodo(metodos[0]);
    }
    <?php endif; ?>
});
</script>

<?php require_once 'templates/footer.php'; ?>
