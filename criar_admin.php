<?php
// criar_admin.php - Script para criar usuário administrador
require_once 'config.php';

echo "<h1>Criando Usuário Administrador</h1>";

try {
    // Verifica se a tabela usuarios existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    $tabela_existe = $stmt->fetch();
    
    if (!$tabela_existe) {
        echo "<p>❌ Tabela 'usuarios' não existe. Criando...</p>";
        
        // Cria a tabela usuarios
        $sql = "CREATE TABLE usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            senha VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "<p>✅ Tabela 'usuarios' criada com sucesso!</p>";
    } else {
        echo "<p>✅ Tabela 'usuarios' já existe.</p>";
    }
    
    // Verifica se já existe um admin
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE role = 'admin'");
    $stmt->execute();
    $admin_existe = $stmt->fetch();
    
    if ($admin_existe) {
        echo "<p>⚠️ Já existe um usuário administrador.</p>";
    } else {
        // Cria o usuário admin
        $nome = "Administrador";
        $email = "admin@loja.com";
        $senha = password_hash("admin123", PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$nome, $email, $senha]);
        
        echo "<p>✅ Usuário administrador criado com sucesso!</p>";
        echo "<p><strong>Email:</strong> admin@loja.com</p>";
        echo "<p><strong>Senha:</strong> admin123</p>";
    }
    
    // Lista todos os usuários
    echo "<h2>Usuários no Sistema:</h2>";
    $stmt = $pdo->query("SELECT id, nome, email, role, data_cadastro FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($usuarios) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Role</th><th>Data Cadastro</th></tr>";
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>" . $usuario['id'] . "</td>";
            echo "<td>" . $usuario['nome'] . "</td>";
            echo "<td>" . $usuario['email'] . "</td>";
            echo "<td>" . $usuario['role'] . "</td>";
            echo "<td>" . $usuario['data_cadastro'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum usuário encontrado.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='login.php'>Ir para Login</a> | <a href='admin/index.php'>Ir para Admin</a></p>";
?>
