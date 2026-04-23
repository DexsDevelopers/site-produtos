<?php
// checkout_pix.php - Checkout PIX via PixGhost
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
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
            if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];
            $tamanho_valor = null;
            if ($produto['tipo'] === 'fisico' && $tamanho_id > 0) {
                $stmt_tam = $pdo->prepare("SELECT valor FROM tamanhos WHERE id = ?");
                $stmt_tam->execute([$tamanho_id]);
                $tamanho_valor = $stmt_tam->fetchColumn();
            }
            $cart_key = $produto_id . ($tamanho_id > 0 ? '_' . $tamanho_id : '');
            $_SESSION['carrinho'][$cart_key] = [
                'id' => $produto['id'], 'nome' => $produto['nome'],
                'preco' => $produto['preco'], 'imagem' => $produto['imagem'],
                'tamanho_id' => $tamanho_id, 'tamanho_valor' => $tamanho_valor,
                'quantidade' => $quantidade
            ];
            header("Location: checkout_pix.php");
            exit();
        }
    }
}

if (empty($_SESSION['carrinho'])) {
    header("Location: carrinho.php");
    exit();
}

// Requer login
$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    header('Location: login.php?msg=faca_login');
    exit();
}

// Busca API Key do PixGhost no banco
$pixghost_key = '';
try {
    $stmt_cfg = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('pixghost_api_key')");
    $cfg = $stmt_cfg->fetchAll(PDO::FETCH_KEY_PAIR);
    $pixghost_key = $cfg['pixghost_api_key'] ?? '';
} catch (Exception $e) {}

// Calcula totais
$carrinho_itens = $_SESSION['carrinho'];
$total_preco = 0;
foreach ($carrinho_itens as $item) {
    $total_preco += $item['preco'] * $item['quantidade'];
}

// Dados de endereço
$whatsapp    = $_POST['whatsapp']    ?? '';
$cep         = $_POST['cep']         ?? '';
$endereco    = $_POST['endereco']    ?? '';
$numero      = $_POST['numero']      ?? '';
$complemento = $_POST['complemento'] ?? '';
$bairro      = $_POST['bairro']      ?? '';
$cidade      = $_POST['cidade']      ?? '';
$estado      = $_POST['estado']      ?? '';

// Cria pedido no banco
$pedido_id = 0;
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, valor_total, status, whatsapp, cep, endereco, numero, complemento, bairro, cidade, estado) VALUES (?, ?, 'Aguardando Pagamento', ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $total_preco, $whatsapp, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado]);
    $pedido_id = $pdo->lastInsertId();

    $stmt_user = $pdo->prepare("UPDATE usuarios SET whatsapp=?, cep=?, endereco=?, numero=?, complemento=?, bairro=?, cidade=?, estado=? WHERE id=?");
    $stmt_user->execute([$whatsapp, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $user_id]);

    $stmt_item = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, tamanho_id, valor_tamanho, nome_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($carrinho_itens as $item) {
        $stmt_item->execute([$pedido_id, $item['id'], $item['tamanho_id'] ?? null, $item['tamanho_valor'] ?? null, $item['nome'], $item['quantidade'], $item['preco']]);
    }

    $sessao_id = session_id();
    $stmt_del = $pdo->prepare("DELETE FROM carrinhos_abandonados WHERE sessao_id = ? OR (usuario_id = ? AND usuario_id IS NOT NULL)");
    $stmt_del->execute([$sessao_id, $user_id]);

    $pdo->commit();
    unset($_SESSION['carrinho']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Erro ao processar pedido: " . $e->getMessage());
}

// Chama API PixGhost
$pix_id    = null;
$pix_code  = null;
$qr_image  = null;
$pix_error = null;
$expires_in = 1200;
$user_nome = $_SESSION['user_nome'] ?? 'Cliente';

if (!empty($pixghost_key) && $pedido_id > 0) {
    $payload = [
        'amount'       => (float) round($total_preco, 2),
        'customer'     => ['name' => $user_nome],
        'callback_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/webhook_pixghost.php',
        'external_id'  => 'pedido_' . $pedido_id
    ];
    $ch = curl_init('https://pixghost.site/api.php');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $pixghost_key
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($raw, true);
    if ($result['success'] ?? false) {
        $pix_id     = $result['pix_id'];
        $pix_code   = $result['pix_code'];
        $qr_image   = $result['qr_image'];
        $expires_in = $result['expires_in'] ?? 1200;
    } else {
        error_log("PixGhost error (pedido #$pedido_id): " . ($raw ?? ''));
        // Fallback automático para InfinitePay
        header('Location: checkout_infinitepay.php?pedido_id=' . $pedido_id);
        exit();
    }
} else {
    error_log("PixGhost sem API key, redirecionando pedido #$pedido_id para InfinitePay");
    header('Location: checkout_infinitepay.php?pedido_id=' . $pedido_id);
    exit();
}

