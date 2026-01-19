<?php
// Teste simplificado do index
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Iniciando teste...<br>";

// Testa se o config.php existe
if (!file_exists('config.php')) {
    die("❌ Arquivo config.php não encontrado!<br>");
}
echo "✅ Arquivo config.php encontrado<br>";

// Tenta incluir o config
try {
    echo "2. Tentando incluir config.php...<br>";
    require_once 'config.php';
    echo "✅ Config.php incluído com sucesso<br>";
} catch (Exception $e) {
    die("❌ Erro ao incluir config.php: " . $e->getMessage() . "<br>");
}

// Verifica se a conexão PDO existe
if (!isset($pdo)) {
    die("❌ Variável \$pdo não foi criada!<br>");
}
echo "✅ Conexão PDO existe<br>";

// Testa queries
try {
    echo "3. Testando queries...<br>";
    
    $banners_principais = $pdo->query("SELECT * FROM banners WHERE tipo = 'principal' AND ativo = 1 ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Query banners_principais OK - " . count($banners_principais) . " registros<br>";
    
    $banners_categorias = $pdo->query("SELECT * FROM banners WHERE tipo = 'categoria' AND ativo = 1 ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Query banners_categorias OK - " . count($banners_categorias) . " registros<br>";
    
    $categorias = $pdo->query("SELECT * FROM categorias ORDER BY ordem ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Query categorias OK - " . count($categorias) . " registros<br>";
    
    $produtos_destaque = $pdo->query("SELECT id, nome, preco, imagem, descricao_curta FROM produtos ORDER BY id DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Query produtos_destaque OK - " . count($produtos_destaque) . " registros<br>";
    
    echo "<br>✅✅✅ TODOS OS TESTES PASSARAM! ✅✅✅<br>";
    echo "<br>Se este arquivo funciona mas o index.php não, o problema está no template (header.php ou HTML).<br>";
    
} catch (Exception $e) {
    echo "❌ Erro nas queries: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>

