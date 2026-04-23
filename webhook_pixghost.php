<?php
// webhook_pixghost.php - Recebe confirmações de pagamento do PixGhost
require_once __DIR__ . '/config.php';

$raw     = file_get_contents('php://input');
$payload = json_decode($raw, true);

error_log("PixGhost webhook recebido: " . $raw);

if (
    is_array($payload) &&
    isset($payload['event'], $payload['status'], $payload['external_id']) &&
    $payload['event'] === 'payment.confirmed' &&
    $payload['status'] === 'paid'
) {
    // external_id no formato "pedido_123"
    $pedido_id = (int) str_replace('pedido_', '', $payload['external_id']);

    if ($pedido_id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE pedidos SET status = 'Pago' WHERE id = ? AND status != 'Pago'");
            $stmt->execute([$pedido_id]);
            error_log("PixGhost: pedido #$pedido_id marcado como Pago");
        } catch (Exception $e) {
            error_log("PixGhost webhook erro DB: " . $e->getMessage());
        }
    }
}

http_response_code(200);
echo 'OK';
