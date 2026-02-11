<?php
// admin/migrar_destaques.php
require_once 'secure.php';

echo "<h1>Migrando Banco de Dados...</h1>";

try {
    // 1. Adicionar destaque à tabela produtos
    try {
        $pdo->exec("ALTER TABLE produtos ADD COLUMN destaque TINYINT(1) DEFAULT 0");
        echo "<p>Coluna 'destaque' adicionada à tabela 'produtos'.</p>";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "<p>Coluna 'destaque' já existe na tabela 'produtos'.</p>";
        }
        else {
            echo "<p style='color:red'>Erro ao adicionar 'destaque' em 'produtos': " . $e->getMessage() . "</p>";
        }
    }

    // 2. Adicionar exibir_home à tabela categorias
    try {
        $pdo->exec("ALTER TABLE categorias ADD COLUMN exibir_home TINYINT(1) DEFAULT 1");
        echo "<p>Coluna 'exibir_home' adicionada à tabela 'categorias'.</p>";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "<p>Coluna 'exibir_home' já existe na tabela 'categorias'.</p>";
        }
        else {
            echo "<p style='color:red'>Erro ao adicionar 'exibir_home' em 'categorias': " . $e->getMessage() . "</p>";
        }
    }

    echo "<h2>Migração finalizada!</h2>";
    echo "<a href='gerenciar_produtos.php'>Voltar para Produtos</a>";

}
catch (Exception $e) {
    echo "<p style='color:red'>Erro crítico: " . $e->getMessage() . "</p>";
}
?>