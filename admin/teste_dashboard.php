<?php
// admin/teste_dashboard.php - Teste Completo do Dashboard
session_start();

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Teste Dashboard</title>";
echo "<script src='https://cdn.tailwindcss.com'></script>";
echo "</head>";
echo "<body class='bg-gray-900 text-white p-8'>";

echo "<h1 class='text-3xl font-bold mb-6'>ðŸ” Teste do Dashboard</h1>";

// Teste 1: Sessão
echo "<div class='bg-gray-800 p-4 rounded-lg mb-4'>";
echo "<h2 class='text-xl font-semibold mb-2'>1. Teste de Sessão</h2>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Não definido') . "</p>";
echo "<p>User Nome: " . ($_SESSION['user_nome'] ?? 'Não definido') . "</p>";
echo "<p>User Role: " . ($_SESSION['user_role'] ?? 'Não definido') . "</p>";
echo "</div>";

// Teste 2: Config
echo "<div class='bg-gray-800 p-4 rounded-lg mb-4'>";
echo "<h2 class='text-xl font-semibold mb-2'>2. Teste de Config</h2>";
try {
    require_once '../config.php';
    echo "<p class='text-green-400'>âœ… Config carregado com sucesso</p>";
    echo "<p>PDO disponível: " . (isset($pdo) ? 'Sim' : 'Não') . "</p>";
} catch (Exception $e) {
    echo "<p class='text-red-400'>âŒ Erro no config: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Teste 3: Conexão com banco
echo "<div class='bg-gray-800 p-4 rounded-lg mb-4'>";
echo "<h2 class='text-xl font-semibold mb-2'>3. Teste de Conexão</h2>";
try {
    if (isset($pdo)) {
        $teste = $pdo->query("SELECT 1 as teste");
        $resultado = $teste->fetch();
        echo "<p class='text-green-400'>âœ… Conexão com banco: OK</p>";
    } else {
        echo "<p class='text-red-400'>âŒ PDO não disponível</p>";
    }
} catch (Exception $e) {
    echo "<p class='text-red-400'>âŒ Erro na conexão: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Teste 4: Queries
echo "<div class='bg-gray-800 p-4 rounded-lg mb-4'>";
echo "<h2 class='text-xl font-semibold mb-2'>4. Teste de Queries</h2>";
try {
    if (isset($pdo)) {
        $total_produtos = $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
        echo "<p class='text-green-400'>âœ… Total produtos: $total_produtos</p>";
        
        $total_usuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
        echo "<p class='text-green-400'>âœ… Total usuários: $total_usuarios</p>";
        
        $produtos = $pdo->query('SELECT * FROM produtos ORDER BY id DESC LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
        echo "<p class='text-green-400'>âœ… Produtos recentes: " . count($produtos) . " encontrados</p>";
        
        if (!empty($produtos)) {
            echo "<ul class='text-sm text-gray-300 ml-4'>";
            foreach ($produtos as $produto) {
                echo "<li>- " . htmlspecialchars($produto['nome']) . " (ID: " . $produto['id'] . ")</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p class='text-red-400'>âŒ PDO não disponível para queries</p>";
    }
} catch (Exception $e) {
    echo "<p class='text-red-400'>âŒ Erro nas queries: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Teste 5: Header
echo "<div class='bg-gray-800 p-4 rounded-lg mb-4'>";
echo "<h2 class='text-xl font-semibold mb-2'>5. Teste de Header</h2>";
try {
    echo "<p>Testando include do header...</p>";
    ob_start();
    include 'templates/header_admin.php';
    $header_content = ob_get_clean();
    echo "<p class='text-green-400'>âœ… Header carregado com sucesso</p>";
    echo "<p>Tamanho do header: " . strlen($header_content) . " caracteres</p>";
} catch (Exception $e) {
    echo "<p class='text-red-400'>âŒ Erro no header: " . $e->getMessage() . "</p>";
}
echo "</div>";

echo "</body>";
echo "</html>";
?>
