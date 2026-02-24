<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "secure.php";
$page_title = "Teste de Carga";
require_once "templates/header_admin.php";

echo "<div class='p-10 bg-white text-black'><h1>OlÃ¡! Se vocÃª estÃ¡ vendo isso, o PHP e o Header estÃ£o funcionando.</h1>";
echo "<p>Agora vamos testar o banco de dados...</p>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM produtos");
    $count = $stmt->fetchColumn();
    echo "<p>ConexÃ£o com banco de dados OK! Total de produtos: " . $count . "</p>";
}
catch (Exception $e) {
    echo "<p style='color:red'>Erro no banco de dados: " . $e->getMessage() . "</p>";
}

echo "</div>";

require_once "templates/footer_admin.php";
?>
