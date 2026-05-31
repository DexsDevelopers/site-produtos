<?php
// salvar_carrinho_ajax.php - Captura de leads pré-checkout em tempo real
session_start();
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$whatsapp = sanitizarEntrada($_POST['whatsapp'] ?? '');
$nome = sanitizarEntrada($_POST['nome'] ?? '');
$email = sanitizarEntrada($_POST['email'] ?? '');

if (empty($_SESSION['carrinho'])) {
    echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
    exit;
}

$sessao_id = session_id();
$user_id = $_SESSION['user_id'] ?? null;
$dados = json_encode($_SESSION['carrinho']);

$valor_total = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $valor_total += ($item['preco'] * $item['quantidade']);
}

try {
    // Tenta encontrar se já existe um carrinho para esta sessão ou usuário
    $stmt = $pdo->prepare("SELECT id FROM carrinhos_abandonados WHERE sessao_id = ? OR (usuario_id = ? AND usuario_id IS NOT NULL) LIMIT 1");
    $stmt->execute([$sessao_id, $user_id]);
    $existente = $stmt->fetch();

    if ($existente) {
        $stmt_up = $pdo->prepare("
            UPDATE carrinhos_abandonados 
            SET dados_carrinho = ?, valor_total = ?, lead_nome = ?, lead_whatsapp = ?, lead_email = ?, data_atualizacao = NOW() 
            WHERE id = ?
        ");
        $stmt_up->execute([$dados, $valor_total, $nome, $whatsapp, $email, $existente['id']]);
    } else {
        $stmt_in = $pdo->prepare("
            INSERT INTO carrinhos_abandonados (usuario_id, sessao_id, dados_carrinho, valor_total, lead_nome, lead_whatsapp, lead_email) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_in->execute([$user_id, $sessao_id, $dados, $valor_total, $nome, $whatsapp, $email]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Erro em salvar_carrinho_ajax.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno ao salvar os dados']);
}
exit;
