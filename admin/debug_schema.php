<?php
// admin/debug_schema.php
require_once __DIR__ . '/../config.php';

echo "<h1>Database Schema</h1>";

try {
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "<h2>Table: $table</h2>";
        $columns = $pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' cellpadding='5'><tr><th>CID</th><th>Name</th><th>Type</th><th>NotNull</th><th>PK</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['cid']}</td>";
            echo "<td>{$col['name']}</td>";
            echo "<td>{$col['type']}</td>";
            echo "<td>{$col['notnull']}</td>";
            echo "<td>{$col['pk']}</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Show row count
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "<p>Rows: $count</p>";
    }
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}