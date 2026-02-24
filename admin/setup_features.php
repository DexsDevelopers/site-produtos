<?php
// admin/setup_features.php - VersÃ£o MySQL/MariaDB
require_once __DIR__ . '/../config.php';

echo "<pre>";
echo "Iniciando configuraÃ§Ã£o para MySQL/MariaDB...\n\n";

// 1. Tabela de Cupons
try {
    $sql = "CREATE TABLE IF NOT EXISTS cupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(50) NOT NULL UNIQUE,
        tipo VARCHAR(20) NOT NULL DEFAULT 'porcentagem', -- 'porcentagem' ou 'fixo'
        valor DECIMAL(10,2) NOT NULL,
        validade DATETIME NULL,
        ativo TINYINT(1) DEFAULT 1,
        usos_max INT DEFAULT 0, -- 0 = ilimitado
        usos_atuais INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Tabela 'cupons' verificada/criada.\n";
}
catch (Exception $e) {
    echo "Erro tabela cupons: " . $e->getMessage() . "\n";
}

// 2. Tabela de Afiliados
try {
    $sql = "CREATE TABLE IF NOT EXISTS afiliados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL UNIQUE,
        codigo VARCHAR(50) NOT NULL UNIQUE,
        saldo DECIMAL(10,2) DEFAULT 0.00,
        chave_pix VARCHAR(255) NULL,
        data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Tabela 'afiliados' verificada/criada.\n";
}
catch (Exception $e) {
    echo "Erro tabela afiliados: " . $e->getMessage() . "\n";
}

// 3. Atualizar Pedidos para Rastreio
function columnExists(PDO $pdo, $table, $column)
{
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE ?");
        $stmt->execute([$column]);
        return $stmt->fetch() !== false;
    }
    catch (Exception $e) {
        return false;
    }
}

try {
    // Adiciona colunas se nÃ£o existirem
    if (!columnExists($pdo, 'pedidos', 'codigo_rastreio')) {
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN codigo_rastreio VARCHAR(100) NULL");
        echo "Coluna 'codigo_rastreio' adicionada.\n";
    }

    if (!columnExists($pdo, 'pedidos', 'transportadora')) {
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN transportadora VARCHAR(100) NULL");
        echo "Coluna 'transportadora' adicionada.\n";
    }

    if (!columnExists($pdo, 'pedidos', 'url_rastreio')) {
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN url_rastreio VARCHAR(255) NULL");
        echo "Coluna 'url_rastreio' adicionada.\n";
    }

    echo "VerificaÃ§Ã£o de colunas em 'pedidos' concluÃ­da.\n";

}
catch (Exception $e) {
    echo "Erro update pedidos: " . $e->getMessage() . "\n";
}

// 4. Inserir Cupons de Exemplo
try {
    $count = $pdo->query("SELECT COUNT(*) FROM cupons")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO cupons (codigo, tipo, valor, ativo) VALUES ('BEMVINDO10', 'porcentagem', 10.00, 1)");
        $pdo->exec("INSERT INTO cupons (codigo, tipo, valor, ativo) VALUES ('FRETEGRATIS', 'fixo', 15.00, 1)");
        echo "Cupons de exemplo inseridos.\n";
    }
    else {
        echo "Tabela cupons jÃ¡ contÃ©m dados.\n";
    }
}
catch (Exception $e) {
    echo "Erro inserir cupons: " . $e->getMessage() . "\n";
}

echo "\nSetup concluÃ­do com sucesso.</pre>";