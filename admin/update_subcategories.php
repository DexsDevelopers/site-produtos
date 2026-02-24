<?php
require_once __DIR__ . '/../config.php';

try {
    // Adiciona a coluna parent_id se ela nÃ£o existir
    $pdo->exec("ALTER TABLE categorias ADD COLUMN parent_id INT DEFAULT NULL");
    echo "Coluna parent_id adicionada com sucesso ou jÃ¡ existia.<br>";

    // Adiciona a constraint de chave estrangeira (opcional, mas recomendado se o banco suportar)
    // Nota: SQLite nÃ£o suporta ALTER TABLE para adicionar FKs facilmente, mas MySQL sim.
    // Como config.php usa MySQL, podemos tentar adicionar a FK.
    try {
        $pdo->exec("ALTER TABLE categorias ADD CONSTRAINT fk_parent_category FOREIGN KEY (parent_id) REFERENCES categorias(id) ON DELETE SET NULL");
        echo "Chave estrangeira adicionada com sucesso.<br>";
    }
    catch (Exception $e) {
        echo "Nota: NÃ£o foi possÃ­vel adicionar a constraint de chave estrangeira (pode ser que jÃ¡ exista ou o banco nÃ£o suporte): " . $e->getMessage() . "<br>";
    }
}
catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "A coluna parent_id jÃ¡ existe.<br>";
    }
    else {
        echo "Erro ao atualizar banco: " . $e->getMessage() . "<br>";
    }
}
?>
