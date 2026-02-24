<?php
require_once "config.php";
try {
    $pdo->exec("ALTER TABLE produtos ADD COLUMN frete_gratis TINYINT(1) DEFAULT 0");
    echo "Coluna 'frete_gratis' adicionada com sucesso ou já existente.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "A coluna 'frete_gratis' já existe.";
    } else {
        echo "Erro: " . $e->getMessage();
    }
}
?>
