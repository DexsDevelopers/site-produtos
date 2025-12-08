<?php
// checkout_pix.php - Página de Checkout com QR Code PIX
// Garantir que não há output antes do header
if (ob_get_level()) ob_clean();

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verifica se há itens no carrinho - mas não redireciona imediatamente para permitir debug
$carrinho_vazio = empty($_SESSION['carrinho']);

try {
    require_once 'config.php';
    require_once 'templates/header.php';
} catch (Exception $e) {
    die("Erro ao carregar arquivos: " . $e->getMessage());
}

$carrinho_itens = $carrinho_vazio ? [] : $_SESSION['carrinho'];
$total_itens = 0;
$total_preco = 0;

if (!$carrinho_vazio) {
    foreach ($carrinho_itens as $item) {
        $total_itens += $item['quantidade'];
        $total_preco += $item['preco'] * $item['quantidade'];
    }
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

// Gera código PIX (formato EMV)
function gerarCodigoPIX($chave, $nome, $cidade, $valor, $descricao = '') {
    // Remove caracteres não numéricos do valor
    $valor_limpo = preg_replace('/[^0-9]/', '', number_format($valor, 2, '.', ''));
    
    // Identificador do payload
    $payload = '00020126';
    
    // Merchant Account Information
    $merchant_account = '0014BR.GOV.BCB.PIX01' . strlen($chave) . $chave;
    $payload .= '26' . str_pad(strlen($merchant_account), 2, '0', STR_PAD_LEFT) . $merchant_account;
    
    // Merchant Category Code
    $payload .= '52040000';
    
    // Transaction Currency (BRL = 986)
    $payload .= '5303986';
    
    // Transaction Amount
    $payload .= '54' . str_pad(strlen($valor_limpo), 2, '0', STR_PAD_LEFT) . $valor_limpo;
    
    // Country Code
    $payload .= '5802BR';
    
    // Merchant Name
    $nome_limpo = substr($nome, 0, 25);
    $payload .= '59' . str_pad(strlen($nome_limpo), 2, '0', STR_PAD_LEFT) . $nome_limpo;
    
    // Merchant City
    $cidade_limpa = substr($cidade, 0, 15);
    $payload .= '60' . str_pad(strlen($cidade_limpa), 2, '0', STR_PAD_LEFT) . $cidade_limpa;
    
    // Additional Data Field Template (descrição opcional)
    if (!empty($descricao)) {
        $descricao_limpa = substr($descricao, 0, 25);
        $additional_data = '05' . str_pad(strlen($descricao_limpa), 2, '0', STR_PAD_LEFT) . $descricao_limpa;
        $payload .= '62' . str_pad(strlen($additional_data), 2, '0', STR_PAD_LEFT) . $additional_data;
    }
    
    // CRC16
    $crc = crc16($payload . '6304');
    $payload .= '6304' . strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    
    return $payload;
}

function crc16($str) {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($str); $i++) {
        $crc ^= ord($str[$i]) << 8;
        for ($j = 0; $j < 8; $j++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc <<= 1;
            }
        }
    }
    return $crc & 0xFFFF;
}

$codigo_pix = '';
if (!empty($chave_pix) && !empty($nome_pix) && !empty($cidade_pix)) {
    $codigo_pix = gerarCodigoPIX($chave_pix, $nome_pix, $cidade_pix, $total_preco, 'Pedido #' . time());
}

// URL para gerar QR Code (usando API pública)
$qr_code_url = '';
if (!empty($codigo_pix)) {
    $qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($codigo_pix);
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
}
</style>

