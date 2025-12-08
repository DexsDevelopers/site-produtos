<?php
// admin/secure.php

// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verifica se o usuário está logado. Se não, redireciona para o login.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../admin_login.php'); // Redireciona para login de admin
    exit();
}

// Inclui a configuração do banco de dados
require_once '../config.php';

// 2. Verifica se o usuário logado é realmente um administrador
try {
    $stmt = $pdo->prepare("SELECT role FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se o usuário não for encontrado ou não tiver o cargo 'admin', redireciona para a página inicial
    if (!$usuario || $usuario['role'] !== 'admin') {
        header('Location: ../index.php');
        exit();
    }
} catch (PDOException $e) {
    // Em caso de erro no banco, redireciona por segurança
    header('Location: ../index.php');
    exit();
}

// Se o script chegou até aqui, o usuário é um admin verificado.
?>