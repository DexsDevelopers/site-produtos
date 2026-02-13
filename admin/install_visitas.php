<?php
require_once '../config.php';
try {
    $sql = "CREATE TABLE IF NOT EXISTS site_visitas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        data_visita DATE NOT NULL,
        hora_visita TIME NOT NULL,
        pagina_visitada VARCHAR(255) DEFAULT 'home',
        user_agent TEXT,
        INDEX (data_visita),
        INDEX (ip_address)
    )";
    $pdo->exec($sql);
    echo "Tabela 'site_visitas' criada/verificada com sucesso.";
}
catch (PDOException $e) {
    echo "Erro ao criar tabela: " . $e->getMessage();
}
?>