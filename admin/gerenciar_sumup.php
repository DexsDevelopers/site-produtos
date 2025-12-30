<?php
// admin/gerenciar_sumup.php - Gerenciar Configurações SumUp
$page_title = 'Gerenciar SumUp';
require_once 'secure.php';
require_once '../includes/sumup_api.php';

$sumup = new SumUpAPI($pdo);
$mensagem = '';
$tipo_mensagem = '';

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_key = trim($_POST['api_key'] ?? '');
    $merchant_code = trim($_POST['merchant_code'] ?? '');
    
    // Validações
    $erros = [];
    
    if (empty($api_key)) {
        $erros[] = 'A API Key é obrigatória.';
    }
    
    if (empty($merchant_code)) {
        $erros[] = 'O Merchant Code é obrigatório.';
    }
    
    if (empty($erros)) {
        if ($sumup->saveCredentials($api_key, $merchant_code)) {
            $mensagem = 'Configurações da SumUp salvas com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao salvar configurações. Verifique os logs.';
            $tipo_mensagem = 'error';
        }
    } else {
        $mensagem = implode('<br>', $erros);
        $tipo_mensagem = 'error';
    }
}

// Obtém credenciais atuais
$credenciais = $sumup->getCredentials();
$api_key_atual = $credenciais['api_key'];
$merchant_code_atual = $credenciais['merchant_code'];

require_once 'templates/header_admin.php';
?>

