<?php
// checkout_produto.php - Endereço de entrega para compra direta ("Comprar Agora")
session_start();
require_once 'config.php';

// ── POST: salvar produto no carrinho + endereço na sessão → redirecionar ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id  = (int)($_POST['produto_id']    ?? 0);
    $tamanho_id  = (int)($_POST['tamanho_id']    ?? 0);
    $tamanho_val = trim($_POST['tamanho_valor']  ?? '');
    $quantidade  = max(1, (int)($_POST['quantidade'] ?? 1));
    $metodo      = in_array($_POST['metodo_pagamento'] ?? '', ['pix','infinitepay'])
                   ? $_POST['metodo_pagamento'] : 'pix';

    if (!$produto_id) { header('Location: index.php'); exit(); }

    $stmt = $pdo->prepare("SELECT id, nome, preco, imagem, tipo FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$produto) { header('Location: index.php'); exit(); }

    if (empty($tamanho_val) && $tamanho_id > 0) {
        $stmt_tam = $pdo->prepare("SELECT valor FROM tamanhos WHERE id = ?");
        $stmt_tam->execute([$tamanho_id]);
        $tamanho_val = $stmt_tam->fetchColumn() ?: '';
    }

    $cart_key = $produto_id . ($tamanho_id > 0 ? '_' . $tamanho_id : '');
    $_SESSION['carrinho'] = [
        $cart_key => [
            'id'            => $produto['id'],
            'nome'          => $produto['nome'],
            'preco'         => $produto['preco'],
            'imagem'        => $produto['imagem'],
            'tamanho_id'    => $tamanho_id ?: null,
            'tamanho_valor' => $tamanho_val ?: null,
            'quantidade'    => $quantidade,
        ]
    ];

    $_SESSION['checkout_address'] = [
        'whatsapp'    => trim($_POST['whatsapp']    ?? ''),
        'cep'         => trim($_POST['cep']         ?? ''),
        'endereco'    => trim($_POST['endereco']    ?? ''),
        'numero'      => trim($_POST['numero']      ?? ''),
        'complemento' => trim($_POST['complemento'] ?? ''),
        'bairro'      => trim($_POST['bairro']      ?? ''),
        'cidade'      => trim($_POST['cidade']      ?? ''),
        'estado'      => trim($_POST['estado']      ?? ''),
    ];

    header('Location: ' . ($metodo === 'infinitepay' ? 'checkout_infinitepay.php' : 'checkout_pix.php'));
    exit();
}

// ── GET: exibir formulário ──
$produto_id  = (int)($_GET['produto_id']  ?? 0);
$tamanho_id  = (int)($_GET['tamanho_id']  ?? 0);
$quantidade  = max(1, (int)($_GET['quantidade'] ?? 1));

if (!$produto_id) { header('Location: index.php'); exit(); }

$stmt = $pdo->prepare("SELECT id, nome, preco, imagem, tipo, preco_antigo FROM produtos WHERE id = ?");
$stmt->execute([$produto_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$produto) { header('Location: index.php'); exit(); }

$tamanho_valor = '';
if ($tamanho_id > 0) {
    $stmt_tam = $pdo->prepare("SELECT valor FROM tamanhos WHERE id = ?");
    $stmt_tam->execute([$tamanho_id]);
    $tamanho_valor = $stmt_tam->fetchColumn() ?: '';
}

$user_data = [];
if (!empty($_SESSION['user_id'])) {
    $stmt_u = $pdo->prepare("SELECT whatsapp, cep, endereco, numero, complemento, bairro, cidade, estado FROM usuarios WHERE id = ?");
    $stmt_u->execute([$_SESSION['user_id']]);
    $user_data = $stmt_u->fetch(PDO::FETCH_ASSOC) ?: [];
}

$pix_status      = 'off';
$infinite_status = 'off';
try {
    $stmt_cfg = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('pix_status','infinite_status')");
    $cfg = $stmt_cfg->fetchAll(PDO::FETCH_KEY_PAIR);
    $pix_status      = $cfg['pix_status']      ?? 'off';
    $infinite_status = $cfg['infinite_status'] ?? 'off';
} catch (Exception $e) {
    $pix_status = 'on'; $infinite_status = 'on';
}

$page_title = 'Finalizar Compra — Endereço de Entrega';
require_once 'templates/header.php';
?>

<style>
#cp-form input, #cp-form select {
    background-color: #111 !important;
    color: #fff !important;
    border: 1px solid #333 !important;
}
#cp-form input::placeholder { color: #666 !important; }
#cp-form input:focus { border-color: #e11d48 !important; outline: none; }
.pay-btn { transition: all .2s; cursor: pointer; }
.pay-btn.selected { border-color: #e11d48 !important; background: rgba(225,29,72,.12) !important; }
.step-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 50%;
    background: #e11d48; color: #fff; font-size: .75rem; font-weight: 800;
    flex-shrink: 0;
}
</style>

