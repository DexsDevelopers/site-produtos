<?php
// admin_cache.php - Gerenciamento de Cache
session_start();
require_once 'config.php';
require_once 'includes/advanced_cache.php';

// Verificar se é admin (assumindo que admin tem user_id = 1 ou verificar de outra forma)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar se é admin - você pode ajustar esta lógica conforme sua estrutura
$is_admin = false;
try {
    $stmt = $pdo->prepare("SELECT tipo FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $is_admin = ($user && $user['tipo'] === 'admin');
} catch (Exception $e) {
    // Se não houver coluna tipo, assumir que user_id = 1 é admin
    $is_admin = ($_SESSION['user_id'] == 1);
}

if (!$is_admin) {
    header('Location: index.php?msg=access_denied');
    exit();
}

$cache = new AdvancedCache('cache/', 3600, true);
$dbCache = new DatabaseCache($pdo, $cache);
$pageCache = new PageCache($cache, true);
$imageCache = new ImageCache('cache/images/', 800, 600, 85);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'clear_all':
            $deleted = $cache->clear();
            $message = "Cache limpo: $deleted arquivos removidos";
            break;
            
        case 'clean_expired':
            $cleaned = $cache->cleanExpired();
            $message = "Cache expirado limpo: $cleaned arquivos removidos";
            break;
            
        case 'clear_images':
            $imageFiles = glob('cache/images/*.jpg');
            $deleted = 0;
            foreach ($imageFiles as $file) {
                if (unlink($file)) $deleted++;
            }
            $message = "Cache de imagens limpo: $deleted arquivos removidos";
            break;
            
        case 'optimize_images':
            $images = glob('assets/uploads/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            $optimized = 0;
            foreach ($images as $image) {
                $imageCache->getOptimizedImage($image);
                $optimized++;
            }
            $message = "Imagens otimizadas: $optimized arquivos processados";
            break;
    }
}

// Obter estatísticas
$stats = $cache->getStats();
$imageStats = [
    'total_files' => count(glob('cache/images/*.jpg')),
    'total_size' => array_sum(array_map('filesize', glob('cache/images/*.jpg'))),
    'total_size_mb' => round(array_sum(array_map('filesize', glob('cache/images/*.jpg'))) / 1024 / 1024, 2)
];

$page_title = 'Gerenciamento de Cache';
require_once 'templates/header.php';
?>

<style>
.cache-stats {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #3B82F6;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #94A3B8;
    font-size: 0.875rem;
}

