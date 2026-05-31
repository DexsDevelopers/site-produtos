<?php
// checkout_infinitepay.php - Integração com Checkout Integrado InfinitePay
session_start();
require_once 'config.php';
require_once 'includes/file_storage.php';

// Fallback: pedido já criado (PIX falhou), retoma com InfinitePay
if (isset($_GET['pedido_id']) && (int)$_GET['pedido_id'] > 0) {
    $pedido_id = (int)$_GET['pedido_id'];
    $user_id   = $_SESSION['user_id'] ?? 0;

    if ($user_id == 0) { header('Location: login.php?msg=faca_login'); exit(); }

    $stmt_tag = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'infinite_tag' LIMIT 1");
    $infinite_tag = $stmt_tag ? $stmt_tag->fetchColumn() : '';
    if (empty($infinite_tag)) { die('InfinitePay não configurado.'); }

    $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$pedido_id, $user_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) { header('Location: carrinho.php'); exit(); }

    $stmt_itens = $pdo->prepare('SELECT * FROM pedido_itens WHERE pedido_id = ?');
    $stmt_itens->execute([$pedido_id]);
    $itens_db = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

    $msg_itens = "";
    foreach ($itens_db as $it) {
        $nome_exibicao = $it['nome_produto'] . (!empty($it['valor_tamanho']) ? ' (Tam: ' . $it['valor_tamanho'] . ')' : '');
        $msg_itens .= "• " . $it['quantidade'] . "x " . $nome_exibicao . " - " . formatarPreco($it['preco_unitario'] * $it['quantidade']) . "\n";
    }
    $text = "Olá! Gostaria de pagar com cartão o meu pedido (#" . $pedido_id . "):\n\n" . $msg_itens . "\nTotal: " . formatarPreco($pedido['valor_total']);
    $wa_url = "https://wa.me/5551996148568?text=" . urlencode($text);
    header('Location: ' . $wa_url);
    exit();
}

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

$stmt_tag = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'infinite_tag' LIMIT 1");
$infinite_tag = $stmt_tag ? $stmt_tag->fetchColumn() : '';

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

// Captura dados de endereço — via POST (checkout.php) ou sessão (checkout_produto.php)
$_addr = !empty($_POST['whatsapp']) ? $_POST : ($_SESSION['checkout_address'] ?? []);
if (!empty($_SESSION['checkout_address'])) unset($_SESSION['checkout_address']);
$whatsapp    = $_addr['whatsapp']    ?? '';
$cep         = $_addr['cep']         ?? '';
$endereco    = $_addr['endereco']    ?? '';
$numero      = $_addr['numero']      ?? '';
$complemento = $_addr['complemento'] ?? '';
$bairro      = $_addr['bairro']      ?? '';
$cidade      = $_addr['cidade']      ?? '';
$estado      = $_addr['estado']      ?? '';

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
    unset($_SESSION['carrinho']);
}
catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    die("Erro ao processar pedido: " . $e->getMessage());
}

// --- RASTREAMENTO DE CONVERSÃO META CAPI ---
try {
    require_once 'includes/meta_capi.php';
    $capi = new MetaCAPI($pdo);
    if ($capi->isConfigured()) {
        $stmt_u = $pdo->prepare("SELECT nome, email, whatsapp, cep, cidade, estado FROM usuarios WHERE id = ?");
        $stmt_u->execute([$user_id]);
        $user_info = $stmt_u->fetch(PDO::FETCH_ASSOC) ?: [];

        $contents = [];
        foreach ($carrinho_itens as $item) {
            $contents[] = [
                'id' => $item['id'],
                'quantity' => (int)$item['quantidade'],
                'item_price' => (float)$item['preco']
            ];
        }

        $customData = [
            'value' => (float)$valor_total,
            'currency' => 'BRL',
            'content_type' => 'product',
            'contents' => $contents
        ];

        $nome_parts = explode(' ', trim($user_info['nome'] ?? ''));
        $fn = $nome_parts[0] ?? '';
        $ln = count($nome_parts) > 1 ? end($nome_parts) : '';

        $userData = [
            'em' => $user_info['email'] ?? '',
            'ph' => $user_info['whatsapp'] ?? $whatsapp ?? '',
            'fn' => $fn,
            'ln' => $ln,
            'ct' => $user_info['cidade'] ?? $cidade ?? '',
            'st' => $user_info['estado'] ?? $estado ?? '',
            'zp' => $user_info['cep'] ?? $cep ?? '',
            'country' => 'BR'
        ];

        $purchase_event_id = 'pur_' . $pedido_id;
        $capi->sendEvent('Purchase', $purchase_event_id, $customData, $userData);
    }
} catch (Exception $e) {
    error_log("Erro Meta CAPI InfinitePay (WhatsApp CAPI): " . $e->getMessage());
}

// Constrói a mensagem do WhatsApp com os detalhes do produto e do preço
$msg_itens = "";
foreach ($carrinho_itens as $item) {
    $nome_exibicao = $item['nome'] . (!empty($item['tamanho_valor']) ? ' (Tam: ' . $item['tamanho_valor'] . ')' : '');
    $msg_itens .= "• " . $item['quantidade'] . "x " . $nome_exibicao . " - " . formatarPreco($item['preco'] * $item['quantidade']) . "\n";
}

$text = "Olá! Gostaria de pagar com cartão o meu pedido (#" . $pedido_id . "):\n\n" . $msg_itens . "\nTotal: " . formatarPreco($valor_total);
$wa_url = "https://wa.me/5551996148568?text=" . urlencode($text);

header('Location: ' . $wa_url);
exit();