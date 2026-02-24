<?php
// admin/debug_dashboard.php - Debug do Dashboard
$page_title = 'Debug Dashboard';
require_once 'secure.php';
require_once 'templates/header_admin.php';

echo "<div class='admin-card rounded-xl p-6 mb-6'>";
echo "<h2 class='text-2xl font-bold text-white mb-4'>ðŸ” Debug do Dashboard</h2>";

// Teste de conexão
echo "<h3 class='text-lg font-semibold text-white mb-2'>1. Teste de Conexão</h3>";
try {
    $teste = $pdo->query("SELECT 1 as teste");
    $resultado = $teste->fetch();
    echo "<p class='text-green-400'>âœ… Conexão com banco: OK</p>";
} catch (Exception $e) {
    echo "<p class='text-red-400'>âŒ Erro na conexão: " . $e->getMessage() . "</p>";
}

// Teste de tabelas
echo "<h3 class='text-lg font-semibold text-white mb-2 mt-4'>2. Teste de Tabelas</h3>";

$tabelas = ['produtos', 'usuarios', 'categorias', 'banners'];
foreach ($tabelas as $tabela) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $tabela");
        $count = $stmt->fetchColumn();
        echo "<p class='text-green-400'>âœ… Tabela $tabela: $count registros</p>";
    } catch (Exception $e) {
        echo "<p class='text-red-400'>âŒ Erro na tabela $tabela: " . $e->getMessage() . "</p>";
    }
}

// Teste de sessão
echo "<h3 class='text-lg font-semibold text-white mb-2 mt-4'>3. Teste de Sessão</h3>";
echo "<p class='text-blue-400'>User ID: " . ($_SESSION['user_id'] ?? 'Não definido') . "</p>";
echo "<p class='text-blue-400'>User Nome: " . ($_SESSION['user_nome'] ?? 'Não definido') . "</p>";
echo "<p class='text-blue-400'>User Role: " . ($_SESSION['user_role'] ?? 'Não definido') . "</p>";

// Teste de queries específicas
echo "<h3 class='text-lg font-semibold text-white mb-2 mt-4'>4. Teste de Queries</h3>";

try {
    $total_produtos = $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
    echo "<p class='text-green-400'>âœ… Total produtos: $total_produtos</p>";
} catch (Exception $e) {
    echo "<p class='text-red-400'>âŒ Erro produtos: " . $e->getMessage() . "</p>";
}

try {
    $total_usuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    echo "<p class='text-green-400'>âœ… Total usuários: $total_usuarios</p>";
} catch (Exception $e) {
    echo "<p class='text-red-400'>âŒ Erro usuários: " . $e->getMessage() . "</p>";
}

try {
    $produtos_recentes = $pdo->query('SELECT * FROM produtos ORDER BY id DESC LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
    echo "<p class='text-green-400'>âœ… Produtos recentes: " . count($produtos_recentes) . " encontrados</p>";
    if (!empty($produtos_recentes)) {
        echo "<ul class='text-sm text-gray-300 ml-4'>";
        foreach ($produtos_recentes as $produto) {
            echo "<li>- " . htmlspecialchars($produto['nome']) . " (ID: " . $produto['id'] . ")</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p class='text-red-400'>âŒ Erro produtos recentes: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Teste do dashboard real
echo "<div class='admin-card rounded-xl p-6'>";
echo "<h3 class='text-lg font-semibold text-white mb-4'>5. Teste do Dashboard Real</h3>";

try {
    // Busca estatísticas detalhadas
    $total_produtos = $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
    $total_usuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    $total_categorias = $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn();
    $total_banners = $pdo->query('SELECT COUNT(*) FROM banners')->fetchColumn();
    
    echo "<div class='grid grid-cols-2 md:grid-cols-4 gap-4'>";
    echo "<div class='bg-blue-600 p-4 rounded-lg text-center'>";
    echo "<p class='text-white font-bold text-2xl'>$total_produtos</p>";
    echo "<p class='text-blue-100'>Produtos</p>";
    echo "</div>";
    
    echo "<div class='bg-green-600 p-4 rounded-lg text-center'>";
    echo "<p class='text-white font-bold text-2xl'>$total_usuarios</p>";
    echo "<p class='text-green-100'>Usuários</p>";
    echo "</div>";
    
    echo "<div class='bg-yellow-600 p-4 rounded-lg text-center'>";
    echo "<p class='text-white font-bold text-2xl'>$total_categorias</p>";
    echo "<p class='text-yellow-100'>Categorias</p>";
    echo "</div>";
    
    echo "<div class='bg-purple-600 p-4 rounded-lg text-center'>";
    echo "<p class='text-white font-bold text-2xl'>$total_banners</p>";
    echo "<p class='text-purple-100'>Banners</p>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='text-red-400'>âŒ Erro no dashboard: " . $e->getMessage() . "</p>";
}

echo "</div>";

require_once 'templates/footer_admin.php';
?>
