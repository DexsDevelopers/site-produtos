<?php
// debug_afiliacao.php - Debug do sistema de afiliação
session_start();
require_once 'config.php';
require_once 'includes/affiliate_system.php';

echo "<h1>Debug do Sistema de Afiliação</h1>";

// Verificar se as tabelas existem
echo "<h2>1. Verificação das Tabelas:</h2>";
$tables = ['afiliados', 'cliques_afiliados', 'vendas_afiliados'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Tabela '$table' existe</p>";
        } else {
            echo "<p>❌ Tabela '$table' NÃO existe</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Erro ao verificar tabela '$table': " . $e->getMessage() . "</p>";
    }
}

// Verificar se há usuários
echo "<h2>2. Verificação de Usuários:</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total de usuários: " . $result['total'] . "</p>";
    
    if ($result['total'] > 0) {
        $stmt = $pdo->query("SELECT id, nome FROM usuarios LIMIT 5");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Primeiros usuários:</p><ul>";
        foreach ($usuarios as $user) {
            echo "<li>ID: {$user['id']} - Nome: {$user['nome']}</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao verificar usuários: " . $e->getMessage() . "</p>";
}

// Testar criação de afiliado
echo "<h2>3. Teste de Criação de Afiliado:</h2>";
try {
    $affiliateSystem = new AffiliateSystem($pdo);
    
    // Usar o primeiro usuário disponível
    $stmt = $pdo->query("SELECT id FROM usuarios LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $user_id = $user['id'];
        echo "<p>Testando com usuário ID: $user_id</p>";
        
        $result = $affiliateSystem->createAffiliateLink($user_id);
        
        if ($result['success']) {
            echo "<p>✅ Afiliado criado com sucesso!</p>";
            echo "<p>Código: " . $result['affiliate_code'] . "</p>";
            echo "<p>Link: " . $result['affiliate_link'] . "</p>";
            
            // Testar registro de clique
            echo "<h3>4. Teste de Registro de Clique:</h3>";
            $click_result = $affiliateSystem->registerClick($result['affiliate_code'], null, '127.0.0.1');
            
            if ($click_result['success']) {
                echo "<p>✅ Clique registrado com sucesso! ID: " . $click_result['click_id'] . "</p>";
                
                // Verificar estatísticas
                $stats = $affiliateSystem->getAffiliateStats($user_id);
                if ($stats) {
                    echo "<h3>5. Estatísticas:</h3>";
                    echo "<ul>";
                    echo "<li>Total de cliques: " . $stats['total_cliques'] . "</li>";
                    echo "<li>Total de vendas: " . $stats['total_vendas'] . "</li>";
                    echo "<li>Total de comissões: R$ " . number_format($stats['total_comissoes'], 2, ',', '.') . "</li>";
                    echo "</ul>";
                }
            } else {
                echo "<p>❌ Erro ao registrar clique: " . $click_result['message'] . "</p>";
            }
        } else {
            echo "<p>❌ Erro ao criar afiliado: " . $result['message'] . "</p>";
        }
    } else {
        echo "<p>❌ Nenhum usuário encontrado no banco de dados</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro geral: " . $e->getMessage() . "</p>";
}

// Verificar se há parâmetro ref na URL
echo "<h2>6. Verificação de Parâmetros:</h2>";
if (isset($_GET['ref'])) {
    echo "<p>✅ Parâmetro 'ref' encontrado: " . htmlspecialchars($_GET['ref']) . "</p>";
} else {
    echo "<p>❌ Parâmetro 'ref' não encontrado na URL</p>";
    echo "<p>Para testar, acesse: debug_afiliacao.php?ref=AFF000001</p>";
}

// Verificar sessão
echo "<h2>7. Verificação de Sessão:</h2>";
if (isset($_SESSION['affiliate_tracking'])) {
    echo "<p>✅ Sessão de afiliação ativa:</p>";
    echo "<pre>" . print_r($_SESSION['affiliate_tracking'], true) . "</pre>";
} else {
    echo "<p>❌ Nenhuma sessão de afiliação ativa</p>";
}
?>
