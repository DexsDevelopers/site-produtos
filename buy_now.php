<?php
// buy_now.php - Adiciona produto ao carrinho e redireciona para o checkout
session_start();
require_once 'config.php';

$produto_id = (int)($_GET['produto_id'] ?? 0);
$tamanho_id = (int)($_GET['tamanho_id'] ?? 0);
$quantidade = 1;

if ($produto_id <= 0) {
    header('Location: index.php');
    exit();
}

try {
    // Busca dados do produto
    $stmt = $pdo->prepare("SELECT id, nome, preco, imagem, tipo FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        $tamanho_valor = null;
        if ($produto['tipo'] === 'fisico' && $tamanho_id > 0) {
            $stmt_tam = $pdo->prepare("SELECT valor FROM tamanhos WHERE id = ?");
            $stmt_tam->execute([$tamanho_id]);
            $tamanho_valor = $stmt_tam->fetchColumn();
        }

        // Inicializa o carrinho se não existir
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }

        // Identificador único (Produto + Tamanho)
        $cart_key = $produto_id . ($tamanho_id > 0 ? '_' . $tamanho_id : '');

        // Adiciona ou atualiza o produto no carrinho
        if (isset($_SESSION['carrinho'][$cart_key])) {
            $_SESSION['carrinho'][$cart_key]['quantidade'] += $quantidade;
        }
        else {
            $_SESSION['carrinho'][$cart_key] = [
                'id' => $produto['id'],
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'imagem' => $produto['imagem'],
                'tamanho_id' => $tamanho_id,
                'tamanho_valor' => $tamanho_valor,
                'quantidade' => $quantidade
            ];
        }
    }
}
catch (Exception $e) {
    error_log("Erro no buy_now: " . $e->getMessage());
}

// Redireciona para o checkout (onde preencherá o endereço)
header('Location: checkout.php');
exit();