<div class="w-full max-w-6xl mx-auto px-4 pt-28 pb-20">
    <h1 class="text-3xl md:text-4xl font-black text-white mb-2">Finalizar Compra</h1>
    <p class="text-brand-gray-text mb-10 text-sm">Preencha o endereço de entrega para continuar.</p>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        <!-- ── Formulário ── -->
        <div class="lg:col-span-3">
            <form id="cp-form" method="POST" action="checkout_produto.php" class="space-y-6">
                <input type="hidden" name="produto_id"    value="<?= $produto_id ?>">
                <input type="hidden" name="tamanho_id"    value="<?= $tamanho_id ?>">
                <input type="hidden" name="tamanho_valor" value="<?= htmlspecialchars($tamanho_valor) ?>">
                <input type="hidden" name="quantidade"    value="<?= $quantidade ?>">
                <input type="hidden" name="metodo_pagamento" id="metodo_pagamento" value="">

                <!-- Bloco 1: Contato -->
                <div class="bg-black border border-white/10 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-3">
                        <span class="step-badge">1</span> Contato
                    </h2>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">WhatsApp / Telefone *</label>
                        <input type="text" name="whatsapp" id="whatsapp" required
                               value="<?= htmlspecialchars($user_data['whatsapp'] ?? '') ?>"
                               placeholder="(51) 99999-9999"
                               class="w-full rounded-xl p-4 text-sm">
                    </div>
                </div>

                <!-- Bloco 2: Endereço -->
                <div class="bg-black border border-white/10 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-3">
                        <span class="step-badge">2</span> Endereço de Entrega
                    </h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">CEP *</label>
                                <div class="relative">
                                    <input type="text" name="cep" id="cep" required maxlength="9"
                                           value="<?= htmlspecialchars($user_data['cep'] ?? '') ?>"
                                           placeholder="00000-000" class="w-full rounded-xl p-4 text-sm">
                                    <div id="cep-loading" class="hidden absolute right-4 top-1/2 -translate-y-1/2">
                                        <i class="fas fa-spinner fa-spin text-red-500 text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">Estado *</label>
                                <input type="text" name="estado" id="estado" required maxlength="2"
                                       value="<?= htmlspecialchars($user_data['estado'] ?? '') ?>"
                                       placeholder="UF" class="w-full rounded-xl p-4 text-sm text-center">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">Rua / Avenida *</label>
                            <input type="text" name="endereco" id="endereco" required
                                   value="<?= htmlspecialchars($user_data['endereco'] ?? '') ?>"
                                   placeholder="Nome da rua ou avenida" class="w-full rounded-xl p-4 text-sm">
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">Número *</label>
                                <input type="text" name="numero" id="numero" required
                                       value="<?= htmlspecialchars($user_data['numero'] ?? '') ?>"
                                       placeholder="123" class="w-full rounded-xl p-4 text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">Complemento</label>
                                <input type="text" name="complemento" id="complemento"
                                       value="<?= htmlspecialchars($user_data['complemento'] ?? '') ?>"
                                       placeholder="Apto, Bloco, etc." class="w-full rounded-xl p-4 text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">Bairro *</label>
                                <input type="text" name="bairro" id="bairro" required
                                       value="<?= htmlspecialchars($user_data['bairro'] ?? '') ?>"
                                       placeholder="Bairro" class="w-full rounded-xl p-4 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">Cidade *</label>
                                <input type="text" name="cidade" id="cidade" required
                                       value="<?= htmlspecialchars($user_data['cidade'] ?? '') ?>"
                                       placeholder="Cidade" class="w-full rounded-xl p-4 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bloco 3: Pagamento -->
                <div class="bg-black border border-white/10 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-3">
                        <span class="step-badge">3</span> Forma de Pagamento
                    </h2>
                    <div class="grid grid-cols-1 <?= ($pix_status === 'on' && $infinite_status === 'on') ? 'md:grid-cols-2' : '' ?> gap-3" id="pay-options">
                        <?php if ($pix_status === 'on'): ?>
                        <button type="button" data-metodo="pix"
                                class="pay-btn flex items-center gap-4 p-5 rounded-xl border-2 border-white/10 bg-white/5 hover:border-red-500 text-left"
                                onclick="selecionarPagamento('pix', this)">
                            <i class="fas fa-qrcode text-2xl text-red-500 w-8 text-center"></i>
                            <div>
                                <div class="font-bold text-white">PIX</div>
                                <div class="text-xs text-gray-400">Aprovação instantânea</div>
                            </div>
                        </button>
                        <?php endif; ?>
                        <?php if ($infinite_status === 'on'): ?>
                        <button type="button" data-metodo="infinitepay"
                                class="pay-btn flex items-center gap-4 p-5 rounded-xl border-2 border-white/10 bg-white/5 hover:border-green-500 text-left"
                                onclick="selecionarPagamento('infinitepay', this)">
                            <i class="fas fa-credit-card text-2xl text-green-400 w-8 text-center"></i>
                            <div>
                                <div class="font-bold text-white">Cartão de Crédito</div>
                                <div class="text-xs text-gray-400">Parcelamento disponível</div>
                            </div>
                        </button>
                        <?php endif; ?>
                    </div>
                    <p id="pay-error" class="hidden text-red-400 text-xs mt-3">Selecione uma forma de pagamento.</p>
                </div>

                <!-- Botão de confirmação -->
                <button type="submit" id="btn-confirmar"
                        class="w-full py-5 bg-white text-black font-black text-base uppercase tracking-wider rounded-2xl hover:bg-gray-100 transition-all flex items-center justify-center gap-3 disabled:opacity-50">
                    <i class="fas fa-lock text-sm"></i>
                    <span id="btn-text">Confirmar Pedido</span>
                </button>

                <div class="flex items-center justify-center gap-6 text-xs text-gray-500">
                    <span><i class="fas fa-shield-alt mr-1"></i> Pagamento seguro</span>
                    <span><i class="fas fa-truck mr-1"></i> Frete grátis</span>
                    <span><i class="fas fa-sync-alt mr-1"></i> Trocas grátis</span>
                </div>
            </form>
        </div>

        <!-- ── Resumo do Pedido ── -->
        <div class="lg:col-span-2">
            <div class="bg-black border border-white/10 rounded-2xl p-6 sticky top-28">
                <h3 class="text-base font-bold text-white uppercase tracking-wider mb-5">Resumo do Pedido</h3>

                <div class="flex gap-4 bg-white/5 border border-white/5 rounded-xl p-4 mb-6">
                    <?php if (!empty($produto['imagem'])): ?>
                    <img src="<?= htmlspecialchars($produto['imagem']) ?>"
                         class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-white text-sm leading-snug"><?= htmlspecialchars($produto['nome']) ?></p>
                        <?php if ($tamanho_valor): ?>
                        <p class="text-[10px] text-red-400 uppercase font-black mt-1">TAM: <?= htmlspecialchars($tamanho_valor) ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-400 mt-1">Qtd: <?= $quantidade ?></p>
                    </div>
                </div>

                <div class="space-y-3 border-t border-white/10 pt-5">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Subtotal</span>
                        <span class="text-white"><?= formatarPreco($produto['preco'] * $quantidade) ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Entrega</span>
                        <span class="text-green-400 font-bold">GRÁTIS</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-white/10 pt-4">
                        <span class="font-black text-white text-lg">TOTAL</span>
                        <span class="font-black text-red-500 text-2xl"><?= formatarPreco($produto['preco'] * $quantidade) ?></span>
                    </div>
                </div>

                <a href="produto.php?id=<?= $produto_id ?>" class="mt-6 block text-center text-xs text-gray-500 hover:text-white transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar ao produto
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara CEP
document.getElementById('cep').addEventListener('input', e => {
    let v = e.target.value.replace(/\D/g, '');
    if (v.length > 5) v = v.slice(0,5) + '-' + v.slice(5,8);
    e.target.value = v;
    if (v.length === 9) buscarCEP(v);
});

