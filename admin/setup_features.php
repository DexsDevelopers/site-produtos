<?php
// admin/setup_features.php
require_once __DIR__ . '/../config.php';

echo "<pre>";

// 1. Tabela de Cupons
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS cupons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo TEXT NOT NULL UNIQUE,
        tipo TEXT NOT NULL DEFAULT 'porcentagem', -- 'porcentagem' ou 'fixo'
        valor REAL NOT NULL,
        validade DATETIME,
        ativo INTEGER DEFAULT 1,
        usos_max INTEGER DEFAULT 0, -- 0 = ilimitado
        usos_atuais INTEGER DEFAULT 0
    )");
    echo "Tabela 'cupons' verificada/criada.\n";
}
catch (Exception $e) {
    echo "Erro tabela cupons: " . $e->getMessage() . "\n";
}

// 2. Tabela de Afiliados (separado de usuários para simplificar)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS afiliados (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL UNIQUE,
        codigo TEXT NOT NULL UNIQUE,
        saldo REAL DEFAULT 0.00,
        chave_pix TEXT,
        data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
    )");
    echo "Tabela 'afiliados' verificada/criada.\n";
}
catch (Exception $e) {
    echo "Erro tabela afiliados: " . $e->getMessage() . "\n";
}

// 3. Atualizar Pedidos para Rastreio
// SQLite não suporta IF NOT EXISTS em ADD COLUMN, então precisamos verificar antes
try {
    $cols = $pdo->query("PRAGMA table_info(pedidos)")->fetchAll(PDO::FETCH_ASSOC);
    $hasRastreio = false;
    foreach ($cols as $col) {
        if ($col['name'] == 'codigo_rastreio')
            $hasRastreio = true;
    }

    if (!$hasRastreio) {
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN codigo_rastreio TEXT");
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN transportadora TEXT");
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN url_rastreio TEXT");
        echo "Colunas de rastreio adicionadas em 'pedidos'.\n";
    }
    else {
        echo "Colunas de rastreio já existem em 'pedidos'.\n";
    }
}
catch (Exception $e) {
    echo "Erro update pedidos: " . $e->getMessage() . "\n";
}

// 4. Inserir Cupons de Exemplo
try {
    $count = $pdo->query("SELECT COUNT(*) FROM cupons")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO cupons (codigo, tipo, valor, ativo) VALUES ('BEMVINDO10', 'porcentagem', 10, 1)");
        $pdo->exec("INSERT INTO cupons (codigo, tipo, valor, ativo) VALUES ('FRETEGRATIS', 'fixo', 15, 1)");
        echo "Cupons de exemplo inseridos.\n";
    }
}
catch (Exception $e) {
    echo "Erro inserir cupons: " . $e->getMessage() . "\n";
}

echo "Setup concluído.</pre>";