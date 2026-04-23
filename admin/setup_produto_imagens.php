<?php
// admin/setup_produto_imagens.php — Cria a tabela de galeria de produtos
require_once 'secure.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS produto_imagens (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            produto_id  INT NOT NULL,
            imagem      VARCHAR(500) NOT NULL,
            ordem       INT DEFAULT 0,
            criado_em   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "<p style='font-family:monospace;color:lime'>Tabela <b>produto_imagens</b> criada (ou já existia).</p>";
} catch (PDOException $e) {
    echo "<p style='font-family:monospace;color:red'>Erro: " . $e->getMessage() . "</p>";
}
