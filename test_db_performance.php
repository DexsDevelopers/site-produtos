<?php
// test_db_performance.php - Teste de Performance do Banco de Dados
require_once 'config_optimized.php';

header('Content-Type: application/json');

$start_time = microtime(true);
$queries = 0;

try {
    // Teste 1: Contagem de registros
    $banners = $pdo->query("SELECT COUNT(*) as total FROM banners WHERE ativo = 1")->fetch();
    $queries++;
    
    $produtos = $pdo->query("SELECT COUNT(*) as total FROM produtos")->fetch();
    $queries++;
    
    $categorias = $pdo->query("SELECT COUNT(*) as total FROM categorias")->fetch();
    $queries++;
    
    // Teste 2: Consulta complexa com JOIN
    $produtos_categoria = $pdo->query("
        SELECT p.id, p.nome, p.preco, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        ORDER BY p.id DESC 
        LIMIT 10
    ")->fetchAll();
    $queries++;
    
    // Teste 3: Consulta com cache
    $banners_cached = getCachedData('test_banners', function() use ($pdo) {
        return $pdo->query("SELECT * FROM banners WHERE ativo = 1 LIMIT 5")->fetchAll();
    }, 60);
    $queries++;
    
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time) * 1000; // em milissegundos
    
    echo json_encode([
        'success' => true,
        'queries' => $queries,
        'time' => round($execution_time, 2),
        'banners' => $banners['total'],
        'produtos' => $produtos['total'],
        'categorias' => $categorias['total'],
        'produtos_categoria' => count($produtos_categoria)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>