.cache-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.action-btn {
    background: rgba(30, 41, 59, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    padding: 1rem;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.action-btn:hover {
    background: rgba(59, 130, 246, 0.2);
    border-color: #3B82F6;
    transform: translateY(-2px);
}

.action-btn.danger:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: #EF4444;
}

.cache-files {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 1.5rem;
}

.file-item {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 0.75rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.file-item:last-child {
    border-bottom: none;
}

.file-name {
    color: white;
    font-weight: 500;
}

.file-size {
    color: #94A3B8;
    font-size: 0.875rem;
}

.file-age {
    color: #6B7280;
    font-size: 0.75rem;
}
</style>

<div class="w-full max-w-7xl mx-auto py-24 px-4">
    <div class="pt-16">
        <h1 class="text-3xl font-bold text-white mb-8">Gerenciamento de Cache</h1>
        
        <?php if (isset($message)): ?>
            <div class="bg-green-500/20 text-green-300 p-4 rounded-lg mb-6">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Estatísticas do Cache -->
        <div class="cache-stats">
            <h2 class="text-xl font-bold text-white mb-6">Estatísticas do Cache</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_files'] ?></div>
                    <div class="stat-label">Total de Arquivos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_size_mb'] ?> MB</div>
                    <div class="stat-label">Tamanho Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['active_files'] ?></div>
                    <div class="stat-label">Arquivos Ativos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['expired_files'] ?></div>
                    <div class="stat-label">Arquivos Expirados</div>
                </div>
            </div>
        </div>
        
        <!-- Estatísticas de Imagens -->
        <div class="cache-stats">
            <h2 class="text-xl font-bold text-white mb-6">Cache de Imagens</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="stat-card">
                    <div class="stat-number"><?= $imageStats['total_files'] ?></div>
                    <div class="stat-label">Imagens em Cache</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $imageStats['total_size_mb'] ?> MB</div>
                    <div class="stat-label">Tamanho das Imagens</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">85%</div>
                    <div class="stat-label">Qualidade de Compressão</div>
                </div>
            </div>
        </div>
        
        <!-- Ações do Cache -->
        <div class="cache-actions">
            <form method="POST" class="action-btn" onsubmit="return confirm('Limpar todo o cache?')">
                <input type="hidden" name="action" value="clear_all">
                <i class="fas fa-trash text-red-400 text-2xl mb-2"></i>
                <div class="font-semibold">Limpar Todo Cache</div>
                <div class="text-sm text-gray-400">Remove todos os arquivos</div>
                <button type="submit" class="mt-2 w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded">
                    Executar
                </button>
            </form>
            
            <form method="POST" class="action-btn" onsubmit="return confirm('Limpar apenas cache expirado?')">
                <input type="hidden" name="action" value="clean_expired">
                <i class="fas fa-clock text-yellow-400 text-2xl mb-2"></i>
                <div class="font-semibold">Limpar Expirado</div>
                <div class="text-sm text-gray-400">Remove apenas arquivos expirados</div>
                <button type="submit" class="mt-2 w-full bg-yellow-600 hover:bg-yellow-700 text-white py-2 rounded">
                    Executar
                </button>
            </form>
            
            <form method="POST" class="action-btn" onsubmit="return confirm('Limpar cache de imagens?')">
                <input type="hidden" name="action" value="clear_images">
                <i class="fas fa-image text-blue-400 text-2xl mb-2"></i>
                <div class="font-semibold">Limpar Imagens</div>
                <div class="text-sm text-gray-400">Remove cache de imagens</div>
                <button type="submit" class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">
                    Executar
                </button>
            </form>
            
            <form method="POST" class="action-btn" onsubmit="return confirm('Otimizar todas as imagens?')">
                <input type="hidden" name="action" value="optimize_images">
                <i class="fas fa-compress text-green-400 text-2xl mb-2"></i>
                <div class="font-semibold">Otimizar Imagens</div>
                <div class="text-sm text-gray-400">Comprime e otimiza imagens</div>
                <button type="submit" class="mt-2 w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">
                    Executar
                </button>
            </form>
        </div>
        
        <!-- Arquivos do Cache -->
        <div class="cache-files">
            <h2 class="text-xl font-bold text-white mb-6">Arquivos em Cache</h2>
            <div class="space-y-2">
                <?php
                $cacheFiles = glob('cache/*.cache');
                $imageFiles = glob('cache/images/*.jpg');
                $allFiles = array_merge($cacheFiles, $imageFiles);
                
                if (empty($allFiles)): ?>
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-folder-open text-4xl mb-4"></i>
                        <p>Nenhum arquivo em cache encontrado</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($allFiles as $file): ?>
                        <div class="file-item">
                            <div class="flex-1">
                                <div class="file-name"><?= basename($file) ?></div>
                                <div class="file-age">
                                    Modificado: <?= date('d/m/Y H:i:s', filemtime($file)) ?>
                                </div>
                            </div>
                            <div class="file-size">
                                <?= round(filesize($file) / 1024, 2) ?> KB
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Configurações do Cache -->
        <div class="cache-files mt-8">
            <h2 class="text-xl font-bold text-white mb-6">Configurações do Cache</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-white mb-4">Cache Geral</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li>• TTL Padrão: 3600 segundos (1 hora)</li>
                        <li>• Compressão: Habilitada</li>
                        <li>• Diretório: cache/</li>
                        <li>• Bloqueio de arquivo: Habilitado</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-4">Cache de Imagens</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li>• Largura máxima: 800px</li>
                        <li>• Altura máxima: 600px</li>
                        <li>• Qualidade JPEG: 85%</li>
                        <li>• Diretório: cache/images/</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh das estatísticas a cada 30 segundos
setInterval(function() {
    location.reload();
}, 30000);

// Confirmar ações destrutivas
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const action = this.querySelector('input[name="action"]').value;
        if (action === 'clear_all' || action === 'clear_images') {
            if (!confirm('Esta ação não pode ser desfeita. Continuar?')) {
                e.preventDefault();
            }
        }
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>
