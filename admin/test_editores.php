<?php
// admin/test_editores.php - Teste das Funcionalidades dos Editores
require_once 'secure.php';
require_once 'templates/header_admin.php';

// Verificar se as tabelas têm as colunas necessárias
function verificarColunas($pdo, $tabela, $colunas) {
    try {
        $stmt = $pdo->query("DESCRIBE $tabela");
        $colunas_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $colunas_faltando = array_diff($colunas, $colunas_existentes);
        
        return [
            'existentes' => $colunas_existentes,
            'faltando' => $colunas_faltando,
            'status' => empty($colunas_faltando) ? 'ok' : 'faltando'
        ];
    } catch (Exception $e) {
        return [
            'existentes' => [],
            'faltando' => $colunas,
            'status' => 'erro',
            'erro' => $e->getMessage()
        ];
    }
}

// Colunas necessárias para categorias
$colunas_categorias = [
    'id', 'nome', 'descricao', 'ordem', 'icone', 'cor', 
    'ativa', 'destaque', 'meta_title', 'meta_description',
    'data_criacao', 'data_atualizacao'
];

// Colunas necessárias para banners
$colunas_banners = [
    'id', 'titulo', 'subtitulo', 'link', 'texto_botao', 'tipo',
    'posicao', 'ativo', 'nova_aba', 'imagem', 'data_criacao', 'data_atualizacao'
];

$status_categorias = verificarColunas($pdo, 'categorias', $colunas_categorias);
$status_banners = verificarColunas($pdo, 'banners', $colunas_banners);

// Testar funcionalidades
$testes = [];

// Teste 1: Verificar se as tabelas existem
try {
    $pdo->query("SELECT COUNT(*) FROM categorias");
    $testes[] = ['nome' => 'Tabela Categorias', 'status' => 'ok', 'mensagem' => 'Tabela existe'];
} catch (Exception $e) {
    $testes[] = ['nome' => 'Tabela Categorias', 'status' => 'erro', 'mensagem' => $e->getMessage()];
}

try {
    $pdo->query("SELECT COUNT(*) FROM banners");
    $testes[] = ['nome' => 'Tabela Banners', 'status' => 'ok', 'mensagem' => 'Tabela existe'];
} catch (Exception $e) {
    $testes[] = ['nome' => 'Tabela Banners', 'status' => 'erro', 'mensagem' => $e->getMessage()];
}

// Teste 2: Verificar permissões de upload
$upload_dir = '../assets/uploads/';
$testes[] = [
    'nome' => 'Diretório de Upload',
    'status' => is_dir($upload_dir) && is_writable($upload_dir) ? 'ok' : 'erro',
    'mensagem' => is_dir($upload_dir) ? 
        (is_writable($upload_dir) ? 'Diretório existe e é gravável' : 'Diretório existe mas não é gravável') :
        'Diretório não existe'
];

// Teste 3: Verificar arquivos necessários
$arquivos_necessarios = [
    'editar_categoria.php',
    'editar_banner.php',
    'processa_categoria_avancado.php',
    'processa_banner_avancado.php',
    'gerenciar_categorias_avancado.php',
    'gerenciar_banners_avancado.php'
];

foreach ($arquivos_necessarios as $arquivo) {
    $testes[] = [
        'nome' => "Arquivo $arquivo",
        'status' => file_exists($arquivo) ? 'ok' : 'erro',
        'mensagem' => file_exists($arquivo) ? 'Arquivo existe' : 'Arquivo não encontrado'
    ];
}