<div class="w-full max-w-4xl mx-auto">
    <h1 class="text-3xl font-black text-white mb-8">
        <i class="fas fa-credit-card mr-3"></i>
        Configurações SumUp
    </h1>
    
    <div class="admin-card rounded-xl p-8">
        <?php if ($mensagem): ?>
            <div class="mb-6 p-4 rounded-lg text-center <?= $tipo_mensagem === 'success' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30' ?>">
                <i class="fas <?= $tipo_mensagem === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                <?= $mensagem ?>
            </div>
        <?php endif; ?>
        
        <!-- Informações Atuais -->
        <?php if ($sumup->isConfigured()): ?>
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg">
            <h3 class="text-lg font-semibold text-white mb-3">
                <i class="fas fa-check-circle mr-2 text-green-400"></i>
                Configuração Atual
            </h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-admin-gray-400">Status:</span>
                    <span class="text-green-400 font-semibold">
                        <i class="fas fa-check-circle mr-1"></i>
                        Configurado e Ativo
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-admin-gray-400">API Key:</span>
                    <span class="text-white font-mono text-xs break-all">
                        <?= !empty($api_key_atual) ? substr($api_key_atual, 0, 20) . '...' . substr($api_key_atual, -10) : 'Não configurada' ?>
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-admin-gray-400">Merchant Code:</span>
                    <span class="text-white font-mono"><?= htmlspecialchars($merchant_code_atual) ?></span>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
            <p class="text-yellow-400 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                SumUp não está configurada. Configure as credenciais abaixo para habilitar pagamentos via cartão.
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Instruções -->
        <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
            <h3 class="text-lg font-semibold text-white mb-3">
                <i class="fas fa-info-circle mr-2 text-blue-400"></i>
                Como obter suas credenciais SumUp
            </h3>
            <ol class="list-decimal list-inside text-white/70 space-y-2 text-sm ml-2">
                <li>Acesse o <a href="https://www.sumup.com/en-us/developer-signup/" target="_blank" class="text-blue-400 hover:text-blue-300 underline font-semibold">portal de desenvolvedores da SumUp</a></li>
                <li>Crie uma conta de desenvolvedor ou faça login na sua conta existente</li>
                <li>No painel de controle, acesse a seção de API Keys</li>
                <li>Gere uma nova chave de API (use <code class="bg-admin-gray-800 px-1 rounded">sk_live_...</code> para produção)</li>
                <li>Copie a <strong>API Key</strong> e o <strong>Merchant Code</strong></li>
                <li>Cole as credenciais nos campos abaixo e salve</li>
            </ol>
            <div class="mt-4 p-3 bg-admin-gray-800/50 rounded border border-admin-gray-700">
                <p class="text-xs text-yellow-400">
                    <i class="fas fa-lightbulb mr-2"></i>
                    <strong>Dica:</strong> Use chaves de teste (<code>sk_test_...</code>) para desenvolvimento e chaves de produção (<code>sk_live_...</code>) apenas quando estiver pronto para receber pagamentos reais.
                </p>
            </div>
        </div>
        
        <!-- Formulário -->
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="api_key" class="block text-sm font-medium text-admin-gray-300 mb-2">
                    <i class="fas fa-key mr-2"></i>
                    API Key <span class="text-red-400">*</span>
                </label>
                <input 
                    type="password" 
                    name="api_key" 
                    id="api_key"
                    value="<?= htmlspecialchars($api_key_atual) ?>"
                    required 
                    class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none font-mono text-sm"
                    placeholder="sk_live_..."
                    autocomplete="off"
                >
                <div class="mt-2 flex items-center gap-2">
                    <input type="checkbox" id="show_api_key" onchange="toggleApiKeyVisibility()" class="cursor-pointer">
                    <label for="show_api_key" class="text-xs text-admin-gray-400 cursor-pointer">Mostrar API Key</label>
                </div>
                <p class="mt-1 text-xs text-admin-gray-400">
                    Chave de API fornecida pela SumUp. Use <code class="bg-admin-gray-800 px-1 rounded">sk_live_...</code> para pagamentos reais ou <code class="bg-admin-gray-800 px-1 rounded">sk_test_...</code> para testes.
                </p>
            </div>
            
            <div>
                <label for="merchant_code" class="block text-sm font-medium text-admin-gray-300 mb-2">
                    <i class="fas fa-store mr-2"></i>
                    Merchant Code <span class="text-red-400">*</span>
                </label>
                <input 
                    type="text" 
                    name="merchant_code" 
                    id="merchant_code"
                    value="<?= htmlspecialchars($merchant_code_atual) ?>"
                    required 
                    class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none font-mono"
                    placeholder="SEU_MERCHANT_CODE"
                    autocomplete="off"
                >
                <p class="mt-1 text-xs text-admin-gray-400">
                    Código único do comerciante registrado na SumUp. Este código identifica sua conta na plataforma.
                </p>
            </div>
            
            <button 
                type="submit" 
                class="w-full mt-8 bg-admin-primary hover:bg-blue-600 text-white font-bold text-lg py-4 rounded-lg transition-colors shadow-lg hover:shadow-xl"
            >
                <i class="fas fa-save mr-2"></i>
                Salvar Configurações
            </button>
        </form>
        
        <div class="mt-8 pt-8 border-t border-admin-gray-700">
            <h3 class="text-lg font-semibold text-white mb-4">Documentação</h3>
            <ul class="space-y-2 text-sm text-admin-gray-300">
                <li>
                    <a href="https://developer.sumup.com/api/merchants/get/" target="_blank" class="text-blue-400 hover:text-blue-300 underline">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Documentação da API SumUp
                    </a>
                </li>
                <li>
                    <a href="https://developer.sumup.com/online-payments/guides/single-payment/" target="_blank" class="text-blue-400 hover:text-blue-300 underline">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Guia de Integração de Pagamentos
                    </a>
                </li>
                <li>
                    <a href="https://js.sumup.com/" target="_blank" class="text-blue-400 hover:text-blue-300 underline">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        SumUp Payment Widget
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
function toggleApiKeyVisibility() {
    const apiKeyInput = document.getElementById('api_key');
    const showCheckbox = document.getElementById('show_api_key');
    
    if (showCheckbox.checked) {
        apiKeyInput.type = 'text';
    } else {
        apiKeyInput.type = 'password';
    }
}
</script>

<?php require_once 'templates/footer_admin.php'; ?>

