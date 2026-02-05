<?php
// obrigado.php

session_start();
require_once 'config.php';

// Segurança: se não houver carrinho ou usuário logado, não há o que processar
if (empty($_SESSION['carrinho']) || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$carrinho_itens = $_SESSION['carrinho'];
$nome_cliente = $_SESSION['user_nome'] ?? 'Cliente';

$pedido_id = $_GET['pedido_id'] ?? null;

// Se o pedido já foi criado (ex: via InfinitePay), apenas mostramos a confirmação
if ($pedido_id) {
    // Verifica se o pedido pertence ao usuário
    $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$pedido_id, $user_id]);
    if (!$stmt->fetch()) {
        die("Pedido não encontrado.");
    }
    // Limpa o carrinho
    unset($_SESSION['carrinho']);
} else {
    // --- LÓGICA PARA SALVAR O PEDIDO NO BANCO DE DADOS (PIX MANUAL) ---
    try {
        $pdo->beginTransaction();

        $valor_total = 0;
        foreach ($carrinho_itens as $item) {
            $valor_total += $item['preco'] * $item['quantidade'];
        }

        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, valor_total, status) VALUES (?, ?, 'Pendente (PIX Manual)')");
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
        unset($_SESSION['carrinho']);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Erro ao processar seu pedido. Por favor, tente novamente.");
    }
}


// --- EXIBIÇÃO DA PÁGINA ---
require_once 'templates/header.php';
?>

<div class="w-full max-w-4xl mx-auto py-24 px-4 text-center">
    <div class="pt-16">
        <h1 class="text-5xl font-black text-brand-red">Obrigado, <?= $nome_cliente ?>!</h1>
        <p class="mt-4 text-2xl text-white">Seu pedido #<?= $pedido_id ?> foi recebido com sucesso.</p>
        <p class="mt-2 text-lg text-brand-gray-text">Enviamos um e-mail de confirmação com os detalhes da sua compra. (Isso é uma simulação).</p>
        
        <a href="index.php" class="mt-10 inline-block bg-brand-red hover:bg-brand-red-dark text-white px-10 py-3 rounded-lg font-bold transition-colors">
            Continuar Comprando
        </a>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>