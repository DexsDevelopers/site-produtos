<?php
// checkout_infinitepay.php - Integração com Checkout Integrado InfinitePay
session_start();
require_once 'config.php';
require_once 'includes/file_storage.php';

// Verifica se há itens no carrinho
if (empty($_SESSION['carrinho'])) {
    header('Location: carrinho.php');
    exit();
}

$fileStorage = new FileStorage();
$infinite_tag = $fileStorage->getInfiniteTag();

if (empty($infinite_tag)) {
    die("InfinitePay não configurado. Por favor, configure a InfiniteTag no painel administrativo.");
}

$carrinho_itens = $_SESSION['carrinho'];
$items = [];
$valor_total = 0;

foreach ($carrinho_itens as $item) {
    $valor_total += $item['preco'] * $item['quantidade'];
    $items[] = [
        'name' => $item['nome'],
        'price' => (int)round($item['preco'] * 100), // Preço em centavos
        'quantity' => (int)$item['quantidade']
    ];
}

// Cria o pedido no banco de dados como "Pendente"
try {
    $user_id = $_SESSION['user_id'] ?? 0;
    if ($user_id == 0) {
        // Se não estiver logado, redireciona para login
        header('Location: login.php?msg=faca_login');
        exit();
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, valor_total, status) VALUES (?, ?, 'Aguardando Pagamento')");
    $stmt->execute([$user_id, $valor_total]);
    $pedido_id = $pdo->lastInsertId();

    $stmt_item = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, nome_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)");
    foreach ($carrinho_itens as $item) {
        $stmt_item->execute([
            $pedido_id,
            $item['id'],
            $item['nome'],
            $item['quantidade'],
            $item['preco']
        ]);
    }
    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Erro ao processar pedido: " . $e->getMessage());
}

// Configuração da requisição
$url = "https://api.infinitepay.io/invoices/public/checkout/links";
$order_nsu = "ORD-" . $pedido_id . "-" . time();

$data = [
    'handle' => $infinite_tag,
    'order_nsu' => $order_nsu,
    'items' => $items,
    'redirect_url' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/obrigado.php?pedido_id=$pedido_id"
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    die("Erro ao conectar com a API da InfinitePay.");
}

$result = json_decode($response, true);

if (isset($result['url'])) {
    // Armazena informações do pedido no banco se necessário
    // Por enquanto, apenas redireciona
    header('Location: ' . $result['url']);
    exit();
} else {
    // Trata erro
    $error_msg = $result['message'] ?? 'Erro desconhecido ao gerar link de pagamento.';
    echo "<h1>Erro no Checkout InfinitePay</h1>";
    echo "<p>$error_msg</p>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    echo '<a href="checkout.php">Voltar</a>';
}
