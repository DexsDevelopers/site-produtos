<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT id, nome, parent_id FROM categorias");
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h1>Categorias no Banco:</h1>";
    echo "<pre>";
    print_r($cats);
    echo "</pre>";

    $stmt2 = $pdo->query("DESCRIBE categorias");
    $cols = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "<h1>Estrutura da Tabela:</h1>";
    echo "<pre>";
    print_r($cols);
    echo "</pre>";
}
catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}