<?php
// sitemap.php - Gera sitemap dinâmico
require_once 'config.php';

// Define o cabeçalho para XML
header('Content-Type: application/xml; charset=utf-8');

// URL base do site
$base_url = 'https://' . $_SERVER['HTTP_HOST'];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Página inicial -->
    <url>
        <loc><?= $base_url ?>/index.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- Página de busca -->
    <url>
        <loc><?= $base_url ?>/busca.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    
    <!-- Página do carrinho -->
    <url>
        <loc><?= $base_url ?>/carrinho.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    
    <!-- Páginas de login e registro -->
    <url>
        <loc><?= $base_url ?>/login.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    
    <url>
        <loc><?= $base_url ?>/registrar.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    
    <?php
    try {
        // Busca todas as categorias
        $categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categorias as $categoria) {
            echo "\n    <!-- Categoria: " . htmlspecialchars($categoria['nome']) . " -->\n";
            echo "    <url>\n";
            echo "        <loc>" . $base_url . "/categoria.php?id=" . $categoria['id'] . "</loc>\n";
            echo "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
            echo "        <changefreq>weekly</changefreq>\n";
            echo "        <priority>0.7</priority>\n";
            echo "    </url>\n";
        }
        
        // Busca todos os produtos
        $produtos = $pdo->query("SELECT * FROM produtos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($produtos as $produto) {
            echo "\n    <!-- Produto: " . htmlspecialchars($produto['nome']) . " -->\n";
            echo "    <url>\n";
            echo "        <loc>" . $base_url . "/produto.php?id=" . $produto['id'] . "</loc>\n";
            echo "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
            echo "        <changefreq>weekly</changefreq>\n";
            echo "        <priority>0.8</priority>\n";
            echo "    </url>\n";
        }
        
    } catch (PDOException $e) {
        error_log("Erro ao gerar sitemap: " . $e->getMessage());
    }
    ?>
</urlset>
