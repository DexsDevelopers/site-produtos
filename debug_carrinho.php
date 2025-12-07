<?php
// debug_carrinho.php - Debug do Carrinho
session_start();
require_once 'config.php';
require_once 'templates/header.php';

echo "<div class='w-full max-w-7xl mx-auto py-24 px-4'>";
echo "<div class='pt-16'>";
echo "<h1 class='text-3xl font-bold text-white mb-8'>🔍 Debug do Carrinho</h1>";

// Debug da sessão
echo "<div class='bg-gray-800 p-6 rounded-lg mb-6'>";
echo "<h2 class='text-xl font-bold text-white mb-4'>1. Sessão do Carrinho</h2>";
echo "<pre class='text-green-400 text-sm overflow-x-auto'>";
print_r($_SESSION['carrinho'] ?? 'Carrinho vazio');
echo "</pre>";
echo "</div>";

// Debug dos produtos no banco
echo "<div class='bg-gray-800 p-6 rounded-lg mb-6'>";
echo "<h2 class='text-xl font-bold text-white mb-4'>2. Produtos no Banco de Dados</h2>";
try {
    $stmt = $pdo->query("SELECT id, nome, preco, checkout_link FROM produtos LIMIT 5");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre class='text-green-400 text-sm overflow-x-auto'>";
    print_r($produtos);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p class='text-red-400'>Erro: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Debug de um produto específico
if (!empty($_SESSION['carrinho'])) {
    $primeiro_item = reset($_SESSION['carrinho']);
    echo "<div class='bg-gray-800 p-6 rounded-lg mb-6'>";
    echo "<h2 class='text-xl font-bold text-white mb-4'>3. Primeiro Item do Carrinho</h2>";
    echo "<pre class='text-green-400 text-sm overflow-x-auto'>";
    print_r($primeiro_item);
    echo "</pre>";
    
    if (isset($primeiro_item['checkout_link'])) {
        echo "<p class='text-white mt-4'>Link de Checkout: <a href='" . htmlspecialchars($primeiro_item['checkout_link']) . "' target='_blank' class='text-blue-400 hover:underline'>" . htmlspecialchars($primeiro_item['checkout_link']) . "</a></p>";
    } else {
        echo "<p class='text-red-400 mt-4'>❌ Link de checkout não encontrado!</p>";
    }
    echo "</div>";
}

// Teste de adicionar produto
echo "<div class='bg-gray-800 p-6 rounded-lg mb-6'>";
echo "<h2 class='text-xl font-bold text-white mb-4'>4. Teste de Adicionar Produto</h2>";
echo "<form method='POST' action='carrinho.php' class='flex gap-4 items-center'>";
echo "<input type='hidden' name='action' value='add'>";
echo "<input type='number' name='produto_id' placeholder='ID do Produto' class='px-3 py-2 bg-gray-700 text-white rounded' required>";
echo "<button type='submit' class='bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded'>Adicionar ao Carrinho</button>";
echo "</form>";
echo "</div>";

// Links de teste
echo "<div class='bg-gray-800 p-6 rounded-lg mb-6'>";
echo "<h2 class='text-xl font-bold text-white mb-4'>5. Links de Teste</h2>";
echo "<div class='space-y-2'>";
echo "<a href='carrinho.php' class='block text-blue-400 hover:underline'>Ver Carrinho</a>";
echo "<a href='checkout.php' class='block text-blue-400 hover:underline'>Ver Checkout</a>";
echo "<a href='index.php' class='block text-blue-400 hover:underline'>Voltar à Loja</a>";
echo "</div>";
echo "</div>";

echo "</div>";
echo "</div>";

require_once 'templates/footer.php';
?>
