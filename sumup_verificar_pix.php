<?php
// sumup_verificar_pix.php - Verifica se código PIX está disponível para um checkout
session_start();
require_once 'config.php';
require_once 'includes/sumup_api.php';

header('Content-Type: application/json');

$checkout_id = $_GET['checkout_id'] ?? null;

if (empty($checkout_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Checkout ID não fornecido'
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

// Obtém status do checkout
$status_response = $sumup->getCheckoutStatus($checkout_id);

if (!$status_response['success']) {
    echo json_encode([
        'success' => false,
        'message' => $status_response['message'] ?? 'Erro ao verificar checkout'
    ]);
    exit;
}

$checkout_data = $status_response['data'];

// Busca código PIX nos artefacts
$pix_code = null;
$pix_qr_code = null;

if (isset($checkout_data['pix']) && isset($checkout_data['pix']['artefacts'])) {
    foreach ($checkout_data['pix']['artefacts'] as $artefact) {
        $name = $artefact['name'] ?? '';
        $content_type = $artefact['content_type'] ?? '';
        $location = $artefact['location'] ?? null;
        $content = $artefact['content'] ?? null;
        
        if ($name === 'code' && $content_type === 'text/plain') {
            if ($content) {
                $pix_code = $content;
            } elseif ($location) {
                // Faz requisição para obter o código
                if (strpos($location, 'http') === 0) {
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => $location,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER => [
                            'Authorization: Bearer ' . $sumup->getCredentials()['api_key']
                        ],
                        CURLOPT_SSL_VERIFYPEER => true,
                        CURLOPT_TIMEOUT => 10
                    ]);
                    $pix_response = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($http_code >= 200 && $http_code < 300) {
                        $pix_code = trim($pix_response);
                    }
                }
            }
        }
        
        if ($name === 'barcode' && strpos($content_type, 'image/') === 0) {
            $pix_qr_code = $location ?? $content ?? null;
        }
    }
}

echo json_encode([
    'success' => true,
    'pix_code' => $pix_code,
    'pix_qr_code' => $pix_qr_code,
    'has_pix' => !empty($pix_code),
    'checkout_status' => $checkout_data['status'] ?? 'unknown'
]);

