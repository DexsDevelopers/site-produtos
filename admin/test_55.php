<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "Start.<br>";
require_once "secure.php";
echo "Past secure.<br>";
try {
    $stmt_cat = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
    $categorias = $stmt_cat ? $stmt_cat->fetchAll(PDO::FETCH_ASSOC) : array();
    echo "Past categories.<br>";
}
catch (Exception $e) {
    echo "Categories error: " . $e->getMessage() . "<br>";
}

require_once "templates/header_admin.php";
echo "Past header.<br>";
?>
