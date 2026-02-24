<?php
// admin/gerenciar_pix.php - Gerenciar Chave PIX
$page_title = 'Gerenciar Chave PIX';
require_once 'secure.php';
require_once '../includes/file_storage.php';

$fileStorage = new FileStorage();
$config = $fileStorage->getConfig();

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chave_pix = trim($_POST['chave_pix'] ?? '');
    $nome_pix = trim($_POST['nome_pix'] ?? '');
    $cidade_pix = trim($_POST['cidade_pix'] ?? '');
    
    // Validações
    $erros = [];
    
    if (empty($chave_pix)) {
        $erros[] = 'A chave PIX é obrigatória.';
    }
    
    if (empty($nome_pix)) {
        $erros[] = 'O nome do recebedor é obrigatório.';
    }
    
    if (empty($cidade_pix)) {
        $erros[] = 'A cidade é obrigatória.';
    }
    
    // Valida formato da chave PIX
    if (!empty($chave_pix)) {
        // Remove caracteres especiais
        $chave_limpa = preg_replace('/[^0-9a-zA-Z@.-]/', '', $chave_pix);
        
        // Valida se é CPF, CNPJ, email, telefone ou chave aleatória
        $valido = false;
        
        // Email
        if (filter_var($chave_limpa, FILTER_VALIDATE_EMAIL)) {
            $valido = true;
        }
        // CPF (11 dígitos)
        elseif (preg_match('/^[0-9]{11}$/', $chave_limpa)) {
            $valido = true;
        }
        // CNPJ (14 dígitos)
        elseif (preg_match('/^[0-9]{14}$/', $chave_limpa)) {
            $valido = true;
        }
        // Telefone (10 ou 11 dígitos começando com +55)
        elseif (preg_match('/^\+55[0-9]{10,11}$/', $chave_limpa)) {
            $valido = true;
        }
        // Chave aleatória (UUID)
        elseif (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $chave_limpa)) {
            $valido = true;
        }
        
        if (!$valido) {
            $erros[] = 'Formato de chave PIX inválido. Use CPF, CNPJ, email, telefone (+5511999999999) ou chave aleatória.';
        }
    }
    
    if (empty($erros)) {
        $resultado = $fileStorage->salvarConfig([
            'chave_pix' => $chave_pix,
            'nome_pix' => $nome_pix,
            'cidade_pix' => $cidade_pix
        ]);
        
        if ($resultado) {
            $_SESSION['success_message'] = 'Chave PIX atualizada com sucesso! Todos os produtos agora usam esta chave.';
            header('Location: gerenciar_pix.php');
            exit();
        } else {
            $erros[] = 'Erro ao salvar configuração.';
        }
    }
}

require_once 'templates/header_admin.php';
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="admin-card rounded-2xl p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    <i class="fas fa-qrcode mr-2 text-admin-primary"></i>
                    Gerenciar Chave PIX
                </h1>
                <p class="text-admin-gray-400">
                    Configure a chave PIX que será usada em todos os produtos do site.
                </p>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-admin-success/20 border border-admin-success text-admin-success px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($erros)): ?>
        <div class="bg-admin-error/20 border border-admin-error text-admin-error px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <ul class="list-disc list-inside">
                <?php foreach ($erros as $erro): ?>
                    <li><?= htmlspecialchars($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulário -->
    <div class="admin-card rounded-xl p-6">
        <form method="POST" class="space-y-6">
            <!-- Chave PIX -->
            <div>
                <label for="chave_pix" class="block text-sm font-medium text-white mb-2">
                    <i class="fas fa-key mr-2 text-admin-primary"></i>
                    Chave PIX *
                </label>
                <input 
                    type="text" 
                    id="chave_pix" 
                    name="chave_pix" 
                    value="<?= htmlspecialchars($config['chave_pix'] ?? '') ?>"
                    placeholder="Ex: seu@email.com, 12345678900, +5511999999999"
                    class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-admin-primary transition-all"
                    required
                >
                <p class="mt-2 text-sm text-admin-gray-400">
                    Aceita: CPF, CNPJ, email, telefone (+5511999999999) ou chave aleatória (UUID)
                </p>
            </div>

            <!-- Nome do Recebedor -->
            <div>
                <label for="nome_pix" class="block text-sm font-medium text-white mb-2">
                    <i class="fas fa-user mr-2 text-admin-primary"></i>
                    Nome do Recebedor *
                </label>
                <input 
                    type="text" 
                    id="nome_pix" 
                    name="nome_pix" 
                    value="<?= htmlspecialchars($config['nome_pix'] ?? '') ?>"
                    placeholder="Nome completo ou razão social"
                    class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-admin-primary transition-all"
                    required
                    maxlength="100"
                >
            </div>

            <!-- Cidade -->
            <div>
                <label for="cidade_pix" class="block text-sm font-medium text-white mb-2">
                    <i class="fas fa-map-marker-alt mr-2 text-admin-primary"></i>
                    Cidade *
                </label>
                <input 
                    type="text" 
                    id="cidade_pix" 
                    name="cidade_pix" 
                    value="<?= htmlspecialchars($config['cidade_pix'] ?? '') ?>"
                    placeholder="Ex: São Paulo"
                    class="w-full bg-admin-gray-800 border border-admin-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-admin-primary transition-all"
                    required
                    maxlength="100"
                >
            </div>

            <!-- Informações Atuais -->
            <?php if (!empty($config['chave_pix'])): ?>
            <div class="bg-admin-gray-800/50 border border-admin-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-white mb-3">
                    <i class="fas fa-info-circle mr-2 text-admin-primary"></i>
                    Configuração Atual
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-admin-gray-400">Chave PIX:</span>
                        <span class="text-white font-mono"><?= htmlspecialchars($config['chave_pix']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-admin-gray-400">Nome:</span>
                        <span class="text-white"><?= htmlspecialchars($config['nome_pix']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-admin-gray-400">Cidade:</span>
                        <span class="text-white"><?= htmlspecialchars($config['cidade_pix']) ?></span>
                    </div>
                    <?php if (!empty($config['ultima_atualizacao'])): ?>
                    <div class="flex justify-between">
                        <span class="text-admin-gray-400">Ãšltima atualização:</span>
                        <span class="text-white"><?= date('d/m/Y H:i', strtotime($config['ultima_atualizacao'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Botões -->
            <div class="flex gap-4 pt-4">
                <button 
                    type="submit" 
                    class="flex-1 bg-admin-primary hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2"
                >
                    <i class="fas fa-save"></i>
                    Salvar Chave PIX
                </button>
                <a 
                    href="index.php" 
                    class="bg-admin-gray-700 hover:bg-admin-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2"
                >
                    <i class="fas fa-arrow-left"></i>
                    Voltar
                </a>
            </div>
        </form>
    </div>

    <!-- Aviso Importante -->
    <div class="bg-admin-warning/20 border border-admin-warning rounded-lg p-6">
        <h3 class="text-lg font-semibold text-admin-warning mb-3">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Informação Importante
        </h3>
        <ul class="space-y-2 text-sm text-admin-gray-300">
            <li>
                <i class="fas fa-check-circle mr-2 text-admin-success"></i>
                A chave PIX configurada aqui será aplicada a <strong>todos os produtos</strong> do site.
            </li>
            <li>
                <i class="fas fa-check-circle mr-2 text-admin-success"></i>
                Ao alterar a chave PIX, todos os produtos automaticamente usarão a nova chave.
            </li>
            <li>
                <i class="fas fa-check-circle mr-2 text-admin-success"></i>
                Certifique-se de que a chave PIX está correta antes de salvar.
            </li>
        </ul>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>

