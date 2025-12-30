<?php
// sumup_get_public_key.php - Retorna a chave pública da SumUp para uso no frontend
session_start();
require_once 'config.php';
require_once 'includes/sumup_api.php';

header('Content-Type: application/json');

$sumup = new SumUpAPI($pdo);

if (!$sumup->isConfigured()) {
    echo json_encode([
        'success' => false,
        'message' => 'SumUp não está configurada'
    ]);
    exit;
}

$public_key = $sumup->getPublicKey();

if (empty($public_key)) {
    echo json_encode([
        'success' => false,
        'message' => 'Chave pública não configurada'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'public_key' => $public_key
]);

