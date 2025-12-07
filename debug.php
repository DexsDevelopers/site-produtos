<?php
// debug.php - Arquivo para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug da Loja</h1>";

// Teste 1: Verificar se o config.php carrega
echo "<h2>1. Testando config.php</h2>";
try {
    require_once 'config.php';
    echo "✅ config.php carregado com sucesso<br>";
    echo "✅ PDO conectado: " . (isset($pdo) ? "Sim" : "Não") . "<br>";
} catch (Exception $e) {
    echo "❌ Erro ao carregar config.php: " . $e->getMessage() . "<br>";
    exit;
}

// Teste 2: Verificar se as tabelas existem
echo "<h2>2. Testando tabelas do banco</h2>";
$tabelas = ['produtos', 'categorias', 'banners', 'usuarios'];
foreach ($tabelas as $tabela) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $tabela");
        $count = $stmt->fetchColumn();
        echo "✅ Tabela '$tabela': $count registros<br>";
    } catch (Exception $e) {
        echo "❌ Erro na tabela '$tabela': " . $e->getMessage() . "<br>";
    }
}

// Teste 3: Verificar se o header carrega
echo "<h2>3. Testando header.php</h2>";
try {
    ob_start();
    require_once 'templates/header.php';
    $header_content = ob_get_clean();
    echo "✅ header.php carregado com sucesso<br>";
    echo "Tamanho do conteúdo: " . strlen($header_content) . " bytes<br>";
} catch (Exception $e) {
    echo "❌ Erro ao carregar header.php: " . $e->getMessage() . "<br>";
}

// Teste 4: Verificar se as funções estão disponíveis
echo "<h2>4. Testando funções</h2>";
echo "formatarPreco: " . (function_exists('formatarPreco') ? "✅ Disponível" : "❌ Não disponível") . "<br>";
echo "sanitizarEntrada: " . (function_exists('sanitizarEntrada') ? "✅ Disponível" : "❌ Não disponível") . "<br>";

// Teste 5: Verificar sessão
echo "<h2>5. Testando sessão</h2>";
echo "Sessão iniciada: " . (session_status() === PHP_SESSION_ACTIVE ? "✅ Sim" : "❌ Não") . "<br>";
echo "ID da sessão: " . session_id() . "<br>";

echo "<hr>";
echo "<p><a href='index_simples.php'>Testar página simples</a> | <a href='index.php'>Testar página original</a></p>";
?>
