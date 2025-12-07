<?php
// clear_cache.php - Limpeza de Cache
require_once 'config_optimized.php';

header('Content-Type: application/json');

try {
    // Limpar cache principal
    $cache_cleared = clearCache();
    
    // Limpar cache de imagens
    $image_cache_dir = 'assets/cache/images/';
    if (is_dir($image_cache_dir)) {
        $files = glob($image_cache_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    // Limpar cache de performance
    $perf_cache_dir = 'cache/';
    if (is_dir($perf_cache_dir)) {
        $files = glob($perf_cache_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cache limpo com sucesso!',
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao limpar cache: ' . $e->getMessage()
    ]);
}
?>