<div class="checkout-pix-container" style="min-height: 100vh; background: #000000 !important; padding: 140px 0 80px !important; position: relative !important; z-index: 1 !important; display: block !important; visibility: visible !important;">
    <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8" style="position: relative; z-index: 2;">
        <div class="pt-8">
            <h1 class="text-4xl md:text-5xl font-black text-white mb-4 text-center" style="color: #ffffff !important; display: block !important; visibility: visible !important; opacity: 1 !important;">
                Pagamento via PIX
            </h1>
            <p class="text-center text-white/70 mb-12 text-lg">
                Escaneie o QR Code ou copie o código para pagar
            </p>
            
            <?php if ($carrinho_vazio): ?>
                <div class="pix-card text-center" style="background: rgba(26, 26, 26, 0.8); padding: 2.5rem; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.1);">
                    <i class="fas fa-shopping-cart text-yellow-400 text-4xl mb-4"></i>
                    <h2 class="text-2xl font-bold text-white mb-4">Carrinho vazio</h2>
                    <p class="text-white/70 mb-6">
                        Adicione produtos ao carrinho antes de finalizar a compra.
                    </p>
                    <a href="carrinho.php" class="copy-button inline-block">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Ir para o Carrinho
                    </a>
                </div>
            <?php elseif (empty($chave_pix) || empty($nome_pix) || empty($cidade_pix)): ?>
                <div class="pix-card text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-4"></i>
                    <h2 class="text-2xl font-bold text-white mb-4">Chave PIX não configurada</h2>
                    <p class="text-white/70 mb-6">
                        A chave PIX ainda não foi configurada no painel administrativo.
                    </p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="admin/gerenciar_pix.php" class="copy-button inline-block">
                            <i class="fas fa-cog mr-2"></i>
                            Configurar Chave PIX
                        </a>
                    <?php else: ?>
                        <p class="text-white/50 text-sm">Entre em contato com o administrador para configurar a chave PIX.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Resumo do Pedido -->
                <div class="lg:col-span-1">
                    <div class="pix-card sticky top-24">
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
                            
                            <div class="flex justify-between items-center pt-4 border-t-2 border-white/20">
                                <span class="text-lg font-bold text-white">Total</span>
                                <span class="text-2xl font-black price-highlight"><?= formatarPreco($total_preco) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- QR Code PIX -->
                <div class="lg:col-span-2">
                    <div class="pix-card text-center">
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-white mb-3">
                                Escaneie o QR Code
                            </h2>
                            <p class="text-white/70 text-base">
                                Abra o app do seu banco e escaneie o QR Code abaixo
                            </p>
                        </div>
                        
                        <?php if (!empty($qr_code_url)): ?>
                        <div class="qr-code-container mb-8">
                            <img src="<?= htmlspecialchars($qr_code_url) ?>" 
                                 alt="QR Code PIX" 
                                 class="w-72 h-72 mx-auto"
                                 style="width: 288px; height: 288px;">
                        </div>
                        <?php endif; ?>
                        
                        <!-- Código PIX Copiável -->
                        <div class="mb-8">
                            <label class="block text-sm font-medium text-white/90 mb-3">
                                Ou copie o código PIX:
                            </label>
                            <div class="pix-code text-left mb-4" id="pix-code">
                                <?= htmlspecialchars($codigo_pix) ?>
                            </div>
                            <button onclick="copiarCodigoPix()" class="copy-button">
                                <i class="fas fa-copy mr-2"></i>
                                Copiar Código PIX
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
                                        <p class="text-white font-medium mb-1">Escaneie ou cole o código</p>
                                        <p class="text-white/60 text-sm">Use a câmera ou cole o código copiado</p>
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
            </div>
                
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
    const codigo = document.getElementById('pix-code').textContent.trim();
    
    navigator.clipboard.writeText(codigo).then(function() {
        // Mostra feedback visual
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-2"></i>Copiado!';
        button.style.background = 'linear-gradient(45deg, #10B981, #059669)';
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.style.background = '';
        }, 2000);
    }).catch(function(err) {
        alert('Erro ao copiar. Selecione o código manualmente e pressione Ctrl+C');
    });
}
</script>

<?php require_once 'templates/footer.php'; ?>

