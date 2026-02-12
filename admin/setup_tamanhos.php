<?php
// admin/setup_tamanhos.php — Migração para sistema de tamanhos
require_once 'secure.php';

$messages = [];

try {
    // 1. Tabela de grupos de tamanho (ex: "Tênis", "Roupas", "Bermudas")
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS grupos_tamanho (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            descricao VARCHAR(255) DEFAULT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $messages[] = "✅ Tabela 'grupos_tamanho' criada/verificada.";

    // 2. Tabela de tamanhos individuais dentro de cada grupo
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tamanhos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            grupo_id INT NOT NULL,
            valor VARCHAR(20) NOT NULL,
            ordem INT DEFAULT 0,
            FOREIGN KEY (grupo_id) REFERENCES grupos_tamanho(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $messages[] = "✅ Tabela 'tamanhos' criada/verificada.";

    // 3. Tabela de tamanhos disponíveis por produto (estoque por tamanho)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS produto_tamanhos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            produto_id INT NOT NULL,
            tamanho_id INT NOT NULL,
            estoque INT DEFAULT 0,
            FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
            FOREIGN KEY (tamanho_id) REFERENCES tamanhos(id) ON DELETE CASCADE,
            UNIQUE KEY unique_produto_tamanho (produto_id, tamanho_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $messages[] = "✅ Tabela 'produto_tamanhos' criada/verificada.";

    // 4. Adicionar campo 'tipo' na tabela produtos (fisico ou digital)
    try {
        $pdo->exec("ALTER TABLE produtos ADD COLUMN tipo ENUM('digital','fisico') DEFAULT 'digital' AFTER categoria_id");
        $messages[] = "✅ Coluna 'tipo' adicionada à tabela 'produtos'.";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            $messages[] = "ℹ️ Coluna 'tipo' já existe.";
        }
        else {
            throw $e;
        }
    }

    // 5. Adicionar campo 'grupo_tamanho_id' na tabela produtos
    try {
        $pdo->exec("ALTER TABLE produtos ADD COLUMN grupo_tamanho_id INT DEFAULT NULL AFTER tipo");
        $messages[] = "✅ Coluna 'grupo_tamanho_id' adicionada à tabela 'produtos'.";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            $messages[] = "ℹ️ Coluna 'grupo_tamanho_id' já existe.";
        }
        else {
            throw $e;
        }
    }

    // 6. Adicionar campos de tamanho em pedido_itens
    try {
        $pdo->exec("ALTER TABLE pedido_itens ADD COLUMN tamanho_id INT DEFAULT NULL AFTER produto_id");
        $pdo->exec("ALTER TABLE pedido_itens ADD COLUMN valor_tamanho VARCHAR(20) DEFAULT NULL AFTER tamanho_id");
        $messages[] = "✅ Colunas de tamanho adicionadas à tabela 'pedido_itens'.";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            $messages[] = "ℹ️ Colunas de tamanho já existem em 'pedido_itens'.";
        }
        else {
            throw $e;
        }
    }

    // 7. Inserir grupos padrão se não existirem
    $count = $pdo->query("SELECT COUNT(*) FROM grupos_tamanho")->fetchColumn();
    if ($count == 0) {
        // Grupo: Tênis
        $pdo->exec("INSERT INTO grupos_tamanho (nome, descricao) VALUES ('Tênis', 'Numeração para calçados')");
        $tenis_id = $pdo->lastInsertId();
        $tenis_sizes = ['34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];
        foreach ($tenis_sizes as $i => $size) {
            $stmt = $pdo->prepare("INSERT INTO tamanhos (grupo_id, valor, ordem) VALUES (?, ?, ?)");
            $stmt->execute([$tenis_id, $size, $i]);
        }
        $messages[] = "✅ Grupo 'Tênis' criado com " . count($tenis_sizes) . " tamanhos.";

        // Grupo: Roupas
        $pdo->exec("INSERT INTO grupos_tamanho (nome, descricao) VALUES ('Roupas', 'Tamanhos para camisetas, calças, etc')");
        $roupas_id = $pdo->lastInsertId();
        $roupas_sizes = ['PP', 'P', 'M', 'G', 'GG', 'XG', 'XXG'];
        foreach ($roupas_sizes as $i => $size) {
            $stmt = $pdo->prepare("INSERT INTO tamanhos (grupo_id, valor, ordem) VALUES (?, ?, ?)");
            $stmt->execute([$roupas_id, $size, $i]);
        }
        $messages[] = "✅ Grupo 'Roupas' criado com " . count($roupas_sizes) . " tamanhos.";

        // Grupo: Bermudas/Shorts
        $pdo->exec("INSERT INTO grupos_tamanho (nome, descricao) VALUES ('Bermudas / Shorts', 'Numeração para bermudas e shorts')");
        $bermuda_id = $pdo->lastInsertId();
        $bermuda_sizes = ['36', '38', '40', '42', '44', '46', '48'];
        foreach ($bermuda_sizes as $i => $size) {
            $stmt = $pdo->prepare("INSERT INTO tamanhos (grupo_id, valor, ordem) VALUES (?, ?, ?)");
            $stmt->execute([$bermuda_id, $size, $i]);
        }
        $messages[] = "✅ Grupo 'Bermudas / Shorts' criado com " . count($bermuda_sizes) . " tamanhos.";
    }
    else {
        $messages[] = "ℹ️ Grupos de tamanho já existem ($count grupos).";
    }

    $messages[] = "🎉 Migração concluída com sucesso!";
}
catch (PDOException $e) {
    $messages[] = "❌ Erro: " . $e->getMessage();
}

// Redireciona ou mostra resultado
$page_title = 'Setup Tamanhos';
require_once 'templates/header_admin.php';
?>

<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-white mb-8">Setup — Sistema de Tamanhos</h1>
    <div class="admin-card rounded-xl p-6 space-y-3">
        <?php foreach ($messages as $msg): ?>
        <div class="p-3 rounded-lg bg-white/5 text-sm text-white">
            <?= $msg?>
        </div>
        <?php
endforeach; ?>
    </div>
    <a href="gerenciar_tamanhos.php"
        class="inline-block mt-6 bg-white text-black px-6 py-3 rounded-full font-bold text-sm uppercase tracking-wider hover:opacity-90 transition-opacity">
        Ir para Gerenciar Tamanhos →
    </a>
</div>

<?php require_once 'templates/footer_admin.php'; ?>