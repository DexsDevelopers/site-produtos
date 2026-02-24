<?php
// admin/index_simples.php - Dashboard Simplificado para Debug
$page_title = 'Dashboard';
require_once 'secure.php';
require_once 'templates/header_admin.php';

// Tratamento de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<div class='admin-card rounded-xl p-6'>";
echo "<h1 class='text-3xl font-bold text-white mb-4'>Dashboard Simplificado</h1>";

try {
    // Teste básico de conexão
    echo "<p class='text-green-400 mb-4'>âœ… Conexão com banco estabelecida</p>";
    
    // Busca estatísticas básicas
    $total_produtos = $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
    $total_usuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    
    echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6 mb-6'>";
    
    // Card de Produtos
    echo "<div class='bg-blue-600 p-6 rounded-lg'>";
    echo "<h3 class='text-white text-lg font-semibold mb-2'>Total de Produtos</h3>";
    echo "<p class='text-white text-3xl font-bold'>$total_produtos</p>";
    echo "</div>";
    
    // Card de Usuários
    echo "<div class='bg-green-600 p-6 rounded-lg'>";
    echo "<h3 class='text-white text-lg font-semibold mb-2'>Total de Usuários</h3>";
    echo "<p class='text-white text-3xl font-bold'>$total_usuarios</p>";
    echo "</div>";
    
    echo "</div>";
    
    // Lista de produtos recentes
    echo "<h3 class='text-white text-xl font-semibold mb-4'>Produtos Recentes</h3>";
    $produtos = $pdo->query('SELECT * FROM produtos ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($produtos)) {
        echo "<div class='space-y-3'>";
        foreach ($produtos as $produto) {
            echo "<div class='bg-gray-700 p-4 rounded-lg'>";
            echo "<h4 class='text-white font-semibold'>" . htmlspecialchars($produto['nome']) . "</h4>";
            echo "<p class='text-gray-300'>Preço: R$ " . number_format($produto['preco'], 2, ',', '.') . "</p>";
            echo "<p class='text-gray-400 text-sm'>ID: " . $produto['id'] . "</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p class='text-gray-400'>Nenhum produto encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<div class='bg-red-600 p-4 rounded-lg'>";
    echo "<h3 class='text-white font-semibold mb-2'>Erro no Dashboard</h3>";
    echo "<p class='text-white'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='text-white text-sm mt-2'>Arquivo: " . $e->getFile() . "</p>";
    echo "<p class='text-white text-sm'>Linha: " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "</div>";

require_once 'templates/footer_admin.php';
?>
