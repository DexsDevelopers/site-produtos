<?php
// admin/test_file_storage.php - Teste do FileStorage
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Teste FileStorage</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#1a1a1a;color:#fff;}";
echo ".success{color:#0f0;}.error{color:#f00;}.info{color:#0ff;}</style></head><body>";

echo "<h1>Teste do FileStorage</h1>";

try {
    echo "<h2 class='info'>1. Carregando FileStorage...</h2>";
    require_once '../includes/file_storage.php';
    $fileStorage = new FileStorage();
    echo "<p class='success'>✅ FileStorage carregado com sucesso</p>";
    
    echo "<h2 class='info'>2. Testando getProdutos()...</h2>";
    $produtos = $fileStorage->getProdutos();
    echo "<p class='success'>✅ Produtos carregados: " . count($produtos) . "</p>";
    
    echo "<h2 class='info'>3. Testando getConfig()...</h2>";
    $config = $fileStorage->getConfig();
    echo "<p class='success'>✅ Config carregado</p>";
    echo "<pre>" . print_r($config, true) . "</pre>";
    
    echo "<h2 class='info'>4. Testando getCategorias()...</h2>";
    $categorias = $fileStorage->getCategorias();
    echo "<p class='success'>✅ Categorias carregadas: " . count($categorias) . "</p>";
    
    echo "<h2 class='info'>5. Verificando permissões...</h2>";
    $dataDir = __DIR__ . '/../data';
    if (is_dir($dataDir)) {
        echo "<p class='success'>✅ Diretório data existe</p>";
        echo "<p class='info'>Permissões: " . substr(sprintf('%o', fileperms($dataDir)), -4) . "</p>";
        echo "<p class='info'>Gravável: " . (is_writable($dataDir) ? 'Sim' : 'Não') . "</p>";
    } else {
        echo "<p class='error'>❌ Diretório data não existe</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p class='error'>Arquivo: " . $e->getFile() . "</p>";
    echo "<p class='error'>Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>

