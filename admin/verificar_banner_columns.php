<?php
// admin/verificar_banner_columns.php - Verificar colunas da tabela banners
require_once 'secure.php';

echo "<h2>VerificaÃ§Ã£o das Colunas da Tabela Banners</h2>";

try {
    // Verificar estrutura atual da tabela
    $stmt = $pdo->query("DESCRIBE banners");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Colunas Existentes:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>{$column['Field']}</strong> - {$column['Type']} - {$column['Null']} - {$column['Default']}</li>";
    }
    echo "</ul>";
    
    // Verificar se as colunas necessÃ¡rias existem
    $required_columns = ['subtitulo', 'texto_botao', 'posicao', 'nova_aba', 'data_criacao', 'data_atualizacao'];
    $existing_columns = array_column($columns, 'Field');
    
    echo "<h3>Colunas NecessÃ¡rias:</h3>";
    echo "<ul>";
    foreach ($required_columns as $col) {
        if (in_array($col, $existing_columns)) {
            echo "<li style='color: green;'><strong>{$col}</strong> - âœ… Existe</li>";
        } else {
            echo "<li style='color: red;'><strong>{$col}</strong> - âŒ Faltando</li>";
        }
    }
    echo "</ul>";
    
    // Se faltam colunas, mostrar comando SQL
    $missing_columns = array_diff($required_columns, $existing_columns);
    if (!empty($missing_columns)) {
        echo "<h3>Comando SQL para Adicionar Colunas Faltantes:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo "ALTER TABLE banners ";
        $alter_statements = [];
        
        if (in_array('subtitulo', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN subtitulo VARCHAR(255)";
        }
        if (in_array('texto_botao', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN texto_botao VARCHAR(50) DEFAULT 'Saiba Mais'";
        }
        if (in_array('posicao', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN posicao INT DEFAULT 0";
        }
        if (in_array('nova_aba', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN nova_aba TINYINT(1) DEFAULT 0";
        }
        if (in_array('data_criacao', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        }
        if (in_array('data_atualizacao', $missing_columns)) {
            $alter_statements[] = "ADD COLUMN data_atualizacao TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP";
        }
        
        echo implode(",\n", $alter_statements);
        echo ";";
        echo "</pre>";
    } else {
        echo "<h3 style='color: green;'>âœ… Todas as colunas necessÃ¡rias estÃ£o presentes!</h3>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erro ao verificar tabela: " . $e->getMessage() . "</p>";
}
?>