require_once 'templates/header.php';
?>

<style>
    .pix-card {
        background: linear-gradient(135deg, rgba(20,20,20,0.95), rgba(10,10,10,0.98));
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    }
    .pix-copy-btn {
        background: linear-gradient(135deg, #32BCAD, #26a899);
        color: white;
        padding: 14px 28px;
        border-radius: 12px;
        border: none;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        font-size: 1rem;
        letter-spacing: 0.05em;
        transition: opacity .2s;
    }
    .pix-copy-btn:hover { opacity: .85; }
    .pix-code-box {
        background: rgba(0,0,0,0.5);
        padding: 1rem;
        border-radius: 10px;
        font-family: monospace;
        font-size: .78rem;
        word-break: break-all;
        color: #e2e2e2;
        border: 1px solid rgba(255,255,255,0.1);
        max-height: 90px;
        overflow-y: auto;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 18px;
        border-radius: 999px;
        font-size: .85rem;
        font-weight: 600;
    }
    .status-pending { background: rgba(251,191,36,.15); color: #fbbf24; border: 1px solid rgba(251,191,36,.3); }
    .status-paid    { background: rgba(34,197,94,.15);  color: #22c55e; border: 1px solid rgba(34,197,94,.3); }
    .price-hl { color: #32BCAD; font-weight: 700; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .spinner { animation: spin 1s linear infinite; display: inline-block; }
</style>

<div style="min-height:100vh; padding: 140px 0 80px; background:#000;">
    <div class="w-full max-w-5xl mx-auto px-4">
        <h1 class="text-4xl font-black text-white mb-2 text-center">Pagamento via PIX</h1>
        <p class="text-center text-white/50 mb-10 text-sm tracking-widest uppercase">Pedido #<?= $pedido_id ?></p>

        <?php if ($pix_error): ?>
        <div class="pix-card text-center max-w-lg mx-auto">
            <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-4"></i>
            <h2 class="text-xl font-bold text-white mb-2">Erro ao gerar PIX</h2>
            <p class="text-white/60 mb-6"><?= htmlspecialchars($pix_error) ?></p>
            <a href="checkout.php" class="pix-copy-btn" style="display:block; text-align:center; text-decoration:none;">Voltar ao Checkout</a>
        </div>
        <?php else: ?>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            <!-- Resumo do Pedido -->
            <div class="lg:col-span-2">
                <div class="pix-card h-full">
                    <h2 class="text-lg font-bold text-white mb-4 pb-3 border-b border-white/10">Resumo do Pedido</h2>
                    <div class="space-y-3 mb-4">
                        <?php foreach ($carrinho_itens as $item): ?>
                        <div class="flex justify-between items-start py-2 border-b border-white/5">
                            <div class="flex-1 pr-3">
                                <p class="text-white text-sm font-medium leading-tight"><?= htmlspecialchars($item['nome']) ?></p>
                                <?php if (!empty($item['tamanho_valor'])): ?>
                                <p class="text-white/40 text-xs mt-1">Tam: <?= htmlspecialchars($item['tamanho_valor']) ?></p>
                                <?php endif; ?>
                                <p class="text-white/40 text-xs">Qtd: <?= $item['quantidade'] ?></p>
                            </div>
                            <p class="text-white font-semibold text-sm whitespace-nowrap"><?= formatarPreco($item['preco'] * $item['quantidade']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="pt-3 border-t border-white/10 flex justify-between items-center">
                        <span class="text-white font-bold">Total</span>
                        <span class="price-hl text-2xl"><?= formatarPreco($total_preco) ?></span>
                    </div>
                    <div class="mt-6 p-3 rounded-lg" style="background:rgba(50,188,173,.08); border:1px solid rgba(50,188,173,.2);">
                        <p class="text-xs text-white/50 text-center">Pagamento instantâneo via PIX</p>
                        <p class="text-xs text-white/50 text-center mt-1">QR Code expira em <span id="countdown" class="font-bold text-white/70"><?= gmdate('i:s', $expires_in) ?></span></p>
                    </div>
                </div>
            </div>

            <!-- QR Code + Código PIX -->
            <div class="lg:col-span-3">
                <div class="pix-card">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-xl font-bold text-white"><i class="fas fa-qrcode mr-2" style="color:#32BCAD;"></i>Escaneie o QR Code</h2>
                        <span class="status-badge status-pending" id="status-badge">
                            <span class="spinner">⟳</span> Aguardando
                        </span>
                    </div>

                    <!-- QR Code Image -->
                    <div class="flex justify-center mb-5">
                        <div style="background:#fff; padding:12px; border-radius:12px; display:inline-block;">
                            <img src="<?= htmlspecialchars($qr_image) ?>" alt="QR Code PIX"
                                 style="width:200px; height:200px; display:block; border-radius:6px;"
                                 onerror="this.style.display='none'; document.getElementById('qr-fallback').style.display='flex';">
                            <div id="qr-fallback" style="display:none; width:200px; height:200px; align-items:center; justify-content:center; flex-direction:column; color:#333;">
                                <i class="fas fa-qrcode" style="font-size:3rem; margin-bottom:8px;"></i>
                                <p style="font-size:.75rem; text-align:center;">Use o código abaixo</p>
                            </div>
                        </div>
                    </div>

                    <!-- Código Copia e Cola -->
                    <p class="text-white/60 text-sm text-center mb-2">Ou copie o código PIX:</p>
                    <div class="pix-code-box" id="pix-code-display"><?= htmlspecialchars($pix_code) ?></div>
                    <button onclick="copiarPix()" class="pix-copy-btn mt-3" id="copy-btn">
                        <i class="fas fa-copy mr-2"></i> Copiar Código PIX
                    </button>

                    <!-- Instruções -->
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
                        <div style="padding:12px; background:rgba(255,255,255,.03); border-radius:10px; border:1px solid rgba(255,255,255,.07);">
                            <p class="text-white/40 text-xs mb-1">1</p>
                            <p class="text-white text-xs font-medium">Abra seu banco</p>
                        </div>
                        <div style="padding:12px; background:rgba(255,255,255,.03); border-radius:10px; border:1px solid rgba(255,255,255,.07);">
                            <p class="text-white/40 text-xs mb-1">2</p>
                            <p class="text-white text-xs font-medium">Escaneie ou cole o código</p>
                        </div>
                        <div style="padding:12px; background:rgba(255,255,255,.03); border-radius:10px; border:1px solid rgba(255,255,255,.07);">
                            <p class="text-white/40 text-xs mb-1">3</p>
                            <p class="text-white text-xs font-medium">Confirme o pagamento</p>
                        </div>
                    </div>

                    <!-- Mensagem de sucesso (oculta) -->
                    <div id="success-msg" style="display:none; margin-top:1.5rem; padding:1rem; background:rgba(34,197,94,.1); border:1px solid rgba(34,197,94,.3); border-radius:12px; text-align:center;">
                        <i class="fas fa-check-circle text-green-400 text-3xl mb-2"></i>
                        <p class="text-white font-bold text-lg">Pagamento Confirmado!</p>
                        <p class="text-white/60 text-sm">Redirecionando...</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
<?php if (!$pix_error && $pix_id): ?>
const PIX_ID    = <?= json_encode($pix_id) ?>;
const PEDIDO_ID = <?= json_encode($pedido_id) ?>;
const OBRIGADO  = 'obrigado.php?pedido_id=' + PEDIDO_ID;
let expiresIn   = <?= (int)$expires_in ?>;
let pollInterval;

// Countdown timer
const countdownEl = document.getElementById('countdown');
const timerInterval = setInterval(() => {
    expiresIn--;
    if (expiresIn <= 0) {
        clearInterval(timerInterval);
        clearInterval(pollInterval);
        if (countdownEl) countdownEl.textContent = 'Expirado';
        return;
    }
    const m = String(Math.floor(expiresIn / 60)).padStart(2, '0');
    const s = String(expiresIn % 60).padStart(2, '0');
    if (countdownEl) countdownEl.textContent = m + ':' + s;
}, 1000);

// Polling de status a cada 4 segundos
pollInterval = setInterval(async () => {
    try {
        const res  = await fetch('pix_status_check.php?pix_id=' + encodeURIComponent(PIX_ID) + '&pedido_id=' + PEDIDO_ID);
        const data = await res.json();
        if (data.status === 'paid') {
            clearInterval(pollInterval);
            clearInterval(timerInterval);
            const badge = document.getElementById('status-badge');
            if (badge) { badge.className = 'status-badge status-paid'; badge.innerHTML = '✓ Pago'; }
            const msg = document.getElementById('success-msg');
            if (msg) msg.style.display = 'block';
            setTimeout(() => window.location.href = OBRIGADO, 2000);
        } else if (data.status === 'expired' || data.status === 'failed') {
            clearInterval(pollInterval);
            clearInterval(timerInterval);
        }
    } catch (e) {}
}, 4000);

function copiarPix() {
    const code = document.getElementById('pix-code-display').textContent.trim();
    navigator.clipboard.writeText(code).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.innerHTML = '<i class="fas fa-check mr-2"></i> Copiado!';
        btn.style.background = 'linear-gradient(135deg,#22c55e,#16a34a)';
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy mr-2"></i> Copiar Código PIX';
            btn.style.background = '';
        }, 2500);
    });
}
<?php endif; ?>
</script>

<?php require_once 'templates/footer.php'; ?>

