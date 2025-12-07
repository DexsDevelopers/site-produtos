<?php
// test_image_performance.php - Teste de Performance de Imagens
require_once 'config_optimized.php';

header('Content-Type: application/json');

$start_time = microtime(true);
$images_processed = 0;

try {
    // Buscar imagens de produtos para teste
    $produtos = $pdo->query("SELECT imagem FROM produtos WHERE imagem IS NOT NULL AND imagem != '' LIMIT 5")->fetchAll();
    
    foreach ($produtos as $produto) {
        if (file_exists($produto['imagem'])) {
            // Teste de otimização
            $optimized = optimizeImage($produto['imagem'], 400, 400, 80);
            $images_processed++;
        }
    }
    
    // Teste de geração de imagens responsivas
    if (!empty($produtos) && file_exists($produtos[0]['imagem'])) {
        $responsive_images = getResponsiveImages($produtos[0]['imagem']);
        $images_processed += count($responsive_images);
    }
    
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time) * 1000;
    
    echo json_encode([
        'success' => true,
        'time' => round($execution_time, 2),
        'images' => $images_processed,
        'original_images' => count($produtos)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>


