<?php
// admin/gerenciar_pagamentos.php - Gerenciar Métodos de Pagamento
$page_title = 'Gerenciar Pagamentos';
require_once 'secure.php';

// Busca configurações atuais no Banco de Dados
try {
    $stmt = $pdo->query("SELECT chave, valor FROM configuracoes");
    $config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    $config = [];
}

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'pixghost') {
            $pixghost_api_key = trim($_POST['pixghost_api_key'] ?? '');
            $pix_status = $_POST['pix_status'] ?? 'off';

            $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
            $stmt->execute(['pixghost_api_key', $pixghost_api_key]);
            $stmt->execute(['pix_status', $pix_status]);

            $_SESSION['success_message'] = 'Configurações do PixGhost atualizadas!';
        } elseif ($action === 'infinitepay') {
            $infinite_tag = trim($_POST['infinite_tag'] ?? '');
            $infinite_status = $_POST['infinite_status'] ?? 'off';
            $infinite_tag = str_replace(['@', '$'], '', $infinite_tag);
            
            $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
            $stmt->execute(['infinite_tag', $infinite_tag]);
            $stmt->execute(['infinite_status', $infinite_status]);
            
            $_SESSION['success_message'] = 'Configurações de InfinitePay atualizadas!';
        }
        
        header('Location: gerenciar_pagamentos.php');
        exit();
    } catch (Exception $e) {
        $error_message = "Erro ao salvar: " . $e->getMessage();
    }
}

require_once 'templates/header_admin.php';
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="admin-card rounded-2xl p-6">
        <h1 class="text-3xl font-bold text-white mb-2">
            <i class="fas fa-credit-card mr-2 text-admin-primary"></i>
            Métodos de Pagamento
        </h1>
        <p class="text-admin-gray-400">Configure como seus clientes podem pagar.</p>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-admin-success/20 border border-admin-success text-admin-success px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- InfinitePay -->
        <div class="admin-card rounded-xl p-6 border-t-4 transition-all <?= ($config['infinite_status'] ?? 'off') === 'on' ? 'border-green-500 opacity-100' : 'border-gray-600 opacity-60' ?>">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="infinitepay">
                
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-black rounded-lg flex items-center justify-center">
                            <img src="https://infinitepay.io/favicon.ico" alt="InfinitePay" class="w-8 h-8">
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">InfinitePay</h2>
                            <p class="text-sm text-admin-gray-400">Checkout Integrado</p>
                        </div>
                    </div>
                    
                    <!-- Switch Ativar/Desativar -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="infinite_status" value="on" class="sr-only peer" <?= ($config['infinite_status'] ?? 'off') === 'on' ? 'checked' : '' ?>>
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        <span class="ml-3 text-sm font-medium text-admin-gray-400 peer-checked:text-green-500">Status</span>
                    </label>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">InfiniteTag (Seu usuário)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-admin-gray-400">$</span>
                            <input 
                                type="text" 
                                name="infinite_tag" 
                                value="<?= htmlspecialchars($config['infinite_tag'] ?? '') ?>"
                                placeholder="exemplo"
                                class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg pl-8 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all"
                            >
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i>
                        Salvar Configurações
                    </button>
                </div>
            </form>

            <div class="mt-6 p-4 bg-admin-gray-800/50 rounded-lg">
                <h4 class="text-sm font-bold text-white mb-2">Como funciona?</h4>
                <ul class="text-xs text-admin-gray-400 space-y-1">
                    <li>â€¢ O cliente é redirecionado para a InfinitePay.</li>
                    <li>â€¢ Suporta Cartão de Crédito e PIX.</li>
                    <li>â€¢ Confirmação automática via Checkout Integrado.</li>
                </ul>
            </div>
        </div>

        <!-- PixGhost (PIX Automático) -->
        <div class="admin-card rounded-xl p-6 border-t-4 transition-all <?= ($config['pix_status'] ?? 'off') === 'on' ? 'border-teal-500 opacity-100' : 'border-gray-600 opacity-60' ?>">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="pixghost">

                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background:rgba(50,188,173,.15);">
                            <i class="fas fa-bolt text-2xl" style="color:#32BCAD;"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">PixGhost</h2>
                            <p class="text-sm text-admin-gray-400">PIX Automático com QR Code</p>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="pix_status" value="on" class="sr-only peer" <?= ($config['pix_status'] ?? 'off') === 'on' ? 'checked' : '' ?>>
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all" style="--tw-peer-checked-bg:#32BCAD;" peer-checked:bg-teal-500></div>
                        <span class="ml-3 text-sm font-medium text-admin-gray-400 peer-checked:text-teal-400">Status</span>
                    </label>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">API Key <span class="text-admin-gray-400 font-normal">(Configurações → Desenvolvedor no PixGhost)</span></label>
                        <input
                            type="password"
                            name="pixghost_api_key"
                            value="<?= htmlspecialchars($config['pixghost_api_key'] ?? '') ?>"
                            placeholder="Sua API Key do PixGhost"
                            class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-teal-500 font-mono"
                        >
                    </div>

                    <button type="submit" class="w-full text-white font-bold py-3 rounded-lg transition-colors flex items-center justify-center gap-2" style="background:#32BCAD;">
                        <i class="fas fa-save"></i>
                        Salvar Configurações
                    </button>
                </div>
            </form>

            <div class="mt-6 p-4 bg-admin-gray-800/50 rounded-lg">
                <h4 class="text-sm font-bold text-white mb-2">Como funciona?</h4>
                <ul class="text-xs text-admin-gray-400 space-y-1">
                    <li>• QR Code e Copia e Cola gerados automaticamente.</li>
                    <li>• Confirmação automática via webhook.</li>
                    <li>• Webhook: <code class="text-teal-400">/webhook_pixghost.php</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>
