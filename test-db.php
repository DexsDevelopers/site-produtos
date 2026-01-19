<?php
// Teste de conexão com banco de dados
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Teste de conexão com banco de dados<br>";

$host = 'localhost';
$dbname = 'u853242961_lojahelmer';
$user = 'u853242961_user2';
$password = 'Lucastav8012@';

try {
    echo "Tentando conectar...<br>";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    echo "✅ Conexão bem-sucedida!<br>";
    
    // Testa uma query simples
    $stmt = $pdo->query("SELECT 1");
    echo "✅ Query de teste funcionou!<br>";
    
    // Verifica se as tabelas existem
    $tables = ['banners', 'categorias', 'produtos'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
            $result = $stmt->fetch();
            echo "✅ Tabela '$table' existe - Total de registros: {$result['total']}<br>";
        } catch (PDOException $e) {
            echo "❌ Erro ao acessar tabela '$table': " . $e->getMessage() . "<br>";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "<br>";
    echo "Código do erro: " . $e->getCode() . "<br>";
}
?>

