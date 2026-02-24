<?php
// adicionar_carrinho.php - Adiciona produto ao carrinho via AJAX
session_start();
require_once 'config.php';

// Define o cabeçalho para JSON
header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Valida e sanitiza os dados
$produto_id = (int)($_POST['produto_id'] ?? 0);
$tamanho_id = (int)($_POST['tamanho_id'] ?? 0);
$quantidade = max(1, (int)($_POST['quantidade'] ?? 1));

if ($produto_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do produto inválido']);
    exit();
}

try {
    // Busca dados do produto
    $stmt = $pdo->prepare("SELECT id, nome, preco, imagem, tipo FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
        exit();
    }

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

    // Calcula o total de itens no carrinho
    $total_itens = 0;
    foreach ($_SESSION['carrinho'] as $item) {
        $total_itens += $item['quantidade'];
    }

    // Salva para Carrinho Abandonado
    if (function_exists('salvarCarrinho')) {
        salvarCarrinho($pdo);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Produto adicionado ao carrinho!',
        'cart_count' => $total_itens,
        'produto_nome' => $produto['nome']
    ]);

}
catch (PDOException $e) {
    error_log("Erro ao adicionar produto ao carrinho: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>