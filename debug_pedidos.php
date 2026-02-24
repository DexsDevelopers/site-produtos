<?php
require_once "config.php";
$stmt = $pdo->query("DESCRIBE pedidos");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Colunas da tabela pedidos:\n";
foreach ($columns as $col) {
    echo "- " . $col["Field"] . " (" . $col["Type"] . ")\n";
}
?>
