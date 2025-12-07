<?php
// teste_afiliacao.php - Página de teste do sistema de afiliação
session_start();
require_once 'config.php';
require_once 'includes/affiliate_system.php';

$affiliateSystem = new AffiliateSystem($pdo);

// Criar um afiliado de teste se não existir
$user_id = 1; // Assumindo que user_id = 1 existe
$result = $affiliateSystem->createAffiliateLink($user_id);

echo "<h1>Teste do Sistema de Afiliação</h1>";

if ($result['success']) {
    echo "<h2>✅ Afiliado criado com sucesso!</h2>";
    echo "<p><strong>Código:</strong> " . $result['affiliate_code'] . "</p>";
    echo "<p><strong>Link:</strong> " . $result['affiliate_link'] . "</p>";
    
    // Testar registro de clique
    echo "<h3>Testando registro de clique...</h3>";
    $click_result = $affiliateSystem->registerClick($result['affiliate_code'], null, '127.0.0.1');
    
    if ($click_result['success']) {
        echo "<p>✅ Clique registrado com sucesso! ID: " . $click_result['click_id'] . "</p>";
    } else {
        echo "<p>❌ Erro ao registrar clique: " . $click_result['message'] . "</p>";
    }
    
    // Verificar estatísticas
    $stats = $affiliateSystem->getAffiliateStats($user_id);
    if ($stats) {
        echo "<h3>Estatísticas do Afiliado:</h3>";
        echo "<ul>";
        echo "<li>Total de cliques: " . $stats['total_cliques'] . "</li>";
        echo "<li>Total de vendas: " . $stats['total_vendas'] . "</li>";
        echo "<li>Total de comissões: R$ " . number_format($stats['total_comissoes'], 2, ',', '.') . "</li>";
        echo "<li>Taxa de conversão: " . $stats['taxa_conversao'] . "%</li>";
        echo "</ul>";
    }
    
} else {
    echo "<h2>❌ Erro ao criar afiliado:</h2>";
    echo "<p>" . $result['message'] . "</p>";
}

// Verificar se as tabelas existem
echo "<h3>Verificação das Tabelas:</h3>";
try {
    $tables = ['afiliados', 'cliques_afiliados', 'vendas_afiliados'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Tabela '$table' existe</p>";
        } else {
            echo "<p>❌ Tabela '$table' NÃO existe</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao verificar tabelas: " . $e->getMessage() . "</p>";
}

// Testar link de afiliação
echo "<h3>Teste de Link de Afiliação:</h3>";
if ($result['success']) {
    $test_link = "index.php?ref=" . $result['affiliate_code'];
    echo "<p><a href='$test_link' target='_blank'>Clique aqui para testar o link de afiliação</a></p>";
}
?>
