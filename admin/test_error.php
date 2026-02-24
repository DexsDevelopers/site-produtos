<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing syntax error...";

// require_once "fail.php";
echo "1";
require_once "secure.php";
echo "2";
require_once "templates/header_admin.php";
echo "3";
$page_title = "Meus Produtos";

$search = isset($_GET["search"]) ? $_GET["search"] : "";
$categoria_id = isset($_GET["categoria_id"]) ? $_GET["categoria_id"] : "";
$ordem = isset($_GET["ordem"]) ? $_GET["ordem"] : "recente";

$stmt_cat = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
$categorias = $stmt_cat ? $stmt_cat->fetchAll(PDO::FETCH_ASSOC) : array();
echo "4";
?>
