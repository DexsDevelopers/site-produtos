<?php
// google_merchant_feed.php - Feed XML dinâmico para Google Merchant e Facebook Ads
require_once 'config.php';

header('Content-Type: application/xml; charset=utf-8');

$base_url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
<channel>
    <title>MACARIO BRAZIL - Feed de Produtos</title>
    <link><?= $base_url ?></link>
    <description>Streetwear, Sneakers e Eletrônicos Premium</description>
    <?php
    try {
        $stmt = $pdo->query("
            SELECT p.*, c.nome as categoria_nome 
            FROM produtos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            ORDER BY p.id DESC
        ");
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($produtos as $p) {
            $link = $base_url . "/produto.php?id=" . $p['id'];
            $imagem = $base_url . "/" . ltrim($p['imagem'], '/');
            $desc = !empty($p['descricao_curta']) ? $p['descricao_curta'] : $p['nome'];
            $desc_clean = htmlspecialchars(strip_tags($desc), ENT_XML1, 'UTF-8');
            $nome_clean = htmlspecialchars($p['nome'], ENT_XML1, 'UTF-8');
            $cat_clean = !empty($p['categoria_nome']) ? htmlspecialchars($p['categoria_nome'], ENT_XML1, 'UTF-8') : 'Geral';
            
            // Tratamento do preço
            $preco = number_format($p['preco'], 2, '.', '');
            ?>
    <item>
        <g:id><?= $p['id'] ?></g:id>
        <g:title><?= $nome_clean ?></g:title>
        <g:description><?= $desc_clean ?></g:description>
        <g:link><?= $link ?></g:link>
        <g:image_link><?= $imagem ?></g:image_link>
        <g:condition>new</g:condition>
        <g:availability>in stock</g:availability>
        <g:price><?= $preco ?> BRL</g:price>
        <g:brand>MACARIO BRAZIL</g:brand>
        <g:google_product_category>166</g:google_product_category>
        <g:product_type><?= $cat_clean ?></g:product_type>
    </item>
            <?php
        }
    } catch (Exception $e) {
        error_log("Erro no Feed: " . $e->getMessage());
    }
    ?>
</channel>
</rss>
