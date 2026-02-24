<?php
// admin/secure.php

// Inicia a sessão se ainda não foi iniciada
if (!isset($_SESSION)) {
    session_start();
}

// Inclui a configuração do banco de dados (necessário para verificação de cookie)
require_once dirname(__FILE__) . '/../config.php';

// 1. Verifica se o usuário está logado. Se não, tenta via cookie ou redireciona.
if (!isset($_SESSION['user_id'])) {

    $logged_via_cookie = false;

    // Tenta Login via Cookie (Lembrar de Mim)
    if (isset($_COOKIE['remember_token'])) {
        try {
            $token_hash = hash('sha256', $_COOKIE['remember_token']);
            $stmt = $pdo->prepare("SELECT u.id, u.nome, u.role FROM user_sessions s JOIN usuarios u ON s.user_id = u.id WHERE s.token_hash = ? AND s.expires_at > NOW()");
            $stmt->execute(array($token_hash));
            $user_cookie = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_cookie && $user_cookie['role'] === 'admin') {
                $_SESSION['user_id'] = $user_cookie['id'];
                $_SESSION['user_nome'] = $user_cookie['nome'];
                $logged_via_cookie = true;
            }
        }
        catch (Exception $e) {
        // Falha silenciosa no cookie
        }
    }

    if (!$logged_via_cookie) {
        header('Location: ../admin_login.php');
        exit();
    }
}

// 2. Verifica se o usuário logado é realmente um administrador
try {
    $stmt = $pdo->prepare("SELECT role FROM usuarios WHERE id = ?");
    $stmt->execute(array($_SESSION['user_id']));
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se o usuário não for encontrado ou não tiver o cargo 'admin', redireciona para a página inicial
    if (!$usuario || $usuario['role'] !== 'admin') {
        // Remove sessão e cookie se inválido
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
        header('Location: ../index.php');
        exit();
    }
}
catch (PDOException $e) {
    header('Location: ../index.php');
    exit();
}

// Se o script chegou até aqui, o usuário é um admin verificado.
?>