<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbname = "u853242961_lojahelmer";
$user = "u853242961_user2";
$password = "Lucastav8012@";
$charset = "utf8mb4";

echo "Testing PDO Connection...<br>";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    );
    $pdo = new PDO($dsn, $user, $password, $options);
    echo "Connection Successful!<br>";

    $stmt = $pdo->query("SELECT COUNT(*) FROM produtos");
    echo "Total products: " . $stmt->fetchColumn() . "<br>";


}
catch (Exception $e) {
    echo "Connection Failed: " . $e->getMessage() . "<br>";
}
?>