// Máscara WhatsApp
document.getElementById('whatsapp').addEventListener('input', e => {
    let v = e.target.value.replace(/\D/g, '');
    if (v.length > 11) v = v.slice(0,11);
    if (v.length > 10)      v = '(' + v.slice(0,2) + ') ' + v.slice(2,7) + '-' + v.slice(7);
    else if (v.length > 6)  v = '(' + v.slice(0,2) + ') ' + v.slice(2,6) + '-' + v.slice(6);
    else if (v.length > 2)  v = '(' + v.slice(0,2) + ') ' + v.slice(2);
    e.target.value = v;
});

// Busca CEP via ViaCEP
async function buscarCEP(cep) {
    const loader = document.getElementById('cep-loading');
    loader.classList.remove('hidden');
    try {
        const r = await fetch('https://viacep.com.br/ws/' + cep.replace(/\D/g,'') + '/json/');
        const d = await r.json();
        if (!d.erro) {
            document.getElementById('endereco').value = d.logradouro;
            document.getElementById('bairro').value   = d.bairro;
            document.getElementById('cidade').value   = d.localidade;
            document.getElementById('estado').value   = d.uf;
            document.getElementById('numero').focus();
        }
    } catch(e) {}
    finally { loader.classList.add('hidden'); }
}

// Seleção de método de pagamento
function selecionarPagamento(metodo, btn) {
    document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('metodo_pagamento').value = metodo;
    document.getElementById('pay-error').classList.add('hidden');
}

// Validação antes de submeter
document.getElementById('cp-form').addEventListener('submit', function(e) {
    if (!document.getElementById('metodo_pagamento').value) {
        e.preventDefault();
        document.getElementById('pay-error').classList.remove('hidden');
        document.getElementById('pay-options').scrollIntoView({behavior:'smooth', block:'center'});
        return;
    }
    const btn = document.getElementById('btn-confirmar');
    btn.disabled = true;
    document.getElementById('btn-text').textContent = 'Processando...';
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
});

// Auto-selecionar PIX se for o único método
<?php if ($pix_status === 'on' && $infinite_status !== 'on'): ?>
document.querySelector('[data-metodo="pix"]').click();
<?php elseif ($infinite_status === 'on' && $pix_status !== 'on'): ?>
document.querySelector('[data-metodo="infinitepay"]').click();
<?php endif; ?>
</script>

<?php require_once 'templates/footer.php'; ?>
