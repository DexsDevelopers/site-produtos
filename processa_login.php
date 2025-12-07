<?php
// processa_login.php

session_start();
require_once 'config.php';

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Validação básica
    if (empty($email) || empty($senha)) {
        $_SESSION['error_message'] = "E-mail e senha são obrigatórios.";
        header("Location: login.php");
        exit();
    }

    // --- PROCURA O USUÁRIO NO BANCO DE DADOS ---
    try {
        $stmt = $pdo->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // --- VERIFICA A SENHA ---
        // Se um usuário foi encontrado E a senha digitada corresponde ao hash no banco de dados
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            
            // Login bem-sucedido!
            // Regenera o ID da sessão para segurança
            session_regenerate_id(true);

            // Armazena os dados do usuário na sessão
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nome'] = $usuario['nome'];

            // Redireciona para a página inicial
            header("Location: index.php");
            exit();

        } else {
            // Se o usuário não foi encontrado ou a senha está incorreta
            $_SESSION['error_message'] = "E-mail ou senha inválidos.";
            header("Location: login.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erro no servidor. Tente novamente.";
        header("Location: login.php");
        exit();
    }
} else {
    // Se não for um POST, redireciona
    header("Location: login.php");
    exit();
}
?>