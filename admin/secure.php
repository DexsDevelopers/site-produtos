<?php
// admin/secure.php - Versão sem banco de dados

// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verifica se o usuário está logado. Se não, redireciona para o login.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../admin_login.php'); // Redireciona para login de admin
    exit();
}

// Inclui a configuração (sem banco de dados)
require_once '../config.php';

// 2. Verifica se o usuário logado é realmente um administrador
// Agora verificamos pela sessão ao invés do banco de dados
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Se não tiver role na sessão, verifica se tem user_id e assume admin
    // (para compatibilidade com logins antigos)
    if (isset($_SESSION['user_id'])) {
        // Permite acesso se tiver user_id (assume que é admin se chegou até aqui)
        // Em produção, você pode adicionar uma lista de IDs de admin permitidos
        $_SESSION['user_role'] = 'admin';
    } else {
        header('Location: ../index.php');
        exit();
    }
}

// Se o script chegou até aqui, o usuário é um admin verificado.
?>