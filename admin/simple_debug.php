<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "DEBUG START<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current File: " . __FILE__ . "<br>";
echo "Trying to load config...<br>";
@include_once "../config.php";
echo "After include config.<br>";
if (isset($pdo)) {
    echo "PDO exists.<br>";
}
else {
    echo "PDO MISSING (or error in config.php)!<br>";
}
echo "DEBUG END";
?>
