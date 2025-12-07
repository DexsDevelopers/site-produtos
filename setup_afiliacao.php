<?php
// setup_afiliacao.php - Configurar sistema de afiliação
require_once 'config.php';

echo "<h1>Configuração do Sistema de Afiliação</h1>";

try {
    // Ler o arquivo SQL
    $sql = file_get_contents('database/create_affiliate_tables.sql');
    
    if ($sql === false) {
        throw new Exception('Não foi possível ler o arquivo SQL');
    }
    
    // Executar o SQL
    $pdo->exec($sql);
    
    echo "<h2>✅ Tabelas criadas com sucesso!</h2>";
    echo "<p>O sistema de afiliação está pronto para uso.</p>";
    
    // Verificar se as tabelas foram criadas
    $tables = ['afiliados', 'cliques_afiliados', 'vendas_afiliados', 'pagamentos_comissoes', 'config_afiliacao'];
    
    echo "<h3>Verificação das Tabelas:</h3>";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Tabela '$table' criada com sucesso</p>";
        } else {
            echo "<p>❌ Tabela '$table' não foi criada</p>";
        }
    }
    
    echo "<h3>Próximos Passos:</h3>";
    echo "<ol>";
    echo "<li><a href='teste_afiliacao.php'>Testar o sistema de afiliação</a></li>";
    echo "<li><a href='afiliado_dashboard.php'>Acessar dashboard do afiliado</a></li>";
    echo "<li><a href='admin_afiliados.php'>Gerenciar afiliados (admin)</a></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro ao configurar sistema:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Verifique se o arquivo 'database/create_affiliate_tables.sql' existe.</p>";
}
?>
