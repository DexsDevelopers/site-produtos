<?php
// processa_registro.php

session_start();
require_once 'config.php'; // Para ter acesso à conexão $pdo

// Verifica se o formulário foi enviado (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // --- VALIDAÇÕES ---
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $_SESSION['error_message'] = "Todos os campos são obrigatórios.";
        header("Location: registrar.php");
        exit();
    }

    if ($senha !== $confirmar_senha) {
        $_SESSION['error_message'] = "As senhas não coincidem.";
        header("Location: registrar.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "O formato do e-mail é inválido.";
        header("Location: registrar.php");
        exit();
    }

    // --- VERIFICA SE O E-MAIL JÁ EXISTE ---
    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Este e-mail já está cadastrado.";
            header("Location: registrar.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erro no servidor. Tente novamente mais tarde.";
        header("Location: registrar.php");
        exit();
    }

    // --- SE PASSOU EM TODAS AS VALIDAÇÕES, CRIA O USUÁRIO ---
    // Criptografa a senha com hash
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    try {
        // Insere o novo usuário no banco de dados
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $email, $senha_hash]);

        // Define uma mensagem de sucesso e redireciona para a página de login
        $_SESSION['success_message'] = "Conta criada com sucesso! Faça o login para continuar.";
        header("Location: login.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Não foi possível criar a conta. Tente novamente.";
        // Em um ambiente real, você logaria o erro: error_log($e->getMessage());
        header("Location: registrar.php");
        exit();
    }

} else {
    // Se alguém tentar acessar o arquivo diretamente, redireciona para a página de registro
    header("Location: registrar.php");
    exit();
}
?>