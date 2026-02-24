<?php
// checkout_pix.php - Página de Checkout PIX Manual
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

ob_start();
session_start();
require_once 'config.php';

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

            $_SESSION['carrinho'][$cart_key] = [
                'id' => $produto['id'],
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'imagem' => $produto['imagem'],
                'tamanho_id' => $tamanho_id,
                'tamanho_valor' => $tamanho_valor,
                'quantidade' => $quantidade
            ];

            header("Location: checkout_pix.php");
            exit();
        }
    }
}

// Verifica se há itens no carrinho
if (empty($_SESSION['carrinho'])) {
    header("Location: carrinho.php");
    exit();
}

$output = ob_get_clean();
if (!empty($output)) {
    error_log("Output inesperado antes do header: " . substr($output, 0, 200));
    ob_start();
}

try {
    require_once 'templates/header.php';
}
catch (Exception $e) {
    error_log("Erro ao carregar header: " . $e->getMessage());
    die("Erro ao carregar arquivos: " . $e->getMessage());
}

$carrinho_itens = $_SESSION['carrinho'];
$total_itens = 0;
$total_preco = 0;

foreach ($carrinho_itens as $item) {
    $total_itens += $item['quantidade'];
    $total_preco += $item['preco'] * $item['quantidade'];
}

// Captura dados do formulário de endereço
$whatsapp = $_POST['whatsapp'] ?? "";
$cep = $_POST['cep'] ?? "";
$endereco = $_POST['endereco'] ?? "";
$numero = $_POST['numero'] ?? "";
$complemento = $_POST['complemento'] ?? "";
$bairro = $_POST['bairro'] ?? "";
$cidade = $_POST['cidade'] ?? "";
$estado = $_POST['estado'] ?? "";

// Cria o pedido no banco de dados (Aguardando Pagamento)
$pedido_id = 0;
if (!empty($carrinho_itens)) {
    try {
        $user_id = $_SESSION['user_id'] ?? 0;
        if ($user_id > 0) {
            $pdo->beginTransaction();
            
            // Salva dados no pedido
            $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, valor_total, status, whatsapp, cep, endereco, numero, complemento, bairro, cidade, estado) VALUES (?, ?, 'Aguardando Pagamento', ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $total_preco, $whatsapp, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado]);
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
            $pdo->commit();
            
            // Limpa o carrinho após sucesso
            unset($_SESSION['carrinho']);
        }
    }
    catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        error_log("Erro ao criar pedido PIX: " . $e->getMessage());
    }
}

// Busca configuração PIX
try {
    if (isset($fileStorage) && is_object($fileStorage)) {
        $chave_pix = $fileStorage->getChavePix();
        $nome_pix = $fileStorage->getNomePix();
        $cidade_pix = $fileStorage->getCidadePix();
    }
    else {
        $chave_pix = "";
        $nome_pix = "";
        $cidade_pix = "";
    }
}
catch (Exception $e) {
    $chave_pix = "";
    $nome_pix = "";
    $cidade_pix = "";
    error_log("Erro ao carregar configuração PIX: " . $e->getMessage());
}
?>

<style>
    .checkout-pix-container {
        background: #000000;
        min-height: 100vh;
        padding: 140px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .pix-card {
        background: linear-gradient(135deg, rgba(20, 20, 20, 0.95), rgba(10, 10, 10, 0.98));
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(10px);
    }
    .copy-button {
        background: linear-gradient(135deg, #ff4500, #ff6347);
        color: white;
        padding: 14px 28px;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
    }
    .pix-code {
        background: rgba(0, 0, 0, 0.4);
        padding: 1.25rem;
        border-radius: 12px;
        font-family: monospace;
        word-break: break-all;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .price-highlight {
        color: #ff4500;
        font-weight: 700;
    }
</style>

<div class="checkout-pix-container">
    <div class="w-full max-w-5xl mx-auto px-4">
        <h1 class="text-4xl font-black text-white mb-4 text-center">Finalizar Pagamento</h1>
        <p class="text-center text-white/70 mb-8 text-lg">Pagamento via PIX</p>

        <?php if (empty($chave_pix)): ?>
            <div class="pix-card text-center">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-4"></i>
                <h2 class="text-2xl font-bold text-white mb-4">Chave PIX não configurada</h2>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="lg:col-span-1">
                    <div class="pix-card">
                        <h2 class="text-xl font-bold text-white mb-6 pb-4 border-b border-white/10">Resumo do Pedido</h2>
                        <div class="space-y-4 mb-6">
                            <?php foreach ($carrinho_itens as $item): ?>
                            <div class="flex justify-between items-start py-3 border-b border-white/5">
                                <div class="flex-1">
                                    <p class="text-white text-sm font-medium"><?= htmlspecialchars($item["nome"])?></p>
                                    <p class="text-white/60 text-xs mt-1">Qtd: <?= $item["quantidade"]?></p>
                                </div>
                                <p class="text-white font-semibold ml-4"><?= formatarPreco($item["preco"] * $item["quantidade"])?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="pt-4 border-t border-white/10">
                            <div class="flex justify-between items-center">
                                <span class="text-white text-lg font-bold">Total:</span>
                                <span class="price-highlight text-2xl"><?= formatarPreco($total_preco)?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="pix-card">
                        <h2 class="text-2xl font-bold text-white mb-4"><i class="fas fa-qrcode mr-2"></i>Chave PIX</h2>
                        <div class="pix-code" id="pix-key-display"><?= htmlspecialchars($chave_pix)?></div>
                        <button onclick="copiarCodigoPix()" class="copy-button mt-4">Copiar Chave PIX</button>
                        <?php if (!empty($nome_pix)): ?>
                            <div class="mt-4 p-3 bg-white/5 rounded-lg border border-white/10">
                                <p class="text-white/70 text-sm"><strong class="text-white">Beneficiário:</strong> <?= htmlspecialchars($nome_pix)?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                <a href="index.php" class="copy-button text-center bg-gray-600">Voltar para Início</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function copiarCodigoPix() {
        const pixKeyElement = document.getElementById("pix-key-display");
        const codigo = pixKeyElement.textContent.trim();
        navigator.clipboard.writeText(codigo).then(() => alert("Copiado!"));
    }
</script>

<?php require_once "templates/footer.php"; ?>
