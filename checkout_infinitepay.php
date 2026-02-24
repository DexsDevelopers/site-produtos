<?php
// checkout_infinitepay.php - Integração com Checkout Integrado InfinitePay
session_start();
require_once 'config.php';
require_once 'includes/file_storage.php';

// Se recebeu produto_id via GET, adiciona ao carrinho primeiro
if (isset($_GET['produto_id']) && !empty($_GET['produto_id'])) {
    $produto_id = (int)$_GET['produto_id'];
    $tamanho_id = (int)($_GET['tamanho_id'] ?? 0);
    $quantidade = max(1, (int)($_GET['quantidade'] ?? 1));

    if ($produto_id > 0) {
        $stmt = $pdo->prepare("SELECT id, nome, preco, imagem, tipo FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            if (!isset($_SESSION['carrinho'])) {
                $_SESSION['carrinho'] = [];
            }

            $tamanho_valor = null;
            if ($produto['tipo'] === 'fisico' && $tamanho_id > 0) {
                $stmt_tam = $pdo->prepare("SELECT valor FROM tamanhos WHERE id = ?");
                $stmt_tam->execute([$tamanho_id]);
                $tamanho_valor = $stmt_tam->fetchColumn();
            }

            // Identificador único (Produto + Tamanho)
            $cart_key = $produto_id . ($tamanho_id > 0 ? '_' . $tamanho_id : '');

            // Adiciona ou atualiza item no carrinho
            $_SESSION['carrinho'][$cart_key] = [
                'id' => $produto['id'],
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'imagem' => $produto['imagem'],
                'tamanho_id' => $tamanho_id,
                'tamanho_valor' => $tamanho_valor,
                'quantidade' => $quantidade
            ];

            // Redireciona para remover parâmetros da URL e processar
            header('Location: checkout_infinitepay.php');
            exit();
        }
    }
}
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
    $nome_exibicao = $item['nome'] . (!empty($item['tamanho_valor']) ? ' - Tamanho: ' . $item['tamanho_valor'] : '');
    $items[] = [
        'name' => $nome_exibicao,
        'description' => $nome_exibicao,
        'price' => (int)round($item['preco'] * 100), // Preço em centavos
        'quantity' => (int)$item['quantidade']
    ];
}

// Captura dados do formulário de endereço
$whatsapp = $_POST['whatsapp'] ?? '';
$cep = $_POST['cep'] ?? '';
$endereco = $_POST['endereco'] ?? '';
$numero = $_POST['numero'] ?? '';
$complemento = $_POST['complemento'] ?? '';
$bairro = $_POST['bairro'] ?? '';
$cidade = $_POST['cidade'] ?? '';
$estado = $_POST['estado'] ?? '';

// Cria o pedido no banco de dados como "Pendente"
try {
    $user_id = $_SESSION['user_id'] ?? 0;
    if ($user_id == 0) {
        // Se não estiver logado, redireciona para login
        header('Location: login.php?msg=faca_login');
        exit();
    }

    $pdo->beginTransaction();

    // Salva dados no pedido
    $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, valor_total, status, whatsapp, cep, endereco, numero, complemento, bairro, cidade, estado) VALUES (?, ?, 'Aguardando Pagamento', ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $valor_total, $whatsapp, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado]);
    $pedido_id = $pdo->lastInsertId();

    // Atualiza dados no perfil do usuário para compras futuras
    $stmt_user = $pdo->prepare("UPDATE usuarios SET whatsapp = ?, cep = ?, endereco = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ? WHERE id = ?");
    $stmt_user->execute([$whatsapp, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $user_id]);

    $stmt_item = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, tamanho_id, valor_tamanho, nome_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($carrinho_itens as $item) {
        $stmt_item->execute([
            $pedido_id,
            $item['id'],
            $item['tamanho_id'] ?? null,
            $item['tamanho_valor'] ?? null,
            $item['nome'],
            $item['quantidade'],
            $item['preco']
        ]);
    }

    // Remove do sistema de carrinhos abandonados (Conversão)
    if (isset($user_id)) {
        $sessao_id = session_id();
        $stmt_del = $pdo->prepare("DELETE FROM carrinhos_abandonados WHERE sessao_id = ? OR (usuario_id = ? AND usuario_id IS NOT NULL)");
        $stmt_del->execute([$sessao_id, $user_id]);
    }

    $pdo->commit();

// O carrinho será limpo na página de obrigado após o retorno do pagamento
}
catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
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
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
];

$context = stream_context_create($options);
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
}
else {
    // Trata erro
    $error_msg = $result['message'] ?? 'Erro desconhecido ao gerar link de pagamento.';
    echo "<h1>Erro no Checkout InfinitePay</h1>";
    echo "<p>$error_msg</p>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    echo '<a href="checkout.php">Voltar</a>';
}