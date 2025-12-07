<?php
// teste_conexao.php - Teste de conexão com o banco
require_once 'config.php';

echo "<h1>Teste de Conexão</h1>";

try {
    // Testa a conexão
    $stmt = $pdo->query("SELECT 1 as teste");
    $resultado = $stmt->fetch();
    
    if ($resultado) {
        echo "<p style='color: green;'>✅ Conexão com o banco de dados funcionando!</p>";
        
        // Testa se as tabelas existem
        $tabelas = ['produtos', 'categorias', 'banners', 'usuarios'];
        
        foreach ($tabelas as $tabela) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM $tabela");
                $count = $stmt->fetchColumn();
                echo "<p style='color: blue;'>📊 Tabela '$tabela': $count registros</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Erro na tabela '$tabela': " . $e->getMessage() . "</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ Erro na consulta de teste</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Voltar para a página inicial</a></p>";
?>
