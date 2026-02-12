<?php
// pedido_detalhes.php
session_start();
require_once 'config.php';

// --- SEGURANÇA MÁXIMA ---
// 1. Se o usuário não estiver logado, redireciona para o login.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Pega os IDs da URL e da sessão
$pedido_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// --- BUSCA OS DADOS DO PEDIDO E VERIFICA A PROPRIEDADE ---
try {
    // 2. Busca o pedido APENAS se o ID do pedido E o ID do usuário baterem.
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$pedido_id, $user_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se a busca não retornar nada, o pedido não existe ou não pertence a este usuário.
    if (!$pedido) {
        // Redireciona para a página de conta para não dar informações ao "invasor".
        header('Location: minha_conta.php');
        exit();
    }

    // 3. Se o pedido foi encontrado, busca os itens dele usando um JOIN para pegar a imagem do produto
    $stmt_itens = $pdo->prepare(
        "SELECT pi.*, p.imagem 
         FROM pedido_itens pi
         LEFT JOIN produtos p ON pi.produto_id = p.id
         WHERE pi.pedido_id = ?"
    );
    $stmt_itens->execute([$pedido_id]);
    $itens_pedido = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

}
catch (PDOException $e) {
    die("Erro ao buscar detalhes do pedido."); // Mensagem de erro genérica
}

require_once 'templates/header.php';
?>

<div class="w-full max-w-4xl mx-auto py-24 px-4">
    <div class="pt-16">
        <a href="minha_conta.php" class="text-brand-red hover:underline mb-6 inline-block">&larr; Voltar para Meus
            Pedidos</a>
        <h1 class="text-4xl md:text-5xl font-black text-white">Detalhes do Pedido #
            <?= htmlspecialchars($pedido['id'])?>
        </h1>

        <div class="mt-8 bg-brand-gray/50 p-6 rounded-xl ring-1 ring-white/10 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-brand-gray-text">Data do Pedido</p>
                <p class="font-semibold text-white">
                    <?= date('d/m/Y', strtotime($pedido['data_pedido']))?>
                </p>
            </div>
            <div>
                <p class="text-sm text-brand-gray-text">Valor Total</p>
                <p class="font-semibold text-brand-red">
                    <?= formatarPreco($pedido['valor_total'])?>
                </p>
            </div>
            <div>
                <p class="text-sm text-brand-gray-text">Status</p>
                <p class="font-semibold text-white">
                    <span class="bg-yellow-500/20 text-yellow-300 text-xs font-semibold px-2.5 py-1 rounded-full">
                        <?= htmlspecialchars($pedido['status'])?>
                    </span>
                </p>
            </div>
        </div>

        <div class="mt-10">
            <h2 class="text-2xl font-bold text-white mb-6">Itens Inclusos</h2>
            <div class="space-y-4">
                <?php foreach ($itens_pedido as $item): ?>
                <div class="flex items-center bg-brand-gray/50 p-4 rounded-lg">
                    <img src="<?= htmlspecialchars($item['imagem'] ?? 'assets/images/placeholder.png')?>"
                        alt="<?= htmlspecialchars($item['nome_produto'])?>"
                        class="w-24 h-24 object-cover rounded-md mr-6">
                    <div class="flex-grow">
                        <h3 class="text-lg font-bold text-white">
                            <?= htmlspecialchars($item['nome_produto'])?>
                        </h3>
                        <?php if (!empty($item['valor_tamanho'])): ?>
                        <p class="text-brand-gray-text text-xs uppercase font-bold tracking-widest mt-1">Tamanho: <span
                                class="text-white">
                                <?= htmlspecialchars($item['valor_tamanho'])?>
                            </span></p>
                        <?php
    endif; ?>
                        <p class="text-brand-gray-text mt-1">Quantidade:
                            <?= $item['quantidade']?>
                        </p>
                        <p class="text-brand-gray-text">Preço Unitário:
                            <?= formatarPreco($item['preco_unitario'])?>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-white">
                            <?= formatarPreco($item['preco_unitario'] * $item['quantidade'])?>
                        </p>
                    </div>
                </div>
                <?php
endforeach; ?>
            </div>
        </div>

    </div>
</div>

<?php
require_once 'templates/footer.php';
?>