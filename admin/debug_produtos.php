<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug de Sistema</h1>";

echo "<h2>1. Verificando Config.php...</h2>";
if (!file_exists("../config.php")) {
    die("ERRO: config.php não encontrado!");
}
require_once "../config.php";
echo "<p style='color:green'>config.php carregado com sucesso.</p>";

echo "<h2>2. Verificando Conexão PDO...</h2>";
if (!isset($pdo)) {
    die("ERRO: Variável \$pdo não definida após carregar config.php!");
}
echo "<p style='color:green'>Variável \$pdo existe.</p>";

echo "<h2>3. Testando Query Simples...</h2>";
try {
    $res = $pdo->query("SELECT 1");
    if ($res) {
        echo "<p style='color:green'>Query 'SELECT 1' OK.</p>";
    }
    else {
        $err = $pdo->errorInfo();
        echo "<p style='color:red'>Erro na query SELECT 1: " . $err[2] . "</p>";
    }
}
catch (Exception $e) {
    echo "<p style='color:red'>Exceção na query SELECT 1: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Verificando Tabelas Principais...</h2>";
$tabelas = array('produtos', 'categorias', 'usuarios', 'pedidos');
foreach ($tabelas as $t) {
    try {
        $res = $pdo->query("SELECT COUNT(*) FROM $t");
        if ($res) {
            echo "<p style='color:green'>Tabela '$t' OK (Total: " . $res->fetchColumn() . ")</p>";
        }
        else {
            echo "<p style='color:red'>Erro ao acessar tabela '$t'.</p>";
        }
    }
    catch (Exception $e) {
        echo "<p style='color:red'>Exceção na tabela '$t': " . $e->getMessage() . "</p>";
    }
}

echo "<h2>5. Testando Sessão...</h2>";
if (!isset($_SESSION)) {
    echo "<p style='color:orange'>Sessão não iniciada (estranho, config.php deveria iniciar).</p>";
}
else {
    echo "<p style='color:green'>Sessão OK. ID: " . session_id() . "</p>";
    echo "<pre>Sessão Debug: " . print_r($_SESSION, true) . "</pre>";
}

echo "<h2>Final do Debug.</h2>";
?>
