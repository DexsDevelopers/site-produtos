<?php
// teste_admin.php - Teste de acesso ao admin
session_start();
require_once 'config.php';

echo "<h1>Teste de Acesso ao Admin</h1>";

// Verifica se está logado
if (isset($_SESSION['user_id'])) {
    echo "<p>✅ Usuário logado: " . $_SESSION['user_id'] . "</p>";
    echo "<p>✅ Nome: " . ($_SESSION['user_nome'] ?? 'N/A') . "</p>";
    
    // Verifica se é admin
    try {
        $stmt = $pdo->prepare("SELECT role FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            echo "<p>✅ Role do usuário: " . $usuario['role'] . "</p>";
            
            if ($usuario['role'] === 'admin') {
                echo "<p>✅ Usuário é administrador!</p>";
                echo "<p><a href='admin/index.php'>Acessar Painel Admin</a></p>";
            } else {
                echo "<p>❌ Usuário não é administrador.</p>";
            }
        } else {
            echo "<p>❌ Usuário não encontrado no banco.</p>";
        }
    } catch (PDOException $e) {
        echo "<p>❌ Erro ao verificar usuário: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Usuário não está logado.</p>";
    echo "<p><a href='login.php'>Fazer Login</a></p>";
}

echo "<hr>";
echo "<p><a href='criar_admin.php'>Criar Usuário Admin</a></p>";
echo "<p><a href='index.php'>Voltar à Loja</a></p>";
?>
