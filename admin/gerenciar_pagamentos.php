<?php
// admin/gerenciar_pagamentos.php - Gerenciar MÃ©todos de Pagamento
$page_title = 'Gerenciar Pagamentos';
require_once 'secure.php';
require_once '../includes/file_storage.php';

$fileStorage = new FileStorage();
$config = $fileStorage->getConfig();

// Processa formulÃ¡rio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'pix') {
        $chave_pix = trim($_POST['chave_pix'] ?? '');
        $nome_pix = trim($_POST['nome_pix'] ?? '');
        $cidade_pix = trim($_POST['cidade_pix'] ?? '');
        $pix_status = $_POST['pix_status'] ?? 'off';
        
        // ValidaÃ§Ãµes
        $erros_pix = [];
        
        if ($pix_status === 'on') {
            if (empty($chave_pix)) $erros_pix[] = 'A chave PIX Ã© obrigatÃ³ria quando o mÃ©todo estÃ¡ ativo.';
            if (empty($nome_pix)) $erros_pix[] = 'O nome do recebedor Ã© obrigatÃ³rio quando o mÃ©todo estÃ¡ ativo.';
            if (empty($cidade_pix)) $erros_pix[] = 'A cidade Ã© obrigatÃ³ria quando o mÃ©todo estÃ¡ ativo.';
        }
        
        if (empty($erros_pix)) {
            $resultado = $fileStorage->salvarConfig([
                'chave_pix' => $chave_pix,
                'nome_pix' => $nome_pix,
                'cidade_pix' => $cidade_pix,
                'pix_status' => $pix_status
            ]);
            
            if ($resultado) {
                $_SESSION['success_message'] = 'ConfiguraÃ§Ãµes de PIX atualizadas!';
                header('Location: gerenciar_pagamentos.php');
                exit();
            }
        }
    } elseif ($action === 'infinitepay') {
        $infinite_tag = trim($_POST['infinite_tag'] ?? '');
        $infinite_status = $_POST['infinite_status'] ?? 'off';
        // Remove @ ou $ se o usuÃ¡rio colocar
        $infinite_tag = str_replace(['@', '$'], '', $infinite_tag);
        
        $erros_infinite = [];
        if ($infinite_status === 'on' && empty($infinite_tag)) {
            $erros_infinite[] = 'A InfiniteTag Ã© obrigatÃ³ria quando o mÃ©todo estÃ¡ ativo.';
        }
        
        if (empty($erros_infinite)) {
            $resultado = $fileStorage->salvarConfig([
                'infinite_tag' => $infinite_tag,
                'infinite_status' => $infinite_status
            ]);
            
            if ($resultado) {
                $_SESSION['success_message'] = 'ConfiguraÃ§Ãµes de InfinitePay atualizadas!';
                header('Location: gerenciar_pagamentos.php');
                exit();
            }
        }
    }
}

require_once 'templates/header_admin.php';
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="admin-card rounded-2xl p-6">
        <h1 class="text-3xl font-bold text-white mb-2">
            <i class="fas fa-credit-card mr-2 text-admin-primary"></i>
            MÃ©todos de Pagamento
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
                        <label class="block text-sm font-medium text-white mb-2">InfiniteTag (Seu usuÃ¡rio)</label>
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
                        Salvar ConfiguraÃ§Ãµes
                    </button>
                </div>
            </form>

            <div class="mt-6 p-4 bg-admin-gray-800/50 rounded-lg">
                <h4 class="text-sm font-bold text-white mb-2">Como funciona?</h4>
                <ul class="text-xs text-admin-gray-400 space-y-1">
                    <li>â€¢ O cliente Ã© redirecionado para a InfinitePay.</li>
                    <li>â€¢ Suporta CartÃ£o de CrÃ©dito e PIX.</li>
                    <li>â€¢ ConfirmaÃ§Ã£o automÃ¡tica via Checkout Integrado.</li>
                </ul>
            </div>
        </div>

        <!-- PIX Manual -->
        <div class="admin-card rounded-xl p-6 border-t-4 transition-all <?= ($config['pix_status'] ?? 'off') === 'on' ? 'border-blue-500 opacity-100' : 'border-gray-600 opacity-60' ?>">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="pix">

                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-admin-primary/20 rounded-lg flex items-center justify-center text-admin-primary">
                            <i class="fas fa-qrcode text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">PIX Manual</h2>
                            <p class="text-sm text-admin-gray-400">TransferÃªncia Direta</p>
                        </div>
                    </div>

                    <!-- Switch Ativar/Desativar -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="pix_status" value="on" class="sr-only peer" <?= ($config['pix_status'] ?? 'off') === 'on' ? 'checked' : '' ?>>
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-medium text-admin-gray-400 peer-checked:text-blue-500">Status</span>
                    </label>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Chave PIX</label>
                        <input 
                            type="text" 
                            name="chave_pix" 
                            value="<?= htmlspecialchars($config['chave_pix'] ?? '') ?>"
                            class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-admin-primary"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Nome do Recebedor</label>
                        <input 
                            type="text" 
                            name="nome_pix" 
                            value="<?= htmlspecialchars($config['nome_pix'] ?? '') ?>"
                            class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-admin-primary"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Cidade</label>
                        <input 
                            type="text" 
                            name="cidade_pix" 
                            value="<?= htmlspecialchars($config['cidade_pix'] ?? '') ?>"
                            class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-admin-primary"
                        >
                    </div>

                    <button type="submit" class="w-full bg-admin-primary hover:bg-blue-600 text-white font-bold py-3 rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i>
                        Salvar ConfiguraÃ§Ãµes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>
