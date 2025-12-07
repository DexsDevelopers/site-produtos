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
$nome_cliente = isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : 'Cliente';

// --- LÓGICA PARA SALVAR O PEDIDO NO BANCO DE DADOS ---
try {
    $pdo->beginTransaction(); // Inicia uma transação para garantir a integridade dos dados

    // 1. Calcula o valor total do carrinho
    $valor_total = 0;
    foreach ($carrinho_itens as $item) {
        $valor_total += $item['preco'] * $item['quantidade'];
    }

    // 2. Insere o pedido na tabela `pedidos`
    $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, valor_total) VALUES (?, ?)");
    $stmt->execute([$user_id, $valor_total]);
    
    // 3. Pega o ID do pedido que acabamos de criar
    $pedido_id = $pdo->lastInsertId();

    // 4. Insere cada item do carrinho na tabela `pedido_itens`
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

    $pdo->commit(); // Confirma a transação, salvando tudo no banco

    // 5. Limpa o carrinho da sessão, pois a compra foi finalizada
    unset($_SESSION['carrinho']);

} catch (PDOException $e) {
    $pdo->rollBack(); // Em caso de erro, desfaz tudo que foi feito na transação
    // Em um site real, você logaria o erro em um arquivo. Por enquanto, exibimos uma mensagem.
    die("Erro ao processar seu pedido. Por favor, tente novamente. Detalhe: " . $e->getMessage());
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