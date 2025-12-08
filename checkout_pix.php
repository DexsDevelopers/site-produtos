<?php
// checkout_pix.php - Página de Checkout com QR Code PIX
session_start();
require_once 'config.php';
require_once 'templates/header.php';

// Verifica se há itens no carrinho
if (empty($_SESSION['carrinho'])) {
    header('Location: carrinho.php');
    exit();
}

$carrinho_itens = $_SESSION['carrinho'];
$total_itens = 0;
$total_preco = 0;

foreach ($carrinho_itens as $item) {
    $total_itens += $item['quantidade'];
    $total_preco += $item['preco'] * $item['quantidade'];
}

// Busca configuração PIX
$chave_pix = $fileStorage->getChavePix();
$nome_pix = $fileStorage->getNomePix();
$cidade_pix = $fileStorage->getCidadePix();

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
.checkout-pix-container {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    min-height: 100vh;
    padding: 120px 0 60px;
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
    background: radial-gradient(circle at 20% 30%, rgba(255, 0, 0, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 0, 0, 0.05) 0%, transparent 50%);
    pointer-events: none;
}

.pix-card {
    background: linear-gradient(145deg, #1a0000, #000000);
    border: 1px solid rgba(255, 0, 0, 0.2);
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1);
    border-radius: 20px;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.pix-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 0, 0, 0.1), transparent);
    transition: left 0.6s;
}

.pix-card:hover::before {
    left: 100%;
}

.qr-code-container {
    background: white;
    padding: 1rem;
    border-radius: 15px;
    display: inline-block;
    margin: 1rem 0;
}

.copy-button {
    background: linear-gradient(45deg, #ff0000, #ff3333);
    color: white;
    padding: 12px 24px;
    border-radius: 25px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.copy-button:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}

.pix-code {
    background: rgba(0, 0, 0, 0.3);
    padding: 1rem;
    border-radius: 10px;
    font-family: monospace;
    word-break: break-all;
    font-size: 0.9rem;
    color: #fff;
    border: 1px solid rgba(255, 0, 0, 0.2);
}
</style>

<div class="checkout-pix-container">
    <div class="w-full max-w-4xl mx-auto px-4">
        <div class="pt-16">
            <h1 class="text-3xl md:text-4xl font-black text-white mb-8 text-center">
                <i class="fas fa-qrcode mr-2 text-brand-red"></i>
                Pagamento via PIX
            </h1>
            
            <?php if (empty($chave_pix)): ?>
                <div class="pix-card text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-4"></i>
                    <h2 class="text-2xl font-bold text-white mb-4">Chave PIX não configurada</h2>
                    <p class="text-brand-gray-text mb-6">
                        A chave PIX ainda não foi configurada no painel administrativo.
                    </p>
                    <a href="admin/gerenciar_pix.php" class="copy-button">
                        <i class="fas fa-cog mr-2"></i>
                        Configurar Chave PIX
                    </a>
                </div>
            <?php else: ?>
                <!-- Resumo do Pedido -->
                <div class="pix-card mb-6">
                    <h2 class="text-2xl font-bold text-white mb-4">
                        <i class="fas fa-shopping-cart mr-2 text-brand-red"></i>
                        Resumo do Pedido
                    </h2>
                    
                    <div class="space-y-3 mb-6">
                        <?php foreach ($carrinho_itens as $item): ?>
                        <div class="flex justify-between items-center py-2 border-b border-brand-gray-light">
                            <span class="text-white"><?= htmlspecialchars($item['nome']) ?> x<?= $item['quantidade'] ?></span>
                            <span class="text-brand-red font-bold"><?= formatarPreco($item['preco'] * $item['quantidade']) ?></span>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="flex justify-between items-center pt-4 border-t-2 border-brand-red">
                            <span class="text-xl font-bold text-white">Total</span>
                            <span class="text-2xl font-black text-brand-red"><?= formatarPreco($total_preco) ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- QR Code PIX -->
                <div class="pix-card text-center">
                    <h2 class="text-2xl font-bold text-white mb-4">
                        <i class="fas fa-qrcode mr-2 text-brand-red"></i>
                        Escaneie o QR Code
                    </h2>
                    
                    <p class="text-brand-gray-text mb-6">
                        Abra o app do seu banco e escaneie o QR Code abaixo para pagar
                    </p>
                    
                    <?php if (!empty($qr_code_url)): ?>
                    <div class="qr-code-container">
                        <img src="<?= htmlspecialchars($qr_code_url) ?>" 
                             alt="QR Code PIX" 
                             class="w-64 h-64 mx-auto">
                    </div>
                    <?php endif; ?>
                    
                    <!-- Código PIX Copiável -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-white mb-2">
                            Ou copie o código PIX:
                        </label>
                        <div class="pix-code" id="pix-code">
                            <?= htmlspecialchars($codigo_pix) ?>
                        </div>
                        <button onclick="copiarCodigoPix()" class="copy-button mt-4">
                            <i class="fas fa-copy mr-2"></i>
                            Copiar Código PIX
                        </button>
                    </div>
                    
                    <!-- Informações do Recebedor -->
                    <div class="mt-6 pt-6 border-t border-brand-gray-light">
                        <h3 class="text-lg font-semibold text-white mb-3">Informações do Recebedor</h3>
                        <div class="space-y-2 text-sm text-brand-gray-text">
                            <p><strong class="text-white">Nome:</strong> <?= htmlspecialchars($nome_pix) ?></p>
                            <p><strong class="text-white">Chave PIX:</strong> <?= htmlspecialchars($chave_pix) ?></p>
                            <p><strong class="text-white">Cidade:</strong> <?= htmlspecialchars($cidade_pix) ?></p>
                        </div>
                    </div>
                    
                    <!-- Instruções -->
                    <div class="mt-6 pt-6 border-t border-brand-gray-light">
                        <h3 class="text-lg font-semibold text-white mb-3">
                            <i class="fas fa-info-circle mr-2 text-brand-red"></i>
                            Instruções
                        </h3>
                        <ol class="text-left text-sm text-brand-gray-text space-y-2 max-w-md mx-auto">
                            <li>1. Abra o app do seu banco</li>
                            <li>2. Escaneie o QR Code ou cole o código PIX</li>
                            <li>3. Confirme o pagamento</li>
                            <li>4. Aguarde a confirmação (geralmente instantânea)</li>
                        </ol>
                    </div>
                </div>
                
                <!-- Botões de Ação -->
                <div class="flex gap-4 justify-center mt-6">
                    <a href="carrinho.php" class="bg-brand-gray-light hover:bg-brand-gray text-white font-bold py-3 px-6 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Voltar ao Carrinho
                    </a>
                    <a href="index.php" class="bg-brand-gray hover:bg-brand-gray-light text-white font-bold py-3 px-6 rounded-lg transition-colors">
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

