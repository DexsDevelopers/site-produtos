<?php
// pix_status_check.php - Proxy de status para PixGhost (AJAX polling)
session_start();
header('Content-Type: application/json');

$pix_id    = trim($_GET['pix_id'] ?? '');
$pedido_id = (int)($_GET['pedido_id'] ?? 0);

if (empty($pix_id) || $pedido_id <= 0) {
    echo json_encode(['success' => false, 'status' => 'error', 'error' => 'Parâmetros inválidos']);
    exit;
}

$ch = curl_init('https://pixghost.site/check_status.php?pix_id=' . urlencode($pix_id));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 10
]);
$raw = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err || $raw === false) {
    echo json_encode(['success' => false, 'status' => 'error', 'error' => 'Falha na conexão']);
    exit;
}

$data = json_decode($raw, true);

// Se pago, atualiza o pedido no banco também
if (($data['status'] ?? '') === 'paid') {
    require_once __DIR__ . '/config.php';
    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET status = 'Pago' WHERE id = ? AND status != 'Pago'");
        $stmt->execute([$pedido_id]);
    } catch (Exception $e) {
        error_log("pix_status_check: erro ao atualizar pedido $pedido_id: " . $e->getMessage());
    }
}

echo $raw;
