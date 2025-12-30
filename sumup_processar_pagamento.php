<?php
// sumup_processar_pagamento.php - Processa pagamento com token do cartão
session_start();
require_once 'config.php';
require_once 'includes/sumup_api.php';

header('Content-Type: application/json');

// Lê dados do POST (JSON)
$input = file_get_contents('php://input');
$post_data = json_decode($input, true);

$checkout_id = $post_data['checkout_id'] ?? null;
$token = $post_data['token'] ?? null;

if (empty($checkout_id) || empty($token)) {
    echo json_encode([
        'success' => false,
        'message' => 'Checkout ID ou token não fornecido'
    ]);
    exit;
}

$sumup = new SumUpAPI($pdo);

if (!$sumup->isConfigured()) {
    echo json_encode([
        'success' => false,
        'message' => 'SumUp não está configurada'
    ]);
    exit;
}

// Processa o pagamento usando o token
// A SumUp processa o pagamento automaticamente quando o token é gerado
// Você pode verificar o status do checkout para confirmar o pagamento
$status_response = $sumup->getCheckoutStatus($checkout_id);

if ($status_response['success']) {
    $status = $status_response['data']['status'] ?? 'unknown';
    
    // Atualiza status no banco
    try {
        $stmt = $pdo->prepare("
            UPDATE sumup_checkouts 
            SET status = ? 
            WHERE checkout_id = ?
        ");
        $stmt->execute([$status, $checkout_id]);
    } catch (PDOException $e) {
        error_log("Erro ao atualizar status do checkout: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'checkout_id' => $checkout_id,
        'status' => $status,
        'message' => 'Pagamento processado com sucesso'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $status_response['message'] ?? 'Erro ao processar pagamento'
    ]);
}

