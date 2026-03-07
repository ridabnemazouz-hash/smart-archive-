<?php
require_once 'includes/config.php';

try {
    // Clients table
    $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        user_id INT, 
        name VARCHAR(255) NOT NULL, 
        phone VARCHAR(20), 
        email VARCHAR(255), 
        total_debt DECIMAL(10,2) DEFAULT 0.00, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Clients table created successfully.\n";

    // Credits table
    $pdo->exec("CREATE TABLE IF NOT EXISTS credits (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        client_id INT, 
        user_id INT, 
        amount DECIMAL(10,2) NOT NULL, 
        type ENUM('gave', 'received') NOT NULL, 
        description TEXT, 
        date DATE, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Credits table created successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
