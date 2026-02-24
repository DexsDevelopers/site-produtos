<?php
// admin/secure.php

// Inicia a sessÃ£o se ainda nÃ£o foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui a configuraÃ§Ã£o do banco de dados (necessÃ¡rio para verificaÃ§Ã£o de cookie)
require_once '../config.php';

// 1. Verifica se o usuÃ¡rio estÃ¡ logado. Se nÃ£o, tenta via cookie ou redireciona.
if (!isset($_SESSION['user_id'])) {

    $logged_via_cookie = false;

    // Tenta Login via Cookie (Lembrar de Mim)
    if (isset($_COOKIE['remember_token'])) {
        try {
            $token_hash = hash('sha256', $_COOKIE['remember_token']);
            $stmt = $pdo->prepare("SELECT u.id, u.nome, u.role FROM user_sessions s JOIN usuarios u ON s.user_id = u.id WHERE s.token_hash = ? AND s.expires_at > NOW()");
            $stmt->execute([$token_hash]);
            $user_cookie = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_cookie && $user_cookie['role'] === 'admin') {
                $_SESSION['user_id'] = $user_cookie['id'];
                $_SESSION['user_nome'] = $user_cookie['nome'];
                $logged_via_cookie = true;
            // Opcional: Renovar token aqui
            }
        }
        catch (Exception $e) {
        // Falha silenciosa no cookie, vai para login
        }
    }

    if (!$logged_via_cookie) {
        header('Location: ../admin_login.php');
        exit();
    }
}

// 2. Verifica se o usuÃ¡rio logado Ã© realmente um administrador
try {
    $stmt = $pdo->prepare("SELECT role FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se o usuÃ¡rio nÃ£o for encontrado ou nÃ£o tiver o cargo 'admin', redireciona para a pÃ¡gina inicial
    if (!$usuario || $usuario['role'] !== 'admin') {
        // Remove sessÃ£o e cookie se invÃ¡lido
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

// Se o script chegou atÃ© aqui, o usuÃ¡rio Ã© um admin verificado.
?>