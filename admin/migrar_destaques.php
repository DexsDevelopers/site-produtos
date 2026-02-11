<?php
// admin/migrar_destaques.php
require_once 'secure.php';

$is_direct_access = (basename($_SERVER['PHP_SELF']) == 'migrar_destaques.php');

if ($is_direct_access)
    echo "<h1>Migrando Banco de Dados...</h1>";

try {
    // 1. Adicionar destaque à tabela produtos
    try {
        $pdo->exec("ALTER TABLE produtos ADD COLUMN destaque TINYINT(1) DEFAULT 0");
        if ($is_direct_access)
            echo "<p>Coluna 'destaque' adicionada à tabela 'produtos'.</p>";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            if ($is_direct_access)
                echo "<p>Coluna 'destaque' já existe na tabela 'produtos'.</p>";
        }
        else {
            if ($is_direct_access)
                echo "<p style='color:red'>Erro ao adicionar 'destaque' em 'produtos': " . $e->getMessage() . "</p>";
        }
    }

    // 2. Adicionar exibir_home à tabela categorias
    try {
        $pdo->exec("ALTER TABLE categorias ADD COLUMN exibir_home TINYINT(1) DEFAULT 1");
        if ($is_direct_access)
            echo "<p>Coluna 'exibir_home' adicionada à tabela 'categorias'.</p>";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            if ($is_direct_access)
                echo "<p>Coluna 'exibir_home' já existe na tabela 'categorias'.</p>";
        }
        else {
            if ($is_direct_access)
                echo "<p style='color:red'>Erro ao adicionar 'exibir_home' em 'categorias': " . $e->getMessage() . "</p>";
        }
    }

    if ($is_direct_access) {
        echo "<h2>Migração finalizada!</h2>";
        echo "<a href='gerenciar_produtos.php'>Voltar para Produtos</a>";
    }

}
catch (Exception $e) {
    echo "<p style='color:red'>Erro crítico: " . $e->getMessage() . "</p>";
}
?>