// Teste 4: Verificar sessão de admin
$testes[] = [
    'nome' => 'Sessão de Admin',
    'status' => isset($_SESSION['user_id']) && isset($_SESSION['user_nome']) ? 'ok' : 'erro',
    'mensagem' => isset($_SESSION['user_id']) ? 'Usuário logado' : 'Usuário não logado'
];
?>

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Teste dos Editores</h1>
        <p class="text-admin-gray-400 mt-2">Verificação das funcionalidades de edição de categorias e banners</p>
    </div>

    <!-- Status Geral -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Status Categorias -->
        <div class="admin-card rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Categorias</h3>
                <span class="px-3 py-1 rounded-full text-sm <?= $status_categorias['status'] === 'ok' ? 'bg-admin-success/20 text-admin-success' : 'bg-admin-error/20 text-admin-error' ?>">
                    <?= $status_categorias['status'] === 'ok' ? 'OK' : 'ERRO' ?>
                </span>
            </div>
            
            <?php if ($status_categorias['status'] === 'ok'): ?>
                <p class="text-admin-gray-400 text-sm">Todas as colunas necessárias estão presentes</p>
            <?php else: ?>
                <div class="space-y-2">
                    <p class="text-admin-error text-sm font-medium">Colunas faltando:</p>
                    <ul class="text-admin-gray-400 text-sm space-y-1">
                        <?php foreach ($status_categorias['faltando'] as $coluna): ?>
                            <li>• <?= $coluna ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="text-admin-warning text-sm mt-2">
                        Execute o arquivo <code>update_database.sql</code> para adicionar as colunas faltantes.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Status Banners -->
        <div class="admin-card rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Banners</h3>
                <span class="px-3 py-1 rounded-full text-sm <?= $status_banners['status'] === 'ok' ? 'bg-admin-success/20 text-admin-success' : 'bg-admin-error/20 text-admin-error' ?>">
                    <?= $status_banners['status'] === 'ok' ? 'OK' : 'ERRO' ?>
                </span>
            </div>
            
            <?php if ($status_banners['status'] === 'ok'): ?>
                <p class="text-admin-gray-400 text-sm">Todas as colunas necessárias estão presentes</p>
            <?php else: ?>
                <div class="space-y-2">
                    <p class="text-admin-error text-sm font-medium">Colunas faltando:</p>
                    <ul class="text-admin-gray-400 text-sm space-y-1">
                        <?php foreach ($status_banners['faltando'] as $coluna): ?>
                            <li>• <?= $coluna ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="text-admin-warning text-sm mt-2">
                        Execute o arquivo <code>update_database.sql</code> para adicionar as colunas faltantes.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Testes Detalhados -->
    <div class="admin-card rounded-xl p-6">
        <h3 class="text-xl font-semibold text-white mb-6">Testes Detalhados</h3>
        
        <div class="space-y-4">
            <?php foreach ($testes as $teste): ?>
                <div class="flex items-center justify-between p-4 bg-admin-gray-800 rounded-lg">
                    <div>
                        <h4 class="font-medium text-white"><?= $teste['nome'] ?></h4>
                        <p class="text-sm text-admin-gray-400"><?= $teste['mensagem'] ?></p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm <?= $teste['status'] === 'ok' ? 'bg-admin-success/20 text-admin-success' : 'bg-admin-error/20 text-admin-error' ?>">
                        <?= $teste['status'] === 'ok' ? 'OK' : 'ERRO' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Ações -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <div class="admin-card rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Testar Editores</h3>
            <div class="space-y-3">
                <a href="editar_categoria.php" class="block w-full bg-admin-primary hover:bg-blue-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Testar Editor de Categorias
                </a>
                <a href="editar_banner.php" class="block w-full bg-admin-secondary hover:bg-purple-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                    <i class="fas fa-image mr-2"></i>
                    Testar Editor de Banners
                </a>
            </div>
        </div>

        <div class="admin-card rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Gerenciamento</h3>
            <div class="space-y-3">
                <a href="gerenciar_categorias_avancado.php" class="block w-full bg-admin-gray-700 hover:bg-admin-gray-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                    <i class="fas fa-tags mr-2"></i>
                    Gerenciar Categorias
                </a>
                <a href="gerenciar_banners_avancado.php" class="block w-full bg-admin-gray-700 hover:bg-admin-gray-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                    <i class="fas fa-images mr-2"></i>
                    Gerenciar Banners
                </a>
            </div>
        </div>
    </div>

    <!-- Instruções -->
    <?php if ($status_categorias['status'] !== 'ok' || $status_banners['status'] !== 'ok'): ?>
        <div class="admin-card rounded-xl p-6 mt-8 bg-admin-warning/10 border border-admin-warning/20">
            <h3 class="text-lg font-semibold text-admin-warning mb-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Ação Necessária
            </h3>
            <div class="space-y-3 text-admin-gray-300">
                <p>Para usar os editores avançados, você precisa atualizar o banco de dados:</p>
                <ol class="list-decimal list-inside space-y-2 ml-4">
                    <li>Execute o arquivo <code>update_database.sql</code> no seu banco de dados</li>
                    <li>Verifique se todas as colunas foram adicionadas</li>
                    <li>Teste novamente esta página</li>
                </ol>
                <div class="mt-4 p-4 bg-admin-gray-800 rounded-lg">
                    <p class="text-sm font-medium text-white mb-2">Comando SQL:</p>
                    <code class="text-admin-gray-300 text-sm">mysql -u usuario -p database < admin/update_database.sql</code>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="admin-card rounded-xl p-6 mt-8 bg-admin-success/10 border border-admin-success/20">
            <h3 class="text-lg font-semibold text-admin-success mb-4">
                <i class="fas fa-check-circle mr-2"></i>
                Tudo Pronto!
            </h3>
            <p class="text-admin-gray-300">
                Todas as funcionalidades estão configuradas corretamente. Você pode começar a usar os editores avançados de categorias e banners.
            </p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer_admin.php'; ?>

