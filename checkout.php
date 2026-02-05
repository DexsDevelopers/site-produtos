<?php
// checkout.php - Página de Checkout com seleção de produtos
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

// Verifica status dos métodos de pagamento
$config = $fileStorage->getConfig();
$infinite_status = $config['infinite_status'] ?? 'off';
$pix_status = $config['pix_status'] ?? 'off';
?>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <h1 class="text-3xl md:text-4xl font-black text-white mb-8">Finalizar Compra</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Lista de Produtos -->
            <div class="space-y-6">
                <h2 class="text-2xl font-bold text-white mb-4">Seus Produtos</h2>
                
                <?php foreach ($carrinho_itens as $item): ?>
                <div class="bg-brand-black border border-brand-gray-light rounded-xl p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <img src="<?= htmlspecialchars($item['imagem']) ?>" 
                             alt="<?= htmlspecialchars($item['nome']) ?>" 
                             class="w-20 h-20 object-cover rounded-lg">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($item['nome']) ?></h3>
                            <p class="text-brand-red text-xl font-bold"><?= formatarPreco($item['preco']) ?></p>
                            <p class="text-brand-gray-text">Quantidade: <?= $item['quantidade'] ?></p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <!-- Botão Comprar - Redireciona para checkout InfinitePay -->
                        <?php if ($infinite_status === 'on'): ?>
                        <a href="checkout_infinitepay.php" 
                           class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                            <i class="fas fa-credit-card"></i>
                            Pagar com Cartão / PIX
                        </a>
                        <?php endif; ?>

                        <!-- Botão PIX Manual -->
                        <?php if ($pix_status === 'on'): ?>
                        <a href="checkout_pix.php" 
                           class="bg-brand-red hover:bg-brand-red-dark text-white font-bold py-3 px-4 rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                            <i class="fas fa-qrcode"></i>
                            PIX Manual
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($infinite_status !== 'on' && $pix_status !== 'on'): ?>
                            <p class="text-yellow-500 text-sm col-span-2 text-center">Nenhum método de pagamento disponível.</p>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2">
                        <a href="produto.php?id=<?= $item['id'] ?>" 
                           class="block w-full bg-brand-gray-light hover:bg-brand-gray text-white font-bold py-3 px-4 rounded-lg transition-colors text-center">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
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
                    
                    <div class="mt-6 space-y-4">
                        <a href="carrinho.php" class="block w-full text-center bg-brand-gray-light hover:bg-brand-gray text-white font-bold py-3 rounded-lg transition-colors">
                            Voltar ao Carrinho
                        </a>
                        
                        <a href="index.php" class="block w-full text-center bg-brand-gray hover:bg-brand-gray-light text-white font-bold py-3 rounded-lg transition-colors">
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
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
