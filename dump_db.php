<?php
// DB Config
$host = 'localhost'; $db = 'smartarchive'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "USERS:\n";
    $stmt = $pdo->query("SELECT id, name, email FROM users");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}\n";
    }
    
    echo "\nDOCUMENTS:\n";
    $stmt = $pdo->query("SELECT id, title, user_id, category FROM documents");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Title: {$row['title']}, UserID: {$row['user_id']}, Cat: {$row['category']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
