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

// Se não houver dados do cliente, tenta obter do POST
if (empty($customer['email']) && !empty($_POST['email'])) {
    $customer['email'] = $_POST['email'];
}
if (empty($customer['name']) && !empty($_POST['name'])) {
    $customer['name'] = $_POST['name'];
}
if (empty($customer['phone']) && !empty($_POST['phone'])) {
    $customer['phone'] = $_POST['phone'];
}

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
$checkout_reference = 'PEDIDO_' . time() . '_' . uniqid();

// Cria checkout
$result = $sumup->createCheckout(
    $total_preco,
    'BRL',
    $checkout_reference,
    $customer
);

if ($result['success']) {
    // Salva referência na sessão para rastreamento
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

