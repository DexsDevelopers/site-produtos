<?php
// sumup_processar.php - Processa pagamento via SumUp
session_start();
require_once 'config.php';
require_once 'includes/sumup_api.php';

header('Content-Type: application/json');

// Verifica se há itens no carrinho
if (empty($_SESSION['carrinho'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Carrinho vazio'
    ]);
    exit;
}

// Calcula total
$total_preco = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $total_preco += $item['preco'] * $item['quantidade'];
}

// Obtém dados do cliente (se logado)
$customer = [];
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT nome, email, telefone FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $customer = [
                'name' => $user['nome'] ?? '',
                'email' => $user['email'] ?? '',
                'phone' => $user['telefone'] ?? ''
            ];
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
    }
}

// Lê dados do POST (JSON)
$input = file_get_contents('php://input');
$post_data = json_decode($input, true);

// Se não houver dados do cliente, tenta obter do POST
if (empty($customer['email']) && !empty($post_data['email'])) {
    $customer['email'] = $post_data['email'];
}
if (empty($customer['name']) && !empty($post_data['name'])) {
    $customer['name'] = $post_data['name'];
}
if (empty($customer['phone']) && !empty($post_data['phone'])) {
    $customer['phone'] = $post_data['phone'];
}

// Determina tipo de pagamento
$payment_type = $post_data['payment_type'] ?? 'card'; // 'pix' ou 'card'

// Cria checkout na SumUp
$sumup = new SumUpAPI($pdo);

if (!$sumup->isConfigured()) {
    echo json_encode([
        'success' => false,
        'message' => 'SumUp não está configurada. Configure no painel administrativo.'
    ]);
    exit;
}

// Gera referência única
$checkout_reference = strtoupper($payment_type) . '_' . time() . '_' . uniqid();

// Cria checkout baseado no tipo
if ($payment_type === 'pix') {
    // Cria checkout PIX
    $result = $sumup->createPixCheckout(
        $total_preco,
        'BRL',
        $checkout_reference,
        $customer
    );
    
    if ($result['success']) {
        $_SESSION['sumup_checkout_reference'] = $checkout_reference;
        $_SESSION['sumup_checkout_id'] = $result['checkout_id'];
        
        // Log para debug
        error_log("SumUp PIX Response completa: " . json_encode($result));
        
        // Tenta obter código PIX de diferentes campos possíveis
        $pix_code = $result['pix_code'] ?? $result['data']['pix_code'] ?? $result['data']['pix']['code'] ?? null;
        $pix_qr_code = $result['pix_qr_code'] ?? $result['data']['pix_qr_code'] ?? $result['data']['pix']['qr_code'] ?? $result['data']['pix']['qr_code_url'] ?? null;
        
        // Se não encontrou código PIX, tenta buscar em transactions ou payment_methods
        if (!$pix_code && isset($result['data']['transactions']) && is_array($result['data']['transactions'])) {
            foreach ($result['data']['transactions'] as $transaction) {
                if (isset($transaction['payment_method']) && strtolower($transaction['payment_method']) === 'pix') {
                    $pix_code = $transaction['pix_code'] ?? $transaction['code'] ?? null;
                    $pix_qr_code = $transaction['qr_code'] ?? $transaction['qr_code_url'] ?? null;
                    if ($pix_code) break;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'checkout_id' => $result['checkout_id'],
            'checkout_reference' => $checkout_reference,
            'redirect_url' => $result['redirect_url'] ?? $result['data']['redirect_url'] ?? null,
            'pix_code' => $pix_code,
            'pix_qr_code' => $pix_qr_code,
            'raw_data' => $result['data'] ?? null, // Inclui dados completos para debug
            'message' => 'Checkout PIX criado com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Erro ao criar checkout PIX'
        ]);
    }
} else {
    // Cria checkout Cartão
    $result = $sumup->createCheckout(
        $total_preco,
        'BRL',
        $checkout_reference,
        $customer
    );
    
    if ($result['success']) {
        $_SESSION['sumup_checkout_reference'] = $checkout_reference;
        $_SESSION['sumup_checkout_id'] = $result['checkout_id'];
        
        echo json_encode([
            'success' => true,
            'checkout_id' => $result['checkout_id'],
            'checkout_reference' => $checkout_reference,
            'redirect_url' => $result['redirect_url'] ?? null,
            'message' => 'Checkout criado com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Erro ao criar checkout'
        ]);
    }
}

