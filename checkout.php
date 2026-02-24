<?php
// checkout.php - Checkout Completo com Endereço e Pagamento
session_start();
require_once 'config.php';
require_once 'templates/header.php';

// Verifica se há itens no carrinho
if (empty($_SESSION['carrinho'])) {
    header('Location: carrinho.php');
    exit();
}

$carrinho_itens = $_SESSION['carrinho'];
$total_itens = 0;
$total_preco = 0;

foreach ($carrinho_itens as $item) {
    $total_itens += $item['quantidade'];
    $total_preco += $item['preco'] * $item['quantidade'];
}

// Verifica status dos métodos de pagamento no Banco de Dados
$pix_status = 'off';
$infinite_status = 'off';
try {
    $stmt_config = $pdo->query("SELECT chave, valor FROM configuracoes");
    $configs_db = $stmt_config->fetchAll(PDO::FETCH_KEY_PAIR);
    $pix_status = $configs_db['pix_status'] ?? 'off';
    $infinite_status = $configs_db['infinite_status'] ?? 'off';
} catch (Exception $e) {
    // Caso a tabela ainda não exista, mantemos por padrão ligado para não travar a venda
    $pix_status = 'on';
    $infinite_status = 'on';
}

// Busca dados do usuário para pre-preencher o checkout
$user_data = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT whatsapp, cep, endereco, numero, complemento, bairro, cidade, estado FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}
?>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <h1 class="text-3xl md:text-4xl font-black text-white mb-8">Finalizar Compra</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Formulário de Endereço e Contato -->
            <div class="space-y-6">
                <div class="bg-brand-black border border-brand-gray-light rounded-2xl p-8">
                    <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                        <i class="fas fa-truck text-brand-red"></i>
                        Informações de Entrega
                    </h2>

                    <style>
                        #checkout-form input {
                            background-color: #111 !important;
                            color: #fff !important;
                            border: 1px solid #333 !important;
                        }
                        #checkout-form input::placeholder {
                            color: #666 !important;
                        }
                    </style>

                    <form id="checkout-form" method="POST" action="checkout_pix.php" class="space-y-4">
                        <!-- Contato -->
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-brand-gray-text uppercase mb-2">WhatsApp / Telefone</label>
                                <input type="text" name="whatsapp" id="whatsapp" required value="<?= htmlspecialchars($user_data['whatsapp'] ?? '') ?>" placeholder="(00) 00000-0000"
                                    class="w-full rounded-xl p-4 transition-all focus:border-brand-red">
                            </div>
                        </div>

                        <!-- CEP e Endereço -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-brand-gray-text uppercase mb-2">CEP</label>
                                <div class="relative">
                                    <input type="text" name="cep" id="cep" required maxlength="9" value="<?= htmlspecialchars($user_data['cep'] ?? '') ?>" placeholder="00000-000"
                                        class="w-full rounded-xl p-4 transition-all focus:border-brand-red">
                                    <div id="cep-loading" class="hidden absolute right-4 top-1/2 -translate-y-1/2">
                                        <i class="fas fa-spinner fa-spin text-brand-red"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-sm font-semibold text-brand-gray-text uppercase mb-2">Estado</label>
                                <input type="text" name="estado" id="estado" required maxlength="2" value="<?= htmlspecialchars($user_data['estado'] ?? '') ?>" placeholder="UF"
                                    class="w-full rounded-xl p-4 transition-all focus:border-brand-red text-center">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-brand-gray-text uppercase mb-2">Endereço</label>
                            <input type="text" name="endereco" id="endereco" required value="<?= htmlspecialchars($user_data['endereco'] ?? '') ?>" placeholder="Nome da rua/avenida"
                                class="w-full rounded-xl p-4 transition-all focus:border-brand-red">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-brand-gray-text uppercase mb-2">Número</label>
                                <input type="text" name="numero" id="numero" required value="<?= htmlspecialchars($user_data['numero'] ?? '') ?>" placeholder="123"
                                    class="w-full rounded-xl p-4 transition-all focus:border-brand-red">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-brand-gray-text uppercase mb-2">Complemento (Opcional)</label>
                                <input type="text" name="complemento" id="complemento" value="<?= htmlspecialchars($user_data['complemento'] ?? '') ?>" placeholder="Apto, Bloco, etc."
                                    class="w-full rounded-xl p-4 transition-all focus:border-brand-red">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-brand-gray-text uppercase mb-2">Bairro</label>
                                <input type="text" name="bairro" id="bairro" required value="<?= htmlspecialchars($user_data['bairro'] ?? '') ?>" placeholder="Bairro"
                                    class="w-full rounded-xl p-4 transition-all focus:border-brand-red">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-brand-gray-text uppercase mb-2">Cidade</label>
                                <input type="text" name="cidade" id="cidade" required value="<?= htmlspecialchars($user_data['cidade'] ?? '') ?>" placeholder="Cidade"
                                    class="w-full rounded-xl p-4 transition-all focus:border-brand-red">
                            </div>
                        </div>

                        <input type="hidden" name="metodo_pagamento" id="metodo_pagamento" value="pix">

                        <div class="pt-6">
                            <h3 class="text-xl font-bold text-white mb-4">Escolha o Metodo de Pagamento</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <?php if ($pix_status === 'on'): ?>
                                <button type="button" onclick="submeterCheckout('pix')"
                                    class="flex flex-col items-center justify-center gap-2 p-6 rounded-xl border-2 border-brand-red/30 bg-brand-red/5 hover:bg-brand-red/10 hover:border-brand-red transition-all group">
                                    <i class="fas fa-qrcode text-3xl text-brand-red"></i>
                                    <span class="font-bold text-white">PIX MANUAL</span>
                                    <span class="text-xs text-brand-gray-text">Envio de comprovante</span>
                                </button>
                                <?php endif; ?>

                                <?php if ($infinite_status === 'on'): ?>
                                <button type="button" onclick="submeterCheckout('infinitepay')"
                                    class="flex flex-col items-center justify-center gap-2 p-6 rounded-xl border-2 border-white/5 bg-white/5 hover:bg-white/10 hover:border-green-500 transition-all group">
                                    <i class="fas fa-credit-card text-3xl text-green-500"></i>
                                    <span class="font-bold text-white">CARTÃO / PIX</span>
                                    <span class="text-xs text-brand-gray-text">Aprovação imediata</span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <script>
                document.getElementById('cep').addEventListener('input', e => {
                    let v = e.target.value.replace(/\D/g, "");
                    if (v.length > 5) v = v.slice(0, 5) + "-" + v.slice(5, 8);
                    e.target.value = v;
                    if (v.length === 9) buscarCEP(v);
                });

                document.getElementById('whatsapp').addEventListener('input', e => {
                    let v = e.target.value.replace(/\D/g, "");
                    if (v.length > 11) v = v.slice(0, 11);
                    if (v.length > 10) {
                        v = "(" + v.slice(0, 2) + ") " + v.slice(2, 7) + "-" + v.slice(7);
                    } else if (v.length > 2) {
                        v = "(" + v.slice(0, 2) + ") " + v.slice(2);
                    }
                    e.target.value = v;
                });

                async function buscarCEP(cep) {
                    const cleanCep = cep.replace(/\D/g, "");
                    const loader = document.getElementById("cep-loading");
                    loader.classList.remove("hidden");
                    try {
                        const response = await fetch(`https://viacep.com.br/ws/${cleanCep}/json/`);
                        const data = await response.json();
                        if (!data.erro) {
                            document.getElementById("endereco").value = data.logradouro;
                            document.getElementById("bairro").value = data.bairro;
                            document.getElementById("cidade").value = data.localidade;
                            document.getElementById("estado").value = data.uf;
                            document.getElementById("numero").focus();
                        }
                    } catch (e) {
                    } finally {
                        loader.classList.add("hidden");
                    }
                }

                function submeterCheckout(metodo) {
                    const form = document.getElementById("checkout-form");
                    const metodoInput = document.getElementById("metodo_pagamento");
                    if (!form.reportValidity()) return;
                    metodoInput.value = metodo;
                    form.action = (metodo === "infinitepay") ? "checkout_infinitepay.php" : "checkout_pix.php";
                    form.submit();
                }
                </script>
            </div>

            <!-- Resumo do Pedido -->
            <div class="lg:col-span-1">
                <div class="bg-brand-gray/30 rounded-xl p-6 sticky top-24">
                    <h3 class="text-xl font-bold text-white mb-6 uppercase tracking-wider">Itens do Pedido</h3>
                    
                    <div class="space-y-4 mb-6 max-h-96 overflow-y-auto pr-2">
                        <?php foreach ($carrinho_itens as $item): ?>
                        <div class="flex items-center gap-4 bg-white/5 p-3 rounded-xl border border-white/5">
                            <img src="<?= htmlspecialchars($item['imagem'])?>" class="w-16 h-16 object-cover rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-bold text-white"><?= htmlspecialchars($item['nome'])?></p>
                                <?php if (!empty($item['tamanho_valor'])): ?>
                                <p class="text-[10px] text-brand-red uppercase font-black">TAM: <?= htmlspecialchars($item['tamanho_valor'])?></p>
                                <?php endif; ?>
                                <p class="text-xs text-brand-gray-text mt-1"><?= $item['quantidade']?>x <?= formatarPreco($item['preco'])?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="space-y-4 border-t border-brand-gray-light pt-6">
                        <div class="flex justify-between">
                            <span class="text-brand-gray-text">Subtotal</span>
                            <span class="text-white"><?= formatarPreco($total_preco)?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-brand-gray-text">Entrega</span>
                            <span class="text-green-400 font-bold">GRÁTIS</span>
                        </div>
                        <div class="border-t border-brand-gray-light pt-4 flex justify-between items-center">
                            <span class="text-white font-black text-lg">TOTAL</span>
                            <span class="text-brand-red font-black text-2xl"><?= formatarPreco($total_preco)?></span>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3">
                        <a href="carrinho.php" class="w-full py-4 text-center bg-brand-gray/20 text-white rounded-xl font-bold hover:bg-brand-gray/40 transition-all">
                            VOLTAR AO CARRINHO
                        </a>
                        <div class="flex items-center justify-center gap-2 text-[10px] text-brand-gray-text uppercase font-bold">
                            <i class="fas fa-lock"></i> Ambiente 100% Seguro
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
