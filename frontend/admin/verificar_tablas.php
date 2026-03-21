<?php
try {
    $db = new PDO('sqlite:../../database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
    
    echo "<h2>Tablas en la base de datos:</h2>";
    foreach ($tables as $table) {
        echo "- " . $table['name'] . "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>