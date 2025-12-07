<?php
// test_cache_performance.php - Teste de Performance do Cache
require_once 'config_optimized.php';

header('Content-Type: application/json');

$start_time = microtime(true);

try {
    // Teste de escrita no cache
    $test_data = [
        'timestamp' => time(),
        'data' => 'Teste de performance do cache',
        'random' => rand(1, 1000)
    ];
    
    $cache->set('performance_test', $test_data, 60);
    
    // Teste de leitura do cache
    $cached_data = $cache->get('performance_test');
    
    // Teste de cache com callback
    $cached_callback = getCachedData('test_callback', function() {
        return [
            'generated_at' => time(),
            'random_data' => str_repeat('test', 100)
        ];
    }, 60);
    
    // Estatísticas do cache
    $stats = $cache->getStats();
    
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time) * 1000;
    
    echo json_encode([
        'success' => true,
        'time' => round($execution_time, 2),
        'files' => $stats['total_files'],
        'size' => round($stats['total_size_mb'], 2),
        'cache_hit' => $cached_data !== null,
        'callback_test' => isset($cached_callback['generated_at'])
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>


