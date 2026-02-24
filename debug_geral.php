<?php
// debug_geral.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>🔍 Verificação de Sistema</h1>";

// 1. Teste de Conexão Manual
echo "<h3>1. Testando conexão com banco...</h3>";
$host = "localhost";
$dbname = "u853242961_lojahelmer";
$user = "u853242961_user2";
$password = "Lucastav8012@";

try {
    $pdo_test = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    echo "<p style='color:green'>✅ Conectado ao banco de dados com sucesso!</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ ERRO DE CONEXÃO: " . $e->getMessage() . "</p>";
}

// 2. Verificação de Arquivos Críticos
echo "<h3>2. Verificando arquivos fundamentais...</h3>";
$arquivos = ['config.php', 'admin/secure.php', 'admin/templates/header_admin.php'];
foreach ($arquivos as $arq) {
    if (file_exists($arq)) {
        echo "<p>✅ Arquivo '$arq' encontrado.</p>";
    } else {
        echo "<p style='color:red'>❌ Arquivo '$arq' NÃO ENCONTRADO.</p>";
    }
}

// 3. Teste de Sessão
echo "<h3>3. Testando Sessão...</h3>";
session_start();
$_SESSION['teste_debug'] = "OK";
echo "<p>Sessão iniciada: " . (isset($_SESSION['teste_debug']) ? "SIM" : "NÃO") . "</p>";

echo "<br><hr><p>Se você vê esta página, o PHP está funcionando e mostrando erros.</p>";
?>
