<?php
// checkout_multiplos.php - Checkout com Múltiplos Gateways de Pagamento
session_start();
require_once 'config.php';
require_once 'includes/payment_gateways.php';

// Verificar se há itens no carrinho
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    header('Location: carrinho.php?msg=empty_cart');
    exit();
}

// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout_multiplos.php');
    exit();
}

$paymentGateways = new PaymentGateways($pdo);
$availableGateways = $paymentGateways->getAvailableGateways();

// Calcular totais
$total_itens = 0;
$total_preco = 0;
$carrinho_itens = $_SESSION['carrinho'];

foreach ($carrinho_itens as $item) {
    $total_itens += $item['quantidade'];
    $total_preco += $item['preco'] * $item['quantidade'];
}

$page_title = 'Finalizar Compra';
require_once 'templates/header.php';
?>

<style>
/* Estilos para checkout com múltiplos pagamentos */
.checkout-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.payment-method {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.payment-method:hover {
    border-color: rgba(59, 130, 246, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.payment-method.selected {
    border-color: #3B82F6;
    background: rgba(59, 130, 246, 0.1);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.payment-method.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.payment-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-bottom: 1rem;
}

.payment-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: white;
    margin-bottom: 0.5rem;
}

.payment-description {
    color: #94A3B8;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.payment-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.payment-features li {
    color: #CBD5E1;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.payment-features li::before {
    content: '✓';
    color: #10B981;
    font-weight: bold;
}

.payment-form {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 2rem;
    margin-top: 2rem;
    display: none;
}

.payment-form.active {
    display: block;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    color: white;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.form-input::placeholder {
    color: #94A3B8;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.card-input {
    position: relative;
}

.card-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
}

.pix-qr {
    text-align: center;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 0.75rem;
    margin: 1rem 0;
}

.pix-qr img {
    max-width: 200px;
    margin-bottom: 1rem;
}

.boleto-info {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin: 1rem 0;
}

.boleto-code {
    background: rgba(0, 0, 0, 0.3);
    padding: 1rem;
    border-radius: 0.5rem;
    font-family: monospace;
    font-size: 0.875rem;
    color: white;
    word-break: break-all;
    margin: 1rem 0;
}

.checkout-summary {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 1.5rem;
    position: sticky;
    top: 2rem;
}

.summary-item {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.summary-total {
    font-size: 1.25rem;
    font-weight: bold;
    color: #EF4444;
}

.btn-checkout {
    width: 100%;
    background: linear-gradient(135deg, #3B82F6, #2563EB);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 0.75rem;
    font-size: 1.125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 1rem;
}

.btn-checkout:hover {
    background: linear-gradient(135deg, #2563EB, #1D4ED8);
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(59, 130, 246, 0.4);
}

.btn-checkout:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .checkout-container {
        padding: 1rem;
    }
}
</style>

<div class="checkout-container">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Formulário de Pagamento -->
        <div class="lg:col-span-2">
            <h1 class="text-3xl font-bold text-white mb-8">Escolha a Forma de Pagamento</h1>
            
            <!-- Métodos de Pagamento -->
            <div class="space-y-4">
                <?php foreach ($availableGateways as $gatewayId => $gateway): ?>
                    <div class="payment-method" 
                         data-gateway="<?= $gatewayId ?>" 
                         onclick="selectPaymentMethod('<?= $gatewayId ?>')">
                        <div class="flex items-start gap-4">
                            <div class="payment-icon" style="background-color: <?= $gateway['color'] ?>">
                                <i class="<?= $gateway['icon'] ?>"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="payment-name"><?= $gateway['name'] ?></h3>
                                <p class="payment-description">
                                    <?php
                                    switch ($gatewayId) {
                                        case 'pagbank':
                                            echo 'Cartão de crédito, débito e PIX com segurança total';
                                            break;
                                        case 'pagseguro':
                                            echo 'Pagamento seguro com cartão ou boleto';
                                            break;
                                        case 'mercadopago':
                                            echo 'Cartão, PIX e boleto com Mercado Pago';
                                            break;
                                        case 'pix':
                                            echo 'Pagamento instantâneo via PIX';
                                            break;
                                        case 'boleto':
                                            echo 'Boleto bancário com vencimento em 3 dias';
                                            break;
                                        default:
                                            echo 'Forma de pagamento segura';
                                    }
                                    ?>
                                </p>
                                <ul class="payment-features">
                                    <?php
                                    switch ($gatewayId) {
                                        case 'pagbank':
                                            echo '<li>Parcelamento em até 12x</li><li>Aprovação instantânea</li><li>Segurança garantida</li>';
                                            break;
                                        case 'pagseguro':
                                            echo '<li>Proteção ao comprador</li><li>Diversas formas de pagamento</li><li>Ambiente seguro</li>';
                                            break;
                                        case 'mercadopago':
                                            echo '<li>PIX instantâneo</li><li>Cartão sem anuidade</li><li>Cashback disponível</li>';
                                            break;
                                        case 'pix':
                                            echo '<li>Pagamento instantâneo</li><li>Sem taxas</li><li>Disponível 24h</li>';
                                            break;
                                        case 'boleto':
                                            echo '<li>Sem taxas</li><li>Vencimento em 3 dias</li><li>Pagamento em qualquer banco</li>';
                                            break;
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Formulários de Pagamento -->
            <?php foreach ($availableGateways as $gatewayId => $gateway): ?>
                <div class="payment-form" id="form-<?= $gatewayId ?>">
                    <?php if ($gatewayId === 'pagbank' || $gatewayId === 'pagseguro' || $gatewayId === 'mercadopago'): ?>
                        <!-- Formulário para Cartão -->
                        <h3 class="text-xl font-bold text-white mb-6">Dados do Cartão</h3>
                        <form id="card-form-<?= $gatewayId ?>">
                            <div class="form-group">
                                <label class="form-label">Número do Cartão</label>
                                <div class="card-input">
                                    <input type="text" 
                                           name="card_number" 
                                           class="form-input" 
                                           placeholder="0000 0000 0000 0000"
                                           maxlength="19"
                                           oninput="formatCardNumber(this)">
                                    <i class="fas fa-credit-card card-icon"></i>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Validade</label>
                                    <input type="text" 
                                           name="card_expiry" 
                                           class="form-input" 
                                           placeholder="MM/AA"
                                           maxlength="5"
                                           oninput="formatExpiry(this)">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">CVV</label>
                                    <input type="text" 
                                           name="card_cvv" 
                                           class="form-input" 
                                           placeholder="123"
                                           maxlength="4">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Nome no Cartão</label>
                                <input type="text" 
                                       name="card_holder" 
                                       class="form-input" 
                                       placeholder="Nome como está no cartão">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">CPF do Titular</label>
                                <input type="text" 
                                       name="card_cpf" 
                                       class="form-input" 
                                       placeholder="000.000.000-00"
                                       maxlength="14"
                                       oninput="formatCPF(this)">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Parcelas</label>
                                <select name="installments" class="form-input">
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>">
                                            <?= $i ?>x de <?= formatarPreco($total_preco / $i) ?>
                                            <?= $i > 1 ? 'sem juros' : '' ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </form>
                        
                    <?php elseif ($gatewayId === 'pix'): ?>
                        <!-- Formulário PIX -->
                        <div class="pix-qr">
                            <h3 class="text-xl font-bold text-white mb-4">Pagamento via PIX</h3>
                            <p class="text-gray-400 mb-4">Escaneie o QR Code ou copie o código PIX</p>
                            <div id="pix-qr-code">
                                <!-- QR Code será gerado aqui -->
                                <div class="loading"></div>
                            </div>
                            <div class="mt-4">
                                <button onclick="copyPixCode()" class="btn-checkout">
                                    <i class="fas fa-copy mr-2"></i>
                                    Copiar Código PIX
                                </button>
                            </div>
                        </div>
                        
                    <?php elseif ($gatewayId === 'boleto'): ?>
                        <!-- Formulário Boleto -->
                        <div class="boleto-info">
                            <h3 class="text-xl font-bold text-white mb-4">Boleto Bancário</h3>
                            <p class="text-gray-400 mb-4">O boleto será gerado após a confirmação do pedido</p>
                            <div class="text-center">
                                <i class="fas fa-file-invoice text-6xl text-gray-400 mb-4"></i>
                                <p class="text-gray-400">Boleto será enviado por email</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Resumo do Pedido -->
        <div class="lg:col-span-1">
            <div class="checkout-summary">
                <h3 class="text-xl font-bold text-white mb-6">Resumo do Pedido</h3>
                
                <div class="space-y-4">
                    <?php foreach ($carrinho_itens as $item): ?>
                        <div class="summary-item">
                            <div>
                                <h4 class="font-semibold text-white"><?= htmlspecialchars($item['nome']) ?></h4>
                                <p class="text-sm text-gray-400">Qtd: <?= $item['quantidade'] ?></p>
                            </div>
                            <span class="text-white font-semibold">
                                <?= formatarPreco($item['preco'] * $item['quantidade']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-item">
                        <span class="text-gray-400">Subtotal</span>
                        <span class="text-white"><?= formatarPreco($total_preco) ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="text-gray-400">Frete</span>
                        <span class="text-green-400">Grátis</span>
                    </div>
                    
                    <div class="summary-item summary-total">
                        <span>Total</span>
                        <span><?= formatarPreco($total_preco) ?></span>
                    </div>
                </div>
                
                <button id="btn-finalizar" 
                        class="btn-checkout" 
                        onclick="finalizarCompra()" 
                        disabled>
                    <i class="fas fa-lock mr-2"></i>
                    Finalizar Compra
                </button>
                
                <div class="mt-4 text-center">
                    <div class="flex items-center justify-center gap-2 text-sm text-gray-400">
                        <i class="fas fa-shield-alt"></i>
                        <span>Compra 100% Segura</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedGateway = null;
let pixCode = null;

// Selecionar método de pagamento
function selectPaymentMethod(gatewayId) {
    // Remover seleção anterior
    document.querySelectorAll('.payment-method').forEach(method => {
        method.classList.remove('selected');
    });
    
    // Esconder formulários anteriores
    document.querySelectorAll('.payment-form').forEach(form => {
        form.classList.remove('active');
    });
    
    // Selecionar novo método
    document.querySelector(`[data-gateway="${gatewayId}"]`).classList.add('selected');
    document.getElementById(`form-${gatewayId}`).classList.add('active');
    
    selectedGateway = gatewayId;
    document.getElementById('btn-finalizar').disabled = false;
    
    // Gerar PIX se necessário
    if (gatewayId === 'pix') {
        generatePixPayment();
    }
}

// Gerar pagamento PIX
async function generatePixPayment() {
    try {
        const response = await fetch('processar_pagamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                gateway: 'pix',
                amount: <?= $total_preco ?>,
                description: 'Compra na loja'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            pixCode = data.data.pix_code;
            document.getElementById('pix-qr-code').innerHTML = `
                <img src="${data.data.qr_code}" alt="QR Code PIX" class="mx-auto">
                <p class="text-sm text-gray-400 mt-2">Escaneie com seu app do banco</p>
            `;
        }
    } catch (error) {
        console.error('Erro ao gerar PIX:', error);
    }
}

// Copiar código PIX
function copyPixCode() {
    if (pixCode) {
        navigator.clipboard.writeText(pixCode).then(() => {
            alert('Código PIX copiado!');
        });
    }
}

// Finalizar compra
async function finalizarCompra() {
    if (!selectedGateway) {
        alert('Selecione uma forma de pagamento');
        return;
    }
    
    const btn = document.getElementById('btn-finalizar');
    btn.disabled = true;
    btn.innerHTML = '<div class="loading"></div> Processando...';
    
    try {
        let formData = {};
        
        // Coletar dados do formulário se for cartão
        if (['pagbank', 'pagseguro', 'mercadopago'].includes(selectedGateway)) {
            const form = document.getElementById(`card-form-${selectedGateway}`);
            formData = new FormData(form);
        }
        
        const response = await fetch('processar_pagamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                gateway: selectedGateway,
                amount: <?= $total_preco ?>,
                description: 'Compra na loja',
                customer: {
                    name: '<?= $_SESSION['user_nome'] ?? '' ?>',
                    email: '<?= $_SESSION['user_email'] ?? '' ?>',
                    tax_id: '<?= $_SESSION['user_cpf'] ?? '' ?>'
                },
                form_data: Object.fromEntries(formData)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Redirecionar para página de sucesso
            window.location.href = 'pagamento_sucesso.php?gateway=' + selectedGateway;
        } else {
            alert('Erro no pagamento: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock mr-2"></i> Finalizar Compra';
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao processar pagamento');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock mr-2"></i> Finalizar Compra';
    }
}

// Formatação de campos
function formatCardNumber(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    input.value = value;
}

function formatExpiry(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    input.value = value;
}

function formatCPF(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    input.value = value;
}

// Auto-selecionar primeiro método
document.addEventListener('DOMContentLoaded', function() {
    const firstMethod = document.querySelector('.payment-method');
    if (firstMethod) {
        firstMethod.click();
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
