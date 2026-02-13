<?php
// admin/db_update_force.php
require_once 'secure.php';
require_once 'templates/header_admin.php';

echo '<div class="container mx-auto p-4 text-white">';
echo '<h1 class="text-2xl font-bold mb-4">Verificação de Banco de Dados</h1>';

try {
    // 1. Verificar Coluna parent_id
    echo "<p>Verificando coluna 'parent_id' na tabela 'categorias'...</p>";
    $stmt = $pdo->query("SHOW COLUMNS FROM categorias LIKE 'parent_id'");
    $exists = $stmt->fetch();

    if (!$exists) {
        echo "<p class='text-yellow-400'>Coluna não encontrada. Adicionando...</p>";
        $pdo->exec("ALTER TABLE categorias ADD COLUMN parent_id INT DEFAULT NULL");
        echo "<p class='text-green-400'>Coluna adicionada com sucesso!</p>";

        // Adiciona FK
        echo "<p>Adicionando Foreign Key...</p>";
        try {
            $pdo->exec("ALTER TABLE categorias ADD CONSTRAINT fk_categoria_pai FOREIGN KEY (parent_id) REFERENCES categorias(id) ON DELETE SET NULL");
            echo "<p class='text-green-400'>Foreign Key adicionada!</p>";
        }
        catch (Exception $e) {
            echo "<p class='text-red-400'>Erro ao adicionar FK (pode já existir ou dados inconsistentes): " . $e->getMessage() . "</p>";
        }
    }
    else {
        echo "<p class='text-green-400'>Coluna já existe.</p>";
    }

    // 2. Mostrar estrutura atual
    echo "<h2 class='text-xl font-bold mt-8 mb-2'>Estrutura Atual:</h2>";
    $stmt = $pdo->query("DESCRIBE categorias");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre class='bg-gray-800 p-4 rounded'>";
    print_r($cols);
    echo "</pre>";

}
catch (Exception $e) {
    echo "<div class='bg-red-500/20 text-red-500 p-4 rounded mt-4'>Erro Crítico: " . $e->getMessage() . "</div>";
}

echo '<a href="gerenciar_categorias.php" class="btn btn-primary mt-8 inline-block">Voltar para Categorias</a>';
echo '</div>';
require_once 'templates/footer_admin.php';
?>