<?php
// sumup_webhook.php - Webhook para receber notificações da SumUp
require_once 'config.php';
require_once 'includes/sumup_api.php';

// Log da requisição
error_log("SumUp Webhook recebido: " . file_get_contents('php://input'));

// Lê dados do webhook
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

// Verifica se é uma notificação válida da SumUp
// Em produção, você deve verificar a assinatura do webhook
// Por enquanto, vamos processar a notificação

$sumup = new SumUpAPI($pdo);

// Processa diferentes tipos de eventos
if (isset($data['event_type'])) {
    $event_type = $data['event_type'];
    $checkout_id = $data['checkout_id'] ?? null;
    $checkout_reference = $data['checkout_reference'] ?? null;
    $status = $data['status'] ?? null;
    
    if ($checkout_reference && $status) {
        // Atualiza status do checkout
        $sumup->updateCheckoutStatus($checkout_reference, $status);
        
        // Se o pagamento foi aprovado, processa o pedido
        if ($status === 'PAID' || $status === 'SUCCESSFUL') {
            try {
                // Busca dados do checkout
                $stmt = $pdo->prepare("SELECT * FROM sumup_checkouts WHERE checkout_reference = ?");
                $stmt->execute([$checkout_reference]);
                $checkout = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($checkout) {
                    // Aqui você pode criar o pedido no sistema
                    // Por exemplo, salvar na tabela de pedidos
                    // Por enquanto, apenas logamos
                    error_log("Pagamento aprovado para checkout: " . $checkout_reference);
                }
            } catch (PDOException $e) {
                error_log("Erro ao processar pedido aprovado: " . $e->getMessage());
            }
        }
    }
}

http_response_code(200);
echo json_encode(['success' => true]);

