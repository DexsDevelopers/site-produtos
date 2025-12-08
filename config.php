<?php
// config.php (VERSÃO SEM BANCO DE DADOS - USANDO ARQUIVOS JSON)

// Inicia a sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- SISTEMA DE ARMAZENAMENTO EM ARQUIVO ---
require_once __DIR__ . '/includes/file_storage.php';
$fileStorage = new FileStorage();

// --- FUNÇÕES GLOBAIS ---
function formatarPreco($preco) {
    if (!is_numeric($preco)) { return 'R$ 0,00'; }
    return 'R$ ' . number_format((float)$preco, 2, ',', '.');
}

function formatarPrecoPagBank($preco) {
    if (!is_numeric($preco)) { return '0.00'; }
    return number_format((float)$preco, 2, '.', '');
}

// --- FUNÇÕES DE SEGURANÇA ---
function sanitizarEntrada($dados) {
    if (is_array($dados)) {
        return array_map('sanitizarEntrada', $dados);
    }
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validarSenha($senha) {
    return strlen($senha) >= 6;
}

function gerarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirecionarComMensagem($url, $tipo, $mensagem) {
    $_SESSION[$tipo . '_message'] = $mensagem;
    header("Location: $url");
    exit();
}

function exibirMensagem($tipo) {
    if (isset($_SESSION[$tipo . '_message'])) {
        $mensagem = $_SESSION[$tipo . '_message'];
        unset($_SESSION[$tipo . '_message']);
        return $mensagem;
    }
    return null;
}

// --- CONFIGURAÇÕES BÁSICAS ---
// Headers básicos de segurança
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
}
?>